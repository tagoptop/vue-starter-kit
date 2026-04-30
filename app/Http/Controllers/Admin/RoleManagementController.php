<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRoleUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RoleManagementController extends Controller
{
    /**
     * Show a simple role assignment screen for admins.
     */
    public function index(): Response
    {
        $users = User::query()
            ->select(['id', 'name', 'email', 'role'])
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/RoleManagement', [
            'users' => $users,
            'assignableRoles' => ['staff', 'checker'],
        ]);
    }

    /**
     * Update user role to staff/checker.
     */
    public function update(UserRoleUpdateRequest $request, User $user): RedirectResponse
    {
        if ($user->role === 'admin') {
            return back()->withErrors([
                'role' => __('Admin role cannot be changed from this page.'),
            ]);
        }

        $user->update([
            'role' => $request->validated('role'),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User role updated.')]);

        return to_route('admin.roles.index');
    }
}
