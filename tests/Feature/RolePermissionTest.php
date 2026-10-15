<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    protected User $adminUser;
    protected User $salesUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->adminUser = User::where('email', 'admin@mantraheal.com')->first() ?? User::factory()->create([
            'role_slug' => 'super_admin',
        ]);

        $this->salesUser = User::where('email', 'rahul.sales@mantraheal.com')->first();
        if (!$this->salesUser) {
            $salesRole = Role::where('slug', 'sales_executive')->first();
            $this->salesUser = User::factory()->create([
                'email' => 'rahul.sales@mantraheal.com',
                'role_id' => $salesRole?->id,
                'role_slug' => 'sales_executive',
            ]);
        }
    }

    public function test_permissions_are_seeded_properly(): void
    {
        $this->assertGreaterThan(0, Permission::count());
        $this->assertGreaterThan(0, Role::count());

        $salesRole = Role::where('slug', 'sales_executive')->first();
        $this->assertNotNull($salesRole);
        $this->assertTrue($salesRole->hasPermission('leads.view'));
        $this->assertFalse($salesRole->hasPermission('settings.manage'));
    }

    public function test_user_permission_helpers(): void
    {
        $this->assertTrue($this->adminUser->isSuperAdmin());
        $this->assertTrue($this->adminUser->hasPermission('any.random.permission'));

        $this->assertTrue($this->salesUser->hasPermission('leads.view'));
        $this->assertFalse($this->salesUser->hasPermission('settings.manage'));
        $this->assertTrue($this->salesUser->hasAnyPermission(['settings.manage', 'leads.view']));
    }

    public function test_super_admin_can_access_administration_routes(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/employees');
        $response->assertStatus(200);
        $response->assertSee('Employees & Role Permissions', false);

        $response = $this->actingAs($this->adminUser)->get('/audit-logs');
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser)->get('/settings');
        $response->assertStatus(200);
    }

    public function test_sales_executive_cannot_access_settings_or_employees(): void
    {
        $response = $this->actingAs($this->salesUser)->get('/settings');
        $response->assertStatus(403);

        $response = $this->actingAs($this->salesUser)->get('/employees');
        $response->assertStatus(403);

        $response = $this->actingAs($this->salesUser)->get('/audit-logs');
        $response->assertStatus(403);
    }

    public function test_sales_executive_can_access_leads_and_orders(): void
    {
        $response = $this->actingAs($this->salesUser)->get(route('leads.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->salesUser)->get(route('orders.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_update_role_permissions(): void
    {
        $role = Role::where('slug', 'sales_executive')->first();
        $perm = Permission::where('slug', 'reports.view')->first();

        $response = $this->actingAs($this->adminUser)->post("/roles/{$role->id}/permissions", [
            'permissions' => array_merge($role->permissions->pluck('id')->toArray(), [$perm->id]),
            'description' => 'Updated sales executive role description',
        ]);

        $response->assertSessionHas('success');
        $role->refresh();
        $this->assertTrue($role->hasPermission('reports.view'));
        $this->assertEquals('Updated sales executive role description', $role->description);
    }

    public function test_admin_can_create_custom_role(): void
    {
        $slug = 'custom_role_' . uniqid();
        $name = 'Custom Role ' . uniqid();
        $perm = Permission::where('slug', 'leads.view')->first();

        $response = $this->actingAs($this->adminUser)->post('/roles', [
            'name' => $name,
            'slug' => $slug,
            'description' => 'Ayurvedic door-to-door wellness consultant',
            'permissions' => [$perm->id],
        ]);

        $response->assertSessionHas('success');
        $newRole = Role::where('slug', $slug)->first();
        $this->assertNotNull($newRole);
        $this->assertTrue($newRole->hasPermission('leads.view'));
    }
}
