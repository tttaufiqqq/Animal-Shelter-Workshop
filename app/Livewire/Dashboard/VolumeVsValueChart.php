<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class VolumeVsValueChart extends Component
{
    public $volumeVsValue;

    public function mount($volumeVsValue)
    {
        $this->volumeVsValue = $volumeVsValue;
    }

    public function render()
    {
        return view('livewire.dashboard.volume-vs-value-chart');
    }
}
