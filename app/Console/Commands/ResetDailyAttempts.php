<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class ResetDailyAttempts extends Command
{
    protected $signature = 'orders:reset-daily-attempts';
    protected $description = 'Reset daily attempts count for all orders';

    public function handle()
    {
        Order::resetDailyAttempts();
        $this->info('Daily attempts reset successfully.');
        
        return Command::SUCCESS;
    }
}