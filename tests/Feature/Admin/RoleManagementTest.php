<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_view_role_management_page()
    {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)
            ->get(route('admin.roles.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_role_management_page()
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.roles.index'))
            ->assertOk();
    }

    public function test_admin_can_assign_checker_role_to_staff_user()
    {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->staff()->create();

        $this->actingAs($admin)
            ->patch(route('admin.roles.update', $staff), [
                'role' => 'checker',
            ])
            ->assertRedirect(route('admin.roles.index'));

        $this->assertSame('checker', $staff->refresh()->role);
    }

    public function test_admin_cannot_change_role_of_admin_from_page()
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.roles.index'))
            ->patch(route('admin.roles.update', $otherAdmin), [
                'role' => 'staff',
            ])
            ->assertSessionHasErrors('role')
            ->assertRedirect(route('admin.roles.index'));

        $this->assertSame('admin', $otherAdmin->refresh()->role);
    }
}
