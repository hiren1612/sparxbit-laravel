<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeAdmin extends Command
{
    protected $signature = 'user:make-admin {email} {--revoke : Remove admin access instead}';

    protected $description = 'Grant (or revoke) admin-panel access for a user by email';

    public function handle(): int
    {
        $user = User::where('email', strtolower(trim($this->argument('email'))))->first();

        if (! $user) {
            $this->error('No user found with that email.');

            return self::FAILURE;
        }

        $user->update(['is_admin' => ! $this->option('revoke')]);

        $this->info(($this->option('revoke') ? 'Revoked admin from ' : 'Granted admin to ').$user->email);

        return self::SUCCESS;
    }
}
