<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class MetricCard extends Component
{
    public $title;
    public $value;
    public $description;
    public $icon;
    public $colorScheme;

    public function mount($title, $value, $description, $icon, $colorScheme = 'blue')
    {
        $this->title = $title;
        $this->value = $value;
        $this->description = $description;
        $this->icon = $icon;
        $this->colorScheme = $colorScheme;
    }

    public function render()
    {
        return view('livewire.dashboard.metric-card');
    }
}
