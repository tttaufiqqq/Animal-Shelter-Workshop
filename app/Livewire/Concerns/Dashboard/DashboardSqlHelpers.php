<?php

namespace App\Livewire\Concerns\Dashboard;

use Illuminate\Support\Facades\DB;

trait DashboardSqlHelpers
{
    private function getMonthExpression($column, $connection = 'booking')
    {
        $driver = DB::connection($connection)->getDriverName();

        return match($driver) {
            'pgsql' => "EXTRACT(MONTH FROM {$column})",
            'mysql' => "MONTH({$column})",
            'sqlite' => "CAST(strftime('%m', {$column}) AS INTEGER)",
            'sqlsrv' => "MONTH({$column})",
            default => "MONTH({$column})",
        };
    }

    private function getYearExpression($column, $connection = 'booking')
    {
        $driver = DB::connection($connection)->getDriverName();

        return match($driver) {
            'pgsql' => "EXTRACT(YEAR FROM {$column})",
            'mysql' => "YEAR({$column})",
            'sqlite' => "strftime('%Y', {$column})",
            'sqlsrv' => "YEAR({$column})",
            default => "YEAR({$column})",
        };
    }
}
