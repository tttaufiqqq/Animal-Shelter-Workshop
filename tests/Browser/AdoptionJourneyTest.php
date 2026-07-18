<?php

use App\Models\Medical;
use App\Models\Vaccination;

/**
 * The multi-step (Details -> Select -> Confirm) booking modal in
 * booking-adoption/main/bookings-table.blade.php + public/js/booking-modal.js.
 * Every selector is scoped by booking id (bookingModal-{id}, next-btn-{id}, etc.
 * - modals are rendered one per booking) per the plan's own note. Assertions on
 * the pay button use the `disabled` DOM property, not CSS classes, since
 * scripts-visit.blade.php-style views in this app apply `!important` styles
 * that would make class-based assertions flaky.
 *
 * Scope: only the 3 steps below actually need a browser (client-side JS state
 * that never round-trips to the server: step navigation gating, the pay
 * button's enabled state, and the fee numbers computed from each checkbox's
 * own data-* attributes). None of them submit the final form, so - matching
 * the same call this suite already made in ReportSubmissionTest.php - there's
 * no need to work around this Pest version's multipart/navigation-click
 * limitations here. The real submission (POST bookings.confirm) is already
 * covered at the HTTP-client level by tests/Feature/Booking/ConfirmAppointmentTest.php.
 *
 * The "View" button that opens the modal is clicked via the explicit selector
 * [onclick="openBookingModal({id})"], not its visible text - a page listing
 * more than one booking repeats that exact text once per row.
 */
function openStep2(\Pest\Browser\Api\PendingAwaitablePage $page, int $bookingId): void
{
    $page->click("[onclick=\"openBookingModal({$bookingId})\"]")
        ->assertVisible("#bookingModal-{$bookingId}")
        ->click("#next-btn-{$bookingId}");
}

it('blocks advancing past step 2 with no animal selected', function () {
    $user = $this->makeAdopter();
    $animal = $this->makeAnimalWithProfile(['species' => 'Dog']);
    $booking = $this->makeBookingFor($user, [$animal]);

    $this->actingAs($user);
    $page = visit('/bookings/index');
    openStep2($page, $booking->id);

    // The one animal on this booking is pre-selected by default (see
    // step2-select.blade.php). Its own CSS hides the checkbox input once
    // checked (real users toggle it via the surrounding <label> instead) -
    // Playwright's uncheck() requires a visible element and hangs on its own
    // 30s actionability wait against a display:none input, confirmed
    // directly. A JS-dispatched click toggles it the same way a native
    // checkbox click would, without that visibility requirement.
    $page->script("document.getElementById('selectAnimal-{$booking->id}-{$animal->id}').click();");
    $page->click("#next-btn-{$booking->id}")
        ->assertSee('Please select at least one animal to adopt.');

    $page->assertScript("document.getElementById('step3-{$booking->id}').classList.contains('hidden')", true);
});

it('keeps the pay button disabled until terms are accepted', function () {
    $user = $this->makeAdopter();
    $animal = $this->makeAnimalWithProfile(['species' => 'Cat']);
    $booking = $this->makeBookingFor($user, [$animal]);

    $this->actingAs($user);
    $page = visit('/bookings/index');
    openStep2($page, $booking->id);

    // populateStep3() runs after a 500ms "Calculating Fees..." loading delay
    // (see nextStep() in booking-modal.js) - give it time to actually populate
    // step 3 and set the button's disabled state before asserting on it.
    $page->click("#next-btn-{$booking->id}")->wait(1);

    $page->assertScript("document.getElementById('submitBtn-{$booking->id}').disabled", true);

    $page->check("#agree_terms_{$booking->id}");

    $page->assertScript("document.getElementById('submitBtn-{$booking->id}').disabled", false);
});

it('shows a fee breakdown matching the server calculation', function () {
    $user = $this->makeAdopter();
    $animal = $this->makeAnimalWithProfile(['species' => 'Dog']);
    Medical::factory()->create(['animalID' => $animal->id]);
    Vaccination::factory()->create(['animalID' => $animal->id]);
    $booking = $this->makeBookingFor($user, [$animal]);

    $this->actingAs($user);
    $page = visit('/bookings/index');
    openStep2($page, $booking->id);
    $page->click("#next-btn-{$booking->id}")->wait(1);

    // Dog base RM20 + 1 medical RM10 + 1 vaccination RM20 = RM50.00 - the same
    // formula CalculatesAdoptionFee::calculateAdoptionFee() uses server-side
    // (and modal-shell.blade.php duplicates client-side to build each
    // checkbox's data-fee attribute - this is the drift Rule 7 flags).
    $page->assertSee('RM 50.00');
});
