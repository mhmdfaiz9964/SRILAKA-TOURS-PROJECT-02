<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Cheque;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChequeController extends Controller
{
    public function index(Request $request)
    {
        $query = Cheque::with(['bank'])->where('payment_status', '!=', 'paid');
        $this->applyFilters($query, $request);

        $cheques = $query->latest()->paginate(10);
        $banks = Bank::all();
        $payers = Cheque::distinct()->pluck('payer_name');
        $third_parties = Cheque::distinct()->whereNotNull('payee_name')->pluck('payee_name');

        return view('cheques.index', compact('cheques', 'banks', 'payers', 'third_parties'));
    }

    public function paymentCheques(Request $request)
    {
        $query = Cheque::with(['bank'])->whereHas('payments')->where('payment_status', '!=', 'paid');
        $this->applyFilters($query, $request);

        $cheques = $query->latest()->paginate(10);
        $banks = Bank::all();
        $payers = Cheque::distinct()->pluck('payer_name');
        $third_parties = Cheque::distinct()->whereNotNull('payee_name')->pluck('payee_name');

        return view('cheques.index', compact('cheques', 'banks', 'payers', 'third_parties'))->with('page_title', 'Payment Cheques');
    }

    public function paidCheques(Request $request)
    {
        $query = Cheque::with(['bank'])->where('payment_status', 'paid');
        $this->applyFilters($query, $request);

        $cheques = $query->latest()->paginate(10);
        $banks = Bank::all();
        $payers = Cheque::distinct()->pluck('payer_name');
        $third_parties = Cheque::distinct()->whereNotNull('payee_name')->pluck('payee_name');

        return view('cheques.index', compact('cheques', 'banks', 'payers', 'third_parties'))->with('page_title', 'Paid Cheques');
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
            $query->where('cheque_number', 'like', "%{$request->search}%");
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
            'cheque_number' => 'required',
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
        $cheque->load(['payments.bank', 'bank']);
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
            'cheque_number' => 'required',
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
}
