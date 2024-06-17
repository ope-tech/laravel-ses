<?php

namespace OpeTech\LaravelSes\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use OpeTech\LaravelSes\Models\LaravelSesSentEmail;

/**
 * @template TModel of \App\LaravelSesSentEmail
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<TModel>
 */
class LaravelSesSentEmailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected $model = LaravelSesSentEmail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => $this->faker->email,
            'message_id' => '00000138111222aa-33322211-cccc-cccc-cccc-ddddaaaa0680-'.rand(100000, 999999),
            'sent_at' => now(),
        ];

    }
}
