<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\UsernameService;
use Illuminate\Console\Command;

class GenerateUsernames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:generate-usernames';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate usernames for existing users who do not have one';

    /**
     * Execute the console command.
     */
    public function handle(UsernameService $usernameService)
    {
        $users = User::whereNull('username')->orWhere('username', '')->get();
        
        if ($users->isEmpty()) {
            $this->info('All users already have usernames.');
            return;
        }
        
        $this->info("Found {$users->count()} users without usernames.");
        
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();
        
        foreach ($users as $user) {
            $username = $usernameService->generateFromName($user->name);
            $user->update(['username' => $username]);
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('Usernames generated successfully!');
    }
}
