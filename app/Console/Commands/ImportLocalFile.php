<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ProductImporterService;
use Illuminate\Support\Facades\File;

class ImportLocalFile extends Command
{
    protected $signature = 'import:local';
    protected $description = 'Imports products from local json file';

    public function handle(ProductImporterService $importer)
    {
        $path = base_path('products.json');

        if (!File::exists($path)) {
            $this->error("File not found: $path");
            return;
        }

        $this->info("Reading file...");

        $json = File::get($path);
        $products = json_decode($json, true);

        if (!$products) {
            $this->error("Invalid JSON format.");
            return;
        }

        $bar = $this->output->createProgressBar(count($products));
        $bar->start();

        foreach ($products as $productData) {
            $importer->importProduct($productData);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Import finished.");
    }
}