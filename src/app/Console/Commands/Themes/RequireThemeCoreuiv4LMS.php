<?php

namespace Backpack\CRUD\app\Console\Commands\Themes;

use Illuminate\Console\Command;

class RequireThemeCoreuiv4LMS extends Command
{
    use InstallsTheme;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tannhatcms:require:theme-coreuiv4-lms
                                {--debug} : Show process output or not. Useful for debugging.';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the CoreUIv4 theme';

    /**
     * Backpack addons install attribute.
     *
     * @var array
     */
    public static array $addon = [
        'name' => 'CoreUIv4LMS',
        'description' => [
            'UI provided by CoreUIv4 LMS, a Bootstrap 5 template.',
            '<fg=blue>https://github.com/TanNhatCMS/theme-coreuiv4-lms/</>',
        ],
        'repo' => 'tannhatcms/theme-coreuiv4-lms',
        'path' => 'vendor/tannhatcms/theme-coreuiv4-lms',
        'command' => 'tannhatcms:require:theme-coreuiv4-lms',
        'view_namespace' => 'tannhatcms.theme-coreuiv4-lms::',
        'publish_tag' => 'theme-coreuiv4-lms-config',
        'provider' => '\TanNhatCMS\ThemeCoreuiv4LMS\AddonServiceProvider',
    ];

    /**
     * Execute the console command.
     *
     * @return void Command-line output
     */
    public function handle(): void
    {
        $this->installTheme();
    }
}
