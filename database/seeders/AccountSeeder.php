<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Rob valós fiókja — lásd docs/seeder-terv.md 1. pont.
 */
class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $account = Account::firstOrCreate(
            ['slug' => 'coachlab'],
            [
                'name' => 'CoachLab',
                'subscription_tier' => 'premium',
                'locale' => 'hu',
                'timezone' => 'Europe/Budapest',
            ]
        );

        $user = User::firstOrCreate(
            ['email' => 'robert.rado@gmail.com'],
            [
                'account_id' => $account->id,
                'name' => 'Rado Robert',
                'password' => Hash::make('changeme123'),
                'role' => 'owner',
                'is_super_admin' => true,
                'locale' => 'hu',
                'email_verified_at' => now(),
            ]
        );

        if (! $account->owner_user_id) {
            $account->update(['owner_user_id' => $user->id]);
        }
    }
}
