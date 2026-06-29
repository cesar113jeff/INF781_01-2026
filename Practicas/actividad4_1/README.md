# INF781 — reCAPTCHA v3 Adaptativo en Laravel 13

Proyecto de seguridad que integra **Google reCAPTCHA v3 con scoring adaptativo** en un formulario de contacto público dentro de una aplicación Laravel 13 con PostgreSQL y Breeze (Blade).

Se eligió reCAPTCHA v3 sobre v2 porque no interrumpe al usuario con desafíos visuales (clic en imágenes, checkboxes). En su lugar, analiza el comportamiento en segundo plano y asigna un score (0.0–1.0) que el servidor evalúa contra un umbral configurable, mejorando la experiencia de usuario sin sacrificar seguridad.

---

## 1. Requisitos previos

- PHP 8.3+
- Composer 2.x
- Node.js + npm
- PostgreSQL 16+
- Cuenta de Google para obtener claves reCAPTCHA v3

---

## 2. Obtención de claves reCAPTCHA v3

1. Ve a https://www.google.com/recaptcha/admin
2. Inicia sesión con tu cuenta de Google.
3. Haz clic en **"Crear"** o **"Registrar un nuevo sitio"**.
4. Selecciona **reCAPTCHA v3** como tipo.
5. Ingresa un nombre (ej. "Formulario Contacto Laravel") y los dominios permitidos (ej. `localhost`).
6. Acepta los términos y envía.
7. Google te proporcionará:
   - **Site Key** — se usará en el frontend (JavaScript).
   - **Secret Key** — se usará en el backend (PHP).
8. Copia ambas claves en tu archivo `.env`.

---

## 3. Instalación reproducible

```bash
# 1. Clonar el repositorio
git clone <url-del-repositorio> INF781-Tarea4-<Apellido>
cd INF781-Tarea4-<Apellido>

# 2. Instalar dependencias PHP
composer install

# 3. Instalar y compilar assets frontend
npm install && npm run build

# 4. Configurar variables de entorno
cp .env.example .env

# Completar en .env:
#   - DB_DATABASE, DB_USERNAME, DB_PASSWORD (PostgreSQL)
#   - RECAPTCHA_SITE_KEY, RECAPTCHA_SECRET_KEY (de Google)
#   - RECAPTCHA_MIN_SCORE (opcional, default 0.5)

# 5. Generar APP_KEY
php artisan key:generate

# 6. Ejecutar migraciones
php artisan migrate

# 7. Iniciar servidor de desarrollo
php artisan serve
```

---

## 4. Uso local

Una vez corriendo el servidor:

1. Abre http://localhost:8000/contact
2. Llena los campos: Nombre, Correo Electrónico, Mensaje.
3. reCAPTCHA v3 se ejecuta automáticamente al hacer clic en **"Enviar Mensaje"**:
   - Obtiene un token invisible mediante `grecaptcha.execute()`.
   - El token se inyecta en el campo oculto `g-recaptcha-response`.
   - El servidor verifica el token contra Google y evalúa el score.
4. Si el score supera el umbral (≥ 0.5), el mensaje se envía correctamente.
5. Si el score es menor al umbral, se muestra un mensaje de error.

---

## 5. Ejecución de pruebas

```bash
php artisan test --filter=CaptchaAlternativeTest
```

Resultado esperado:

```
PASS  Tests\Feature\CaptchaAlternativeTest
  ✓ test_token_valido_con_score_alto_acepta_formulario
  ✓ test_score_bajo_rechaza_formulario
  ✓ test_token_ausente_rechaza_formulario

Tests:    3 passed
```

Las pruebas usan `Http::fake()` para simular las respuestas de Google sin hacer llamadas reales a la API.

---

## 6. Decisiones de diseño

### a) ¿Por qué reCAPTCHA v3 en lugar de v2 o mews/captcha?

reCAPTCHA v3 elimina la fricción del usuario. Mientras que v2 obliga al usuario a resolver un desafío visual (seleccionar imágenes, escribir texto distorsionado) y `mews/captcha` genera imágenes que un humano debe transcribir, v3 trabaja en segundo plano analizando el comportamiento real del usuario (movimientos del ratón, tiempo en página, patrón de escritura). El resultado es un score numérico que el servidor evalúa. Esto ofrece una experiencia fluida sin barreras visuales, ideal para formularios de contacto donde se busca maximizar la tasa de conversión sin comprometer la seguridad.

### b) ¿Por qué el umbral de score en 0.5 y cómo ajustarlo?

El valor 0.5 representa un平衡 entre seguridad y usabilidad. Google recomienda comenzar con 0.5 y ajustar según los datos reales de tráfico:
- **0.3** — más permisivo, acepta más usuarios pero deja pasar más bots.
- **0.7** — más restrictivo, bloquea más bots pero puede rechazar usuarios legítimos con comportamientos atípicos (VPN, navegadores poco comunes).

Para ajustarlo, cambia `RECAPTCHA_MIN_SCORE` en `.env`. No requiere modificar código. El valor se lee desde `config('services.recaptcha.min_score')`.

### c) ¿Por qué la regla vive en `app/Rules` y no en el controlador?

Separar la lógica de verificación en una clase dedicada (`RecaptchaV3Rule`) que implementa `ValidationRule` ofrece tres ventajas:
1. **Reutilización** — la misma regla puede aplicarse a cualquier formulario (registro, login, contacto) sin duplicar código.
2. **Testeabilidad** — se puede probar la lógica de verificación de forma aislada, mockeando el HTTP Client.
3. **Mantenibilidad** — si Google cambia su API, solo se modifica un archivo, no cada controlador.

### d) Dificultades encontradas y cómo se resolvieron

- **Token expirado en pruebas**: Las pruebas con `Http::fake()` inicialmente no interceptaban la URL correcta porque el patrón de fake no coincidía. Se resolvió usando `'google.com/recaptcha/api/siteverify'` como key exacta del array de fake.

- **Score como string vs float**: Google devuelve el score como número flotante en JSON, pero al extraerlo con `$data['score']` podía venir como `null`. Se agregó casting explícito `(float) ($data['score'] ?? 0.0)` para garantizar la comparación numérica.

- **Validación sin token no llegaba al servidor**: El formulario original intentaba hacer submit sin token si JavaScript fallaba. Se resolvió previniendo el submit por defecto con `e.preventDefault()` y solo llamando `form.submit()` después de que `grecaptcha.execute()` devolviera el token.

- **Confirmación visual al usuario**: v3 no muestra ningún indicador visual al usuario. Para dar retroalimentación, se agregó un mensaje de error específico cuando el score es bajo, visible en el formulario mediante `@error('g-recaptcha-response')`.

---

## 7. Capturas de pantalla

```
[Captura: formulario con verificación exitosa]
Muestra el formulario de contacto con los campos completados y el
mensaje "Mensaje enviado correctamente." en una caja verde.

[Captura: mensaje de error por score bajo]
Muestra el formulario con el mensaje de error
"Actividad sospechosa detectada. Intenta más tarde."
en texto rojo debajo del botón de envío.
```

---

## 8. Licencia y autor

**MIT License**

Autor: <Nombre Apellido> — INF781 UATF 2025

---

## Archivos del proyecto

```
INF781-Tarea4-<Apellido>/
├── app/
│   ├── Http/Controllers/ContactController.php
│   └── Rules/RecaptchaV3Rule.php
├── resources/views/contact/form.blade.php
├── routes/web.php
├── tests/Feature/CaptchaAlternativeTest.php
├── docs/screenshots/
│   └── .gitkeep
├── .env.example
├── composer.json
└── README.md
```
