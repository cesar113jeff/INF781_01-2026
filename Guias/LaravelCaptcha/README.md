# INF781 CAPTCHA Lab

Proyecto de seguridad con autenticación y múltiples capas de protección CAPTCHA en Laravel 13 + PostgreSQL.

---

## Requisitos

- PHP ^8.3
- Composer
- PostgreSQL 18
- Node.js + npm

## Instalación

```bash
# 1. Clonar e instalar dependencias
composer install
npm install

# 2. Configurar variables de entorno
cp .env.example .env
# Editar .env con credenciales de base de datos y reCAPTCHA

# 3. Generar APP_KEY y migrar
php artisan key:generate
php artisan migrate

# 4. Compilar assets con Vite
npm run build

# 5. Iniciar servidor de desarrollo
php artisan serve
```

## Configuración

### Base de Datos (PostgreSQL)

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=inf781_captcha
DB_USERNAME=cesar
DB_PASSWORD=postgres
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### Google reCAPTCHA v2

```env
RECAPTCHA_SITE_KEY="6Le1DPwsAAAAAB_QjkQ2bK7dBovyJ62sH7Vuzq36"
RECAPTCHA_SECRET_KEY="6Le1DPwsAAAAAI49fKTFco_EqKBbpnko46iUheyQ"
```

Registrado en `config/services.php`:

```php
'recaptcha' => [
    'site_key' => env('RECAPTCHA_SITE_KEY'),
    'secret_key' => env('RECAPTCHA_SECRET_KEY'),
    'verify_url' => 'https://www.google.com/recaptcha/api/siteverify',
],
```

---

## Capas de Protección

### 1. Google reCAPTCHA v2 — Registro de Usuarios

**Regla de validación personalizada** (`app/Rules/Recaptcha.php`):

```php
public function validate(string $attribute, mixed $value, Closure $fail): void
{
    $response = Http::asForm()->post(config('services.recaptcha.verify_url'), [
        'secret'   => config('services.recaptcha.secret_key'),
        'response' => $value,
        'remoteip' => request()->ip(),
    ]);

    if (!($response->json()['success'] ?? false)) {
        $fail('La verificación CAPTCHA falló. Inténtalo de nuevo.');
    }
}
```

Aplicada en `app/Http/Controllers/Auth/RegisteredUserController.php`:

```php
$request->validate([
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
    'password' => ['required', 'confirmed', Rules\Password::defaults()],
    'g-recaptcha-response' => ['required', new Recaptcha()],
]);
```

**Vista** (`resources/views/auth/register.blade.php`): Widget reCAPTCHA antes del botón de envío con `@push('scripts')` para cargar la API de Google.

### 2. CAPTCHA Local (mews/captcha) — Inicio de Sesión

Paquete `mews/captcha` instalado y configurado en `config/captcha.php`.

**Form Request** (`app/Http/Requests/Auth/LoginRequest.php`):

```php
public function rules(): array
{
    return [
        'email' => ['required', 'string', 'email'],
        'password' => ['required', 'string'],
        'captcha' => ['required', 'captcha'],
    ];
}
```

**Vista** (`resources/views/auth/login.blade.php`): Imagen CAPTCHA generada con `captcha_src('default')` + botón para recargar.

### 3. Formulario de Contacto — Honeypot + CAPTCHA + Rate Limiting

**Controlador** (`app/Http/Controllers/ContactController.php`):

```php
public function store(Request $request)
{
    // Honeypot: si el campo oculto "website" tiene texto, es un bot
    if (!empty($request->input('website'))) {
        return back()->with('status', 'Tu mensaje fue enviado correctamente.');
    }

    $data = $request->validate([
        'name' => ['required', 'string', 'max:120'],
        'email' => ['required', 'email', 'max:180'],
        'message' => ['required', 'string', 'min:10', 'max:2000'],
        'captcha' => ['required', 'captcha'],
    ]);

    Contact::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'message' => $data['message'],
        'ip' => $request->ip(),
    ]);

    return back()->with('status', 'Tu mensaje fue enviado correctamente.');
}
```

**Rutas** (`routes/web.php`):

```php
Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');
```

**Vista** (`resources/views/contact/show.blade.php`): Campo honeypot invisible (off-screen) + CAPTCHA local + rate limit de 5 intentos por minuto.

### Modelo Contact

Migración con columnas `name`, `email`, `message`, `ip`:

```php
Schema::create('contacts', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email');
    $table->text('message');
    $table->ipAddress('ip')->nullable();
    $table->timestamps();
});
```

---

## Resumen de Archivos Modificados/Creados

### Nuevos
| Archivo | Propósito |
|---------|-----------|
| `app/Rules/Recaptcha.php` | Regla de validación Google reCAPTCHA |
| `app/Http/Controllers/ContactController.php` | Controlador con honeypot + captcha |
| `app/Models/Contact.php` | Modelo Eloquent para mensajes |
| `database/migrations/*_create_contacts_table.php` | Migración tabla contacts |
| `resources/views/contact/show.blade.php` | Formulario de contacto |
| `config/captcha.php` | Configuración mews/captcha |
| `tests/Feature/CaptchaProtectionTest.php` | Tests de seguridad |

### Modificados
| Archivo | Cambio |
|---------|--------|
| `config/services.php` | Agregado arreglo `recaptcha` |
| `app/Http/Controllers/Auth/RegisteredUserController.php` | Validación `g-recaptcha-response` |
| `app/Http/Requests/Auth/LoginRequest.php` | Regla `captcha` + mensajes de error |
| `resources/views/auth/register.blade.php` | Widget reCAPTCHA + `@push('scripts')` |
| `resources/views/auth/login.blade.php` | Campo CAPTCHA local |
| `resources/views/layouts/guest.blade.php` | `@stack('scripts')` |
| `routes/web.php` | Rutas `/contact` con throttle |
| `.env` | Credenciales PostgreSQL y reCAPTCHA |
| `phpunit.xml` | Conexión PostgreSQL para tests |

---

## Tests

```bash
# Todos los tests
php artisan test

# Solo tests de protección CAPTCHA
php artisan test --filter=CaptchaProtectionTest
```

**CaptchaProtectionTest** (3 tests):
1. `test_register_falla_sin_recaptcha_token` — registro sin reCAPTCHA es rechazado
2. `test_login_falla_sin_captcha` — login sin código captcha es rechazado
3. `test_contacto_honeypot_descarta_envio_silenciosamente` — bots que llenan el honeypot son descartados sin guardar datos

## Comandos Útiles

```bash
php artisan serve              # Servidor de desarrollo
php artisan migrate             # Ejecutar migraciones
php artisan make:rule Recaptcha # Crear regla de validación
php artisan make:model Contact -m  # Modelo con migración
npm run build                   # Compilar assets (Vite)
npm run dev                     # Desarrollo con hot-reload
```

## Estructura del Proyecto

```
Guias/LaravelCaptcha/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/RegisteredUserController.php
│   │   │   └── ContactController.php
│   │   └── Requests/Auth/LoginRequest.php
│   ├── Models/Contact.php
│   └── Rules/Recaptcha.php
├── config/
│   ├── captcha.php
│   └── services.php
├── database/migrations/
│   └── *_create_contacts_table.php
├── resources/views/
│   ├── auth/
│   │   ├── login.blade.php
│   │   └── register.blade.php
│   ├── contact/show.blade.php
│   └── layouts/guest.blade.php
├── routes/web.php
└── tests/Feature/CaptchaProtectionTest.php
```
