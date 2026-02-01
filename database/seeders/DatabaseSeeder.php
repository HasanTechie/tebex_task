<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Seller;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Seller::factory()->count(10)->create();
        Customer::factory()->count(10)->create();
    }
}
