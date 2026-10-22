<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ExportMysqlSchema extends Command
{
    protected $signature = 'db:export-mysql-schema {--path=sparxbit-mysql-schema.sql}';

    protected $description = 'Generate a MySQL schema .sql from the migrations (no DB connection needed)';

    public function handle(): int
    {
        // A MySQL connection used only to compile DDL — pretend() never executes
        // a real query, so no live MySQL server is required.
        config(['database.connections.mysql_export' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'sparx_bit',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => 'InnoDB',
        ]]);

        $previous = config('database.default');
        DB::setDefaultConnection('mysql_export');
        $conn = DB::connection('mysql_export');

        // Give it a harmless in-memory PDO so "pretend" never reaches a real
        // MySQL server (data inserts would otherwise force a connection).
        $dummy = new \PDO('sqlite::memory:');
        $conn->setPdo($dummy)->setReadPdo($dummy);

        $files = collect(File::files(database_path('migrations')))
            ->sortBy(fn ($f) => $f->getFilename());

        $statements = [];
        foreach ($files as $file) {
            $migration = require $file->getPathname();
            $queries = $conn->pretend(function () use ($migration) {
                $migration->up();
            });
            foreach ($queries as $q) {
                $statements[] = $q['query'];
            }
        }

        DB::setDefaultConnection($previous);

        $out = "-- Sparx Bit — MySQL schema\n";
        $out .= "-- Generated from Laravel migrations on ".now()->toDateTimeString()."\n";
        $out .= "-- Import into an empty `sparx_bit` database (schema only, no data).\n\n";
        $out .= "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n";

        // Laravel's own migrations table, so the app treats the DB as migrated.
        $out .= "create table `migrations` (`id` int unsigned not null auto_increment primary key, `migration` varchar(255) not null, `batch` int not null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;\n";

        foreach ($statements as $s) {
            $out .= rtrim($s, ';').";\n";
        }

        // Record every migration as run (batch 1).
        $out .= "\n-- Mark migrations as run\n";
        foreach ($files as $file) {
            $name = str_replace('.php', '', $file->getFilename());
            $out .= "insert into `migrations` (`migration`, `batch`) values ('".addslashes($name)."', 1);\n";
        }

        $out .= "\nSET FOREIGN_KEY_CHECKS = 1;\n";

        $path = base_path($this->option('path'));
        File::put($path, $out);

        $this->info('Wrote '.count($statements).' statements to '.$path);

        return self::SUCCESS;
    }
}
