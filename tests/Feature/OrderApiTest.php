<?php

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

it('updates an order and returns the reordered table', function () {
    Order::query()->insert([
        ['name' => 'apple', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'banana', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'cherry', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
    ]);

    $response = $this->putJson('/api/items/2', [
        'name' => 'mango',
        'order' => 1,
    ]);

    $response->assertOk();
    $response->assertJsonPath('0.id', 2);
    $response->assertJsonPath('0.name', 'mango');
    $response->assertJsonPath('0.order', 1);
    expect($response->json())->not->toHaveKey('data');
    expect(Order::query()->orderBy('order')->pluck('id')->all())->toBe([2, 1, 3]);
});

it('renders the public orders page', function () {
    Order::query()->insert([
        ['name' => 'apple', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
    ]);

    $response = $this->get('/orders');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('orders')
        ->has('items', 1)
        ->where('items.0.name', 'apple'));
});

it('renders the admin orders page and allows create update and delete through web routes', function () {
    $first = Order::query()->create(['name' => 'apple', 'order' => 1]);
    $second = Order::query()->create(['name' => 'banana', 'order' => 2]);

    $this->get('/admin/orders')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/orders')
            ->has('items', 2));

    $this->post('/admin/orders', [
        'name' => 'cherry',
        'order' => 2,
    ])->assertRedirect('/admin/orders');

    expect(Order::query()->orderBy('order')->pluck('name')->all())->toBe([
        'apple',
        'cherry',
        'banana',
    ]);

    $this->put("/admin/orders/{$second->id}", [
        'name' => 'blueberry',
        'order' => 1,
    ])->assertRedirect('/admin/orders');

    expect(Order::query()->orderBy('order')->pluck('name')->all())->toBe([
        'blueberry',
        'apple',
        'cherry',
    ]);

    $this->delete("/admin/orders/{$first->id}")
        ->assertRedirect('/admin/orders');

    expect(Order::query()->orderBy('order')->pluck('name')->all())->toBe([
        'blueberry',
        'cherry',
    ]);
});
