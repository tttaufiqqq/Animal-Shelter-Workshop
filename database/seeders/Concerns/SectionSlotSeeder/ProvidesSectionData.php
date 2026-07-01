<?php

namespace Database\Seeders\Concerns\SectionSlotSeeder;

trait ProvidesSectionData
{
    private function getSections(): array
    {
        return [
            [
                'name'        => 'Cat Zone',
                'description' => 'Dedicated area for cats with climbing structures and cozy spaces',
                'slots'       => [
                    ['capacity' => 1, 'count' => 24],
                    ['capacity' => 2, 'count' => 6],
                ],
            ],
            [
                'name'        => 'Dog Area',
                'description' => 'Spacious area for dogs with play equipment and exercise space',
                'slots'       => [
                    ['capacity' => 1, 'count' => 36],
                    ['capacity' => 2, 'count' => 4],
                ],
            ],
            [
                'name'        => 'Puppy Nursery',
                'description' => 'Special care area for puppies and young dogs',
                'slots'       => [
                    ['capacity' => 1, 'count' => 5],
                    ['capacity' => 4, 'count' => 7],
                    ['capacity' => 8, 'count' => 3],
                ],
            ],
            [
                'name'        => 'Kitten Corner',
                'description' => 'Warm and safe environment for kittens',
                'slots'       => [
                    ['capacity' => 1, 'count' => 8],
                    ['capacity' => 5, 'count' => 10],
                    ['capacity' => 8, 'count' => 2],
                ],
            ],
            [
                'name'        => 'Medical Ward',
                'description' => 'Quarantine and recovery area for animals receiving treatment',
                'slots'       => [
                    ['capacity' => 1, 'count' => 10],
                ],
            ],
            [
                'name'        => 'Isolation Unit',
                'description' => 'Separate area for animals with contagious conditions',
                'slots'       => [
                    ['capacity' => 1, 'count' => 8],
                ],
            ],
        ];
    }
}
