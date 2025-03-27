<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class PermissionsController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->where('id', '!=', auth()->id())->get();

        return Inertia::render('Admin/PermissionsDashboard', [
            'roles' => Role::with('permissions')
                ->where('name', '!=', 'super_admin')
                ->get(),
            'permissions' => Permission::where('name', '!=', 'manage app')->get(),
            'users' => $users
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name'
        ]);

        Permission::create([
            'name' => $validated['name'],
            'guard_name' => 'web'
        ]);

        return redirect()->back();
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
        ]);

        $permission->update(['name' => $validated['name']]);

        return redirect()->back();
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()->back();
    }

    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissionIds' => 'nullable|array',
            'permissionIds.*' => 'exists:permissions,id'
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web'
        ]);

        if (!empty($validated['permissionIds'])) {
            $permissions = Permission::whereIn('id', $validated['permissionIds'])->get();
            $role->syncPermissions($permissions);
        }

        return redirect()->back();
    }

    public function updateRole(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissionIds' => 'nullable|array',
            'permissionIds.*' => 'exists:permissions,id'  // Validate each permission ID
        ]);

        $role->update(['name' => $validated['name']]);

        // Always sync permissions, even if empty array is provided
        $permissions = Permission::whereIn('id', $validated['permissionIds'] ?? [])->get();
        $role->syncPermissions($permissions);

        return redirect()->back();
    }

    public function destroyRole(Role $role)
    {
        $role->delete();

        return redirect()->back();
    }

    public function updateUserRoles(Request $request, User $user)
    {
        $validated = $request->validate([
            'roleIds' => 'array',
        ]);

        $roles = Role::whereIn('id', $validated['roleIds'])->get();
        $user->syncRoles($roles);

        return redirect()->back();
    }
}



