<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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

        if (file_exists($testDbPath)) {
            if (!$force) {
                $this->warn('Тестовая БД уже существует. Используйте флаг --force чтобы пересоздать её заново.');
                return;
            };

            unlink($testDbPath);
        }

        touch($testDbPath);

        config()->set('cache.enabled', false);
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $testDbPath);
        \DB::reconnect();
        \Artisan::call('migrate --seed');

        $this->info('Тестовая БД успешно создана.');
    }
}
