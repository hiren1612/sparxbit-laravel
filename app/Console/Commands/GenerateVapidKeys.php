<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeys extends Command
{
    protected $signature = 'webpush:vapid {--show : Only print, do not write to .env}';

    protected $description = 'Generate a VAPID key pair for browser Web Push and write it to .env';

    public function handle(): int
    {
        $keys = VAPID::createVapidKeys();
        $public = $keys['publicKey'];
        $private = $keys['privateKey'];

        $this->newLine();
        $this->line('<info>VAPID_PUBLIC_KEY</info>  = '.$public);
        $this->line('<info>VAPID_PRIVATE_KEY</info> = '.$private);
        $this->newLine();

        if ($this->option('show')) {
            $this->comment('Copy these two lines into your .env (not committed).');

            return self::SUCCESS;
        }

        $path = base_path('.env');
        if (! is_file($path)) {
            $this->error('.env not found — copy the two lines above in manually.');

            return self::FAILURE;
        }

        $env = file_get_contents($path);
        $env = $this->setEnv($env, 'VAPID_PUBLIC_KEY', $public);
        $env = $this->setEnv($env, 'VAPID_PRIVATE_KEY', $private);
        file_put_contents($path, $env);

        $this->info('Wrote VAPID_PUBLIC_KEY and VAPID_PRIVATE_KEY to .env.');
        $this->comment('Run "php artisan config:clear" and make sure the same keys are set on the server.');

        return self::SUCCESS;
    }

    private function setEnv(string $env, string $key, string $value): string
    {
        $line = $key.'="'.$value.'"';
        if (preg_match('/^'.preg_quote($key, '/').'=.*$/m', $env)) {
            return preg_replace('/^'.preg_quote($key, '/').'=.*$/m', $line, $env);
        }

        return rtrim($env, "\n")."\n".$line."\n";
    }
}
