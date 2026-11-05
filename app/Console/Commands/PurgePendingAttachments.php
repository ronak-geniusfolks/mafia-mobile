<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Attachment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PurgePendingAttachments extends Command
{
    protected $signature = 'attachments:purge-pending {--hours=48 : Delete pending uploads older than this many hours}';

    protected $description = 'Delete abandoned pending (unlinked) document uploads and their files';

    public function handle(): int
    {
        $hours  = (int) $this->option('hours');
        $cutoff = now()->subHours($hours);

        $stale = Attachment::stalePending($cutoff)->get();

        $deleted = 0;
        foreach ($stale as $att) {
            $full = public_path($att->file_path);
            if (is_file($full)) {
                @unlink($full);
            }
            $att->delete();
            $deleted++;
        }

        // Remove any now-empty pending directories.
        $pendingRoot = public_path('attachments/pending');
        if (is_dir($pendingRoot)) {
            foreach (File::directories($pendingRoot) as $dir) {
                if (count((array) glob($dir . '/*')) === 0) {
                    @rmdir($dir);
                }
            }
        }

        $this->info("Purged {$deleted} abandoned pending attachment(s) older than {$hours}h.");

        return self::SUCCESS;
    }
}
