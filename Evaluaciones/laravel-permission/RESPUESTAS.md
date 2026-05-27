# Respuestas — Evaluación RBAC AlmaTrack S.R.L.

## Pregunta teórica (FASE 6)

Spatie/laravel-permission cachea los permisos en memoria para evitar consultas repetitivas a la base de datos en cada request, lo que mejora significativamente el rendimiento. Sin embargo, si olvidas limpiar esa caché tras modificar roles o permisos en producción, los cambios no se reflejarán hasta que la caché expire o se reinicie el servidor. Esto puede generar una brecha de seguridad activa: por ejemplo, si se revoca el permiso `aprobar movimiento` a un supervisor despedido, este podría seguir accediendo a la ruta de aprobación durante horas hasta que la caché se invalide. Por eso es obligatorio llamar a `app(PermissionRegistrar::class)->forgetCachedPermissions()` inmediatamente después de cualquier mutación, y en producción ejecutar `php artisan permission:cache-reset` tras cambios manuales.

## Aislamiento de guards (FASE 4.3)

El permiso `ver productos` del guard `web` y el del guard `api` son registros distintos en la tabla `permissions` (diferente `guard_name`). Cuando Spatie evalúa `$user->can('ver productos')` en una petición API autenticada con Sanctum, usa el guard activo (`api`), por lo que un token de repartidor nunca podría pasar el middleware `permission:crear productos` del guard `web` — aunque existiera un permiso con ese nombre en web, el guard no coincide y retorna 403. Este aislamiento es automático y estructural; no depende de lógica condicional en el código.
