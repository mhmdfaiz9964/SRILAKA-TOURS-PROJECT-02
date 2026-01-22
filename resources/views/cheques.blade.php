@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="content-header d-flex justify-content-between align-items-center">
        <h1 class="content-title">Cheques</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fa-solid fa-file-export me-2"></i> Export
            </button>
            <button class="btn btn-primary rounded-pill px-4">
                <i class="fa-solid fa-plus me-2"></i> New Cheque
            </button>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body">
                    <p class="small opacity-75 mb-1">Total Cheques</p>
                    <h3 class="fw-bold mb-0">LKR 1,200,000.00</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success text-white">
                <div class="card-body">
                    <p class="small opacity-75 mb-1">Cleared</p>
                    <h3 class="fw-bold mb-0">LKR 850,000.00</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-warning text-white">
                <div class="card-body">
                    <p class="small opacity-75 mb-1">Pending</p>
                    <h3 class="fw-bold mb-0">LKR 350,000.00</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Cheque No</th>
                            <th>Date</th>
                            <th>Bank</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-4 fw-bold">CHQ-98765</td>
                            <td>2026-01-25</td>
                            <td>Commercial Bank</td>
                            <td class="fw-bold">LKR 150,000.00</td>
                            <td><span class="badge bg-warning text-dark">Pending</span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light text-primary me-2"><i class="fa-solid fa-check"></i></button>
                                <button class="btn btn-sm btn-light text-danger"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
