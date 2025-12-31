<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class PageHeader extends Component
{
    public $title;
    public $description;

    public function mount($title = 'Booking Analytics Dashboard', $description = 'Overview of booking performance and trends')
    {
        $this->title = $title;
        $this->description = $description;
    }

    public function render()
    {
        return view('livewire.dashboard.page-header');
    }
}
