<?php

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\GreetSettingController;
use App\Http\Controllers\Admin\OrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
|
| No authentication. These are the customer-facing pages.
|
*/

Route::inertia('/', 'welcome')->name('home');

Route::get('/greet', [GreetSettingController::class, 'index'])->name('greet');
Route::get('/orders', [OrderController::class, 'site'])->name('orders.site');

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
|
| Any signed-in user, no role required.
|
| `verified` is harmless even though User does not implement MustVerifyEmail:
| EnsureEmailIsVerified only enforces verification on models that implement that
| contract, so today it just acts as a second `auth` check. It is included so the
| gate is already in place if email verification is switched on later.
|
*/

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('/dashboard', 'dashboard')->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Admin routes
|--------------------------------------------------------------------------
|
| Two layers of defence, and both matter:
|
|   1. The group-level `role:` middleware answers "may this person see the admin
|      area at all?". It is the coarse gate — fail it and you get a 403 before
|      any controller is reached.
|
|   2. The per-route `can:` middleware answers "may this person perform THIS
|      action?". This is why the routes below are written out individually rather
|      than as Route::resource(): a resource route applies one middleware stack to
|      all seven actions, so `destroy` could not be held to a stricter permission
|      than `index`. That granularity is the entire point of permissions.
|
| Editor is included in the role list on purpose — an editor may enter the admin
| area, but the `can:` middleware still stops them at the delete routes, because
| RoleName::Editor::defaultPermissions() never grants `events.delete`.
|
| Route names are unchanged from before this refactor. prefix('admin') + name('admin.')
| reproduces exactly `admin.events.index`, `admin.orders.show`, and so on, so every
| existing route('admin.…') call in the controllers keeps working untouched.
|
*/

Route::middleware([
    'auth',
    'verified',
    RoleName::middleware(RoleName::Admin, RoleName::SuperAdmin, RoleName::Editor),
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Greet setting -------------------------------------------------------
        Route::get('greet', [GreetSettingController::class, 'edit'])
            ->middleware(PermissionName::ViewGreetSetting->middleware())
            ->name('greet.edit');

        Route::put('greet', [GreetSettingController::class, 'update'])
            ->middleware(PermissionName::UpdateGreetSetting->middleware())
            ->name('greet.update');

        // Orders --------------------------------------------------------------
        Route::get('orders', [OrderController::class, 'index'])
            ->middleware(PermissionName::ViewOrders->middleware())
            ->name('orders.index');

        Route::get('orders/{order}', [OrderController::class, 'show'])
            ->middleware(PermissionName::ViewOrders->middleware())
            ->name('orders.show');

        Route::post('orders', [OrderController::class, 'store'])
            ->middleware(PermissionName::CreateOrders->middleware())
            ->name('orders.store');

        Route::put('orders/{order}', [OrderController::class, 'update'])
            ->middleware(PermissionName::UpdateOrders->middleware())
            ->name('orders.update');

        Route::delete('orders/{order}', [OrderController::class, 'destroy'])
            ->middleware(PermissionName::DeleteOrders->middleware())
            ->name('orders.destroy');

        // Events --------------------------------------------------------------
        // Note the ordering: `events/create` is declared before `events/{event}/edit`
        // is irrelevant here, but keeping literal segments ahead of wildcards is a
        // habit worth having — otherwise `{event}` can swallow `create`.
        Route::get('events', [EventController::class, 'index'])
            ->middleware(PermissionName::ViewEvents->middleware())
            ->name('events.index');

        Route::get('events/create', [EventController::class, 'create'])
            ->middleware(PermissionName::CreateEvents->middleware())
            ->name('events.create');

        Route::post('events', [EventController::class, 'store'])
            ->middleware(PermissionName::CreateEvents->middleware())
            ->name('events.store');

        Route::get('events/{event}/edit', [EventController::class, 'edit'])
            ->middleware(PermissionName::UpdateEvents->middleware())
            ->name('events.edit');

        Route::put('events/{event}', [EventController::class, 'update'])
            ->middleware(PermissionName::UpdateEvents->middleware())
            ->name('events.update');

        Route::delete('events/{event}', [EventController::class, 'destroy'])
            ->middleware(PermissionName::DeleteEvents->middleware())
            ->name('events.destroy');
    });

require __DIR__.'/settings.php';
