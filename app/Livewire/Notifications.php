<?php

namespace App\Livewire;

use App\Livewire\Concerns\Notifications\LoadsNotifications;
use App\Livewire\Concerns\Notifications\MapsNotificationTypes;
use App\Livewire\Concerns\Notifications\NotificationInteractions;
use Livewire\Component;
use Illuminate\Support\Collection;

class Notifications extends Component
{
    use LoadsNotifications,
        MapsNotificationTypes,
        NotificationInteractions;

    public bool $isOpen = false;
    public int $unreadCount = 0;
    public Collection $notifications;
    public array $databaseErrors = [];
    public bool $danishDbOnline = true;

    protected $listeners = ['notificationRead' => 'markAsRead'];
}
