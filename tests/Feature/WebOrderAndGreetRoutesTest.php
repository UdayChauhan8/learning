<?php

use App\Enums\RoleName;
use App\Models\GreetSetting;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Updated for RBAC
|--------------------------------------------------------------------------
|
| The /admin/* assertions below used to run as a guest, because those routes had
| no middleware at all. They now sit behind ['auth', 'verified', 'role:…'] plus a
| per-route 'can:…', so each one signs in as an admin first.
|
| The public /greet and /orders assertions are unchanged — those routes are still
| deliberately open to everyone.
|
*/

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

/**
 * An account that clears both the role gate and the greet/orders permissions.
 */
function adminUser(): User
{
    return User::factory()->withRole(RoleName::Admin)->create();
}

it('renders the public greet page', function () {
    GreetSetting::query()->create([
        'message' => 'Hello from the database.',
    ]);

    $this->get('/greet')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('greet')
            ->where('message', 'Hello from the database.'));
});

it('renders the admin greet page for an admin', function () {
    GreetSetting::query()->create([
        'message' => 'Edit me.',
    ]);

    $this->actingAs(adminUser())
        ->get('/admin/greet')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/greet-setting')
            ->where('message', 'Edit me.'));
});

it('renders the public and admin orders pages', function () {
    Order::query()->create([
        'name' => 'apple',
        'order' => 1,
    ]);

    // Both assertions read `items.data`, not `items`: the controller paginates, so
    // the prop is a paginator whose top level is metadata (current_page, total, …).
    // The `has('items', 1)` these lines used to carry was counting those 13 keys.
    $this->get('/orders')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('orders')
            ->has('items.data', 1));

    $this->actingAs(adminUser())
        ->get('/admin/orders')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/orders')
            ->has('items.data', 1));
});

it('keeps the admin greet page closed to guests', function () {
    // Regression guard for the hole this refactor closed: before RBAC, an
    // unauthenticated visitor got a 200 here.
    $this->get('/admin/greet')->assertRedirect(route('login'));
    $this->get('/admin/orders')->assertRedirect(route('login'));
});
