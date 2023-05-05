<?php

namespace Backpack\CRUD\app\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeTemporaryFolder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backpack:purge-temporary-folder {--older-than=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes files from temporary folder older than X hours';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $temporaryDisk = config('backpack.base.temporary_disk');
        $temporaryFolder = config('backpack.base.temporary_folder');
        $purgeFilesOlderThan = $this->option('older-than') ?? config('backpack.base.purge_temporary_files_older_than');
        collect(Storage::disk($temporaryDisk)->listContents($temporaryFolder, true))
        ->each(function ($file) use ($temporaryDisk, $purgeFilesOlderThan) {
            if ($file['type'] == 'file' && $file['timestamp'] < now()->subHours($purgeFilesOlderThan)->getTimestamp()) {
                Storage::disk($temporaryDisk)->delete($file['path']);
            }
        });
    }
}
