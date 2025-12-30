<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class YearFilter extends Component
{
    public $years;
    public $selectedYear;

    public function mount($years, $selectedYear)
    {
        $this->years = $years;
        $this->selectedYear = $selectedYear;
    }

    public function updatedSelectedYear($value)
    {
        $this->dispatch('yearChanged', year: $value);
    }

    public function render()
    {
        return view('livewire.dashboard.year-filter');
    }
}
