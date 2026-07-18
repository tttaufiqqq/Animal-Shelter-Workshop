<?php

it('scores identical energy levels as a perfect 1.0', function (string $level) {
    expect(matchScorer()->matchEnergyLevel($level, $level))->toBe(1.0);
})->with(['low', 'medium', 'high']);

it('scores adjacent energy levels as 0.6', function (string $activity, string $energy) {
    expect(matchScorer()->matchEnergyLevel($activity, $energy))->toBe(0.6);
})->with([
    'low activity, medium energy' => ['low', 'medium'],
    'medium activity, low energy' => ['medium', 'low'],
    'medium activity, high energy' => ['medium', 'high'],
    'high activity, medium energy' => ['high', 'medium'],
]);

it('scores opposite energy levels (low vs high) as 0.2', function (string $activity, string $energy) {
    expect(matchScorer()->matchEnergyLevel($activity, $energy))->toBe(0.2);
})->with([
    ['low', 'high'],
    ['high', 'low'],
]);

it('defaults an unrecognized level to medium', function () {
    expect(matchScorer()->matchEnergyLevel('unknown', 'medium'))->toBe(1.0)
        ->and(matchScorer()->matchEnergyLevel('unknown', 'high'))->toBe(0.6)
        ->and(matchScorer()->matchEnergyLevel('unknown', 'unknown'))->toBe(1.0);
});
