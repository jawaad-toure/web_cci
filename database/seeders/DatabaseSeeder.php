<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Repositories\Repository;
// use App\Repositories\Data;

class DatabaseSeeder extends Seeder {
    /**
     * Seed the application's database.
     */
    public function run(): void  {
        touch("database/database.sqlite");
        $repository = new Repository();        
        $repository->createDatabase();
        $repository->fillDatabase();
        $repository->updateRanking();
        $repository->addUser('user@example.com', 'secret');
    }
}
