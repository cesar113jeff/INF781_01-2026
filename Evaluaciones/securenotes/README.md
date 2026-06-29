# SecureNotes API - INF781 Examen

API REST para notas personales con autenticación JWT (Access + Refresh tokens), argon2, NestJS 11 y PostgreSQL.

## Prerrequisitos

- Node.js 20+
- PostgreSQL 14+
- npm o yarn

## Instalación

```bash
git clone <repository-url>
cd inf781-examen-securenotes
npm install
```

## Configuración

1. Copiar el archivo de ejemplo de variables de entorno:

```bash
cp .env.example .env
```

2. Editar `.env` y completar los valores:

```env
# Base de datos
DB_HOST=localhost
DB_PORT=5432
DB_USERNAME=tu_usuario_postgres
DB_PASSWORD=tu_password
DB_NAME=securenotes

# JWT - Genera strings aleatorios de al menos 64 caracteres
JWT_ACCESS_SECRET=tu_access_secret_super_largo_aqui_64caracteres_minimo
JWT_REFRESH_SECRET=tu_refresh_secret_super_largo_aqui_64caracteres_minimo
JWT_ACCESS_EXPIRES_IN=15m
JWT_REFRESH_EXPIRES_IN=7d

# App
NODE_ENV=development
PORT=3000
```

## Base de datos

Crear la base de datos en PostgreSQL:

```sql
CREATE DATABASE securenotes;
```

Las tablas se crearán automáticamente con `synchronize: true` en modo desarrollo.

## Arranque

```bash
# Modo desarrollo con hot-reload
npm run start:dev

# Modo producción
npm run build
npm run start:prod
```

## Endpoints

### Auth
| Método | Ruta | Descripción | Auth |
|--------|------|-------------|------|
| POST | /auth/register | Registro de usuario | No |
| POST | /auth/login | Login | No |
| POST | /auth/refresh | Renovar access token | Cookie |
| GET | /auth/me | Perfil del usuario | Bearer |
| POST | /auth/logout | Logout (revoca sesión) | Bearer |
| GET | /auth/sessions | Listar sesiones activas | Bearer |

### Notes
| Método | Ruta | Descripción | Auth |
|--------|------|-------------|------|
| POST | /notes | Crear nota | Bearer |
| GET | /notes | Listar notas del usuario | Bearer |
| GET | /notes/:id | Obtener nota por ID | Bearer |
| PATCH | /notes/:id | Actualizar nota | Bearer |
| DELETE | /notes/:id | Eliminar nota | Bearer |

## Colección Postman

Importa la colección desde el workspace "SecureNotes - INF781 Examen" en Postman para probar todos los endpoints y escenarios de seguridad.

Workspace: https://www.postman.com/workspace/securenotes-inf781-examen

## Seguridad Implementada

- **Hashing**: argon2 para contraseñas y refresh tokens
- **JWT**: Access token (15min) + Refresh token (7d) con secretos distintos
- **Cookies**: httpOnly, secure (producción), sameSite strict, path restringido
- **Rotación de refresh tokens**: Cada uso emite uno nuevo y revoca el anterior
- **Detección de reuso**: Si se detecta un refresh token ya usado, se revocan TODAS las sesiones del usuario
- **Anti-IDOR**: Las notas se buscan siempre filtrando por el owner
- **No revelación**: Las notas ajenas devuelven 404, nunca 403

## Scripts de Prueba

La colección Postman incluye scripts automáticos que:
- Extraen y guardan tokens en variables de entorno
- Verifican respuestas HTTP correctas
- Confirman que el password nunca se expone en respuestas
- Validan escenarios de seguridad (IDOR, token expirado, reuso de refresh)

## Licencia

MIT
