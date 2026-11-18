# API de Reservas para Coworking

API REST en Laravel para gestionar la reserva de espacios de un coworking
(salas, escritorios y cabinas). Usa Laravel Sanctum para autenticación con
tokens y un sistema propio de roles y permisos.

## Requisitos
- PHP 8.2+
- Composer
- MySQL / MariaDB

## Instalación
```bash
git clone <URL_DEL_REPOSITORIO>
cd coworking_api
composer install
cp .env.example .env
php artisan key:generate
```
Configura la base de datos en `.env` (ver variables abajo), crea la base
vacía y ejecuta:
```bash
php artisan migrate:fresh --seed
php artisan serve
```
La API queda en `http://127.0.0.1:8000/api`.

## Variables de entorno
| Variable | Ejemplo | Descripción |
|---|---|---|
| `APP_TIMEZONE` | `America/Lima` | Zona horaria usada al comparar horas de reservas |
| `DB_CONNECTION` | `mysql` | Motor de base de datos |
| `DB_HOST` | `127.0.0.1` | Servidor de la BD |
| `DB_PORT` | `3306` | Puerto |
| `DB_DATABASE` | `coworking_api` | Nombre de la BD |
| `DB_USERNAME` | `root` | Usuario |
| `DB_PASSWORD` | *(vacío)* | Contraseña |

## Credenciales de prueba
Todas con la contraseña `password123`.

| Rol | Email | Permisos |
|---|---|---|
| admin | admin@coworking.test | 25 (todos) |
| staff | staff@coworking.test | 6 |
| client | client@coworking.test | 4 |
| client | client2@coworking.test | 4 |

## Autenticación
Enviar en cada petición protegida:
```
Accept: application/json
Authorization: Bearer <token>
```
El token se obtiene en `POST /api/login` (`data.token`).

## Formato de respuestas
```json
{ "success": true, "message": "Reserva creada correctamente", "data": {} }
```
```json
{ "success": false, "message": "El espacio ya está reservado en ese horario", "errors": null }
```
Códigos usados: 200, 201, 401, 403, 404, 409, 422, 500.

## Endpoints
| Método | Ruta | Permiso | Descripción |
|---|---|---|---|
| POST | /api/register | público | Registro (rol client) |
| POST | /api/login | público | Inicio de sesión |
| POST | /api/logout | autenticado | Cierra sesión |
| GET | /api/me | autenticado | Usuario con roles y permisos |
| GET | /api/spaces | spaces.view | Catálogo (filtros: `space_type_id`, `min_capacity`, `start_time` + `end_time`) |
| GET | /api/spaces/{id} | spaces.view | Detalle de espacio |
| POST/PUT/DELETE | /api/spaces | spaces.create/update/delete | CRUD de espacios |
| GET | /api/space-types | spaces.view | Tipos de espacio |
| GET/POST/PUT/DELETE | /api/space-types | space_types.* | CRUD de tipos |
| POST | /api/reservations | reservations.create | Crear reserva (queda pending) |
| GET | /api/reservations | reservations.view_own | Historial propio (`?status=`) |
| PATCH | /api/reservations/{id}/cancel | reservations.cancel_own | Cancelar reserva propia |
| GET | /api/reservations/pending | reservations.view_pending | Reservas pendientes |
| PATCH | /api/reservations/{id}/approve | reservations.approve | Confirmar |
| PATCH | /api/reservations/{id}/reject | reservations.reject | Rechazar |
| PATCH | /api/reservations/{id}/complete | reservations.complete | Completar |
|
