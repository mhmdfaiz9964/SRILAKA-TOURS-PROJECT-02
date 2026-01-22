@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="content-header">
        <h1 class="content-title">Add New Cheque</h1>
    </div>

    <div class="row">
        <div class="col-md-10">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('cheques.store') }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Cheque Number</label>
                                <input type="text" name="cheque_number" class="form-control @error('cheque_number') is-invalid @enderror" value="{{ old('cheque_number') }}" required>
                                @error('cheque_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Cheque Date</label>
                                <input type="date" name="cheque_date" class="form-control @error('cheque_date') is-invalid @enderror" value="{{ old('cheque_date') }}" required>
                                @error('cheque_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Select Bank</label>
                                <select name="bank_id" class="form-select @error('bank_id') is-invalid @enderror" required>
                                    <option value="">Choose Bank</option>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                    @endforeach
                                </select>
                                @error('bank_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Reason</label>
                                <div class="input-group">
                                    <select name="cheque_reason_id" id="reason_select" class="form-select @error('cheque_reason_id') is-invalid @enderror" required>
                                        <option value="">Choose Reason</option>
                                        @foreach($reasons as $reason)
                                            <option value="{{ $reason->id }}">{{ $reason->reason }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#addReasonModal">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                                @error('cheque_reason_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Amount (LKR)</label>
                                <input type="number" step="0.01" name="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" required>
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Payer Name</label>
                                <input type="text" name="payer_name" class="form-control @error('payer_name') is-invalid @enderror" value="{{ old('payer_name') }}" required>
                                @error('payer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Notes</label>
                                <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary rounded-pill px-4">Create Cheque</button>
                            <a href="{{ route('cheques.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Reason Modal -->
<div class="modal fade" id="addReasonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Add New Reason</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Reason Name</label>
                    <input type="text" id="new_reason_input" class="form-control rounded-3" placeholder="e.g. Booking Deposit">
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" onclick="saveReason()">Save Reason</button>
            </div>
        </div>
    </div>
</div>

<script>
    function saveReason() {
        const reason = document.getElementById('new_reason_input').value;
        if(!reason) return;

        fetch("{{ route('cheque-reasons.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('reason_select');
            const option = new Option(data.reason, data.id);
            select.add(option);
            select.value = data.id;
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('addReasonModal'));
            modal.hide();
            document.getElementById('new_reason_input').value = '';
            
            Toast.fire({
                icon: 'success',
                title: 'New reason added'
            });
        })
        .catch(error => {
            Toast.fire({
                icon: 'error',
                title: 'Failed to add reason'
            });
        });
    }
</script>
@endsection
