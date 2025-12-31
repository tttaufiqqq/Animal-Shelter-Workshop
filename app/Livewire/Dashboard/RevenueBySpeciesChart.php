<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class RevenueBySpeciesChart extends Component
{
    public $topAnimals;

    public function mount($topAnimals)
    {
        $this->topAnimals = $topAnimals;
    }

    public function render()
    {
        return view('livewire.dashboard.revenue-by-species-chart');
    }
}
