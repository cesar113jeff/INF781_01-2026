# Práctica 4 — Integración de reCAPTCHA v3 en Laravel 13 con Scoring Adaptativo

Este proyecto corresponde a la Práctica 4 de la materia **Seguridad de Software (INF781)** de la **Universidad Autónoma Tomás Frías (UATF)**. Consiste en la implementación segura y nativa de Google reCAPTCHA v3 en un formulario de contacto de Laravel 13, utilizando el cliente HTTP incorporado en el framework sin recurrir a paquetes de terceros.

reCAPTCHA v3 permite proteger el sitio contra spam y abuso sin interrumpir la experiencia del usuario (sin casillas de verificación ni desafíos visuales molestas), proporcionando en su lugar una puntuación (score) de riesgo entre 0.0 y 1.0 que permite tomar decisiones en el servidor.

---

## 2. Requisitos previos

Para poder ejecutar e instalar este proyecto correctamente, se requiere:

- **PHP 8.3** o superior.
- **Composer** (gestor de dependencias de PHP).
- **Node.js** y **npm** (para la compilación de estilos y scripts).
- **PostgreSQL** (base de datos relacional).
- Cuenta de Google para la generación de claves de reCAPTCHA.

---

## 3. Obtención de claves

Para registrar tu sitio y obtener las credenciales de reCAPTCHA v3:

1. Ve a la consola de administración de Google reCAPTCHA: [https://www.google.com/recaptcha/admin](https://www.google.com/recaptcha/admin).
2. Haz clic en el botón de creación o registro de un nuevo sitio.
3. Elige una etiqueta descriptiva (ej. `INF781-Tarea4`).
4. Selecciona el tipo de reCAPTCHA: **reCAPTCHA v3**.
5. Agrega los dominios de prueba y desarrollo (por ejemplo: `localhost` y `127.0.0.1`).
6. Acepta las Condiciones de Servicio de reCAPTCHA y haz clic en **Enviar**.
7. Copia la **Clave de sitio (Site Key)** y la **Clave secreta (Secret Key)** generadas.
8. Colócalas en tu archivo de configuración de entorno `.env` en las variables `RECAPTCHA_SITE_KEY` y `RECAPTCHA_SECRET_KEY` respectivamente.

---

## 4. Instalación reproducible

Sigue esta secuencia exacta de comandos para desplegar el proyecto localmente de forma reproducible:

```bash
# 1. Clonar el repositorio del proyecto
git clone <URL_DEL_REPOSITORIO>
cd INF781-Tarea4-Condori

# 2. Instalar las dependencias de Laravel mediante Composer
composer install

# 3. Instalar y compilar los recursos de frontend (Tailwind/Vite)
npm install
npm run build

# 4. Crear el archivo de configuración local a partir de la plantilla
cp .env.example .env

# 5. Configurar el archivo .env con la conexión PostgreSQL y las claves de reCAPTCHA:
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=inf781_captcha
# DB_USERNAME=tu_usuario
# DB_PASSWORD=tu_contrasena
# RECAPTCHA_SITE_KEY=tu_clave_de_sitio_obtenida
# RECAPTCHA_SECRET_KEY=tu_clave_secreta_obtenida
# RECAPTCHA_MIN_SCORE=0.5

# 6. Generar la clave única de la aplicación
php artisan key:generate

# 7. Ejecutar las migraciones de la base de datos para crear las tablas
php artisan migrate

# 8. Iniciar el servidor de desarrollo local
php artisan serve
```

---

## 5. Uso local

1. Asegúrate de que el servidor local de Laravel esté activo ejecutando `php artisan serve` (por defecto correrá en `http://localhost:8000`).
2. Abre tu navegador e ingresa a la ruta protegida: [http://localhost:8000/contact](http://localhost:8000/contact).
3. Verás un formulario público con campos para Nombre, Correo Electrónico y Mensaje.
4. En la esquina inferior derecha aparecerá la insignia flotante oficial de **Google reCAPTCHA**, indicando que el script del lado del cliente se ha cargado e inicializado correctamente.
5. Completa los campos del formulario y presiona **Enviar Mensaje**.
6. El formulario enviará el token de seguridad generado por el frontend. Si la puntuación (score) retornada por Google es igual o superior al umbral (`0.5`), el mensaje se procesará y se te redirigirá con un mensaje de éxito.

---

## 6. Ejecución de pruebas

Para validar el correcto funcionamiento de las reglas de negocio y de validación de seguridad implementadas, ejecuta el siguiente comando:

```bash
php artisan test --filter CaptchaAlternativeTest
```

### Resultado esperado:

```text
  PASS  Tests\Feature\CaptchaAlternativeTest
  ✓ token valido con score alto acepta formulario ....................... 1.23s
  ✓ score bajo rechaza formulario ....................................... 0.45s
  ✓ token ausente rechaza formulario .................................... 0.12s

  Tests:  3 passed (8 assertions)
  Duration: 1.80s
```

---

## 7. Decisiones de diseño

- **(a) Elección de reCAPTCHA v3:** Se eligió reCAPTCHA v3 sobre las versiones anteriores y otras alternativas debido a que preserva por completo la fluidez en la experiencia de usuario (UX). Al no requerir interacciones mecánicas intrusivas por parte del usuario legítimo (como resolver puzles o marcar checkboxes), previene la fricción en el embudo de conversión del formulario, al mismo tiempo que bloquea eficazmente los bots basándose en un análisis pasivo del comportamiento.
- **(b) Umbral de Score en 0.5 y adaptabilidad:** El umbral mínimo aceptable se fijó en `0.5`, que es el valor estándar recomendado por Google para equilibrar la tolerancia de falsos positivos y falsos negativos en formularios generales. Este valor se lee dinámicamente desde el archivo `config/services.php` utilizando la variable de entorno `RECAPTCHA_MIN_SCORE`, lo que permite a los administradores ajustar la sensibilidad del filtro (p. ej., subirlo a `0.7` para endurecer la seguridad) de forma inmediata en producción sin tener que redesplegar el código.
- **(c) Arquitectura basada en Regla de Validación Server-Side (`app/Rules`):** La lógica de verificación con la API de Google se encapsuló en una clase de validación independiente (`RecaptchaV3Rule.php`). Esta decisión respeta el principio de responsabilidad única (SRP), manteniendo el controlador `ContactController.php` limpio de lógica de infraestructura o llamadas de red externas. Además, facilita la reutilización inmediata de la misma validación en cualquier otro formulario (como registros, inicios de sesión o comentarios) del proyecto.
- **(d) Dificultades encontradas y soluciones:** La principal dificultad técnica fue realizar pruebas automatizadas robustas y deterministas para respuestas con puntajes bajos y tokens ausentes, sin realizar llamadas HTTP reales a los servidores de Google (lo que generaría lentitud y falsos negativos en entornos de CI/CD). Esto se resolvió utilizando el mockeador nativo de Laravel `Http::fake()`, el cual interceptó las llamadas a la API de verificación de Google simulando las respuestas JSON correspondientes (`score: 0.9` y `score: 0.2`) y permitiendo que la suite de pruebas verifique tanto el código HTTP `422` como los errores en la sesión.

---

## 8. Capturas de pantalla

Aquí se presentan las referencias a las capturas correspondientes al comportamiento del formulario:

- `[Captura: formulario con verificación exitosa]` — Muestra el formulario de contacto cargando el badge de reCAPTCHA v3 y procesando un envío exitoso redirigiendo con el banner verde de confirmación.
- `[Captura: mensaje de error por score bajo]` — Muestra el formulario de contacto fallando la validación y desplegando el error *"Actividad sospechosa detectada. Intenta más tarde."* ante un intento automatizado de envío.

---

## 9. Licencia y autor

Este proyecto se distribuye bajo la licencia **MIT**.

**Autor:** Cesar Condori (cesar113jeff) — INF781 UATF 2025.
