# SIAR – Guía de instalación después de clonar desde GitHub

Guía paso a paso para dejar funcionando **SIAR (Sistema Integral de Administración para Restaurantes)** en un equipo nuevo, a partir del repositorio de GitHub.

> **Stack:** Laravel 13 · PHP ≥ 8.4 · MySQL (XAMPP) · Blade + Bootstrap 5 · Laravel Breeze · spatie/laravel-permission
> **Repositorio:** `https://github.com/BaronaKev/SIAR` · rama de trabajo: `feature/mesas-gestion_usuarios`

---

## 0. Requisitos previos

| Herramienta | Versión mínima | Cómo comprobarlo |
|---|---|---|
| PHP | 8.4 (lo exige `composer.lock`) | `php -v` |
| Composer | 2.x | `composer -V` |
| MySQL / MariaDB | La de XAMPP | Panel de XAMPP → MySQL en verde |
| Node.js + npm | Node 20 o superior | `node -v` y `npm -v` |
| Git | Cualquiera reciente | `git --version` |

**Extensiones de PHP** que deben estar activas en `php.ini` (en XAMPP: `E:\Xampp\php\php.ini`, quitar el `;` del inicio de la línea):

```ini
extension=fileinfo     ; subida de imágenes de productos
extension=mbstring
extension=openssl
extension=pdo_mysql
extension=pdo_sqlite   ; las pruebas automatizadas usan SQLite en memoria
extension=sqlite3
extension=zip          ; Composer descarga paquetes más rápido
```

> ⚠️ Si `php -v` muestra una versión menor a 8.4, `composer install` va a fallar. Actualiza el PHP de XAMPP o instala PHP 8.4 y ponlo primero en el `PATH`.

---

## 1. Clonar el repositorio

Abre una terminal (CMD, PowerShell o Git Bash) en la carpeta del servidor web:

```bash
cd E:\Xampp\htdocs
git clone https://github.com/BaronaKev/SIAR.git
cd SIAR
git checkout feature/mesas-gestion_usuarios
git pull
```

Si ya lo tenías clonado y solo quieres traer los últimos cambios:

```bash
git pull origin feature/mesas-gestion_usuarios
```

---

## 2. Instalar dependencias

GitHub **no** guarda las carpetas `vendor/` ni `node_modules/` (están en `.gitignore`), así que hay que reconstruirlas:

```bash
composer install
npm install
npm run build
```

- `composer install` → descarga Laravel, Breeze, Spatie Permission, PHPUnit, etc.
- `npm install` + `npm run build` → compila los estilos/JS de las vistas de Breeze (login, registro, perfil) con Vite.
  Mientras desarrollas puedes usar `npm run dev` en otra terminal en lugar de `npm run build`.

---

## 3. Crear el archivo `.env`

El `.env` tampoco se sube a GitHub (contiene credenciales). Se crea a partir de la plantilla:

```bash
# CMD / PowerShell
copy .env.example .env

# Git Bash / Linux / macOS
cp .env.example .env
```

Genera la llave de la aplicación:

```bash
php artisan key:generate
```

Abre `.env` y ajusta, como mínimo, estas variables:

```env
APP_NAME=SIAR
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=siar
DB_USERNAME=root
DB_PASSWORD=

# Parámetros propios de SIAR (config/siar.php)
SIAR_IMPUESTO_PORCENTAJE=8
SIAR_COCINA_REFRESCO=15

# Recuperación de contraseña: en local los correos se escriben en storage/logs/laravel.log
MAIL_MAILER=log
```

> `DB_PASSWORD` va vacío en una instalación por defecto de XAMPP. Si tu MySQL tiene contraseña, escríbela ahí.
> `SIAR_COCINA_REFRESCO` es el intervalo (en segundos) con que se refresca la vista de cocina; ajústalo si quieres otro valor.

---

## 4. Crear la base de datos (vacía)

Con XAMPP encendido (Apache + MySQL), crea **solo la base de datos**, sin tablas. Opciones:

**a) phpMyAdmin:** `http://localhost/phpmyadmin` → *Nueva* → nombre `siar` → cotejamiento `utf8mb4_unicode_ci` → *Crear*.

**b) Línea de comandos:**

```bash
E:\Xampp\mysql\bin\mysql -u root -e "CREATE DATABASE siar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

---

## 5. Bajar las tablas y la información registrada (migraciones + seeders)

Este es el paso clave. Laravel crea **todas las tablas** a partir de los archivos de `database/migrations/` y carga los **datos iniciales** (roles, permisos, usuarios de prueba, catálogo, etc.) desde `database/seeders/`.

### Instalación limpia (primera vez)

```bash
php artisan migrate --seed
```

### Reconstruir todo desde cero (borra y vuelve a crear)

Úsalo cuando un compañero agregó/cambió migraciones o la base quedó inconsistente:

```bash
php artisan migrate:fresh --seed
```

> ⚠️ `migrate:fresh` **elimina todas las tablas y sus datos** antes de recrearlas. No lo uses si tienes información que quieras conservar (ver sección 7).

### Otros comandos útiles

| Comando | Para qué sirve |
|---|---|
| `php artisan migrate` | Crea solo las tablas/columnas nuevas que aún no existen (no borra nada). |
| `php artisan db:seed` | Ejecuta todos los seeders (`DatabaseSeeder`) sobre las tablas existentes. |
| `php artisan db:seed --class=RoleSeeder` | Ejecuta un seeder específico (p. ej. roles y permisos). |
| `php artisan migrate:status` | Muestra qué migraciones ya se ejecutaron. |
| `php artisan migrate:rollback` | Deshace el último lote de migraciones. |
| `php artisan permission:cache-reset` | Limpia la caché de roles/permisos de Spatie (hazlo si cambiaste `RoleSeeder`). |

**Tablas que deben quedar creadas** (además de las internas de Laravel: `migrations`, `sessions`, `cache`, `jobs`, `password_reset_tokens`):

`users`, `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`, `categorias`, `productos`, `mesas`, `reservas`, `pedidos`, `detalle_pedidos`, `facturas`, `pagos`, `auditorias`.

Verifícalo en phpMyAdmin o con:

```bash
php artisan db:show
```

---

## 6. Enlazar el almacenamiento y levantar el servidor

Las imágenes de los productos se guardan en `storage/app/public`. Para que el navegador las vea:

```bash
php artisan storage:link
```

Limpia cachés (recomendado tras cada `git pull`) y arranca:

```bash
php artisan optimize:clear
php artisan serve
```

Abre **http://localhost:8000** e inicia sesión con un usuario creado por los seeders.

> Las credenciales de prueba están definidas en `database/seeders/` (revisa `DatabaseSeeder.php` / `RoleSeeder.php`). Si no existe un usuario administrador, créalo así:
>
> ```bash
> php artisan tinker
> ```
> ```php
> $u = \App\Models\User::create(['name' => 'Admin', 'email' => 'admin@siar.test', 'password' => bcrypt('password'), 'estado' => 'activo']);
> $u->assignRole('Administrador');
> ```
> *(Ajusta el valor de `estado` y el nombre del rol a como estén definidos en el proyecto.)*

---

## 7. Compartir los datos reales registrados (exportar / importar)

Los seeders solo cargan datos **de ejemplo**. Si un compañero tiene pedidos, productos o facturas reales en su base y quieres esa misma información, hay que pasar un respaldo `.sql`.

### Exportar (quien tiene los datos)

```bash
E:\Xampp\mysql\bin\mysqldump -u root --routines --single-transaction siar > siar_respaldo.sql
```

o en phpMyAdmin: seleccionar `siar` → *Exportar* → *Rápido* → formato SQL.

### Importar (quien los recibe)

```bash
E:\Xampp\mysql\bin\mysql -u root siar < siar_respaldo.sql
```

o en phpMyAdmin: seleccionar `siar` → *Importar* → elegir el archivo.

Después de importar:

```bash
php artisan migrate            # aplica migraciones más nuevas que el respaldo, si las hay
php artisan permission:cache-reset
```

> 🔒 **No subas el `.sql` con datos reales ni el `.env` a GitHub.** El respaldo incluye correos y hashes de contraseñas. Compártelo por un canal privado (Drive, Teams). Para datos que todo el equipo debe tener siempre, lo correcto es agregarlos a un **seeder**.

---

## 8. Ejecutar las pruebas

```bash
php artisan test
```

Con reporte de cobertura (requiere Xdebug o PCOV activo):

```bash
php artisan test --coverage --min=70
```

El proyecto tiene 82 pruebas (RBAC, flujo del restaurante, modelo de datos, autenticación) con ~83 % de cobertura de líneas. El requisito RNF009 exige ≥ 70 %.

---

## 9. Resumen rápido (copiar y pegar)

```bash
cd E:\Xampp\htdocs
git clone https://github.com/BaronaKev/SIAR.git
cd SIAR
git checkout feature/mesas-gestion_usuarios
composer install
npm install
npm run build
copy .env.example .env
php artisan key:generate
# → editar .env (DB_DATABASE=siar, DB_USERNAME=root, DB_PASSWORD=)
# → crear la base de datos "siar" vacía en phpMyAdmin
php artisan migrate --seed
php artisan storage:link
php artisan optimize:clear
php artisan serve
```

---

## 10. Problemas frecuentes

| Error | Causa | Solución |
|---|---|---|
| `Your PHP version (8.2.x) does not satisfy that requirement` | PHP de XAMPP menor a 8.4 | Actualizar PHP o usar uno ≥ 8.4 en el `PATH`. |
| `No application encryption key has been specified` | Falta `APP_KEY` | `php artisan key:generate` |
| `SQLSTATE[HY000] [1049] Unknown database 'siar'` | No se creó la BD | Paso 4. |
| `SQLSTATE[HY000] [2002] No se puede establecer una conexión` | MySQL apagado | Encender MySQL en XAMPP. |
| `SQLSTATE[42S01] Table '...' already exists` | Migraciones a medias | `php artisan migrate:fresh --seed` (borra datos). |
| `Vite manifest not found` | No se compilaron los assets | `npm install && npm run build` |
| Las imágenes de productos no cargan | Falta el enlace simbólico | `php artisan storage:link` |
| `There is no role named ...` / permisos no se aplican | Caché de Spatie desactualizada o faltó el seeder | `php artisan db:seed --class=RoleSeeder` y `php artisan permission:cache-reset` |
| Usuario no puede entrar aunque la contraseña es correcta | Usuario con `estado` inactivo (middleware `EnsureUserIsActive`) | Activarlo desde el módulo de usuarios o en la BD. |
| `could not find driver (sqlite)` al correr pruebas | Falta `pdo_sqlite` | Activar `extension=pdo_sqlite` y `extension=sqlite3` en `php.ini`. |
