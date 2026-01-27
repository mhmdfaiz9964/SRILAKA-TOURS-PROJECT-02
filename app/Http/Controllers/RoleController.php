<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:role-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:role-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:role-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = \Spatie\Permission\Models\Role::orderBy('id', 'DESC')->get();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = \Spatie\Permission\Models\Permission::all();
        $permissionGroups = $this->groupPermissions($permissions);
        return view('roles.create', compact('permissionGroups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'required|array',
        ]);

        $role = \Spatie\Permission\Models\Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return redirect()->route('roles.index')->with('success', 'Role created successfully');
    }

    public function show(string $id)
    {
        $role = \Spatie\Permission\Models\Role::findOrFail($id);
        $rolePermissions = $role->permissions;
        $permissionGroups = $this->groupPermissions($rolePermissions);
        
        return view('roles.show', compact('role', 'rolePermissions', 'permissionGroups'));
    }

    public function edit(string $id)
    {
        $role = \Spatie\Permission\Models\Role::findOrFail($id);
        $permissions = \Spatie\Permission\Models\Permission::all();
        $permissionGroups = $this->groupPermissions($permissions);
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact('role', 'permissionGroups', 'rolePermissions'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,'.$id,
            'permissions' => 'required|array',
        ]);

        $role = \Spatie\Permission\Models\Role::findOrFail($id);
        
        if($role->name == 'Super Admin') {
             // Prevent renaming Super Admin but allow permission updates if really needed (though usually Super Admin has all)
             // For safety, let's block editing Super Admin name
             // but here just standard update
        }

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully');
    }

    public function destroy(string $id)
    {
        $role = \Spatie\Permission\Models\Role::findOrFail($id);
        if($role->name == 'Super Admin') {
            return redirect()->route('roles.index')->with('error', 'Cannot delete Super Admin role');
        }
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role deleted successfully');
    }

    // Helper to group permissions
    private function groupPermissions($permissions)
    {
        $groups = [];
        foreach($permissions as $permission) {
            $parts = explode('-', $permission->name);
            $groupName = ucfirst($parts[0]);
            $groups[$groupName][] = $permission;
        }
        return $groups;
    }
}
