<?php

namespace App\Services;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProductImporterService
{
    public function importProduct(array $data): void
    {
        if (empty($data['code'])) {
            return;
        }

        $code = trim($data['code'], '"');

        Product::updateOrCreate(
            ['code' => $code],
            array_merge($data, [
                'code' => $code,
                'imported_t' => Carbon::now(),
                'status' => 'published',
            ])
        );
    }

    /**
     * Lê um arquivo .gz remoto linha a linha e importa os primeiros 100 produtos.
     */
    public function importFromUrl(string $url): int
    {
        $count = 0;
        $maxImports = 100;

        // O wrapper compress.zlib:// permite ler .gz como arquivo normal
        $handle = fopen("compress.zlib://$url", "r");

        if (!$handle) {
            Log::error("Failed to open stream: $url");
            return 0;
        }

        while (($line = fgets($handle)) !== false) {
            if ($count >= $maxImports) break;

            $data = json_decode($line, true);
            
            if ($data) {
                $this->importProduct($data);
                $count++;
            }
        }

        fclose($handle);
        return $count;
    }
}