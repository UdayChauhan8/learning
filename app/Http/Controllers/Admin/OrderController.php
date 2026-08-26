<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function site(): Response
    {
        $items = Order::query()->orderByRaw('`order` ASC')->paginate(10);


        return Inertia::render('orders', [
            'items' => $items,
        ]);
    }

    public function index(): Response
    {
$items = Order::query()->orderByRaw('`order` ASC')->paginate(10);

        return Inertia::render('admin/orders', [
            'items' => $items,
        ]);
    }

    public function show(Order $order): Response
    {
        $items = Order::query()->orderByRaw('`order` ASC')->paginate(10);

        return Inertia::render('admin/orders', [
            'items' => $items,
            'editing' => $order,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'order' => ['required', 'integer', 'min:1'],
        ]);

        $this->shiftOrdersForInsert((int) $validated['order']);
        Order::query()->create($validated);

        return redirect()->route('admin.orders.index')->with('success', 'Order created successfully.');
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'order' => ['required', 'integer', 'min:1'],
        ]);

        $this->resequenceOrdersForUpdate($order, (int) $validated['order']);
        $order->update($validated);

        return redirect()->route('admin.orders.index')->with('success', 'Order updated successfully.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $deletedOrder = $order->order;
        $order->delete();

        Order::query()
            ->where('order', '>', $deletedOrder)
            ->decrement('order');

        return redirect()->route('admin.orders.index')->with('success', 'Order deleted successfully.');
    }

    private function shiftOrdersForInsert(int $order): void
    {
        Order::query()
            ->where('order', '>=', $order)
            ->increment('order');
    }

    private function resequenceOrdersForUpdate(Order $order, int $newOrder): void
    {
        $oldOrder = (int) $order->order;

        if ($oldOrder === $newOrder) {
            return;
        }

        if ($newOrder < $oldOrder) {
            Order::query()
                ->where('order', '>=', $newOrder)
                ->where('order', '<', $oldOrder)
                ->where('id', '!=', $order->id)
                ->increment('order');

            return;
        }

        Order::query()
            ->where('order', '>', $oldOrder)
            ->where('order', '<=', $newOrder)
            ->where('id', '!=', $order->id)
            ->decrement('order');
    }
}
