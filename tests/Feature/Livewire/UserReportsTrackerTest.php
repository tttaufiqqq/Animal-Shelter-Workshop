<?php

use App\Livewire\UserReportsTracker;
use App\Models\Report;
use Livewire\Livewire;

beforeEach(function () {
    Report::query()->delete();
});

it('only shows the authenticated user\'s own reports', function () {
    $user = $this->makeAdopter();
    $other = $this->makeAdopter();
    Report::factory()->create(['userID' => $user->id]);
    Report::factory()->create(['userID' => $other->id]);

    $this->actingAs($user);

    Livewire::test(UserReportsTracker::class)
        ->assertViewHas('userReports', fn ($reports) => $reports->total() === 1
            && $reports->first()->userID === $user->id);
});

it('detects a real status change across a poll (regression: reportStatuses must survive hydration)', function () {
    // reportStatuses used to be `protected`, which Livewire drops on every
    // hydrate/dehydrate cycle between requests (only public properties
    // persist in the snapshot) - so checkForStatusChanges() could never see
    // a prior status to diff against and this feature never actually fired.
    // Confirmed red-then-green: made the property public.
    $user = $this->makeAdopter();
    $this->actingAs($user);
    $report = Report::factory()->create(['userID' => $user->id, 'report_status' => Report::STATUS_PENDING]);

    $component = Livewire::test(UserReportsTracker::class);

    $report->update(['report_status' => Report::STATUS_ASSIGNED]);

    $component->call('checkForStatusChanges')
        ->assertSet('hasStatusChanges', true)
        ->assertDispatched('status-changed');

    expect($component->get('statusChanges'))->toBe([[
        'report_id' => $report->id,
        'old_status' => Report::STATUS_PENDING,
        'new_status' => Report::STATUS_ASSIGNED,
    ]]);
});

it('does not flag a status change for another user\'s report', function () {
    $user = $this->makeAdopter();
    $other = $this->makeAdopter();
    $this->actingAs($user);
    $ownReport = Report::factory()->create(['userID' => $user->id, 'report_status' => Report::STATUS_PENDING]);
    $otherReport = Report::factory()->create(['userID' => $other->id, 'report_status' => Report::STATUS_PENDING]);

    $component = Livewire::test(UserReportsTracker::class);

    $ownReport->update(['report_status' => Report::STATUS_PENDING]);
    $otherReport->update(['report_status' => Report::STATUS_ASSIGNED]);

    $component->call('checkForStatusChanges')
        ->assertSet('hasStatusChanges', false)
        ->assertNotDispatched('status-changed');
});

it('stays quiet on a poll with no status changes', function () {
    $user = $this->makeAdopter();
    $this->actingAs($user);
    Report::factory()->create(['userID' => $user->id, 'report_status' => Report::STATUS_PENDING]);

    Livewire::test(UserReportsTracker::class)
        ->call('checkForStatusChanges')
        ->assertSet('hasStatusChanges', false)
        ->assertNotDispatched('status-changed');
});

it('acknowledges status changes, clearing the banner and dispatching an event', function () {
    $user = $this->makeAdopter();
    $this->actingAs($user);
    $report = Report::factory()->create(['userID' => $user->id, 'report_status' => Report::STATUS_PENDING]);

    $component = Livewire::test(UserReportsTracker::class);
    $report->update(['report_status' => Report::STATUS_ASSIGNED]);
    $component->call('checkForStatusChanges')->assertSet('hasStatusChanges', true);

    $component->call('acknowledgeChanges')
        ->assertSet('hasStatusChanges', false)
        ->assertSet('statusChanges', [])
        ->assertDispatched('changes-acknowledged');
});

it('refreshes reports, re-baselines tracked statuses, and dispatches an event', function () {
    $user = $this->makeAdopter();
    $this->actingAs($user);
    $report = Report::factory()->create(['userID' => $user->id, 'report_status' => Report::STATUS_PENDING]);

    $component = Livewire::test(UserReportsTracker::class);
    $report->update(['report_status' => Report::STATUS_ASSIGNED]);

    $component->call('refreshReports')
        ->assertDispatched('reports-refreshed');

    // Baseline was refreshed to 'Assigned', so an immediate re-poll with no
    // further change should not re-fire.
    $component->call('checkForStatusChanges')
        ->assertSet('hasStatusChanges', false)
        ->assertNotDispatched('status-changed');
});
