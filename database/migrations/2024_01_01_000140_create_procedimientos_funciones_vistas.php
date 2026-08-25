<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * PEA #16 (DCL/TCL, procedimientos y funciones) + PEA #19 (vistas
 * estadísticas). MySQL no permite crear procedimientos/funciones a
 * través del Query Builder de Laravel, por lo que se usa SQL nativo
 * con DB::unprepared().
 *
 * NOTA DE COMPATIBILIDAD: algunos hostings gratuitos compartidos (por
 * ejemplo InfinityFree) restringen el privilegio CREATE ROUTINE para
 * cuentas de usuario normales. Si esta migración falla por permisos al
 * desplegar en ese tipo de hosting, la aplicación sigue funcionando
 * igual: la misma lógica de fn_porcentaje_aciertos() y
 * sp_actualizar_rendimiento() está también implementada en PHP dentro
 * de app/Services/StatisticsService.php y
 * app/Services/AdaptiveLearningService.php como respaldo funcional.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_actualizar_rendimiento');
        DB::unprepared(<<<'SQL'
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
                INTO v_total, v_aciertos, v_errores, v_tiempo
                FROM respuestas
                WHERE alumno_id = p_alumno_id;

                IF v_total > 0 THEN
                    SET v_porcentaje = (v_aciertos / v_total) * 100;
                END IF;

                INSERT INTO rendimiento_alumnos (
                    alumno_id, total_ejercicios, total_aciertos, total_errores,
                    porcentaje_aciertos, porcentaje_errores, tiempo_promedio_segundos,
                    ultima_evaluacion, created_at, updated_at
                )
                VALUES (
                    p_alumno_id, v_total, v_aciertos, v_errores,
                    v_porcentaje, 100 - v_porcentaje, v_tiempo,
                    NOW(), NOW(), NOW()
                )
                ON DUPLICATE KEY UPDATE
                    total_ejercicios = v_total,
                    total_aciertos = v_aciertos,
                    total_errores = v_errores,
                    porcentaje_aciertos = v_porcentaje,
                    porcentaje_errores = 100 - v_porcentaje,
                    tiempo_promedio_segundos = v_tiempo,
                    ultima_evaluacion = NOW(),
                    updated_at = NOW();
            END
        SQL);

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_estadisticas_generales');
        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE sp_estadisticas_generales()
            BEGIN
                SELECT
                    COUNT(DISTINCT r.alumno_id) AS total_alumnos,
                    COUNT(r.id) AS total_respuestas,
                    COALESCE(SUM(r.es_correcta = TRUE), 0) AS total_aciertos,
                    COALESCE(SUM(r.es_correcta = FALSE), 0) AS total_errores,
                    COALESCE(AVG(r.tiempo_segundos), 0) AS tiempo_promedio
                FROM respuestas r;
            END
        SQL);

        DB::unprepared('DROP FUNCTION IF EXISTS fn_porcentaje_aciertos');
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION fn_porcentaje_aciertos(p_aciertos INT, p_total INT)
            RETURNS DECIMAL(5,2)
            DETERMINISTIC
            BEGIN
                IF p_total = 0 THEN
                    RETURN 0;
                END IF;
                RETURN ROUND((p_aciertos / p_total) * 100, 2);
            END
        SQL);

        DB::unprepared('DROP FUNCTION IF EXISTS fn_calcular_puntaje');
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION fn_calcular_puntaje(p_correcta BOOLEAN, p_tiempo INT, p_tiempo_limite INT, p_dificultad INT)
            RETURNS INT
            DETERMINISTIC
            BEGIN
                DECLARE v_base INT DEFAULT 100;
                DECLARE v_bonus INT DEFAULT 0;

                IF p_correcta = FALSE THEN
                    RETURN 0;
                END IF;

                IF p_tiempo_limite > 0 THEN
                    SET v_bonus = GREATEST(0, ROUND(((p_tiempo_limite - p_tiempo) / p_tiempo_limite) * 50));
                END IF;

                RETURN (v_base * p_dificultad / 5) + v_bonus;
            END
        SQL);

        DB::unprepared('DROP VIEW IF EXISTS vw_rendimiento_alumnos');
        DB::unprepared(<<<'SQL'
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
            LEFT JOIN rendimiento_alumnos r ON u.id = r.alumno_id
            WHERE u.rol = 'alumno'
        SQL);

        DB::unprepared('DROP VIEW IF EXISTS vw_estadisticas_generales');
        DB::unprepared(<<<'SQL'
            CREATE VIEW vw_estadisticas_generales AS
            SELECT
                COUNT(DISTINCT r.alumno_id) AS total_alumnos,
                COUNT(r.id) AS total_respuestas,
                COALESCE(SUM(r.es_correcta = TRUE), 0) AS total_aciertos,
                COALESCE(SUM(r.es_correcta = FALSE), 0) AS total_errores,
                COALESCE(AVG(r.tiempo_segundos), 0) AS tiempo_promedio,
                COALESCE(AVG(CASE WHEN r.es_correcta = TRUE THEN 100 ELSE 0 END), 0) AS promedio_general
            FROM respuestas r
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP VIEW IF EXISTS vw_estadisticas_generales');
        DB::unprepared('DROP VIEW IF EXISTS vw_rendimiento_alumnos');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_calcular_puntaje');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_porcentaje_aciertos');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_estadisticas_generales');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_actualizar_rendimiento');
    }
};
