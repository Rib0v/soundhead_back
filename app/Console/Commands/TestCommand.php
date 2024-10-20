<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use NunoMaduro\Collision\Adapters\Laravel\Commands\TestCommand as BaseTestCommand;

/**
 * Расширяет базовую команду test,
 * создавая перед началом тестовую БД,
 * если её ещё не существует
 */
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

        Artisan::call('make:testdb');

        $this->info('Database created. Running tests...');
    }
}
