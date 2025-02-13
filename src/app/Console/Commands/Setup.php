<?php

namespace Backpack\CRUD\app\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;


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
    protected $description = 'Setup LMS.';

    /**
     * Execute the console command.
     *
     * @return void Command-line output
     */
    public function handle(): void
    {
        $this->infoBlock('Setup LMS.', 'Installing Basset');
        // Install Backpack Basset
        $this->progressBlock('Installing Basset');
        $this->executeArtisanProcess('basset:install --no-check --no-interaction');
        $this->closeProgressBlock();
        $this->infoBlock('Setup LMS.', 'Basset Check');
        //execute basset checks
        $this->progressBlock('Basset Check');
        $this->call('basset:check');
        $this->closeProgressBlock();
        // Done
        $url = Str::of(config('app.url'))->finish('/')->append('admin/');
        $this->infoBlock('Backpack installation complete.', 'done');
        $this->note("Go to <fg=blue>$url</> to access your new admin panel.");
        $this->note('You may need to run <fg=blue>php artisan serve</> to serve your Laravel project.');
        $this->newLine();
    }
}
