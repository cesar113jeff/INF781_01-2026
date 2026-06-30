# INF781 - JWT Sessions (Lab 8) — Postman

Colección de pruebas para el Laboratorio 8: Autenticación JWT con sesiones, rotación de refresh tokens, detección de reúso, revocación y cookies httpOnly.

## Requisitos previos

El servidor NestJS debe estar corriendo en `http://localhost:3000` antes de ejecutar cualquier request:

```bash
npm run start:dev
```

## Crear Workspace en Postman

1. Abre Postman.
2. En la barra lateral izquierda, haz clic en **Workspaces** > **Create Workspace**.
3. Nombra el workspace **INF781 - JWT Sessions**.
4. Selecciona **Personal** o **Team** según tu caso.
5. Haz clic en **Create**.

## Importar Environment

1. Dentro del workspace, ve a la pestaña **Environments** (barra lateral izquierda).
2. Haz clic en **Import** (esquina superior derecha).
3. Selecciona el archivo `inf781-jwt-sessions.postman_environment.json`.
4. Postman creará el environment **INF781 - JWT Sessions (Local)** con las variables predefinidas.
5. Activa el environment haciendo clic en el ícono de ojo junto al nombre del environment en la barra superior derecha.

## Importar Colección

1. En la barra lateral izquierda, haz clic en **Collections**.
2. Haz clic en **Import**.
3. Selecciona el archivo `inf781-jwt-sessions.postman_collection.json`.
4. La colección **INF781 - JWT Sessions (Lab 8)** aparecerá con 4 carpetas y 15 requests.

## Variables del Environment

| Variable | Valor inicial | Descripción |
|---|---|---|
| `baseUrl` | `http://localhost:3000` | URL base del servidor NestJS |
| `accessToken` | *(vacío)* | Se llena automáticamente tras login/register |
| `userEmail` | `usuario@test.com` | Email del usuario de prueba |
| `userPassword` | `mipassword123` | Contraseña del usuario de prueba |
| `userEmailSecondDevice` | `usuario@test.com` | Email para pruebas de sesiones múltiples |

### ¿Por qué no hay variable `refreshToken`?

El refresh token viaja **exclusivamente** como cookie httpOnly con `path=/auth`. Postman lo maneja automáticamente a través del Cookie Jar cada vez que ejecutas una request contra `http://localhost:3000`. Exponerlo como variable de entorno comprometería la seguridad del patrón que se implementa en el laboratorio.

## Orden de ejecución de las requests

Ejecuta las requests en el orden en que aparecen en la colección. Cada request puede depender del estado dejado por la anterior:

### 01 - Registro y Login
1. **POST /auth/register** — Registra el usuario (201). Guarda accessToken.
2. **POST /auth/login** — Inicia sesión (200). Actualiza accessToken.
3. **POST /auth/login (credenciales inválidas)** — Verifica rechazo (401).
4. **POST /auth/register (correo duplicado)** — Verifica rechazo (403).

### 02 - Rutas protegidas
5. **GET /auth/me** — Perfil del usuario autenticado (200).
6. **GET /auth/me (sin token)** — Verifica rechazo sin token (401).
7. **GET /auth/sessions** — Lista sesiones activas (200).

### 03 - Refresh y Rotación
8. **POST /auth/refresh** — Rota el refresh token. **Ejecuta esta request antes de la siguiente.**
9. **POST /auth/refresh (reúso de token rotado)** — Reutiliza el token antiguo. Debe fallar con 403.
10. **GET /auth/sessions (verificar revocación global)** — Tras reúso detectado, todas las sesiones se revocan.

### 04 - Sesiones múltiples y Logout
11. **POST /auth/login (Dispositivo A)** — Login con User-Agent personalizado.
12. **POST /auth/login (Dispositivo B)** — Login con otro User-Agent.
13. **GET /auth/sessions (2 sesiones)** — Verifica 2 sesiones activas.
14. **POST /auth/logout** — Cierra sesión del dispositivo actual.
15. **GET /auth/sessions (1 sesión)** — Verifica que queda 1 sesión activa.

## Caso de prueba: reúso detectado (Request 9)

El request **"POST /auth/refresh (reúso de token rotado)"** prueba la detección de reúso de refresh tokens:

1. La request anterior (request 8) ejecuta un pre-request script que captura el valor de la cookie `refreshToken` **antes** de que se ejecute el refresh, guardándolo en la variable `oldRefreshTokenForReuseTest`.
2. La request 9 envía esa cookie antigua manualmente via header `Cookie: refreshToken={{oldRefreshTokenForReuseTest}}`.
3. El backend detecta que el token ya fue rotado (no coincide con el hash almacenado), ejecuta `revokeAll` para todas las sesiones del usuario, y devuelve 403.

**Nota:** El script de pre-request de la request 8 captura la cookie automáticamente. Sin embargo, si necesitas re-ejecutar la prueba de reúso, debes volver a ejecutar la request 8 primero para que la cookie antigua quede actualizada en la variable.

## Limitación: múltiples dispositivos desde un solo Cookie Jar

Las requests 11 y 12 (Dispositivo A y B) comparten el mismo Cookie Jar de Postman. Esto significa que la cookie `refreshToken` se sobreescribe con cada login. Para una prueba más estricta de aislamiento por dispositivo:

- **Opción 1:** Usa **Postman Runner** con un entorno de cookies aislado por iteración.
- **Opción 2:** Abre dos instancias separadas de Postman (o dos workspaces distintos).
- **Opción 3:** Desactiva "Send cookies" temporalmente en la configuración de request.

Aun así, el backend muestra dos sesiones activas distintas con `sessionId` diferentes en `/auth/sessions`, ya que cada login crea una nueva sesión con un `userAgent` distinto.

## Notas técnicas

- Todos los scripts de test están escritos en JavaScript (`pm.test`, `pm.expect`).
- Las variables se guardan en el environment (`pm.environment.set`), no en variables de colección.
- El refresh token nunca se expone como variable de Postman.
- Los bodies de las requests usan `{{userEmail}}` y `{{userPassword}}` del environment, nunca valores hardcodeados.
