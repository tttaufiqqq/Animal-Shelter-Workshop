<?php

namespace App\Livewire\Concerns\Notifications;

use Illuminate\Support\Facades\Log;

trait NotificationInteractions
{
    public function mount()
    {
        try {
            $this->notifications = collect([]);
            $this->loadNotifications();
        } catch (\Exception $e) {
            Log::error('Notifications component mount failed: ' . $e->getMessage());
            $this->notifications = collect([]);
            $this->unreadCount = 0;
            $this->databaseErrors = ['system' => 'Notification system temporarily unavailable'];
            $this->danishDbOnline = false;
        }
    }

    public function toggleDropdown()
    {
        try {
            $this->isOpen = !$this->isOpen;

            if ($this->isOpen) {
                $this->loadNotifications();
            }
        } catch (\Exception $e) {
            Log::error('Failed to toggle notifications dropdown: ' . $e->getMessage());
            $this->isOpen = !$this->isOpen;
        }
    }

    public function markAsRead($notificationId)
    {
        $readNotifications = session()->get('read_notifications', []);

        if (!in_array($notificationId, $readNotifications)) {
            $readNotifications[] = $notificationId;
            session()->put('read_notifications', $readNotifications);
        }

        $this->loadNotifications();
    }

    public function markAllAsRead()
    {
        $allIds = $this->notifications->pluck('id')->toArray();
        session()->put('read_notifications', array_merge(
            session()->get('read_notifications', []),
            $allIds
        ));

        $this->loadNotifications();
    }

    private function isNotificationRead($notificationId): bool
    {
        $readNotifications = session()->get('read_notifications', []);
        return in_array($notificationId, $readNotifications);
    }

    private function getStatusColor($status): string
    {
        return match(strtolower($status ?? '')) {
            'success', 'successful', 'completed' => 'green',
            'pending' => 'yellow',
            'failed', 'cancelled' => 'red',
            default => 'gray',
        };
    }

    public function render()
    {
        try {
            if (!isset($this->notifications)) {
                $this->notifications = collect([]);
            }
            return view('livewire.notifications');
        } catch (\Exception $e) {
            Log::error('Notifications component render failed: ' . $e->getMessage());
            $this->notifications = collect([]);
            $this->unreadCount = 0;
            return view('livewire.notifications');
        }
    }
}
