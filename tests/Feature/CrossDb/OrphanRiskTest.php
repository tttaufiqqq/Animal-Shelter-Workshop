<?php

use App\Models\Adoption;
use App\Models\Animal;
use Illuminate\Support\Facades\DB;

/**
 * docs/foreign-keys.md's Orphan Risk section claims the app mitigates orphaned
 * cross-DB references by "always deleting from the referencing side (booking/
 * adoption) before deleting the referenced entity (animal) in controller logic."
 * ManagesAnimals::destroy() now actually enforces this itself (it wasn't
 * before — these tests originally proved the gap; they now pin the fix).
 */

it('blocks deleting an animal while an adoption row still references it', function () {
    $admin = $this->makeAdmin();
    $animal = $this->makeAnimalWithProfile();
    $adoption = Adoption::factory()->create(['animalID' => $animal->id]);

    $response = $this->actingAs($admin)->delete("/animal/{$animal->id}");

    $response->assertSessionHasErrors('error');
    expect(Animal::find($animal->id))->not->toBeNull();
    expect(Adoption::find($adoption->id))->not->toBeNull();
});

it('blocks deleting an animal while a pending booking still references it via animal_booking', function () {
    $admin = $this->makeAdmin();
    $user = $this->makeAdopter();
    $animal = $this->makeAnimalWithProfile();
    $booking = $this->makeBookingFor($user, [$animal]);

    $response = $this->actingAs($admin)->delete("/animal/{$animal->id}");

    $response->assertSessionHasErrors('error');
    expect(Animal::find($animal->id))->not->toBeNull();
    $stillReferenced = DB::connection('booking')->table('animal_booking')
        ->where('bookingID', $booking->id)
        ->where('animalID', $animal->id)
        ->exists();
    expect($stillReferenced)->toBeTrue();
});

it('avoids the orphan when the referencing adoption row is deleted first, as the docs recommend', function () {
    $admin = $this->makeAdmin();
    $animal = $this->makeAnimalWithProfile();
    $adoption = Adoption::factory()->create(['animalID' => $animal->id]);

    // Delete from the referencing side first, per docs/foreign-keys.md's stated
    // mitigation, instead of relying on the app to do it.
    $adoption->delete();
    $this->actingAs($admin)->delete("/animal/{$animal->id}");

    expect(Animal::find($animal->id))->toBeNull();
    expect(Adoption::find($adoption->id))->toBeNull();
});
