<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'D3mmy',
            'email' => 'dummy@example.com',
            'role' => Role::Admin,
            'active' => true
        ]);

        Customer::factory(10)->create();

        Supplier::factory(10)->create();

        $brands = ['Apple', 'Dell', 'Asus', 'Lenovo'];

        foreach ($brands as $brand) {
            $brand = strtolower($brand);
            Brand::create([
                'name' => $brand,
                'active' => fake()->boolean(),
                'website' => "https://{$brand}.com"
            ]);
        }

        $categories = ['Laptop', 'Smartphone', 'Accessory'];

        foreach ($categories as $cat) {
            Category::create([
                'name' => $cat,
                'active' => fake()->randomElement([true, false]),
                'description' => fake()->sentence(1)
            ]);
        }

        // Customer::factory(10)->create();
    }
}
