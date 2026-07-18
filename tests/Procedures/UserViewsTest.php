<?php

use App\Models\AdopterProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

uses(Tests\Concerns\TruncatesDistributedDatabases::class);

beforeEach(function () {
    $this->assertTestDatabase('users');
    Role::firstOrCreate(['name' => 'adopter']);
});

afterEach(function () {
    $this->truncate('users', ['audit_logs', 'model_has_roles', 'adopter_profile', 'users']);
});

function fetchUserProfileView(int $userId): ?object
{
    return DB::connection('users')->selectOne('SELECT * FROM v_user_full_profile WHERE id = ?', [$userId]);
}

// --- v_user_full_profile ---

it('joins the adopter profile and aggregates role names', function () {
    $user = User::factory()->create();
    $user->assignRole('adopter');
    AdopterProfile::factory()->create(['adopterID' => $user->id, 'housing_type' => 'condo']);

    $row = fetchUserProfileView($user->id);

    expect($row->housing_type)->toBe('condo');
    expect($row->roles)->toBe('adopter');
});

it('defaults roles to the literal string User when no role is assigned', function () {
    $user = User::factory()->create();

    $row = fetchUserProfileView($user->id);

    expect($row->roles)->toBe('User');
});

it('flags is_high_risk once failed_login_attempts reaches 3', function () {
    $user = User::factory()->create(['failed_login_attempts' => 3]);

    $row = fetchUserProfileView($user->id);

    expect($row->is_high_risk)->toBeTrue();
});

it('does not flag is_locked once locked_until has passed', function () {
    // Written via a Postgres-side expression (not PHP's now()) deliberately:
    // this "users" connection's timestamp columns are `timestamp without time
    // zone`, and the app timezone (Asia/Kuala_Lumpur, UTC+8) differs from the
    // Postgres session timezone (UTC) — a PHP-computed Carbon value written
    // through Eloquent gets stored as literal digits and misread as UTC,
    // skewing it ~8h into the future. That's a real app-wide timezone defect
    // (flagged separately), not something this view test should also trip on.
    $user = User::factory()->create(['account_status' => 'locked']);
    DB::connection('users')->statement(
        "UPDATE users SET locked_until = NOW() - INTERVAL '1 minute' WHERE id = ?",
        [$user->id]
    );

    $row = fetchUserProfileView($user->id);

    expect($row->is_locked)->toBeFalse();
});

// --- v_high_risk_users ---

it('excludes a clean user with zero failed logins and an active account', function () {
    $user = User::factory()->create(['failed_login_attempts' => 0, 'account_status' => 'active']);

    $rows = DB::connection('users')->select('SELECT * FROM v_high_risk_users WHERE id = ?', [$user->id]);

    expect($rows)->toBeEmpty();
});

it('ranks a suspended user as critical risk ahead of a merely-locked one', function () {
    $suspended = User::factory()->create(['account_status' => 'suspended', 'failed_login_attempts' => 1]);
    $locked = User::factory()->create(['account_status' => 'locked', 'failed_login_attempts' => 1]);

    $rows = DB::connection('users')->select('SELECT * FROM v_high_risk_users');
    $byId = collect($rows)->keyBy('id');

    expect($byId[$suspended->id]->risk_level)->toBe('critical');
    expect($byId[$locked->id]->risk_level)->toBe('high');
});

// --- v_active_users_with_profiles ---

it('excludes an active user with no adopter profile via the inner join', function () {
    $user = User::factory()->create(['account_status' => 'active']);

    $rows = DB::connection('users')->select('SELECT * FROM v_active_users_with_profiles WHERE id = ?', [$user->id]);

    expect($rows)->toBeEmpty();
});

it('excludes a suspended user even if they have a complete adopter profile', function () {
    $user = User::factory()->create(['account_status' => 'suspended']);
    AdopterProfile::factory()->create([
        'adopterID' => $user->id, 'housing_type' => 'condo', 'activity_level' => 'high',
        'experience' => 'expert', 'preferred_species' => 'dog',
    ]);

    $rows = DB::connection('users')->select('SELECT * FROM v_active_users_with_profiles WHERE id = ?', [$user->id]);

    expect($rows)->toBeEmpty();
});

it('marks a profile complete only once housing, activity, experience, and species are all set', function () {
    $user = User::factory()->create(['account_status' => 'active']);
    AdopterProfile::factory()->create([
        'adopterID' => $user->id, 'housing_type' => 'condo', 'activity_level' => 'high',
        'experience' => 'expert', 'preferred_species' => 'dog',
    ]);

    $row = DB::connection('users')->selectOne('SELECT * FROM v_active_users_with_profiles WHERE id = ?', [$user->id]);

    expect($row->is_profile_complete)->toBeTrue();
    expect((int) $row->readiness_score)->toBe(100);
});
