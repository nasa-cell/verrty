# ORDENIA IA: ordenar números de mayor a menor (con Verrity)

Sistema web educativo para practicar **ordenar números de mayor a menor**. Tiene tres roles (alumno, profesor y
administrador), una IA propia llamada **Verrity** que ayuda al profesor, voz que lee los ejercicios, reportes en PDF y
una base de datos MySQL completa.

Hecho con **PHP 8.2+, Laravel 12 y MySQL**.

## Contenido

- [Cuentas y contraseñas de prueba](#cuentas-y-contraseñas-de-prueba)
- [Qué hace cada rol](#qué-hace-cada-rol)
- [Verrity, la IA del sistema](#verrity-la-ia-del-sistema)
- [Voz de Verrity](#voz-de-verrity)
- [Base de datos](#base-de-datos)
- [Instalar en tu PC (paso a paso)](#instalar-en-tu-pc-paso-a-paso)
- [Subir a un hosting gratis (InfinityFree)](#subir-a-un-hosting-gratis-infinityfree)
- [Carpetas del proyecto](#carpetas-del-proyecto)
- [Problemas comunes](#problemas-comunes)

## Cuentas y contraseñas de prueba

Vienen ya cargadas en la base de datos:

| Rol | Correo | Contraseña |
|---|---|---|
| Profesor | `profesor@ordenia.com` | `profesor123` |
| Alumno | `alumno@ordenia.com` | `alumno123` |
| Administrador | `admin@ordenia.com` | `admin123` |
| Alumno (Juan Pérez) | `juan@ordenia.com` | `alumno123` |
| Alumno (María Gómez) | `maria@ordenia.com` | `alumno123` |
| Alumno (Carlos Ruiz) | `carlos@ordenia.com` | `alumno123` |

**Código para recuperar la contraseña del profesor:** `2026`
(en la pantalla de inicio de sesión, enlace «¿Sos profesor y olvidaste tu contraseña?»). Se cambia con
`RECUPERACION_CODIGO_PROFESOR` en el archivo `.env`.

> Son cuentas de prueba. Si lo publicas en internet, cambia estas contraseñas y el código de recuperación.

## Qué hace cada rol

### Alumno
- Juega a **ordenar números de mayor a menor** con temporizador por ejercicio.
- La dificultad se adapta sola según sus aciertos y su tiempo:
  - Niveles 1–2: 3 o 4 números positivos.
  - Niveles 3–5: 5 a 7 números positivos.
  - Niveles 6–7: 8 a 10 números positivos.
  - Niveles 8–9: 8 a 10 números con **negativos**.
  - Nivel 10: 8 a 10 números **decimales**.
- Ve su racha de aciertos 🔥, tiempo promedio, últimas partidas y ranking.
- Gana logros: *Primer ejercicio*, *Cinco aciertos*, *Diez aciertos*, *Racha de 10* y *Maestro del orden* (90 % de precisión).
- Se puede registrar solo desde la pantalla de **Registro**.

### Profesor
- Panel con estadísticas de todos los alumnos y buscador de alumnos.
- Le pregunta cosas a **Verrity**, por ejemplo «¿Quién necesita practicar más?», «¿Cuál es el mejor alumno?» o
  «¿Quién tiene más errores?». Para un alumno concreto usa el selector «pregunta + alumno» (ejercicios, ejercicios de
  hoy, errores, racha, puntaje, porcentaje, tiempo o dificultad).
- Le pide a Verrity generar ejercicios para un alumno.
- **Reportes**: general o por alumno, para ver en pantalla, imprimir o descargar en **PDF**.
- Recupera su contraseña con el código del sistema (no hace falta correo).

### Administrador
- Cambia la configuración de la IA (cómo sube o baja la dificultad).
- Activa o desactiva usuarios.

## Verrity, la IA del sistema

Verrity **no usa ninguna API externa** (ni ChatGPT ni Gemini): funciona sin internet y gratis.

- Entiende las preguntas del profesor con reglas propias y busca al alumno nombrado en la tabla `usuarios`.
- Responde siempre con datos reales de la base de datos (aciertos, tiempos, rachas).
- Un **árbol de decisión** (`DecisionTreeService`) decide si el alumno sube, baja o se queda en su dificultad, y cada
  decisión se guarda en la tabla `decisiones_ia`.
- Los ejercicios que genera se guardan en `generaciones_ia` y `ejercicios`.

## Voz de Verrity

La voz lee los ejercicios y las respuestas de Verrity. Hay dos formas:

1. **Voz Piper (mejor calidad, sin internet)**: el programa Piper ya viene en `storage/piper/`. Falta solo el **modelo de
   voz** (pesa 109 MB y GitHub no deja subir archivos de más de 100 MB). Descarga estos dos archivos y ponlos en
   `storage/app/voces/`:
   - [es_AR-daniela-high.onnx](https://huggingface.co/rhasspy/piper-voices/resolve/main/es/es_AR/daniela/high/es_AR-daniela-high.onnx)
   - [es_AR-daniela-high.onnx.json](https://huggingface.co/rhasspy/piper-voices/resolve/main/es/es_AR/daniela/high/es_AR-daniela-high.onnx.json)
2. **Voz del navegador (automática)**: si no pones el modelo, o el hosting no deja ejecutar programas, el sistema usa
   la voz del navegador sin que tengas que hacer nada.

El Piper incluido es para **Windows** (`piper.exe`). Para usar otra voz, cambia `PIPER_MODEL` en `.env`
(ver `storage/app/voces/LEEME.md`).

## Base de datos

Está en la carpeta **`base-de-datos/`**:

| Archivo | Para qué |
|---|---|
| `ordenar-mayor-a-menor.sql` | **Base completa para tu PC** (MySQL Workbench, phpMyAdmin o consola). Crea la base `ordenia_ia` con todo. |
| `mayorymenor-phpmyadmin.sql` | Igual, pero **sin** `CREATE DATABASE`, para hostings como InfinityFree donde la base ya existe. |

> ⚠️ `ordenar-mayor-a-menor.sql` empieza con `DROP DATABASE IF EXISTS ordenia_ia`: si ya tienes una base con ese
> nombre, **la borra** y la crea de nuevo.

Qué trae:

- **23 tablas**: `usuarios`, `configuracion`, `configuracion_ia`, `rendimiento_alumnos`, `ejercicios`, `respuestas`,
  `generaciones_ia`, `decisiones_ia`, `partidas`, `progreso`, `logros`, `usuario_logros`, `reportes`, `auditoria`,
  `sesiones_usuarios` y las que Laravel necesita (`sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`,
  `failed_jobs`, `password_reset_tokens`, `migrations`).
- **Procedimiento** `sp_actualizar_rendimiento` (recalcula el rendimiento de un alumno).
- **Función** `fn_porcentaje_aciertos`.
- **Vistas** `vw_rendimiento_alumnos` y `vw_estadisticas_generales`.
- **Datos iniciales**: configuración, configuración de la IA, los 5 logros y las 6 cuentas de prueba.

También están las **migraciones** de Laravel en `database/migrations/`, que crean las mismas tablas, y los seeders con
los datos de prueba (`php artisan migrate --seed`).

## Instalar en tu PC (paso a paso)

Probado en Windows con PHP 8.5 y MySQL 9.6, partiendo de una copia recién descargada.

### Necesitas
- [PHP 8.2 o más nuevo](https://windows.php.net/download/) con las extensiones `pdo_mysql`, `mbstring`, `openssl`,
  `fileinfo`, `gd` y `zip` activadas en `php.ini`.
- [Composer](https://getcomposer.org/download/).
- [MySQL 8 o más nuevo](https://dev.mysql.com/downloads/installer/) (o MariaDB / XAMPP).

### Pasos

1. **Descargar el proyecto**
   ```
   git clone https://github.com/nasa-cell/verrty.git
   cd verrty
   ```
   (o botón verde **Code → Download ZIP** y descomprimir).

2. **Instalar las librerías de PHP**
   ```
   composer install
   ```

3. **Crear la base de datos**: importa `base-de-datos/ordenar-mayor-a-menor.sql`.
   - En MySQL Workbench: *File → Open SQL Script*, elige el archivo y pulsa el rayo ⚡.
   - En consola:
     ```
     mysql -u root -p < base-de-datos/ordenar-mayor-a-menor.sql
     ```

4. **Crear el archivo `.env`** copiando el ejemplo:
   ```
   copy .env.example .env
   ```
   Ábrelo y pon los datos de tu MySQL:
   ```
   DB_DATABASE=ordenia_ia
   DB_USERNAME=root
   DB_PASSWORD=tu_contraseña_de_mysql
   ```

5. **Generar la clave de la aplicación**
   ```
   php artisan key:generate
   ```

6. **Encender el sistema**
   ```
   php artisan serve
   ```
   Abre **http://localhost:8000** y entra con una de las [cuentas de prueba](#cuentas-y-contraseñas-de-prueba).

7. *(Opcional)* Descarga el modelo de voz Piper (ver [Voz de Verrity](#voz-de-verrity)).

**Sin importar el .sql:** en el paso 3 basta con crear una base vacía `ordenia_ia` y, después del paso 5, ejecutar
`php artisan migrate --seed`.

## Subir a un hosting gratis (InfinityFree)

1. En el panel de InfinityFree crea una base MySQL y abre **phpMyAdmin**.
2. Elige esa base e importa `base-de-datos/mayorymenor-phpmyadmin.sql`.
3. Sube el proyecto (con la carpeta `vendor` ya creada con `composer install`) y apunta el dominio a la carpeta `public/`.
4. En `.env` pon los datos de la base que te dio InfinityFree (`DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`),
   `APP_ENV=production` y `APP_DEBUG=false`.
5. InfinityFree no deja ejecutar Piper: la voz usará la del navegador sola.

## Carpetas del proyecto

| Carpeta | Qué tiene |
|---|---|
| `app/Http/Controllers/` | Controladores por rol: `Alumno/` (panel y juego), `Profesor/` (panel y Verrity), `Admin/`, `Auth/` (inicio de sesión, registro, recuperación), `ReporteController` y `VozController`. |
| `app/Services/` | La lógica: `VerrityAIService` (IA), `DecisionTreeService` (árbol de decisión), `AdaptiveLearningService` (dificultad adaptable), `ExerciseGeneratorService` (crea ejercicios), `StatisticsService`, `ReportService` (PDF), `TimerService` (temporizador) y `VoiceService` (voz Piper). |
| `app/Models/` | Modelos de las tablas. |
| `resources/views/` | Pantallas: `alumno/`, `profesor/`, `admin/`, `auth/`, `reportes/` y `layouts/`. |
| `routes/web.php` | Todas las rutas, separadas por rol. |
| `config/recuperacion.php` | Código de recuperación del profesor. |
| `base-de-datos/` | Los dos archivos `.sql` de la base de datos. |
| `database/migrations/`, `database/seeders/` | Tablas y datos de prueba en formato Laravel. |
| `storage/piper/` | Programa de voz Piper para Windows. |
| `storage/app/voces/` | Aquí va el modelo de voz que se descarga aparte. |
| `_sin_usar/` | Archivos que ya no se usan (guardados por si acaso). |

## Problemas comunes

| Problema | Solución |
|---|---|
| `could not find driver` | Activa `extension=pdo_mysql` en `php.ini`. |
| `No application encryption key has been specified` | Ejecuta `php artisan key:generate`. |
| `Access denied for user 'root'` | Revisa `DB_USERNAME` y `DB_PASSWORD` en `.env`. |
| `Table 'ordenia_ia.sessions' doesn't exist` | Falta importar la base: repite el paso 3. |
| No se escucha la voz Piper | Falta el modelo en `storage/app/voces/`. Mientras tanto se usa la voz del navegador. |
| No carga el PDF de reportes | Ejecuta `composer install` de nuevo (usa `barryvdh/laravel-dompdf`). |
