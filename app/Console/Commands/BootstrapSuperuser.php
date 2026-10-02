<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class BootstrapSuperuser extends Command
{
    protected $signature = 'app:bootstrap-superuser';

    protected $description = 'Create or promote the initial application superuser';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->ask('Superuser email')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('A valid email address is required.');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->first();
        $existingAdmin = User::query()
            ->where('role', 'superuser')
            ->when($user, fn ($query) => $query->where('id', '!=', $user->id))
            ->exists();

        if ($existingAdmin) {
            $this->error('A superuser already exists.');

            return self::FAILURE;
        }

        if ($user) {
            if (!$this->confirm("Promote {$user->name} {$user->lastname} ({$email}) to superuser?")) {
                return self::SUCCESS;
            }
        } else {
            $name = $this->ask('First name');
            $lastname = $this->ask('Last name');
            $password = $this->secret('Password (minimum 8 characters)');
            $confirmation = $this->secret('Confirm password');

            if (strlen((string) $password) < 8 || $password !== $confirmation) {
                $this->error('Passwords must match and contain at least 8 characters.');

                return self::FAILURE;
            }

            $user = new User([
                'name' => $name,
                'lastname' => $lastname,
                'email' => $email,
                'password' => Hash::make($password),
            ]);
        }

        $user->forceFill([
            'role' => 'superuser',
            'building_quota' => 0,
            'is_active' => true,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        $this->info('Superuser account is ready.');

        return self::SUCCESS;
    }
}
