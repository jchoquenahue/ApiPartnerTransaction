<?php

namespace Database\Seeders;

use App\Models\Logg;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call('UsersTableSeeder');
        Logg::factory(20)->create();
    }
}
