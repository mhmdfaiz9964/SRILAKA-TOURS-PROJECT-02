<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Cheque;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ChequesExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Reminder;

class ChequeController extends Controller
{
    public function index(Request $request)
    {
        $query = Cheque::with(['bank'])->withSum('payments', 'amount')->where('payment_status', '!=', 'paid');
        $this->applyFilters($query, $request);
        $this->applySorting($query, $request);

        $cheques = $query->paginate(10)->withQueryString();
        $total_balance = $query->get()->sum(fn($c) => $c->amount - ($c->payments_sum_amount ?? 0));
        $banks = Bank::all();
        $payers = Cheque::distinct()->pluck('payer_name');
        $third_parties = Cheque::distinct()->whereNotNull('payee_name')->pluck('payee_name');

        $page_title = 'RTN Cheque';
        return view('cheques.index', compact('cheques', 'banks', 'payers', 'third_parties', 'total_balance', 'page_title'));
    }

    public function paymentCheques(Request $request)
    {
        $query = Payment::with(['cheque.bank'])->where('payment_method', 'cheque');

        // Apply filters if any
        if ($request->search) {
            $query->whereHas('cheque', function($q) use ($request) {
                $q->where('payer_name', 'like', "%{$request->search}%")
                  ->orWhere('cheque_number', 'like', "%{$request->search}%");
            });
        }

        $payments = $query->latest('payment_date')->paginate(10)->withQueryString();
        $total_balance = $query->sum('amount');
        
        $banks = Bank::all();
        $payers = Cheque::distinct()->pluck('payer_name');
        $third_parties = Cheque::distinct()->whereNotNull('payee_name')->pluck('payee_name');

        return view('cheques.index', [
            'cheques' => $payments,
            'banks' => $banks,
            'payers' => $payers,
            'third_parties' => $third_parties,
            'total_balance' => $total_balance,
            'page_title' => 'Payment Cheques'
        ]);
    }

    public function paidCheques(Request $request)
    {
        $query = Cheque::with(['bank'])->withSum('payments', 'amount')->where('payment_status', 'paid');
        $this->applyFilters($query, $request);
        $this->applySorting($query, $request);

        $cheques = $query->paginate(10)->withQueryString();
        $total_balance = $query->get()->sum(fn($c) => $c->amount - ($c->payments_sum_amount ?? 0));
        $banks = Bank::all();
        $payers = Cheque::distinct()->pluck('payer_name');
        $third_parties = Cheque::distinct()->whereNotNull('payee_name')->pluck('payee_name');

        return view('cheques.index', compact('cheques', 'banks', 'payers', 'third_parties', 'total_balance'))->with('page_title', 'Paid Cheques');
    }

    public function export(Request $request)
    {
        $format = $request->get('format', 'excel');
        $query = Cheque::with(['bank'])->withSum('payments', 'amount');
        
        // Context-aware export based on the current view
        if ($request->has('view')) {
            if ($request->view == 'payment') {
                $query->whereHas('payments')->where('payment_status', '!=', 'paid');
            } elseif ($request->view == 'paid') {
                $query->where('payment_status', 'paid');
            } else {
                $query->where('payment_status', '!=', 'paid');
            }
        }

        $this->applyFilters($query, $request);
        $this->applySorting($query, $request);
        
        $cheques = $query->get();

        if ($format == 'pdf') {
            $pdf = Pdf::loadView('cheques.pdf', compact('cheques'));
            return $pdf->download('cheques_report_' . now()->format('YmdHis') . '.pdf');
        }

        return Excel::download(new ChequesExport($cheques), 'cheques_export_' . now()->format('YmdHis') . '.xlsx');
    }

    private function applyFilters($query, $request)
    {
        if ($request->bank_id) {
            $query->where('bank_id', $request->bank_id);
        }
        if ($request->payer_name) {
            $query->where('payer_name', $request->payer_name);
        }
        if ($request->third_party) {
            $query->where('payee_name', $request->third_party);
        }
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('cheque_date', [$request->start_date, $request->end_date]);
        }
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('cheque_number', 'like', "%{$request->search}%")
                  ->orWhere('payer_name', 'like', "%{$request->search}%")
                  ->orWhere('payee_name', 'like', "%{$request->search}%");
            });
        }
    }

    private function applySorting($query, $request)
    {
        $sort = $request->get('sort', 'latest');
        
        switch ($sort) {
            case 'oldest':
                $query->orderBy('cheque_date', 'asc');
                break;
            case 'amount_high':
                $query->orderBy('amount', 'desc');
                break;
            case 'amount_low':
                $query->orderBy('amount', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('payer_name', 'asc');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }
    }

    public function create()
    {
        $banks = Bank::all();
        return view('cheques.create', compact('banks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cheque_number' => 'required|digits:6',
            'cheque_date' => 'required|date',
            'bank_id' => 'required|exists:banks,id',
            'amount' => 'required|numeric',
            'payer_name' => 'required',
        ]);

        Cheque::create($request->all());

        return redirect()->route('cheques.index')->with('success', 'Cheque created successfully');
    }

    public function show(Cheque $cheque)
    {
        $cheque->load(['payments.bank', 'bank', 'reminders']);
        $totalPaid = $cheque->payments->sum('amount');
        $banks = Bank::all();
        return view('cheques.show', compact('cheque', 'totalPaid', 'banks'));
    }

    public function edit(Cheque $cheque)
    {
        $banks = Bank::all();
        return view('cheques.edit', compact('cheque', 'banks'));
    }

    public function update(Request $request, Cheque $cheque)
    {
        $request->validate([
            'cheque_number' => 'required|digits:6',
            'cheque_date' => 'required|date',
            'bank_id' => 'required|exists:banks,id',
            'amount' => 'required|numeric',
            'payer_name' => 'required',
        ]);

        $cheque->update($request->all());

        return redirect()->route('cheques.index')->with('success', 'Cheque updated successfully');
    }

    public function updateThirdPartyStatus(Request $request, Cheque $cheque)
    {
        $request->validate([
            'third_party_payment_status' => 'required|in:paid,pending',
            'third_party_notes' => 'nullable'
        ]);

        $cheque->update([
            'third_party_payment_status' => $request->third_party_payment_status,
            'third_party_notes' => $request->third_party_notes
        ]);

        return back()->with('success', '3rd party status updated');
    }

    public function addPayment(Request $request, Cheque $cheque)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:bank_transfer,cash,cheque',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        $data = $request->except('document');
        $data['cheque_id'] = $cheque->id;

        if ($request->hasFile('document')) {
            $data['document'] = $request->file('document')->store('payments', 'public');
        }

        Payment::create($data);

        $totalPaid = $cheque->payments()->sum('amount');
        if ($totalPaid >= $cheque->amount) {
            $cheque->update(['payment_status' => 'paid']);
        } elseif ($totalPaid > 0) {
            $cheque->update(['payment_status' => 'partial paid']);
        }

        return back()->with('success', 'Payment added successfully');
    }

    public function destroy(Cheque $cheque)
    {
        $cheque->delete();
        return redirect()->route('cheques.index')->with('success', 'Cheque deleted successfully');
    }

    public function storeReminder(Request $request, Cheque $cheque)
    {
        $request->validate([
            'reminder_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        Reminder::create([
            'cheque_id' => $cheque->id,
            'payer_name' => $cheque->payer_name,
            'reminder_date' => $request->reminder_date,
            'notes' => $request->notes
        ]);

        return back()->with('success', 'Reminder set successfully');
    }

    public function completeReminder(Reminder $reminder)
    {
        $reminder->update(['is_read' => true]);
        return back()->with('success', 'Reminder marked as completed');
    }
}
