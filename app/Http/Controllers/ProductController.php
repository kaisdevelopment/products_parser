<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    /**
     * GET /
     * Detalhes da API, status da conexão e uso de memória.
     */
    public function apiDetails()
    {
        $dbConnection = 'OK';
        try {
            DB::connection('mongodb')->getMongoClient()->listDatabases();
        } catch (\Exception $e) {
            $dbConnection = 'Error: ' . $e->getMessage();
        }

        return response()->json([
            'status' => 'OK',
            'database_connection' => $dbConnection,
            'uptime' =>  trim(exec('uptime')), // Comando Linux para uptime
            'memory_usage' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
            'last_cron_run' => cache()->get('last_cron_run', 'Never'), // Vamos configurar isso no Cron depois
        ]);
    }

    /**
     * GET /products
     * Lista todos os produtos com paginação.
     */
    public function index(Request $request)
    {
        // Paginação simples (default 15 itens por página)
        $products = Product::paginate($request->get('limit', 15));
        return response()->json($products);
    }

    /**
     * GET /products/{code}
     * Obtém informação de um produto específico.
     */
    public function show(string $code)
    {
        $product = Product::where('code', $code)->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($product);
    }

    /**
     * PUT /products/{code}
     * Atualiza dados do produto.
     */
    public function update(Request $request, string $code)
    {
        $product = Product::where('code', $code)->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        // Atualiza apenas os campos enviados (Patch behavior)
        $product->update($request->all());

        return response()->json(['message' => 'Product updated', 'product' => $product]);
    }

    /**
     * DELETE /products/{code}
     * Muda o status para 'trash' (Soft Delete lógico).
     */
    public function destroy(string $code)
    {
        $product = Product::where('code', $code)->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $product->update(['status' => 'trash']);

        return response()->json(['message' => 'Product moved to trash']);
    }
}