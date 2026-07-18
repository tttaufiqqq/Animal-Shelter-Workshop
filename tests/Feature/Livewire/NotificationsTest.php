<?php

use App\Livewire\Notifications;
use App\Models\Adoption;
use App\Models\Animal;
use App\Models\Booking;
use App\Models\Transaction;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;

function forceBookingOffline(): void
{
    Config::set('database.connections.booking.host', '127.0.0.1');
    Config::set('database.connections.booking.port', 1);
}

it('shows a system-offline notification and zero unread count when booking db is down', function () {
    forceBookingOffline();

    $component = Livewire::test(Notifications::class);

    expect($component->get('danishDbOnline'))->toBeFalse()
        ->and($component->get('unreadCount'))->toBe(0);
    $notifications = $component->get('notifications');
    expect($notifications)->toHaveCount(1)
        ->and($notifications->first()['type'])->toBe('system');
});

it('maps a transaction into a notification with the status colour and formatted amount', function () {
    $user = $this->makeAdopter(['name' => 'Fee Payer']);
    Transaction::factory()->create([
        'userID' => $user->id,
        'status' => 'Success',
        'amount' => 42.5,
        'type' => 'FPX Online Banking',
    ]);

    $notifications = Livewire::test(Notifications::class)->get('notifications');

    $notification = $notifications->firstWhere('type', 'transaction');
    expect($notification)->not->toBeNull()
        ->and($notification['color'])->toBe('green')
        ->and($notification['message'])->toContain('Fee Payer')
        ->and($notification['message'])->toContain('RM42.50');
});

it('maps a failed transaction to the red status colour', function () {
    Transaction::factory()->create(['status' => 'Failed', 'userID' => null]);

    $notification = Livewire::test(Notifications::class)->get('notifications')
        ->firstWhere('type', 'transaction');

    expect($notification['color'])->toBe('red');
});

it('maps an adoption into a notification naming the animal and fee', function () {
    $animal = $this->makeAnimalWithProfile(['name' => 'Buddy']);
    Adoption::factory()->create(['animalID' => $animal->id, 'fee' => 20, 'transactionID' => null]);

    $notification = Livewire::test(Notifications::class)->get('notifications')
        ->firstWhere('type', 'adoption');

    expect($notification)->not->toBeNull()
        ->and($notification['message'])->toContain('Buddy')
        ->and($notification['message'])->toContain('RM20.00');
});

it('maps a booking into a notification with the real attached-animal count (regression: cross-server animals() relation)', function () {
    // Booking::animals() joins the booking-connection animal_booking table
    // against the animals-connection animal table in one query - a real
    // cross-server join MySQL/MariaDB can't do (confirmed via a probe:
    // Booking::with(['user','animals'])->get() throws QueryException
    // "Base table or view not found: animal_booking" the moment a booking
    // has an attached animal). That used to kill this whole notifications
    // block via the outer try/catch, setting databaseErrors['bookings'] on
    // every request with a recent booking. Fixed to eager-load the
    // same-connection animalBookings() pivot relation instead.
    $user = $this->makeAdopter(['name' => 'Booker']);
    $animal = $this->makeAnimalWithProfile();
    $booking = $this->makeBookingFor($user, [$animal], ['status' => 'Confirmed']);

    $component = Livewire::test(Notifications::class);

    expect($component->get('databaseErrors'))->not->toHaveKey('bookings');

    $notification = $component->get('notifications')->firstWhere('type', 'booking');
    expect($notification)->not->toBeNull()
        ->and($notification['message'])->toContain('Booker')
        ->and($notification['message'])->toContain('1 animal')
        ->and($notification['status'])->toBe('Confirmed');
});

it('sorts merged notifications by time descending and caps at 20', function () {
    for ($i = 0; $i < 12; $i++) {
        Transaction::factory()->create(['userID' => null, 'created_at' => now()->subMinutes($i)]);
    }
    for ($i = 0; $i < 12; $i++) {
        Adoption::factory()->create(['transactionID' => null, 'created_at' => now()->subMinutes($i + 100)]);
    }

    $notifications = Livewire::test(Notifications::class)->get('notifications');

    expect($notifications->count())->toBe(20);
    $times = $notifications->pluck('time')->map(fn ($t) => $t->timestamp)->values()->all();
    expect($times)->toBe(collect($times)->sortDesc()->values()->all());
});

it('marks a single notification as read and reduces the unread count', function () {
    Transaction::factory()->create(['userID' => null]);

    $component = Livewire::test(Notifications::class);
    $id = $component->get('notifications')->first()['id'];
    expect($component->get('unreadCount'))->toBe(1);

    $component->call('markAsRead', $id)
        ->assertSet('unreadCount', 0);
});

it('marks all notifications as read via markAllAsRead', function () {
    Transaction::factory()->create(['userID' => null]);
    Transaction::factory()->create(['userID' => null]);

    $component = Livewire::test(Notifications::class);
    expect($component->get('unreadCount'))->toBe(2);

    $component->call('markAllAsRead')
        ->assertSet('unreadCount', 0);
});

it('toggles the dropdown open and reloads notifications', function () {
    Livewire::test(Notifications::class)
        ->assertSet('isOpen', false)
        ->call('toggleDropdown')
        ->assertSet('isOpen', true)
        ->call('toggleDropdown')
        ->assertSet('isOpen', false);
});
