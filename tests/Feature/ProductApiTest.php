<?php

use App\Models\Product;
use function Pest\Laravel\getJson;
use function Pest\Laravel\putJson;

// Teste do Endpoint Raiz (Health Check)
it('returns api details and status ok', function () {
    getJson('/api')
        ->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'database_connection',
            'uptime',
            'memory_usage'
        ]);
});

// Teste de Listagem (GET /products)
it('can list products with pagination', function () {
    // Cria 10 produtos fake no banco para testar
    Product::factory()->count(10)->create();

    getJson('/api/products')
        ->assertStatus(200)
        ->assertJsonStructure([
            'current_page',
            'data',
            'total'
        ]);
});

// Teste de Detalhe (GET /products/{code})
it('can show a specific product', function () {
    // Gera um código único para não bater com dados antigos do banco
    $uniqueCode = (string) rand(1000000, 9999999);
    
    $product = Product::factory()->create(['code' => $uniqueCode]);

    getJson("/api/products/{$uniqueCode}")
        ->assertStatus(200)
        ->assertJson([
            'code' => $uniqueCode,
            'product_name' => $product->product_name
        ]);
});

// Teste de Atualização (PUT /products/{code}) - Diferencial 5
it('can update a product', function () {
    // Gera um código único para este teste
    $uniqueCode = (string) rand(1000000, 9999999);

    // Cria o produto inicial no banco
    $product = Product::factory()->create([
        'code' => $uniqueCode, 
        'status' => 'draft'
    ]);

    // Envia a requisição de atualização
    putJson("/api/products/{$uniqueCode}", [
        'status' => 'published',
        'product_name' => 'Nome Editado'
    ])
    ->assertStatus(200)
    ->assertJsonFragment([
        'message' => 'Product updated'
    ]);

    // Verifica no banco se o status realmente mudou de 'draft' para 'published'
    expect(Product::where('code', $uniqueCode)->first()->status)->toBe('published');
});

// Teste de 404
it('returns 404 for invalid product', function () {
    getJson('/api/products/999999999')
        ->assertStatus(404);
});