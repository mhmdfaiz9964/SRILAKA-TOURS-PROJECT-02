@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h4 class="fw-bold text-gray-800">Create New Role</h4>
        <p class="text-muted small">Define a new role and assign permissions</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form action="{{ route('roles.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Role Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-lg rounded-3 @error('name') is-invalid @enderror" placeholder="e.g. Sales Manager" value="{{ old('name') }}">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <h5 class="fw-bold text-primary mb-3">Assign Permissions</h5>
                        
                        <div class="row g-4">
                            @foreach($permissionGroups as $groupName => $permissions)
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 border rounded-3 shadow-sm permission-card">
                                    <div class="card-header bg-light border-bottom py-2 d-flex justify-content-between align-items-center">
                                        <span class="fw-bold small text-uppercase">{{ $groupName }}</span>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input select-all-group" type="checkbox" role="switch" data-group="{{ Str::slug($groupName) }}">
                                        </div>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="d-flex flex-column gap-2">
                                            @foreach($permissions as $permission)
                                            <div class="form-check">
                                                <input class="form-check-input permission-item group-{{ Str::slug($groupName) }}" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="perm_{{ $permission->id }}">
                                                <label class="form-check-label small" for="perm_{{ $permission->id }}">
                                                    {{ $permission->name }}
                                                </label>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-5">
                            <a href="{{ route('roles.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5" style="background: #6366f1; border: none;">Create Role</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Select All Handler
        document.querySelectorAll('.select-all-group').forEach(function(toggle) {
            toggle.addEventListener('change', function() {
                const group = this.dataset.group;
                const isChecked = this.checked;
                document.querySelectorAll('.group-' + group).forEach(function(checkbox) {
                    checkbox.checked = isChecked;
                });
            });
        });

        // If all items checked, check the toggle
        document.querySelectorAll('.permission-item').forEach(function(item) {
            item.addEventListener('change', function() {
                // Find group from class
                let groupClass = Array.from(this.classList).find(c => c.startsWith('group-'));
                if(groupClass) {
                    let group = groupClass.replace('group-', '');
                    let allChecked = true;
                    document.querySelectorAll('.' + groupClass).forEach(i => {
                        if(!i.checked) allChecked = false;
                    });
                    
                    let toggle = document.querySelector('.select-all-group[data-group="' + group + '"]');
                    if(toggle) toggle.checked = allChecked;
                }
            });
        });
    });
</script>
@endsection
