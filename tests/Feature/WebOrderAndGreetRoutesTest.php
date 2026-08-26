<?php

use App\Models\GreetSetting;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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

it('renders the admin greet page', function () {
    GreetSetting::query()->create([
        'message' => 'Edit me.',
    ]);

    $this->get('/admin/greet')
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

    $this->get('/orders')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('orders')
            ->has('items', 1));

    $this->get('/admin/orders')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/orders')
            ->has('items', 1));
});
