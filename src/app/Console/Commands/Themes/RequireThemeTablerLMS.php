<?php

namespace Backpack\CRUD\app\Console\Commands\Themes;

use Illuminate\Console\Command;

class RequireThemeTablerLMS extends Command
{
    use InstallsTheme;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tannhatcms:require:theme-tabler-lms
                                {--debug} : Show process output or not. Useful for debugging.';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Tabler theme for LMS CMS.';

    /**
     * Backpack addons install attribute.
     *
     * @var array
     */
    public static array $addon = [
        'name' => 'Tabler <fg=green>(default)</>',
        'description' => [
            'UI provided by Tabler, a Bootstrap 5 template. Lots of new features, including a dark mode.',
            '<fg=blue>https://github.com/TanNhatCMS/theme-tabler-lms</>',
        ],
        'repo' => 'tannhatcms/theme-tabler-lms',
        'path' => 'vendor/tannhatcms/theme-tabler-lms',
        'command' => 'tannhatcms:require:theme-tabler-lms',
        'view_namespace' => 'tannhatcms.theme-tabler-lms::',
        'publish_tag' => 'theme-tabler-lms-config',
        'provider' => '\TanNhatCMS\ThemeTablerLMS\AddonServiceProvider',
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
