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
    $product = Product::factory()->create(['code' => '12345']);

    getJson('/api/products/12345')
        ->assertStatus(200)
        ->assertJson([
            'code' => '12345',
            'product_name' => $product->product_name
        ]);
});

// Teste de Atualização (PUT /products/{code}) - Diferencial 5
it('can update a product', function () {
    $product = Product::factory()->create(['code' => '67890', 'status' => 'draft']);

    putJson('/api/products/67890', [
        'status' => 'published',
        'product_name' => 'Nome Editado'
    ])
    ->assertStatus(200)
    ->assertJsonFragment([
        'message' => 'Product updated'
    ]);

    // Verifica no banco se mudou mesmo
    expect(Product::where('code', '67890')->first()->status)->toBe('published');
});

// Teste de 404
it('returns 404 for invalid product', function () {
    getJson('/api/products/999999999')
        ->assertStatus(404);
});