<?php

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('adds sample orders', function () {
    $this->artisan('orders:manage', [
        'action' => 'add',
        'count' => 12,
    ])->assertExitCode(0);

    $orders = Order::query()->orderBy('order')->get(['name', 'order']);

    expect($orders)->toHaveCount(12);
    expect($orders->pluck('order')->all())->toBe(range(1, 12));
    expect($orders->pluck('name')->all())->toMatchArray([
        'apple',
        'banana',
        'cherry',
        'grape',
        'mango',
        'peach',
        'pear',
        'plum',
        'kiwi',
        'lemon',
        'apple-11',
        'banana-12',
    ]);
});

it('removes sample orders', function () {
    Order::query()->insert([
        ['name' => 'Alpha', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'Bravo', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'Charlie', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'Delta', 'order' => 4, 'created_at' => now(), 'updated_at' => now()],
    ]);

    $this->artisan('orders:manage', [
        'action' => 'remove',
        'count' => 2,
    ])->assertExitCode(0);

    expect(Order::count())->toBe(2);
});

it('rejects invalid actions', function () {
    $this->artisan('orders:manage', [
        'action' => 'skip',
    ])->assertExitCode(1);
});

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
