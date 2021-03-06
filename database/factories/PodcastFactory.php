<?php

use Faker\Generator as Faker;

$factory->define(App\Podcast::class, function (Faker $faker) {
    return [
        'title' => ucwords(implode(' ',$faker->words)),
        'description' => $faker->text,
        'image_url' => $faker->imageUrl(),
    ];
});