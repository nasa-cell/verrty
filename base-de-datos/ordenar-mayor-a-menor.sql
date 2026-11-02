-- ============================================================
-- ORDENIA IA - BASE DE DATOS COMPLETA
-- ============================================================

DROP DATABASE IF EXISTS ordenia_ia;

CREATE DATABASE ordenia_ia
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE ordenia_ia;

-- ============================================================
-- 1. USUARIOS
-- Seguridad, roles y autenticación
-- ============================================================

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('alumno','profesor','administrador') NOT NULL DEFAULT 'alumno',
    estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    ultimo_acceso DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- 2. CONFIGURACIÓN DEL SISTEMA
-- ============================================================

CREATE TABLE configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_sistema VARCHAR(100) NOT NULL DEFAULT 'ORDENIA IA',
    tiempo_limite_segundos INT NOT NULL DEFAULT 30,
    cantidad_minima_numeros INT NOT NULL DEFAULT 3,
    cantidad_maxima_numeros INT NOT NULL DEFAULT 10,
    puntaje_base INT NOT NULL DEFAULT 100,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 3. CONFIGURACIÓN DE VERRITY IA
-- ============================================================

CREATE TABLE configuracion_ia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    porcentaje_subir DECIMAL(5,2) NOT NULL DEFAULT 85.00,
    porcentaje_mantener DECIMAL(5,2) NOT NULL DEFAULT 60.00,
    porcentaje_bajar DECIMAL(5,2) NOT NULL DEFAULT 59.99,
    dificultad_minima INT NOT NULL DEFAULT 1,
    dificultad_maxima INT NOT NULL DEFAULT 10,
    numeros_minimos INT NOT NULL DEFAULT 3,
    numeros_maximos INT NOT NULL DEFAULT 10,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- 4. RENDIMIENTO DE ALUMNOS
-- ============================================================

CREATE TABLE rendimiento_alumnos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alumno_id INT NOT NULL UNIQUE,
    total_ejercicios INT NOT NULL DEFAULT 0,
    total_aciertos INT NOT NULL DEFAULT 0,
    total_errores INT NOT NULL DEFAULT 0,
    porcentaje_aciertos DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    porcentaje_errores DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    tiempo_promedio_segundos DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    dificultad_actual INT NOT NULL DEFAULT 1,
    racha_actual INT NOT NULL DEFAULT 0,
    mejor_racha INT NOT NULL DEFAULT 0,
    puntaje_total INT NOT NULL DEFAULT 0,
    ultima_evaluacion DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_rendimiento_alumno
        FOREIGN KEY (alumno_id)
        REFERENCES usuarios(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT chk_dificultad
        CHECK (dificultad_actual BETWEEN 1 AND 10)
);

-- ============================================================
-- 5. EJERCICIOS GENERADOS POR VERRITY
-- ============================================================

CREATE TABLE ejercicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alumno_id INT NOT NULL,
    profesor_id INT NULL,
    numeros_generados TEXT NOT NULL,
    respuesta_correcta TEXT NOT NULL,
    cantidad_numeros INT NOT NULL,
    dificultad INT NOT NULL DEFAULT 1,
    tipo_numeros ENUM(
        'positivos',
        'negativos',
        'mixtos',
        'decimales'
    ) NOT NULL DEFAULT 'positivos',
    generado_por_ia BOOLEAN NOT NULL DEFAULT TRUE,
    estado ENUM(
        'pendiente',
        'respondido',
        'vencido'
    ) NOT NULL DEFAULT 'pendiente',
    tiempo_limite_segundos INT NOT NULL DEFAULT 30,
    fecha_generacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_inicio DATETIME NULL,
    fecha_fin DATETIME NULL,

    CONSTRAINT fk_ejercicio_alumno
        FOREIGN KEY (alumno_id)
        REFERENCES usuarios(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_ejercicio_profesor
        FOREIGN KEY (profesor_id)
        REFERENCES usuarios(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT chk_ejercicio_dificultad
        CHECK (dificultad BETWEEN 1 AND 10),

    CONSTRAINT chk_cantidad_numeros
        CHECK (cantidad_numeros BETWEEN 3 AND 10)
);

-- ============================================================
-- 6. RESPUESTAS
-- ============================================================

CREATE TABLE respuestas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ejercicio_id INT NOT NULL,
    alumno_id INT NOT NULL,
    respuesta_usuario TEXT NOT NULL,
    es_correcta BOOLEAN NOT NULL DEFAULT FALSE,
    tiempo_segundos INT NOT NULL DEFAULT 0,
    puntaje_obtenido INT NOT NULL DEFAULT 0,
    respondido_por_tiempo BOOLEAN NOT NULL DEFAULT FALSE,
    fecha_respuesta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_respuesta_ejercicio
        FOREIGN KEY (ejercicio_id)
        REFERENCES ejercicios(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_respuesta_alumno
        FOREIGN KEY (alumno_id)
        REFERENCES usuarios(id)
        ON DELETE CASCADE,

    CONSTRAINT chk_tiempo_respuesta
        CHECK (tiempo_segundos >= 0),

    CONSTRAINT chk_puntaje
        CHECK (puntaje_obtenido >= 0)
);

-- ============================================================
-- 7. GENERACIONES DE VERRITY IA
-- Registra cada vez que el profesor pulsa:
-- "GENERAR NUEVOS EJERCICIOS"
-- ============================================================

CREATE TABLE generaciones_ia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profesor_id INT NOT NULL,
    alumno_id INT NOT NULL,

    porcentaje_anterior DECIMAL(5,2) NOT NULL,
    tiempo_promedio_anterior DECIMAL(10,2) NOT NULL,

    dificultad_anterior INT NOT NULL,
    dificultad_nueva INT NOT NULL,

    cantidad_numeros INT NOT NULL,

    numeros_generados TEXT NOT NULL,

    decision_ia ENUM(
        'subir_dificultad',
        'mantener_dificultad',
        'bajar_dificultad'
    ) NOT NULL,

    motivo_decision TEXT NOT NULL,

    frase_verrity VARCHAR(500) NULL,

    fecha_generacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_generacion_profesor
        FOREIGN KEY (profesor_id)
        REFERENCES usuarios(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_generacion_alumno
        FOREIGN KEY (alumno_id)
        REFERENCES usuarios(id)
        ON DELETE CASCADE,

    CONSTRAINT chk_dificultad_anterior
        CHECK (dificultad_anterior BETWEEN 1 AND 10),

    CONSTRAINT chk_dificultad_nueva
        CHECK (dificultad_nueva BETWEEN 1 AND 10)
);

-- ============================================================
-- 8. HISTORIAL DE DECISIONES DE IA
-- ============================================================

CREATE TABLE decisiones_ia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alumno_id INT NOT NULL,
    porcentaje_aciertos DECIMAL(5,2) NOT NULL,
    tiempo_promedio DECIMAL(10,2) NOT NULL,
    errores INT NOT NULL DEFAULT 0,
    dificultad_anterior INT NOT NULL,
    decision VARCHAR(100) NOT NULL,
    dificultad_resultante INT NOT NULL,
    explicacion TEXT NOT NULL,
    fecha_decision DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_decision_alumno
        FOREIGN KEY (alumno_id)
        REFERENCES usuarios(id)
        ON DELETE CASCADE
);

-- ============================================================
-- 9. PARTIDAS / EVALUACIONES
-- ============================================================

CREATE TABLE partidas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alumno_id INT NOT NULL,
    total_ejercicios INT NOT NULL DEFAULT 0,
    aciertos INT NOT NULL DEFAULT 0,
    errores INT NOT NULL DEFAULT 0,
    puntaje_total INT NOT NULL DEFAULT 0,
    tiempo_total_segundos INT NOT NULL DEFAULT 0,
    tiempo_promedio_segundos DECIMAL(10,2) DEFAULT 0.00,
    porcentaje DECIMAL(5,2) DEFAULT 0.00,

    estado ENUM(
        'iniciada',
        'finalizada',
        'finalizada_por_tiempo'
    ) NOT NULL DEFAULT 'iniciada',

    fecha_inicio DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_fin DATETIME NULL,

    CONSTRAINT fk_partida_alumno
        FOREIGN KEY (alumno_id)
        REFERENCES usuarios(id)
        ON DELETE CASCADE
);

-- ============================================================
-- 10. PROGRESO
-- ============================================================

CREATE TABLE progreso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alumno_id INT NOT NULL UNIQUE,
    ejercicios_completados INT NOT NULL DEFAULT 0,
    aciertos INT NOT NULL DEFAULT 0,
    errores INT NOT NULL DEFAULT 0,
    porcentaje_progreso DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    dificultad_actual INT NOT NULL DEFAULT 1,
    puntaje_total INT NOT NULL DEFAULT 0,
    ultima_actividad DATETIME NULL,

    CONSTRAINT fk_progreso_alumno
        FOREIGN KEY (alumno_id)
        REFERENCES usuarios(id)
        ON DELETE CASCADE
);

-- ============================================================
-- 11. LOGROS
-- ============================================================

CREATE TABLE logros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NOT NULL,
    requisito INT NOT NULL DEFAULT 0,
    icono VARCHAR(100) NULL,
    activo BOOLEAN DEFAULT TRUE
);

CREATE TABLE usuario_logros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alumno_id INT NOT NULL,
    logro_id INT NOT NULL,
    fecha_obtenido DATETIME DEFAULT CURRENT_TIMESTAMP,

    UNIQUE(alumno_id, logro_id),

    CONSTRAINT fk_usuario_logro_alumno
        FOREIGN KEY (alumno_id)
        REFERENCES usuarios(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_usuario_logro
        FOREIGN KEY (logro_id)
        REFERENCES logros(id)
        ON DELETE CASCADE
);

-- ============================================================
-- 12. REPORTES
-- ============================================================

CREATE TABLE reportes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profesor_id INT NOT NULL,
    alumno_id INT NULL,
    tipo VARCHAR(100) NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    contenido LONGTEXT NOT NULL,
    fecha_generacion DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_reporte_profesor
        FOREIGN KEY (profesor_id)
        REFERENCES usuarios(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_reporte_alumno
        FOREIGN KEY (alumno_id)
        REFERENCES usuarios(id)
        ON DELETE SET NULL
);

-- ============================================================
-- 13. AUDITORÍA / SEGURIDAD
-- ============================================================

CREATE TABLE auditoria (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    accion VARCHAR(100) NOT NULL,
    modulo VARCHAR(100) NOT NULL,
    descripcion TEXT NULL,
    ip VARCHAR(45) NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_auditoria_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON DELETE SET NULL
);

-- ============================================================
-- 14. SESIONES DE USUARIOS (auditoría propia de ORDENIA, NO es
-- la tabla de sesiones de Laravel — esa es la sección 14-B)
-- ============================================================

CREATE TABLE sesiones_usuarios (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    ip VARCHAR(45) NULL,
    user_agent TEXT NULL,
    inicio DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso DATETIME DEFAULT CURRENT_TIMESTAMP,
    cierre DATETIME NULL,
    activa BOOLEAN DEFAULT TRUE,

    CONSTRAINT fk_sesion_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON DELETE CASCADE
);

-- ============================================================
-- 14-B. TABLAS QUE NECESITA EL FRAMEWORK LARAVEL
--
-- ============================================================

CREATE TABLE password_reset_tokens (
    email VARCHAR(255) NOT NULL PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
);

CREATE TABLE sessions (
    id VARCHAR(255) NOT NULL PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    INDEX sessions_user_id_index (user_id),
    INDEX sessions_last_activity_index (last_activity)
);

CREATE TABLE cache (
    `key` VARCHAR(255) NOT NULL PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration INT NOT NULL,
    INDEX cache_expiration_index (expiration)
);

CREATE TABLE cache_locks (
    `key` VARCHAR(255) NOT NULL PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INT NOT NULL,
    INDEX cache_locks_expiration_index (expiration)
);

CREATE TABLE jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    INDEX jobs_queue_index (queue)
);

CREATE TABLE job_batches (
    id VARCHAR(255) NOT NULL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INT NOT NULL,
    pending_jobs INT NOT NULL,
    failed_jobs INT NOT NULL,
    failed_job_ids LONGTEXT NOT NULL,
    options MEDIUMTEXT NULL,
    cancelled_at INT NULL,
    created_at INT NOT NULL,
    finished_at INT NULL
);

CREATE TABLE failed_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL UNIQUE,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de control de migraciones de Laravel. Se deja precargada
-- marcando como "ya corridas" todas las migraciones que este
-- script ya aplicó a mano, para que "php artisan migrate" no
-- intente volver a crear estas mismas tablas después.
CREATE TABLE migrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INT NOT NULL
);

INSERT INTO migrations (migration, batch) VALUES
('0001_01_01_000000_create_usuarios_table', 1),
('0001_01_01_000001_create_cache_table', 1),
('0001_01_01_000002_create_jobs_table', 1),
('2024_01_01_000010_create_configuracion_table', 1),
('2024_01_01_000020_create_configuracion_ia_table', 1),
('2024_01_01_000030_create_rendimiento_alumnos_table', 1),
('2024_01_01_000040_create_ejercicios_table', 1),
('2024_01_01_000050_create_respuestas_table', 1),
('2024_01_01_000060_create_generaciones_ia_table', 1),
('2024_01_01_000070_create_decisiones_ia_table', 1),
('2024_01_01_000080_create_partidas_table', 1),
('2024_01_01_000090_create_progreso_table', 1),
('2024_01_01_000100_create_logros_tables', 1),
('2024_01_01_000110_create_reportes_table', 1),
('2024_01_01_000120_create_auditoria_table', 1),
('2024_01_01_000130_create_sesiones_usuarios_table', 1),
('2024_01_01_000140_create_procedimientos_funciones_vistas', 1);

-- ============================================================
-- 15. PROCEDIMIENTO: ACTUALIZAR RENDIMIENTO
-- ============================================================

DELIMITER $$

CREATE PROCEDURE sp_actualizar_rendimiento(IN p_alumno_id INT)
BEGIN

    DECLARE v_total INT DEFAULT 0;
    DECLARE v_aciertos INT DEFAULT 0;
    DECLARE v_errores INT DEFAULT 0;
    DECLARE v_porcentaje DECIMAL(5,2) DEFAULT 0;
    DECLARE v_tiempo DECIMAL(10,2) DEFAULT 0;

    SELECT
        COUNT(*),
        COALESCE(SUM(es_correcta = TRUE), 0),
        COALESCE(SUM(es_correcta = FALSE), 0),
        COALESCE(AVG(tiempo_segundos), 0)
    INTO
        v_total,
        v_aciertos,
        v_errores,
        v_tiempo
    FROM respuestas
    WHERE alumno_id = p_alumno_id;

    IF v_total > 0 THEN
        SET v_porcentaje = (v_aciertos / v_total) * 100;
    END IF;

    INSERT INTO rendimiento_alumnos (
        alumno_id,
        total_ejercicios,
        total_aciertos,
        total_errores,
        porcentaje_aciertos,
        porcentaje_errores,
        tiempo_promedio_segundos,
        ultima_evaluacion
    )
    VALUES (
        p_alumno_id,
        v_total,
        v_aciertos,
        v_errores,
        v_porcentaje,
        100 - v_porcentaje,
        v_tiempo,
        NOW()
    )
    ON DUPLICATE KEY UPDATE
        total_ejercicios = v_total,
        total_aciertos = v_aciertos,
        total_errores = v_errores,
        porcentaje_aciertos = v_porcentaje,
        porcentaje_errores = 100 - v_porcentaje,
        tiempo_promedio_segundos = v_tiempo,
        ultima_evaluacion = NOW();

END$$

DELIMITER ;

-- ============================================================
-- 16. FUNCIÓN: CALCULAR PORCENTAJE
-- ============================================================

DELIMITER $$

CREATE FUNCTION fn_porcentaje_aciertos(
    p_aciertos INT,
    p_total INT
)
RETURNS DECIMAL(5,2)
DETERMINISTIC
BEGIN

    IF p_total = 0 THEN
        RETURN 0;
    END IF;

    RETURN ROUND((p_aciertos / p_total) * 100, 2);

END$$

DELIMITER ;

-- ============================================================
-- 17. VISTAS PARA REPORTES ESTADÍSTICOS
-- ============================================================

CREATE VIEW vw_rendimiento_alumnos AS
SELECT
    u.id,
    CONCAT(u.nombre, ' ', u.apellido) AS alumno,
    u.email,
    COALESCE(r.total_ejercicios, 0) AS ejercicios,
    COALESCE(r.total_aciertos, 0) AS aciertos,
    COALESCE(r.total_errores, 0) AS errores,
    COALESCE(r.porcentaje_aciertos, 0) AS porcentaje,
    COALESCE(r.tiempo_promedio_segundos, 0) AS tiempo_promedio,
    COALESCE(r.dificultad_actual, 1) AS dificultad,
    COALESCE(r.puntaje_total, 0) AS puntaje
FROM usuarios u
LEFT JOIN rendimiento_alumnos r
    ON u.id = r.alumno_id
WHERE u.rol = 'alumno';

-- ============================================================
-- 18. VISTA ESTADÍSTICA GENERAL
-- ============================================================

CREATE VIEW vw_estadisticas_generales AS
SELECT
    COUNT(DISTINCT r.alumno_id) AS total_alumnos,
    COUNT(r.id) AS total_respuestas,
    COALESCE(SUM(r.es_correcta = TRUE), 0) AS total_aciertos,
    COALESCE(SUM(r.es_correcta = FALSE), 0) AS total_errores,
    COALESCE(AVG(r.tiempo_segundos), 0) AS tiempo_promedio,
    COALESCE(
        AVG(
            CASE
                WHEN r.es_correcta = TRUE THEN 100
                ELSE 0
            END
        ),
        0
    ) AS promedio_general
FROM respuestas r;

-- ============================================================
-- ÍNDICES
-- ============================================================

CREATE INDEX idx_usuarios_rol
ON usuarios(rol);

CREATE INDEX idx_ejercicios_alumno
ON ejercicios(alumno_id);

CREATE INDEX idx_ejercicios_fecha
ON ejercicios(fecha_generacion);

CREATE INDEX idx_respuestas_alumno
ON respuestas(alumno_id);

CREATE INDEX idx_respuestas_fecha
ON respuestas(fecha_respuesta);

CREATE INDEX idx_generaciones_alumno
ON generaciones_ia(alumno_id);

CREATE INDEX idx_generaciones_fecha
ON generaciones_ia(fecha_generacion);

CREATE INDEX idx_decisiones_alumno
ON decisiones_ia(alumno_id);

CREATE INDEX idx_partidas_alumno
ON partidas(alumno_id);

-- ============================================================
-- CONFIGURACIÓN INICIAL
-- ============================================================

INSERT INTO configuracion (
    nombre_sistema,
    tiempo_limite_segundos,
    cantidad_minima_numeros,
    cantidad_maxima_numeros,
    puntaje_base
)
VALUES (
    'ORDENIA IA',
    30,
    3,
    10,
    100
);

INSERT INTO configuracion_ia (
    porcentaje_subir,
    porcentaje_mantener,
    porcentaje_bajar,
    dificultad_minima,
    dificultad_maxima,
    numeros_minimos,
    numeros_maximos
)
VALUES (
    85.00,
    60.00,
    59.99,
    1,
    10,
    3,
    10
);

-- ============================================================
-- LOGROS INICIALES
-- ============================================================

INSERT INTO logros
(nombre, descripcion, requisito, icono)
VALUES
('Primer ejercicio',
 'Completar el primer ejercicio',
 1, 'primer-ejercicio'),

('Cinco aciertos',
 'Conseguir 5 respuestas correctas',
 5, 'cinco-aciertos'),

('Diez aciertos',
 'Conseguir 10 respuestas correctas',
 10, 'diez-aciertos'),

('Racha de 10',
 'Conseguir una racha de 10 aciertos',
 10, 'racha'),

('Maestro del orden',
 'Alcanzar 90% de precisión',
 90, 'maestro');

-- ============================================================
-- USUARIOS DE PRUEBA
-- Hashes bcrypt REALES generados con Hash::make() de Laravel
-- (el script original traía un placeholder de texto plano,
-- '$2y$12$REEMPLAZAR_CON_HASH_LARAVEL', que no es un hash válido
-- y nunca hubiera dejado entrar a nadie).
--
-- Credenciales de acceso (contraseñas en texto plano, solo para
-- que las uses al iniciar sesión — la BD solo guarda el hash):
--   profesor@ordenia.com    / profesor123
--   alumno@ordenia.com      / alumno123
--   admin@ordenia.com       / admin123
--   juan@ordenia.com        / alumno123
--   maria@ordenia.com       / alumno123
--   carlos@ordenia.com      / alumno123
-- ============================================================

INSERT INTO usuarios
(nombre, apellido, email, password, rol)
VALUES
('Profesor', 'Demo', 'profesor@ordenia.com',
 '$2y$12$huKDCnVCjmIL3fK3d1TFN.3PKNT.Xh2DwaJlEOQbN2NI9DWUi8AE2', 'profesor'),

('Alumno', 'Demo', 'alumno@ordenia.com',
 '$2y$12$mHLitFF3zO64S2ZZUvB2OOaP9nn.ywikEawiLJAlUcDaw3nU3pg3i', 'alumno'),

('Administrador', 'Demo', 'admin@ordenia.com',
 '$2y$12$I7z7xPh4MRFNhZ4LJsp6quE3X9VzgDsKdmcHuAQJlZYrBx815Uw8W', 'administrador'),

('Juan', 'Pérez', 'juan@ordenia.com',
 '$2y$12$z1VXAAXBTSf97O4sZD9O/e7Ezzo9RJLKDx.DJP8whprWWPWx2nbwS', 'alumno'),

('María', 'Gómez', 'maria@ordenia.com',
 '$2y$12$iHghFXhYseIIALxwipTjO./346gB7VjZXDwiroPlgi.ABu7eBBuNy', 'alumno'),

('Carlos', 'Ruiz', 'carlos@ordenia.com',
 '$2y$12$ITrDwjDr72a5gaadQWbVge7adGvFrk3BQqRI3bczm4BT26/zq0H/G', 'alumno');

-- ============================================================
-- FIN DE BASE DE DATOS ORDENIA IA (COMPLETA)
-- ============================================================