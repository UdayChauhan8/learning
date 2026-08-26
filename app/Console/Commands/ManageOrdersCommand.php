<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('orders:manage {action : add or remove} {count=1 : Number of entries to add or remove}')]
#[Description('Add or remove sample order entries')]
class ManageOrdersCommand extends Command
{
    public function handle(): int
    {
        $action = strtolower((string) $this->argument('action'));
        $count = max(1, (int) $this->argument('count'));
        $fruitNames = [
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
        ];

        if ($action === 'add') {
            $nextOrder = (int) (Order::query()->max('order') ?? 0) + 1;
            $rows = [];

            for ($index = 0; $index < $count; $index++) {
                $fruitName = $fruitNames[$index % count($fruitNames)];

                $rows[] = [
                    'name' => $index < count($fruitNames) ? $fruitName : "{$fruitName}-" . ($index + 1),
                    'order' => $nextOrder++,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($rows) === 1000) {
                    Order::query()->insert($rows);
                    $rows = [];
                }
            }

            if ($rows !== []) {
                Order::query()->insert($rows);
            }

            $this->components->info("Added {$count} order entry/entries.");

            return self::SUCCESS;
        }

        if ($action === 'remove') {
            $ids = Order::query()
                ->orderByDesc('id')
                ->limit($count)
                ->pluck('id');

            $removed = Order::query()
                ->whereIn('id', $ids)
                ->delete();

            $this->components->info("Removed {$removed} order entry/entries.");

            return self::SUCCESS;
        }

        $this->components->error('Action must be either "add" or "remove".');

        return self::FAILURE;
    }
}
