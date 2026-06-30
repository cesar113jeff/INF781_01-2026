# inf781-jwt-sessions

API de autenticación con NestJS y PostgreSQL que emplea access tokens y refresh tokens, con persistencia y rotación del refresh, revocación real, detección de reúso y entrega mediante cookies httpOnly, soportando sesiones en múltiples dispositivos.

## Requisitos previos

- Node.js 20 LTS o superior
- npm
- PostgreSQL 14 o superior en ejecución
- Un cliente HTTP que gestione cookies (Postman, Bruno o REST Client de VS Code)

## Instalación

```bash
# Clonar el repositorio
git clone <url-del-repositorio>
cd inf781-jwt-sessions

# Instalar dependencias
npm install
```

## Configuración de base de datos

1. Crear una base de datos y usuario en PostgreSQL:

```sql
CREATE USER jwt_sessions_user WITH PASSWORD 'jwt_sessions_password';
CREATE DATABASE jwt_sessions_db OWNER jwt_sessions_user;
```

2. Copiar el archivo de ejemplo de variables de entorno:

```bash
cp .env.example .env
```

3. Editar `.env` con los datos reales de conexión a PostgreSQL (los valores por defecto asumen `localhost:5432`).

## Ejecución

```bash
# Desarrollo (con watch)
npm run start:dev

# Producción
npm run build
npm run start:prod
```

El servidor arranca en `http://localhost:3000`.

## Prueba manual de los 6 flujos

Usa Postman, Bruno o curl. El refresh token se maneja automáticamente como cookie httpOnly; en Postman habilita **Settings > Cookies > Enable cookies**.

### 1. Registro — `POST /auth/register`

```http
POST http://localhost:3000/auth/register
Content-Type: application/json

{
  "email": "usuario@test.com",
  "password": "mipassword123"
}
```

Respuesta (201):
```json
{
  "accessToken": "eyJhbGciOiJIUzI1NiIs..."
}
```

El `refreshToken` llega automáticamente como cookie httpOnly. Cópialo del panel de cookies de Postman si necesitas usarlo manualmente.

### 2. Login — `POST /auth/login`

```http
POST http://localhost:3000/auth/login
Content-Type: application/json

{
  "email": "usuario@test.com",
  "password": "mipassword123"
}
```

Respuesta (200):
```json
{
  "accessToken": "eyJhbGciOiJIUzI1NiIs..."
}
```

### 3. Datos del usuario actual — `GET /auth/me`

```http
GET http://localhost:3000/auth/me
Authorization: Bearer <accessToken>
```

Respuesta (200):
```json
{
  "sub": "uuid-del-usuario",
  "email": "usuario@test.com",
  "sessionId": "uuid-de-la-sesion"
}
```

### 4. Listar sesiones activas — `GET /auth/sessions`

```http
GET http://localhost:3000/auth/sessions
Authorization: Bearer <accessToken>
```

Respuesta (200):
```json
[
  {
    "id": "uuid-de-la-sesion",
    "userAgent": "PostmanRuntime/7.x",
    "createdAt": "2026-06-29T12:00:00.000Z",
    "expiresAt": "2026-07-06T12:00:00.000Z"
  }
]
```

### 5. Renovar access token — `POST /auth/refresh`

No necesita body ni header de Authorization. La cookie `refreshToken` se envía automáticamente.

```http
POST http://localhost:3000/auth/refresh
```

Respuesta (200):
```json
{
  "accessToken": "eyJhbGciOiJIUzI1NiIs..."
}
```

Un nuevo `refreshToken` se envía como cookie (rotación).

### 6. Cerrar sesión — `POST /auth/logout`

Requiere la cookie `refreshToken`.

```http
POST http://localhost:3000/auth/logout
```

Respuesta (200):
```json
{
  "message": "Sesión cerrada"
}
```

La cookie se limpia automáticamente.

## Estructura del proyecto

```
src/
  app.module.ts          # Módulo raíz con TypeORM y ConfigModule
  main.ts                # Bootstrap: cookie-parser, CORS, ValidationPipe
  users/
    user.entity.ts       # Entidad User
    users.service.ts     # Servicio de usuarios
    users.module.ts      # Módulo de usuarios
  auth/
    auth.module.ts       # Módulo de autenticación
    auth.service.ts      # Lógica: registro, login, refresh, logout
    auth.controller.ts   # Endpoints: /auth/*
    refresh-token.entity.ts  # Entidad RefreshToken (una fila por sesión)
    dto/
      register.dto.ts    # DTO de registro
      login.dto.ts       # DTO de login
    strategies/
      access-token.strategy.ts   # Estrategia JWT para access token
      refresh-token.strategy.ts  # Estrategia JWT para refresh token (cookie)
    guards/
      access-token.guard.ts
      refresh-token.guard.ts
    decorators/
      get-current-user.decorator.ts
```

## Correcciones respecto al código de referencia de la guía

1. **Rutas de import absolutas → relativas**: La guía usa `import { User } from 'src/users/user.entity'` en `refresh-token.entity.ts` y `import { UsersService } from 'src/users/users.service'` en `auth.service.ts`. Estas rutas absolutas (`src/...`) no son resueltas correctamente por TypeScript/CommonJS sin configuración adicional de `baseUrl`/`paths`. Se cambiaron a rutas relativas (`../users/user.entity`, `../users/users.service`).

2. **`cookie-parser` import en `main.ts`**: La guía usa `import * as cookieParser from 'cookie-parser'` y luego `cookieParser()`. Con `esModuleInterop: true` y las tipos de `@types/cookie-parser`, el namespace import no es callable. Se cambió a `import cookieParser from 'cookie-parser'` (import default).

3. **Tipos de `ConfigService.get()` en estrategias**: `config.get('JWT_ACCESS_SECRET')` retorna `string | undefined`, pero `secretOrKey` de passport-jwt requiere `string | Buffer`. Se agregó `!` (non-null assertion) ya que las variables de entorno deben estar definidas al arrancar.

4. **Tipos de `passReqToCallback` en `RefreshTokenStrategy`**: TypeScript no puede inferir correctamente el tipo del union `StrategyOptionsWithRequest | StrategyOptionsWithoutRequest` al pasar el objeto inline. Se extrajo a una variable tipada explícita como `StrategyOptionsWithRequest`.

5. **`tsconfig.json` CommonJS**: NestJS 11 scaffoldea con `module: "nodenext"`, pero la guía especifica NestJS 11 CommonJS. Se cambió a `module: "commonjs"` y `moduleResolution: "node"`.

## Variables de entorno

| Variable | Descripción | Ejemplo |
|---|---|---|
| `NODE_ENV` | Entorno | `development` |
| `CLIENT_ORIGIN` | Origen permitido para CORS | `http://localhost:5173` |
| `DATABASE_HOST` | Host de PostgreSQL | `localhost` |
| `DATABASE_PORT` | Puerto de PostgreSQL | `5432` |
| `DATABASE_USER` | Usuario de PostgreSQL | `jwt_sessions_user` |
| `DATABASE_PASSWORD` | Contraseña de PostgreSQL | `jwt_sessions_password` |
| `DATABASE_NAME` | Nombre de la base de datos | `jwt_sessions_db` |
| `JWT_ACCESS_SECRET` | Secreto para firmar access tokens | `mi-secreto` |
| `JWT_REFRESH_SECRET` | Secreto para firmar refresh tokens | `mi-secreto-refresh` |
| `JWT_ACCESS_EXPIRES` | Caducidad del access token | `15m` |
| `JWT_REFRESH_EXPIRES` | Caducidad del refresh token | `7d` |
