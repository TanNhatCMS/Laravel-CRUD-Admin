<?php

namespace Backpack\CRUD\app\Console\Commands;

use Composer\InstalledVersions;
use Illuminate\Console\Command;

class Version extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backpack:version';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show the version of PHP and Backpack packages.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('### PHP VERSION:');
        $this->line(phpversion());
        $this->line('');

        $this->comment('### PHP EXTENSIONS:');
        $this->line(implode(', ', get_loaded_extensions()));
        $this->line('');

        $this->comment('### LARAVEL VERSION:');
        $this->line(InstalledVersions::getVersion('laravel/framework'));
        $this->line('');

        $this->comment('### BACKPACK PACKAGE VERSIONS:');
        $packages = InstalledVersions::getInstalledPackages();
        foreach ($packages as $package) {
            if($package == 'backpack/crud') {
                $this->line($package.': '. InstalledVersions::getPrettyVersion('tannhatcms/crud-lms'));
            }
            if (str_starts_with($package, 'backpack/')) {
                $this->line($package.': '. InstalledVersions::getPrettyVersion($package));
            }
        }
    }
}
