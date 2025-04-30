<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ResetDailyAttempts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:reset-daily-attempts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Réinitialise les compteurs de tentatives quotidiennes pour toutes les commandes';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Réinitialisation des tentatives quotidiennes...');
        
        try {
            // Récupérer les commandes non finalisées
            $orders = Order::whereNotIn('status', ['delivered', 'cancelled', 'returned'])
                ->where('daily_attempt_count', '>', 0)
                ->get();
            
            $count = 0;
            
            foreach ($orders as $order) {
                $order->daily_attempt_count = 0;
                $order->save();
                $count++;
            }
            
            $this->info("$count commandes ont été réinitialisées avec succès.");
            Log::info("Réinitialisation des tentatives quotidiennes: $count commandes traitées");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Une erreur est survenue: ' . $e->getMessage());
            Log::error('Erreur lors de la réinitialisation des tentatives quotidiennes: ' . $e->getMessage());
            
            return Command::FAILURE;
        }
    }
}