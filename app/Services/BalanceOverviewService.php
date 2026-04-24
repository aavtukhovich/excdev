<?php

namespace App\Services;

use App\Models\Operation;
use App\Models\User;
use App\Models\UserBalance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BalanceOverviewService
{
    public function dashboard(User $user, int $limit = 5): array
    {
        $balance = UserBalance::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => '0.00'],
        );

        $recentOperations = Operation::query()
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->limit($limit)
            ->get();

        return [
            'balance' => $balance,
            'recent_operations' => $recentOperations,
            'refresh_interval_seconds' => (int) config('balance.refresh_interval_seconds', 10),
            'refreshed_at' => now(),
        ];
    }

    public function operations(User $user, ?string $search, string $sortDirection, int $perPage): LengthAwarePaginator
    {
        $query = Operation::query()
            ->where('user_id', $user->id)
            ->orderBy('created_at', $sortDirection);

        if ($search !== null && $search !== '') {
            if (DB::connection()->getDriverName() === 'pgsql') {
                $query->where('description', 'ilike', "%{$search}%");
            } else {
                $query->whereRaw('LOWER(description) LIKE ?', ['%'.mb_strtolower($search).'%']);
            }
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
