<?php

namespace Tests\Concerns;

use App\Models\Animal;
use App\Models\AnimalProfile;
use App\Models\Booking;
use App\Models\Role;
use App\Models\User;
use App\Models\VisitList;

/**
 * Shared builders so tests read as intent, not setup. Every helper here
 * mirrors the real app's data shape (see docs/db-architecture.md) rather
 * than inventing a simplified one, so tests exercise the same cross-DB
 * paths production traffic does.
 */
trait SeedsMinimalDomain
{
    protected function seedRoles(): void
    {
        foreach (['public user', 'admin', 'caretaker', 'adopter'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }

    protected function makeAdopter(array $attributes = []): User
    {
        return $this->makeUserWithRole('adopter', $attributes);
    }

    protected function makeAdmin(array $attributes = []): User
    {
        return $this->makeUserWithRole('admin', $attributes);
    }

    protected function makeCaretaker(array $attributes = []): User
    {
        return $this->makeUserWithRole('caretaker', $attributes);
    }

    protected function makePublicUser(array $attributes = []): User
    {
        return $this->makeUserWithRole('public user', $attributes);
    }

    private function makeUserWithRole(string $role, array $attributes): User
    {
        $this->seedRoles();

        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }

    protected function makeAnimalWithProfile(array $animalAttributes = [], array $profileAttributes = []): Animal
    {
        $animal = Animal::factory()->create($animalAttributes);

        AnimalProfile::factory()->create(array_merge(
            ['animalID' => $animal->id],
            $profileAttributes
        ));

        return $animal->fresh();
    }

    /**
     * @param  iterable<Animal>  $animals
     */
    protected function makeBookingFor(User $user, iterable $animals = [], array $attributes = []): Booking
    {
        $booking = Booking::factory()->create(array_merge(
            ['userID' => $user->id],
            $attributes
        ));

        foreach (collect($animals) as $animal) {
            $booking->animalBookings()->create(['animalID' => $animal->id]);
        }

        return $booking->fresh();
    }

    /**
     * @param  iterable<Animal>  $animals
     */
    protected function makeVisitListFor(User $user, iterable $animals = []): VisitList
    {
        $visitList = VisitList::factory()->create(['userID' => $user->id]);

        $animalIds = collect($animals)->pluck('id')->all();
        if (! empty($animalIds)) {
            $visitList->animals()->attach($animalIds);
        }

        return $visitList->fresh();
    }
}
