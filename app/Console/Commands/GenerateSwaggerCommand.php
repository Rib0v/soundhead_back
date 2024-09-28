<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateSwaggerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swagger:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate swagger docs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $openApi = \OpenApi\Generator::scan($this->getPathList());
        $res = file_put_contents($this->getYamlFilePath(), $openApi->toYaml());

        $this->showResultMessage($res);
    }

    private function getPathList(): array
    {
        return array_map(fn($path) => base_path($path), config('swagger.dirs4scan'));
    }

    private function getYamlFilePath(): string
    {
        return base_path(config('swagger.yaml'));
    }

    private function showResultMessage($result): void
    {
        if ($result) {
            $this->info('Generated');
        } else {
            $this->warn('Fail');
        }
    }
}
