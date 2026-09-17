<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncPhHolidays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holidays:sync {year? : Year to fetch holidays for (defaults to current year)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync official Philippine holidays into the LODISv2 events table';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $year = $this->argument('year') ?? date('Y');
        $url = "https://date.nager.at/api/v3/PublicHolidays/{$year}/PH";

        $this->info("Fetching Philippine holidays for year {$year}...");

        $response = Http::timeout(10)->get($url);

        if ($response->failed()) {
            $this->error("Failed to fetch holiday data from endpoint: {$url}");
            return Command::FAILURE;
        }

        $holidays = $response->json();
        $count = 0;

        foreach ($holidays as $holiday) {
            Event::updateOrCreate(
                [
                    'event_date' => $holiday['date'],
                    'title'      => $holiday['localName'] ?? $holiday['name'],
                ],
                [
                    'description'  => $holiday['name'] . ' (Official Public Holiday)',
                    'type'         => 'Holiday',
                    'is_recurring' => false,
                    'is_active'    => true,
                ]
            );
            $count++;
        }

        $this->info("Successfully populated {$count} Philippine holidays for {$year}.");

        return Command::SUCCESS;
    }
}