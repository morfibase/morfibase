<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PDO;

class InstallAppCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'morfibase:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs the app.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dbPath = database_path('database.sqlite');

        // Create the SQLite file if it doesn't exist
        if (!file_exists($dbPath)) {
            touch($dbPath);
            chmod($dbPath, 0600);
        }

        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->exec('PRAGMA journal_mode=WAL;');
        $pdo->exec('PRAGMA synchronous=NORMAL;');
        $pdo->exec('PRAGMA foreign_keys=ON;');
        $pdo->exec('PRAGMA busy_timeout=5000;');
    }
}
