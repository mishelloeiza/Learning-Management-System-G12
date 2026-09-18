# Learning-Management-System-G12


## Conexion a la base de datos: 1 conexion, 3 usuarios de MySQL

No hay archivos de conexion duplicados. Todo vive en `config/`, una sola vez:

- `config/Connection.php` — la clase de conexion, escrita una sola vez,
  recibe el rol ("admin", "estudiante" o "notuser") y elige el usuario
  de MySQL correspondiente
- `config/Configuration.php` — define las constantes leyendo `config/.env`
- `config/.env` — UN SOLO archivo, con las credenciales de los 3 roles
- `config/usuarios_mysql.sql` — crea las vistas y los 3 usuarios reales
  de MySQL con sus privilegios (GRANT)

### Los 3 usuarios de MySQL

| Usuario MySQL | Usado por | Accede a |
|---|---|---|
| `administrador` | `api/admin/*` | Toda la base (`ALL PRIVILEGES`) |
| `estudiante` | `api/estudiante/*` | Vista `usuarios_1` (su propio perfil) y `carreras` |
| `notuser` | `api/notuser/*` | Solo `SELECT` en la vista `usuarios_3` (login compartido, antes de saber el rol) |

### Las vistas de `usuarios` (ya filtradas por rol)

`script_bd_tutorias.sql` ya define 3 vistas filtradas por rol:

- `usuarios_1` → solo estudiantes (`id_rol = 1`)
- `usuarios_2` → solo tutores (`id_rol = 2`)
- `usuarios_3` → solo administradores (`id_rol = 3`)

`adm_login.php` usa `usuarios_3` y `stu_login.php` usa `usuarios_1` a
propósito: cada login solo debe reconocer credenciales de su propio rol.

**Bug encontrado y corregido:** `api/notuser/not_login.php` (el login
compartido, antes de saber si es estudiante o admin) estaba consultando
`usuarios_3` — es decir, solo dejaba entrar a administradores. Se corrigió
para usar una vista nueva, `usuarios_login` (creada en
`config/usuarios_mysql.sql`), que sí incluye todos los roles pero con
columnas mínimas (solo lo necesario para validar credenciales).

### Como usarlo

1. Corre `config/usuarios_mysql.sql` en phpMyAdmin (una sola vez).
2. Copia `config/.env.example` como `config/.env` y llena las
   contrasenas reales (deben coincidir con las del paso 1).
3. La conexion se crea indicando el rol:

```php
$cn = new Connection("admin");       // en api/admin/*
$cn = new Connection("estudiante");  // en api/estudiante/*
$cn = new Connection("notuser");     // en api/notuser/*
```

El `.env` **no se sube a git** (esta en `config/.gitignore`). Cada quien
en el equipo crea su propio `.env` local a partir de `.env.example`.
