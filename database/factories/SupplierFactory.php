<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'bank_name' => $this->faker->company(),
            'account_number' => $this->faker->bankAccountNumber(),
            'description' => $this->faker->sentence(),
            'active' => $this->faker->boolean(90), // 90% chance of being active
        ];
    }
}
