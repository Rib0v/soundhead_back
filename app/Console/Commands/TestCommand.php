<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use NunoMaduro\Collision\Adapters\Laravel\Commands\TestCommand as BaseTestCommand;

class TestCommand extends BaseTestCommand
{
    public function handle()
    {
        $this->createTestDatabaseIfNotExists();
        parent::handle();
    }

    protected function createTestDatabaseIfNotExists(): void
    {
        if (file_exists(database_path('database-test.sqlite'))) {
            return;
        }

        $this->info('Creating test database...');

        \Artisan::call('make:testdb');

        $this->info('Database created. Running tests...');
    }
}
