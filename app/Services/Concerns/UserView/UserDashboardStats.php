<?php

namespace App\Services\Concerns\UserView;

use Illuminate\Support\Facades\DB;

trait UserDashboardStats
{
    public function getUserAccountStats(): ?object
    {
        $result = DB::connection('users')->select('SELECT * FROM v_user_account_stats');

        return $result[0] ?? null;
    }

    public function getAdopterProfileStats(): ?object
    {
        $result = DB::connection('users')->select('SELECT * FROM v_adopter_profile_stats');

        return $result[0] ?? null;
    }

    public function getDashboardStats(): array
    {
        return [
            'user_stats' => $this->getUserAccountStats(),
            'adopter_stats' => $this->getAdopterProfileStats(),
        ];
    }

    public function refreshMaterializedViews(): string
    {
        $result = DB::connection('users')->select('SELECT refresh_all_taufiq_stats()');

        return $result[0]->refresh_all_taufiq_stats ?? 'Refresh completed';
    }
}
