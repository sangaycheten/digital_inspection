<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_permissions_and_admin_user_are_seeded(): void
    {
        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder'])->assertSuccessful();

        $this->assertDatabaseHas('roles', ['name' => 'admin', 'guard_name' => 'web']);
        $this->assertDatabaseHas('permissions', ['name' => 'manage users', 'guard_name' => 'web']);

        $user = User::where('email', 'admin@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('admin'));
    }
}
