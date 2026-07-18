<?php

use App\Models\Animal;

it('forbids a guest from writing an animal profile', function () {
    $animal = Animal::factory()->create();

    $response = $this->post("/animal/profile/store/{$animal->id}", [
        'size' => 'small',
        'energy_level' => 'low',
        'good_with_kids' => true,
        'good_with_pets' => true,
        'temperament' => 'calm',
        'medical_needs' => 'none',
    ]);

    $response->assertRedirect(route('login'));
});

it('forbids a guest from writing an adopter profile', function () {
    $response = $this->post('/adopter/profile/store', [
        'housing_type' => 'condo',
        'has_children' => false,
        'has_other_pets' => false,
        'activity_level' => 'low',
        'experience' => 'beginner',
        'preferred_species' => 'cat',
        'preferred_size' => 'any',
    ]);

    $response->assertRedirect(route('login'));
});
