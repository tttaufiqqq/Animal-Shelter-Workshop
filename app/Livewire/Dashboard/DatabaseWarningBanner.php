<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class DatabaseWarningBanner extends Component
{
    public $dbDisconnected;

    public function mount($dbDisconnected = [])
    {
        $this->dbDisconnected = $dbDisconnected;
    }

    public function render()
    {
        return view('livewire.dashboard.database-warning-banner');
    }
}
