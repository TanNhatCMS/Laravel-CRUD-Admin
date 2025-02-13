<?php

namespace Backpack\CRUD\app\Console\Commands;

use Illuminate\Console\Command;


class Setup extends Command
{
    use Traits\PrettyCommandOutput;

    protected $progressBar;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tannhatcms:lms:setup
                                {--timeout=300} : How many seconds to allow each process to run.
                                {--debug} : Show process output or not. Useful for debugging.';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install LMS.';

    /**
     * Execute the console command.
     *
     * @return void Command-line output
     */
    public function handle(): void
    {
        $this->infoBlock('Installing LMS CRUD.', 'Step 1');

        // Publish files
        $this->progressBlock('Installing CRUD.');
        $this->executeArtisanProcess('backpack:install', $this->option('no-interaction') ? ['--no-interaction' => true] : []);
        $this->closeProgressBlock();
        $this->newLine();
    }










    public function themes()
    {
        return collect($this->themes)
            ->map(function ($class) {
                return (object) $class::$addon;
            })->each(function (&$theme) {
                $isInstalled = file_exists($theme->path);
                $theme->status = $isInstalled ? 'installed' : 'not installed';
                $theme->statusColor = $isInstalled ? 'green' : 'yellow';
            });
    }
}
