# Pruebas con Thunderclient — AlmaTrack Inventory

## Web Routes (guard `web`)

Requieren sesión previa: hacer `POST /login` primero con credentials, Thunderclient maneja la cookie automáticamente.

### Login

```
POST http://localhost/login
Content-Type: application/x-www-form-urlencoded

email=admin@almatrack.com&password=password
```

| Rol | Email |
|-----|-------|
| admin | admin@almatrack.com |
| supervisor | supervisor@almatrack.com |
| almacenista | almacenista@almatrack.com |

### Products

| Método | Ruta | Permiso | Body |
|--------|------|---------|------|
| `GET` | `http://localhost/products` | ver productos | — |
| `GET` | `http://localhost/products/create` | crear productos | — |
| `POST` | `http://localhost/products` | crear productos | `name=Producto X&sku=PROD-X&description=Desc&price=10.50&stock=100` |
| `GET` | `http://localhost/products/{id}` | ver productos | — |
| `GET` | `http://localhost/products/{id}/edit` | editar productos | — |
| `PUT` | `http://localhost/products/{id}` | editar productos | `name=Producto Y&sku=PROD-Y&description=Desc&price=15.00&stock=50` |
| `DELETE` | `http://localhost/products/{id}` | eliminar productos | — |

### Movements

| Método | Ruta | Permiso | Body |
|--------|------|---------|------|
| `GET` | `http://localhost/movements` | ver productos | — |
| `GET` | `http://localhost/movements/create` | registrar movimiento | — |
| `POST` | `http://localhost/movements` | registrar movimiento | `product_id=1&type=entry&quantity=10&warehouse_id=1&notes=Ingreso` |
| `GET` | `http://localhost/movements/{id}/edit` | (implicit) | — |
| `PUT` | `http://localhost/movements/{id}` | (implicit) | `product_id=1&type=entry&quantity=5&warehouse_id=1` |
| `DELETE` | `http://localhost/movements/{id}` | (implicit) | — |
| `POST` | `http://localhost/movements/{id}/approve` | aprobar movimiento | — |

### Roles

| Método | Ruta | Permiso | Body |
|--------|------|---------|------|
| `GET` | `http://localhost/roles` | gestionar roles | — |
| `GET` | `http://localhost/roles/create` | gestionar roles | — |
| `POST` | `http://localhost/roles` | gestionar roles | `name=editor&permissions[]=1&permissions[]=2` |
| `GET` | `http://localhost/roles/{id}/edit` | gestionar roles | — |
| `PUT` | `http://localhost/roles/{id}` | gestionar roles | `name=editor-v2&permissions[]=1` |
| `DELETE` | `http://localhost/roles/{id}` | gestionar roles | — |

> `DELETE /roles/{id}` lanza 403 si el rol es `admin` (protegido).

### Logout

```
POST http://localhost/logout
```

---

## API Routes (guard `api`)

Requieren token Sanctum en header:

```
Authorization: Bearer {token}
```

Obtener token (desde Tinker o similar):

```bash
php artisan tinker
> $user = User::where('email', 'repartidor@almatrack.com')->first();
> $user->createToken('api-token')->plainTextToken;
```

| Método | Ruta | Permiso | Body |
|--------|------|---------|------|
| `GET` | `http://localhost/api/products` | ver productos (api) | — |
| `POST` | `http://localhost/api/deliveries/{id}/confirm` | confirmar entrega (api) | — (body vacío) |

---

## Matriz de permisos por rol

| Rol | Guard | Permisos |
|-----|-------|----------|
| admin | web | ver productos, crear productos, editar productos, eliminar productos, registrar movimiento, aprobar movimiento, gestionar roles |
| supervisor | web | ver productos, crear productos, editar productos, registrar movimiento, aprobar movimiento |
| almacenista | web | ver productos, registrar movimiento (solo en su almacén) |
| repartidor | api | ver productos, confirmar entrega |
