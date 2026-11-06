# 📘 DOCUMENTO MAESTRO DE CONTEXTO Y ESPECIFICACIÓN TÉCNICA — PROYECTO SIAR

> **Sistema Integral de Administración para Restaurantes (SIAR) / Restaurante U**  
> **Asignatura:** Ingeniería de Software II — Sexto Semestre  
> **Institución:** Institución Universitaria Antonio José Camacho (UNIAJC), Cali, Colombia  
> **Equipo:** Kevin Andrés Barona Barreiro, Natalia Solis Hijido, Johan Eduardo Restrepo  
> **Fecha de Actualización:** 03 de Noviembre de 2026

---

## 1. RESUMEN EJECUTIVO Y OBJETIVO
SIAR es un sistema web integral diseñado para modernizar y centralizar la gestión operativa y comercial de un restaurante. Permite administrar usuarios con roles diferenciados (RBAC), el catálogo del menú agrupado por categorías, la disponibilidad de mesas en tiempo real, el flujo completo de pedidos (desde la orden en salón hasta cocina) y el cierre de cuentas con facturación y reportes gerenciales.

### Documentación de Referencia Procesada
1. **`Proyecto de semestre.docx`**: Requerimientos de la cátedra, entregables por corte (1er corte 30%, 2do corte 30%, 3er corte 40%), criterios de evaluación y normas ISO/IEC 25010.
2. **`Restaurante U.docx`**: Documentación técnica detallada con 32 Requerimientos Funcionales (RF001 a RF032), 10 No Funcionales (RNF001 a RNF010), 32 Historias de Usuario, 32 Casos de Uso con diagramas UML, Modelo ER y Arquitectura C4.
3. **`Manual Laravel - CRUD v.2.pdf`**: Guía pedagógica oficial que estipula la creación sistemática de componentes mediante comandos de `php artisan` (`make:model -mcr`, validaciones cliente/servidor, `@csrf`, Blade templates, redirecciones con mensajes flash).
4. **`ProyectoNata/siar-app`**: Prototipo preliminar analizado; se descartó el frontend con Tailwind CSS para adoptar de forma estandarizada **Bootstrap 5.3.3 CDN**.

---

## 2. STACK TECNOLÓGICO Y ENTORNO LOCAL
- **Framework:** Laravel 13.x
- **Lenguaje:** PHP 8.3.x
- **Gestor de Paquetes:** Composer 2.x
- **Base de Datos:** MySQL (XAMPP local, base de datos: `siar`)
- **Ruta del Proyecto en Disco:** `E:\Xampp\htdocs\SIAR`
- **Control de Versiones:** Git & GitHub (flujo colaborativo por ramas `feature/`)
- **Autenticación:** Laravel Breeze (motor Blade sin Tailwind compilado)
- **Control de Acceso (RBAC):** Paquete `spatie/laravel-permission`
- **Frontend:** Blade Templates + **Bootstrap 5.3.3 CDN** + **Bootstrap Icons 1.11.3 CDN**

### CDN Oficial Bootstrap Implementado en `resources/views/layouts/app.blade.php`:
```html
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmH1kGS2V3Q0j/N3kRoVUHFiMUU" crossorigin="anonymous"></script>
```

---

## 3. ARQUITECTURA Y PATRONES DE DISEÑO
1. **MVC (Modelo-Vista-Controlador):** Separación estricta de responsabilidades en Laravel (`app/Models`, `resources/views`, `app/Http/Controllers`).
2. **RBAC (Role-Based Access Control):** Implementado con Spatie Permission. Roles definidos:
   - `administrador`: Control total del sistema.
   - `mesero`: Consulta y reserva de mesas, creación y seguimiento de pedidos.
   - `cocinero`: Visualización de pedidos en preparación y cambio de disponibilidad del menú.
   - `cajero`: Facturación, aplicación de descuentos, registro de pagos y comprobantes.
3. **Patrón State:** Manejo de transiciones para estados de Mesa (`Disponible`, `Ocupada`, `Reservada`, `Mantenimiento`) y de Pedidos (`Pendiente`, `En preparación`, `Listo`, `Entregado`, `Facturado`, `Cancelado`).
4. **Patrón Observer:** Automatización para alerta a cocina cuando un pedido pasa a preparación y descuento de stock.

---

## 4. DIVISIÓN DEL TRABAJO (4 DESARROLLADORES EN PARALELO)

| Desarrollador | Rama Git | Módulos Asignados | Dependencias |
|---|---|---|---|
| **Persona 1 (Kevin)** | `feature/mesas-usuarios` | **Mesas** + **Usuarios & Roles** | Ninguna (Base lista) |
| **Persona 2** | `feature/categorias-productos` | **Categorías** + **Productos (Menú)** | FK de Categoría en Producto |
| **Persona 3** | `feature/empleados-dashboard` | **Empleados** + **Dashboard Analítico** | Relación con Users y conteo de modelos |
| **Persona 4** | `feature/pedidos-facturas` | **Pedidos**, **DetallePedido** + **Facturación** | Depende de Mesas y Productos |

### Orden Obligatorio de Integración (Merge a `main`):
`Persona 1` ➔ `Persona 2` ➔ `Persona 3` ➔ `Persona 4`

---

## 5. ESTADO ACTUAL DE IMPLEMENTACIÓN (FASE 1, 2 Y 3)

### Fase 1 — Configuración Base ✅
- Creación del proyecto en `E:\Xampp\htdocs\SIAR`.
- Configuración de `.env` (`DB_DATABASE=siar`, `DB_USERNAME=root`, `DB_PASSWORD=`).
- Repositorio Git inicializado y sincronizado con GitHub.

### Fase 2 — Autenticación, RBAC y Plantilla Base ✅
- Instalación de Breeze + Spatie Permission.
- Creación y ejecución de `RoleSeeder` con roles y permisos iniciales.
- Modelo `User.php` configurado con el trait `HasRoles`.
- Plantilla maestra responsiva creada en `resources/views/layouts/app.blade.php` con Navbar, Sidebar condicional con directivas `@can` y contenedor para `@yield('content')` y `{{ $slot }}`.
- Vista de login estilizada en Bootstrap en `resources/views/auth/login.blade.php`.

### Fase 3 — Implementación Completa por Kevin (Persona 1) ✅

#### A. Módulo de Mesas:
- **Migración:** `database/migrations/2026_11_03_224100_create_mesas_table.php`
  - Campos: `numero` (string unique), `capacidad` (integer), `ubicacion` (string nullable), `estado` (enum: 'Disponible', 'Ocupada', 'Reservada', 'Mantenimiento').
- **Modelo:** `app/Models/Mesa.php` con `$fillable` y scopes de consulta.
- **Controlador:** `app/Http/Controllers/MesaController.php`
  - Métodos implementados: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`, `cambiarEstado`.
  - Validación del servidor con mensajes en español y reglas de unicidad.
- **Vistas Blade (Bootstrap 5.3.3):**
  - `resources/views/mesas/index.blade.php`: Listado responsivo, buscador por texto, filtro dropdown por estado, badges de colores, paginación y menú de cambio rápido de estado.
  - `resources/views/mesas/create.blade.php`: Formulario de alta con validación visual `is-invalid` y mensajes de error.
  - `resources/views/mesas/edit.blade.php`: Formulario de modificación con método `PUT`.
  - `resources/views/mesas/show.blade.php`: Ficha técnica de la mesa y botones de acción rápida para liberar/ocupar/reservar.

#### B. Módulo de Usuarios y Gestión de Roles:
- **Controlador:** `app/Http/Controllers/UsuarioController.php`
  - Métodos implementados: `index` (con filtro de rol y buscador), `create`, `store` (con `Hash::make` y `assignRole`), `show`, `edit`, `update` (con `syncRoles`), `destroy` (con protección anti-autoeliminación).
- **Vistas Blade (Bootstrap 5.3.3):**
  - `resources/views/usuarios/index.blade.php`: Listado con avatares, badges por rol (Administrador, Mesero, Cocinero, Cajero) y botones de acción.
  - `resources/views/usuarios/create.blade.php`: Formulario de creación con selector dinámico de roles de Spatie.
  - `resources/views/usuarios/edit.blade.php`: Modificación de datos y rol, con opción de conservar o renovar contraseña.
  - `resources/views/usuarios/show.blade.php`: Perfil detallado del usuario con insignia de rol y desglose de todos los permisos asignados.

#### C. Enrutamiento (`routes/web.php`):
```php
Route::middleware('auth')->group(function () {
    // Mesas
    Route::patch('mesas/{mesa}/cambiar-estado', [MesaController::class, 'cambiarEstado'])->name('mesas.cambiarEstado');
    Route::resource('mesas', MesaController::class);

    // Usuarios
    Route::resource('usuarios', UsuarioController::class);
});
```

---

#### D. Módulo de Categorías (Persona 2) ✅:
- **Migración:** `database/migrations/2026_11_03_233000_create_categorias_table.php` (nombre unique, descripción).
- **Modelo:** `app/Models/Categoria.php` con relación `hasMany(Producto::class)`.
- **Controlador:** `app/Http/Controllers/CategoriaController.php` con 7 métodos CRUD y regla CU010 (impide eliminar categorías con productos asociados).
- **Vistas:** `resources/views/categorias/` (`index`, `create`, `edit`, `show`) en Bootstrap 5.3.3.

#### E. Módulo de Productos / Menú (Persona 2) ✅:
- **Migración:** `database/migrations/2026_11_03_233500_create_productos_table.php` (categoria_id, nombre, descripción, precio, stock, estado, imagen).
- **Modelo:** `app/Models/Producto.php` con relación `belongsTo(Categoria::class)` y scope `scopeDisponibles()`.
- **Controlador:** `app/Http/Controllers/ProductoController.php` con CRUD completo, subida de imágenes, auto-cálculo de 'Agotado' si stock=0 (RF011) y acción rápida `cambiarDisponibilidad`.
- **Vistas:** `resources/views/productos/` (`index`, `create`, `edit`, `show`) con filtros por categoría, alertas de stock bajo y badges de disponibilidad.

#### F. Matriz de Pruebas Automatizadas (Feature Tests) ✅:
- **Archivo de Pruebas:** `tests/Feature/SiarModulosTest.php`
- Cobertura: Mesas (crear, cambiar estado), Usuarios (crear con rol RBAC, anti-autoeliminación), Categorías (crear, validar unicidad, proteger borrado con productos), Productos (auto-agotado si stock=0, alternancia de disponibilidad).

---

## 6. GUÍA DE CONTINUIDAD PARA EL DESARROLLADOR O SIGUIENTE SESIÓN DE IA

Si eres una IA o un desarrollador continuando este proyecto, sigue estas instrucciones exactas:

### 1. Comandos a ejecutar en terminal local (`E:\Xampp\htdocs\SIAR`):
Para aplicar las migraciones de Categorías y Productos:
```bash
php artisan migrate
```
Para ejecutar la suite de pruebas automatizadas:
```bash
php artisan test --filter=SiarModulosTest
```

### 2. Para continuar con el Módulo de Pedidos y Facturación (Persona 4):
```bash
php artisan make:model Pedido -mcr
php artisan make:model DetallePedido -m
php artisan make:model Factura -mcr
```
- `pedidos`: `mesa_id`, `user_id`, `estado` ('Pendiente', 'En preparación', 'Listo', 'Entregado', 'Facturado', 'Cancelado'), `total`.
- `detalle_pedidos`: `pedido_id`, `producto_id`, `cantidad`, `precio_unitario`.
- `facturas`: `pedido_id`, `subtotal`, `impuestos`, `descuentos`, `total_final`, `metodo_pago`.

### 3. Directiva de Diseño Obligatoria:
- Todas las vistas **DEBEN** extender `@extends('layouts.app')` y alojar el contenido dentro de `@section('content') ... @endsection`.
- Usar únicamente clases nativas de Bootstrap 5 (`card`, `table`, `btn btn-primary`, `badge`, `form-control`, etc.).
- Proteger botones y secciones según el rol con directivas `@can('permiso')` o `@role('nombre-rol')`.
