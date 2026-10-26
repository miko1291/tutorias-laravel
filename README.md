# Control de Tutorías

Sistema del Programa Institucional de Tutorías del Instituto Tecnológico Superior de Valladolid, desarrollado en Laravel.

Incluye expedientes de alumnos, asignación de grupos y tutores, asistencia, diagnósticos, canalizaciones y actividades del programa tutorial. El administrador gestiona las cuentas, los catálogos y las opciones del expediente.

## Instalación local

Requiere PHP 8.3 o superior y Composer. Para compilar los recursos de Vite también se necesita Node.js.

1. Instala las dependencias con `composer install`.
2. Copia `.env.example` a `.env` y configura la conexión de base de datos, la URL y el correo.
3. Genera la clave con `php artisan key:generate`.
4. Crea las tablas con `php artisan migrate`.
5. Configura el servidor local para que apunte a la carpeta `public`.

Los estilos y scripts del portal están en `public/css` y `public/js`. Para los recursos de Vite, usa `npm install` y `npm run build`.

## Pruebas

```powershell
php artisan test
node --test tests/Unit/app-shell.test.cjs
```

Las pruebas de PHP usan una base SQLite en memoria, según `phpunit.xml`.

## Datos de prueba

En el entorno local se pueden cargar cuentas y registros ficticios:

```powershell
php artisan db:seed --class=PruebasPortalSeeder
```

Este seeder está limitado a los entornos `local` y `testing`.

## Migración de datos desde Yii2

La conexión `legacy` apunta por defecto a la base `tutorias` en el mismo servidor MySQL. Configura las variables `LEGACY_DB_*` en `.env` si el origen usa otra conexión.

Revisa primero los datos que se copiarán:

```powershell
php artisan tutorias:migrate-legacy --dry-run
```

Para ejecutar la copia:

```powershell
php artisan tutorias:migrate-legacy
```

El comando conserva los IDs, hashes y roles. Usa una transacción y no sobrescribe filas existentes. Para limitar la copia a ciertas tablas, agrega opciones como `--table=user --table=auth_assignment`.

## Catálogos del expediente

Los estados, municipios y demás listas se editan desde **Catálogos → Estados, municipios y listas**. Los municipios iniciales de Yucatán, Quintana Roo, Campeche y Tabasco proceden del [Catálogo Único del INEGI](https://www.inegi.org.mx/servicios/catalogounico.html).

Las preguntas y las secciones se administran desde **Gestión → Diseñar expediente**. Los manuales por perfil están disponibles en **Ayuda**.

## Dependencias

Laravel se distribuye bajo licencia MIT. Las demás dependencias conservan sus respectivas licencias.
