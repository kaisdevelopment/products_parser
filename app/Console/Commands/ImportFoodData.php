<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ProductImporterService;
use App\Models\ImportHistory;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ImportFoodData extends Command
{
    protected $signature = 'food:import';
    protected $description = 'Downloads and imports data from Open Food Facts';

    public function handle(ProductImporterService $importer)
    {
        $this->info('Starting Import Process...');
        
        // Atualiza cache para o endpoint de status da API
        cache()->put('last_cron_run', Carbon::now()->toDateTimeString());

        $indexUrl = 'https://challenges.coode.sh/food/data/json/index.txt';
        $baseUrl = 'https://challenges.coode.sh/food/data/json/';

        // Obtém lista de arquivos
        $response = Http::get($indexUrl);
        
        if ($response->failed()) {
            $this->error('Failed to fetch file list.');
            return;
        }

        $files = array_filter(explode("\n", $response->body()));

        foreach ($files as $filename) {
            $filename = trim($filename);
            if (empty($filename)) continue;

            // Verifica se já foi importado
            if (ImportHistory::where('filename', $filename)->where('status', 'success')->exists()) {
                $this->info("Skipping $filename (Already imported)");
                continue;
            }

            $this->info("Processing $filename...");
            
            try {
                $count = $importer->importFromUrl($baseUrl . $filename);
                
                // Registra no histórico
                ImportHistory::create([
                    'filename' => $filename,
                    'processed_at' => Carbon::now(),
                    'products_count' => $count,
                    'status' => 'success'
                ]);

                $this->info("Imported $count products from $filename");

            } catch (\Exception $e) {
                $this->error("Error importing $filename: " . $e->getMessage());
                
                ImportHistory::create([
                    'filename' => $filename,
                    'processed_at' => Carbon::now(),
                    'products_count' => 0,
                    'status' => 'failed'
                ]);
            }
        }

        $this->info('All tasks finished.');
    }
}