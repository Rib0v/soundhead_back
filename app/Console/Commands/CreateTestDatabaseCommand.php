<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class CreateTestDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:testdb {--F|force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates database for tests';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');

        $testDbPath = database_path('database-test.sqlite');

        if (file_exists($testDbPath) && !$force) {
            $this->warn('Тестовая БД уже существует. Используйте флаг --force чтобы пересоздать её заново.');
            return 1;
        }

        $this->createDb($testDbPath);

        $this->info('Тестовая БД успешно создана.');
        return 0;
    }

    protected function createDb(string $testDbPath): void
    {
        if (file_exists($testDbPath)) {
            unlink($testDbPath);
        }

        touch($testDbPath);

        config()->set('cache.enabled', false);
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $testDbPath);
        DB::reconnect();
        Artisan::call('migrate --seed');
    }
}
