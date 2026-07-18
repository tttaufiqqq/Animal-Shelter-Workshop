<?php

use App\Models\AuditLog;

function makeAuditLog(array $overrides = []): AuditLog
{
    return AuditLog::create(array_merge([
        'category' => 'authentication',
        'action' => 'login_success',
        'user_name' => 'Jane Doe',
        'user_email' => 'jane@example.com',
        'status' => 'success',
        'performed_at' => now(),
    ], $overrides));
}

it('shows category counts on the audit dashboard', function () {
    $admin = $this->makeAdmin();
    makeAuditLog(['category' => 'payment']);
    makeAuditLog(['category' => 'authentication']);

    $response = $this->actingAs($admin)->get('/admin/audit');

    $response->assertOk();
    $stats = $response->original->getData()['stats'];
    expect($stats['payment'])->toBeGreaterThanOrEqual(1)
        ->and($stats['authentication'])->toBeGreaterThanOrEqual(1);
});

// scopeSearch() uses Postgres-only ILIKE — not a bug in practice, since
// AuditLog::$connection is hardcoded to 'users' (Postgres) and never runs
// against any other engine (unlike most models in this app, which are
// queried across multiple connections). Pinning that it actually works,
// not just assuming it does because the SQL looks right.
it('finds a log by a case-insensitive partial match on user_name', function () {
    $admin = $this->makeAdmin();
    makeAuditLog(['user_name' => 'Alice Wonderland']);
    makeAuditLog(['user_name' => 'Bob Builder']);

    $response = $this->actingAs($admin)->get('/admin/audit/all?search=alice');

    $response->assertOk();
    $logs = $response->original->getData()['logs'];
    expect($logs->pluck('user_name')->all())->toBe(['Alice Wonderland']);
});

it('filters audit logs by category on the /all view', function () {
    $admin = $this->makeAdmin();
    makeAuditLog(['category' => 'animal']);
    makeAuditLog(['category' => 'rescue']);

    $response = $this->actingAs($admin)->get('/admin/audit/all?category=animal');

    $response->assertOk();
    $logs = $response->original->getData()['logs'];
    expect($logs->pluck('category')->unique()->all())->toBe(['animal']);
});

it('exports a csv with a row per log', function () {
    $admin = $this->makeAdmin();
    makeAuditLog(['category' => 'authentication', 'action' => 'login_success']);
    makeAuditLog(['category' => 'authentication', 'action' => 'login_failed', 'status' => 'failure']);

    $response = $this->actingAs($admin)->get('/admin/audit/export/authentication');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    $csv = $response->streamedContent();
    $lines = array_filter(explode("\n", trim($csv)));
    expect($lines)->toHaveCount(3); // header + 2 rows
});
