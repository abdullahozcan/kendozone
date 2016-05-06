<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

use App\Association;
use App\Category;
use App\CategoryTournament;
use App\Federation;
use App\Tournament;
use App\TournamentLevel;
use App\User;
use Webpatser\Countries\Countries;

$factory->define(App\Federation::class, function (Faker\Generator $faker) {
    $countries = Countries::all()->pluck('id')->toArray();
    $users = User::all()->pluck('id')->toArray();

    return [
        'name' => $faker->name,
        'president_id' => $faker->randomElement($users),
        'address' => $faker->address,
        'phone' => $faker->phoneNumber,
        'country_id' => $faker->randomElement($countries),
    ];
});

$factory->define(App\Association::class, function (Faker\Generator $faker) {
    $users = User::all()->pluck('id')->toArray();
    $federations = Federation::all()->pluck('id')->toArray();

    return [
        'name' => $faker->name,
        'federation_id' => $faker->randomElement($federations),
        'president_id' => $faker->randomElement($users),
        'address' => $faker->address,
        'phone' => $faker->phoneNumber,
    ];
});

$factory->define(App\Club::class, function (Faker\Generator $faker) {
    $users = User::all()->pluck('id')->toArray();
    $associations = Association::all()->pluck('id')->toArray();

    return [
        'name' => $faker->name,
        'association_id' => $faker->randomElement($associations),
        'president_id' => $faker->randomElement($users),
        'address' => $faker->address,
        'phone' => $faker->phoneNumber,
    ];
});


$factory->define(App\User::class, function (Faker\Generator $faker) {
    $countries = Countries::all()->pluck('id')->toArray();

    return [
        'name' => $faker->name,
        'firstname' => $faker->firstName,
        'lastname' => $faker->lastName,
        'email' => $faker->email,
        'password' => bcrypt(str_random(10)),
        'grade_id' => $faker->numberBetween(1, 5),
        'country_id' => $faker->randomElement($countries),
        'city' => $faker->city,
        'latitude' => $faker->latitude,
        'longitude' => $faker->longitude,
        'role_id' => $faker->numberBetween(1, 5),
        'verified' => true,
        'remember_token' => str_random(10),
        'provider' => '',
        'provider_id' => str_random(5)

    ];
});


$factory->define(App\Tournament::class, function (Faker\Generator $faker) {
    $users = User::all()->pluck('id')->toArray();
    $levels = TournamentLevel::all()->pluck('id')->toArray();
    $dateIni = $faker->dateTimeBetween('now', '+2 weeks')->format('Y-m-d');
    return [
        'user_id' => $faker->randomElement($users),
        'name' => $faker->colorName,
        'dateIni' => $dateIni,
        'dateFin' => $dateIni,
        'registerDateLimit' => $dateIni,
        'sport' => 1,
        'type' => $faker->boolean(),
        'mustPay' => $faker->boolean(),
        'venue' => $faker->address,
        'latitude' => $faker->latitude,
        'longitude' => $faker->longitude,
        'level_id' => $faker->randomElement($levels),
    ];
});


$factory->define(App\CategoryTournament::class, function (Faker\Generator $faker) {
    $tournaments = Tournament::all()->pluck('id')->toArray();
    $categories = Category::all()->pluck('id')->toArray();

    return [
        'tournament_id' => $faker->randomElement($tournaments),
        'category_id' => $faker->randomElement($categories),
    ];
});
$factory->define(App\CategoryTournamentUser::class, function (Faker\Generator $faker) {
    $tcs = CategoryTournament::all()->pluck('id')->toArray();
    $users = User::all()->pluck('id')->toArray();

    return [
        'category_tournament_id' => $faker->randomElement($tcs),
        'user_id' => $faker->randomElement($users),
        'confirmed' => $faker->numberBetween(0, 1),
    ];
});


$factory->define(App\CategorySettings::class, function (Faker\Generator $faker) use ($factory) {
    $tcs = CategoryTournament::all()->pluck('id')->toArray();

    return [
        'category_tournament_id' => $faker->randomElement($tcs),
        'isTeam' => $faker->boolean(),
        'teamSize' => $faker->numberBetween(0, 6),
        'fightingAreas' => $faker->numberBetween(0, 4),
        'fightDuration' => "03:00",
        'hasRoundRobin' => $faker->boolean(),
        'roundRobinWinner' => $faker->numberBetween(1, 2),
        'hasEncho' => $faker->boolean(),
        'enchoQty' => $faker->numberBetween(0, 4),
        'enchoDuration' => "01:00",
        'hasHantei' => $faker->boolean(),
        'cost' => $faker->numberBetween(0, 100),
    ];
});

