<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Factories\UsuarioFactory;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        UsuarioFactory::factory(1)->create();
    }
}
