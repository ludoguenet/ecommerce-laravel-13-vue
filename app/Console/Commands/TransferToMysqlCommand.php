<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Pdo\Mysql;

#[Signature('db:transfer-to-mysql {--database=laravel_coffee : Target MySQL database name}')]
#[Description('Transfer all tables and data from SQLite (database/database.sqlite) to MySQL')]
class TransferToMysqlCommand extends Command
{
    public function handle(): int
    {
        $database = (string) $this->option('database');

        $this->registerConnections($database);

        if (! $this->testServerConnection()) {
            $this->error('Cannot connect to MySQL server. Check DB_HOST, DB_PORT, DB_USERNAME, and DB_PASSWORD.');

            return self::FAILURE;
        }

        $this->ensureDatabaseExists($database);

        $this->info("Running migrations on MySQL ({$database})...");
        Artisan::call('migrate', ['--database' => 'mysql_target', '--force' => true], $this->output);

        $tables = $this->getSqliteTables();

        if (empty($tables)) {
            $this->warn('No tables found in SQLite database.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Transferring %d table(s)...', count($tables)));
        $this->newLine();

        DB::connection('mysql_target')->statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($tables as $table) {
            $this->transferTable($table);
        }

        DB::connection('mysql_target')->statement('SET FOREIGN_KEY_CHECKS=1');

        $this->newLine();
        $this->info("Transfer complete. All SQLite data has been copied to {$database}.");

        return self::SUCCESS;
    }

    private function registerConnections(string $database): void
    {
        $sharedOptions = extension_loaded('pdo_mysql') ? array_filter([
            (PHP_VERSION_ID >= 80500 ? Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA) => env('MYSQL_ATTR_SSL_CA'),
        ]) : [];

        $base = [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => $sharedOptions,
        ];

        Config::set('database.connections.mysql_server', array_merge($base, ['database' => '']));
        Config::set('database.connections.mysql_target', array_merge($base, ['database' => $database]));
    }

    private function testServerConnection(): bool
    {
        try {
            DB::connection('mysql_server')->getPdo();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function ensureDatabaseExists(string $database): void
    {
        DB::connection('mysql_server')->statement(
            "CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );

        $this->line("  Database <fg=green>{$database}</> is ready.");
    }

    private function sqliteConnection(): Connection
    {
        Config::set('database.connections.sqlite_source', [
            'driver' => 'sqlite',
            'database' => database_path('database.sqlite'),
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);

        return DB::connection('sqlite_source');
    }

    /**
     * @return string[]
     */
    private function getSqliteTables(): array
    {
        $rows = $this->sqliteConnection()
            ->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

        return array_column(array_map(fn ($r) => (array) $r, $rows), 'name');
    }

    private function transferTable(string $table): void
    {
        $total = $this->sqliteConnection()->table($table)->count();

        $this->components->twoColumnDetail("<fg=cyan>{$table}</>", "{$total} row(s)");

        DB::connection('mysql_target')->table($table)->truncate();

        $this->sqliteConnection()->table($table)->orderBy(DB::raw('rowid'))->chunk(500, function ($rows) use ($table): void {
            DB::connection('mysql_target')->table($table)->insert(
                $rows->map(fn ($row) => (array) $row)->toArray()
            );
        });
    }
}
