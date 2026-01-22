@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="content-header d-flex justify-content-between align-items-center">
        <h1 class="content-title">Banks</h1>
        <button class="btn btn-primary rounded-pill px-4">
            <i class="fa-solid fa-plus me-2"></i> Add Bank
        </button>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Bank Name</th>
                            <th>Account No</th>
                            <th>Branch</th>
                            <th>Balance</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-box rounded-3" style="width: 40px; height: 40px; background: #fff0f0; color: #ff4d4d; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa-solid fa-building-columns"></i>
                                    </div>
                                    <p class="mb-0 fw-bold">Commercial Bank</p>
                                </div>
                            </td>
                            <td>1234567890</td>
                            <td>Colombo Main</td>
                            <td class="fw-bold text-success">LKR 450,000.00</td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light text-primary me-2"><i class="fa-solid fa-edit"></i></button>
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
