<?php

namespace Database\Seeders;

use App\Models\Mesa;
use Illuminate\Database\Seeder;

class MesaSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Mesa::firstOrCreate(
                ['numero' => (string) $i],
                [
                    'capacidad' => $i <= 6 ? 4 : 6,
                    'ubicacion' => $i <= 6 ? 'Salón principal' : 'Terraza',
                    'estado'    => 'Disponible',
                ]
            );
        }
    }
}
