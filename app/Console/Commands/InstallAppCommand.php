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

    protected array $options;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $envFile = base_path('.env');
        $createEnv = true;
        
        if (file_exists($envFile)) {
            $createEnv = $this->confirm('.env file already exists, do you want to override it?');
        }

        if($createEnv) {
            $database = $this->choice(
                'Which database do you want to use? (Default: SQLite)',
                [
                    'SQLite', 
                ],
                0 // Defaults to SQLite
            );

            $envExample = match ($database) {
                'SQLite' => base_path('.env.sqlite.example'),
                'MySQL' => base_path('.env.mysql.example')
            };

            copy($envExample, $envFile);
            $this->info('.env file created');
            $this->call('key:generate', [
                '--force' => true,
            ]);
        }

        // Ask user for app details
        $appName = $this->ask('What is your application name?');
        $appUrl  = $this->ask('What is your application URL? (e.g. https://morfibase.com)');

        // Update .env file
        $envContent = file_get_contents($envFile);

        // Replace APP_NAME
        $envContent = preg_replace(
            '/^APP_NAME=.*/m',
            'APP_NAME="'.addslashes($appName).'"',
            $envContent
        );

        // Replace APP_URL
        $envContent = preg_replace(
            '/^APP_URL=.*/m',
            'APP_URL='.$appUrl,
            $envContent
        );

        // Save changes
        file_put_contents($envFile, $envContent);

        $this->info('.env file updated with APP_NAME and APP_URL.');

        // Create db
        $dbPath = database_path('database.sqlite');

        // Create the SQLite file if it doesn't exist
        if (!file_exists($dbPath)) {
            touch($dbPath);
            chmod($dbPath, 0600);
            $this->info('SQLite database created');
        } else {
            $this->warn("\n\nSQLite database already exists, skipping");
        }

        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->exec('PRAGMA journal_mode=WAL;');
        $pdo->exec('PRAGMA synchronous=NORMAL;');
        $pdo->exec('PRAGMA foreign_keys=ON;');
        $pdo->exec('PRAGMA busy_timeout=5000;');

        // Run migrations
        $this->call('migrate', [
            '--force' => true,
        ]);
        $this->info('Database migrated successfully.');

        // Create user
        $this->info("\n\nCreate your first account.");

        $name = $this->ask('Name');
        $email = $this->ask('Email');
        $password = $this->secret('Password');

        $this->call('make:filament-user', [
            '--name' => $name,
            '--email' => $email,
            '--password' => $password
        ]);
    }
}
