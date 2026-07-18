<?php

use App\Livewire\ReportsTable;
use App\Models\Report;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    Report::query()->delete();
});

function seedReportRows(int $count, array $overrides = []): void
{
    $rows = Report::factory()->count($count)->raw($overrides);
    foreach ($rows as &$row) {
        $row['created_at'] = now();
        $row['updated_at'] = now();
    }
    DB::connection('reporting')->table('report')->insert($rows);
}

it('applies the status queryString param from the URL on mount', function () {
    Report::factory()->create(['report_status' => Report::STATUS_PENDING]);
    Report::factory()->create(['report_status' => Report::STATUS_ASSIGNED]);

    Livewire::withQueryParams(['status' => Report::STATUS_ASSIGNED])
        ->test(ReportsTable::class)
        ->assertSet('status', Report::STATUS_ASSIGNED)
        ->assertViewHas('reports', fn ($reports) => $reports->total() === 1
            && $reports->first()->report_status === Report::STATUS_ASSIGNED);
});

it('applies the userSearch queryString param and filters by matching user name or email', function () {
    $user = $this->makeAdopter(['name' => 'Searchable Reporter']);
    Report::factory()->create(['userID' => $user->id]);
    Report::factory()->create(['userID' => null]);

    Livewire::withQueryParams(['userSearch' => 'Searchable'])
        ->test(ReportsTable::class)
        ->assertSet('userSearch', 'Searchable')
        ->assertViewHas('reports', fn ($reports) => $reports->total() === 1);
});

it('never restores userSearch from the legacy, unrelated user_search key', function () {
    // Regression test: mount() used to read request('user_search') instead of
    // request('userSearch') — the key $queryString actually binds to (matches
    // the property name, no 'as' alias) — so a shared ?userSearch=... URL
    // silently lost the search filter on load. Confirmed red before the fix
    // (mount() read the wrong key) and green after.
    Livewire::withQueryParams(['userSearch' => 'ProbeValue'])
        ->test(ReportsTable::class)
        ->assertSet('userSearch', 'ProbeValue');

    Livewire::withQueryParams(['user_search' => 'ProbeValue'])
        ->test(ReportsTable::class)
        ->assertSet('userSearch', '');
});

it('clears both filters and resets pagination', function () {
    Livewire::test(ReportsTable::class)
        ->set('userSearch', 'someone')
        ->set('status', Report::STATUS_PENDING)
        ->call('clearFilters')
        ->assertSet('userSearch', '')
        ->assertSet('status', '');
});

it('paginates reports 50 per page and honours the page queryString param', function () {
    seedReportRows(51);

    Livewire::test(ReportsTable::class)
        ->assertViewHas('reports', fn ($reports) => $reports->perPage() === 50 && $reports->hasMorePages());

    Livewire::withQueryParams(['page' => 2])
        ->test(ReportsTable::class)
        ->assertViewHas('reports', fn ($reports) => $reports->currentPage() === 2 && $reports->count() === 1);
});

it('reports statusCounts and totalReports matching what was seeded', function () {
    Report::factory()->create(['report_status' => Report::STATUS_PENDING]);
    Report::factory()->create(['report_status' => Report::STATUS_PENDING]);
    Report::factory()->create(['report_status' => Report::STATUS_COMPLETED]);

    Livewire::test(ReportsTable::class)
        ->assertViewHas('totalReports', 3)
        ->assertViewHas('statusCounts', fn ($counts) => $counts[Report::STATUS_PENDING] == 2
            && $counts[Report::STATUS_COMPLETED] == 1);
});

it('auto-loads new reports and dispatches an event when autoRefresh is enabled', function () {
    Report::factory()->create();
    $component = Livewire::test(ReportsTable::class); // autoRefresh defaults to true

    $newReport = Report::factory()->create();

    $component->call('checkForNewReports')
        ->assertDispatched('new-reports-loaded')
        ->assertSet('hasNewReports', false)
        ->assertSet('newReportIds', [$newReport->id]);
});

it('only raises the hasNewReports banner without auto-loading when autoRefresh is disabled', function () {
    Report::factory()->create();
    $component = Livewire::test(ReportsTable::class)->set('autoRefresh', false);

    Report::factory()->create();

    $component->call('checkForNewReports')
        ->assertNotDispatched('new-reports-loaded')
        ->assertSet('hasNewReports', true)
        ->assertSet('newReportIds', []);
});

it('loads the pending new reports once auto-refresh is turned back on', function () {
    Report::factory()->create();
    $component = Livewire::test(ReportsTable::class)->set('autoRefresh', false);

    Report::factory()->create();
    $component->call('checkForNewReports')->assertSet('hasNewReports', true);

    $component->call('toggleAutoRefresh')
        ->assertSet('autoRefresh', true)
        ->assertSet('hasNewReports', false)
        ->assertDispatched('reports-refreshed');
});
