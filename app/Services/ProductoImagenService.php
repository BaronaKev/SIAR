<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Responsabilidad única: guardar y borrar las imágenes del menú en el disco "public".
 */
final class ProductoImagenService
{
    private const DISCO = 'public';
    private const CARPETA = 'productos';

    public function guardar(UploadedFile $archivo): string
    {
        return $archivo->store(self::CARPETA, self::DISCO);
    }

    public function reemplazar(?string $rutaActual, UploadedFile $nuevo): string
    {
        $this->eliminar($rutaActual);

        return $this->guardar($nuevo);
    }

    public function eliminar(?string $ruta): void
    {
        if ($ruta !== null && Storage::disk(self::DISCO)->exists($ruta)) {
            Storage::disk(self::DISCO)->delete($ruta);
        }
    }
}
