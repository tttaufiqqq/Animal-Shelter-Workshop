<?php

// Auto-loaded by Pest for every suite (see vendor/pestphp/pest/src/Bootstrappers/BootFiles.php —
// tests/Helpers.php is one of the fixed filenames Pest includes before running any test).

use App\Http\Controllers\Concerns\AnimalManagement\CalculatesMatchScore;

function matchScorer()
{
    return new class {
        use CalculatesMatchScore;
    };
}

function adopterProfile(array $overrides = [])
{
    return (object) array_merge([
        'preferred_species' => 'dog',
        'preferred_size' => 'medium',
        'activity_level' => 'medium',
        'has_children' => false,
        'has_other_pets' => false,
        'housing_type' => 'condo',
    ], $overrides);
}

function animalProfile(array $overrides = [])
{
    return (object) array_merge([
        'size' => 'medium',
        'energy_level' => 'medium',
        'good_with_kids' => false,
        'good_with_pets' => false,
    ], $overrides);
}

function animalStub(array $overrides = [])
{
    return (object) array_merge([
        'species' => 'dog',
    ], $overrides);
}
