<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\PortfolioTimeSeeder;

class SeedPortfolioTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed-portfolio-time';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed portfolio data and time management settings for the primary organization';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Seeding Portfolio and Time Management data...');

        $seeder = new PortfolioTimeSeeder();
        $seeder->run();

        $this->success('Portfolio and Time Management seeded successfully!');
    }

    /**
     * Print success message in a nice format.
     */
    protected function success($message)
    {
        $this->line("<info>SUCCESS:</info> {$message}");
    }
}
