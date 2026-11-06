<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Seeder;

/**
 * Categorías y productos de ejemplo (precios en COP).
 */
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menu = [
            'Entradas' => [
                ['nombre' => 'Empanadas vallunas (3 und.)', 'precio' => 9000,  'stock' => 40],
                ['nombre' => 'Patacón con hogao',           'precio' => 12000, 'stock' => 30],
            ],
            'Platos fuertes' => [
                ['nombre' => 'Bandeja paisa',      'precio' => 32000, 'stock' => 20],
                ['nombre' => 'Sancocho de gallina', 'precio' => 28000, 'stock' => 15],
                ['nombre' => 'Arroz atollado',      'precio' => 26000, 'stock' => 15],
            ],
            'Bebidas' => [
                ['nombre' => 'Lulada',                'precio' => 8000, 'stock' => 50],
                ['nombre' => 'Jugo natural en agua',  'precio' => 6000, 'stock' => 50],
                ['nombre' => 'Gaseosa 400 ml',        'precio' => 5000, 'stock' => 60],
            ],
            'Postres' => [
                ['nombre' => 'Cholado',          'precio' => 10000, 'stock' => 20],
                ['nombre' => 'Arroz con leche',  'precio' => 7000,  'stock' => 0],
            ],
        ];

        foreach ($menu as $nombreCategoria => $productos) {
            $categoria = Categoria::firstOrCreate(['nombre' => $nombreCategoria]);

            foreach ($productos as $producto) {
                Producto::withTrashed()->updateOrCreate(
                    ['nombre' => $producto['nombre']],
                    [
                        'categoria_id' => $categoria->id,
                        'precio'       => $producto['precio'],
                        'stock'        => $producto['stock'],
                        'estado'       => $producto['stock'] > 0 ? 'Disponible' : 'Agotado',
                    ]
                );
            }
        }
    }
}
