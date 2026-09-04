<?php

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\Event;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

/*
|--------------------------------------------------------------------------
| Setup
|--------------------------------------------------------------------------
|
| RefreshDatabase is opted into per-file here because tests/Pest.php has the
| global `->use(RefreshDatabase::class)` commented out. Without this line these
| tests would run against the real `Learning` database and the seeder below would
| write into it.
|
*/

uses(RefreshDatabase::class);

beforeEach(function () {
    // Roles and permissions are reference data, not fixtures — the whole
    // authorization layer resolves against these rows, so every test needs them.
    $this->seed(RolesAndPermissionsSeeder::class);
});

/*
|--------------------------------------------------------------------------
| Layer 1: authentication (are you signed in?)
|--------------------------------------------------------------------------
*/

it('redirects guests away from the admin area', function () {
    // This is the hole that existed before RBAC: /admin/* answered 200 to anyone.
    $this->get(route('admin.events.index'))
        ->assertRedirect(route('login'));
});

/*
|--------------------------------------------------------------------------
| Layer 2: the role gate (may you see the admin area at all?)
|--------------------------------------------------------------------------
*/

it('forbids a signed-in user with no admin role', function () {
    $user = User::factory()->withRole(RoleName::User)->create();

    // 403, not a redirect: they are authenticated, just not permitted.
    $this->actingAs($user)
        ->get(route('admin.events.index'))
        ->assertForbidden();
});

it('allows an admin into the admin area', function () {
    $admin = User::factory()->withRole(RoleName::Admin)->create();

    $this->actingAs($admin)
        ->get(route('admin.events.index'))
        ->assertOk();
});

/*
|--------------------------------------------------------------------------
| Layer 3: the permission gate (may you perform THIS action?)
|--------------------------------------------------------------------------
*/

it('lets an editor view events but not delete them', function () {
    // The case that proves roles and permissions are doing different jobs. The
    // editor clears the group-level role:… gate, then fails the route-level can:…
    // gate on destroy. A role-only design could not express this.
    $editor = User::factory()->withRole(RoleName::Editor)->create();

    $this->actingAs($editor)
        ->get(route('admin.events.index'))
        ->assertOk();

    expect($editor->can(PermissionName::DeleteEvents->value))->toBeFalse();
});

it('blocks an editor from deleting an event over http', function () {
    // The assertion above proves the *Gate* denies events.delete. This one proves
    // the `can:events.delete` middleware on the route actually consults that gate —
    // a distinction worth testing, because an in-process $user->can() check still
    // passes if the middleware were accidentally left off the route. This is the
    // test that would fail if routes/web.php lost a can: on a destructive verb.
    $editor = User::factory()->withRole(RoleName::Editor)->create();
    $event = Event::query()->create(['title' => 'Launch party']);

    $this->actingAs($editor)
        ->delete(route('admin.events.destroy', $event))
        ->assertForbidden();

    // 403 must mean nothing happened — a gate that blocks the response but lets the
    // side effect through would be worse than no gate at all.
    expect(Event::query()->whereKey($event->getKey())->exists())->toBeTrue();
});

it('lets an admin delete an event over http', function () {
    // The positive half of the pair above: same route, same verb, a role that does
    // hold events.delete. Without this, a middleware string with a typo in the
    // permission name would deny everyone and still look "secure" in the tests.
    $admin = User::factory()->withRole(RoleName::Admin)->create();
    $event = Event::query()->create(['title' => 'Launch party']);

    $this->actingAs($admin)
        ->delete(route('admin.events.destroy', $event))
        ->assertRedirect();

    expect(Event::query()->whereKey($event->getKey())->exists())->toBeFalse();
});

it('blocks a plain user from creating an order over http', function () {
    // Second resource, write verb: confirms the two-layer stack is applied across
    // the admin group rather than only on the events routes. A `user` fails at the
    // outer role: gate, so this never even reaches can:orders.create.
    $user = User::factory()->withRole(RoleName::User)->create();

    $this->actingAs($user)
        ->post(route('admin.orders.store'), ['name' => 'cherry', 'order' => 1])
        ->assertForbidden();

    expect(Order::query()->count())->toBe(0);
});

it('grants a super admin every ability, including undefined ones', function () {
    $user = User::factory()->withRole(RoleName::SuperAdmin)->create();

    // The second assertion is the real test of Gate::before: the bypass returns
    // true without ever consulting the permissions table, so an ability that has
    // no row anywhere still passes.
    expect($user->can(PermissionName::DeleteEvents->value))->toBeTrue()
        ->and($user->can('an.ability.that.does.not.exist'))->toBeTrue();
});

it('denies an ability that no role grants', function () {
    $user = User::factory()->withRole(RoleName::User)->create();

    // Guards the Gate::before contract from the other side. If that callback ever
    // returns `false` instead of `null` on a miss this still passes — but the
    // policy-fallthrough test below would break.
    expect($user->can(PermissionName::ViewEvents->value))->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| The cache
|--------------------------------------------------------------------------
*/

it('reflects role changes after syncing', function () {
    $user = User::factory()->withRole(RoleName::Editor)->create();

    expect($user->hasRole(RoleName::Editor))->toBeTrue();

    $user->syncRoles(RoleName::User);

    // This is the test that catches a broken flushRoleCache(). Without the flush,
    // roleNames() would still be serving the memoised Collection from before the
    // sync and the first assertion below would fail — the classic "I assigned the
    // role but it still says access denied" bug.
    expect($user->hasRole(RoleName::Editor))->toBeFalse()
        ->and($user->hasRole(RoleName::User))->toBeTrue();
});

it('does not duplicate a role that is assigned twice', function () {
    $user = User::factory()->withRole(RoleName::Admin)->create();

    $user->assignRole(RoleName::Admin);

    // syncWithoutDetaching plus the composite primary key on role_user make this
    // a no-op rather than an integrity-constraint violation.
    expect($user->roles()->count())->toBe(1);
});

it('accumulates permissions from every assigned role', function () {
    $user = User::factory()->withRole(RoleName::User)->create();

    expect($user->can(PermissionName::ViewEvents->value))->toBeFalse();

    $user->assignRole(RoleName::Editor);

    // permissionNames() flattens across all roles and de-duplicates, so adding a
    // second role widens access without removing what the first one granted.
    expect($user->can(PermissionName::ViewEvents->value))->toBeTrue()
        ->and($user->hasRole(RoleName::User))->toBeTrue();
});
