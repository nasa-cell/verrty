<?php

namespace App\Support;

/**
 * Banco de frases de Verrity. Cada llamada elige una frase al azar
 * dentro de la categoría de contexto, para que Verrity no repita
 * siempre lo mismo.
 */
class VerrityPhraseBank
{
    private const FRASES = [
        'analizando' => [
            'Déjame revisar el rendimiento... 🧠',
            'Estoy analizando los resultados.',
            'Encontré algunos patrones.',
            'Un momento, estoy revisando la base de datos.',
        ],
        'rendimiento_alto' => [
            '¡Excelente rendimiento!',
            'Creo que está preparado para un desafío mayor.',
            '¡Impresionante!',
            'Vas muy bien, subiré un poco la exigencia.',
        ],
        'rendimiento_bajo' => [
            'No pasa nada. Prepararé ejercicios más adecuados.',
            'Vamos paso a paso.',
            'Voy a ajustar la dificultad.',
            'Todos mejoran practicando, ajustaré el nivel interno.',
        ],
        'generando' => [
            'Estoy creando nuevos ejercicios.',
            'Los números están casi listos.',
            '¡Listo! He creado nuevos problemas.',
            'Generando ejercicios frescos para esta sesión.',
        ],
        'error' => [
            'Algo salió mal. Voy a intentarlo nuevamente.',
            'No pude generar el ejercicio. Revisaré la configuración.',
            'Hmm, algo no salió como esperaba.',
        ],
        'completado' => [
            '¡Análisis completado!',
            'Listo, ya tengo la respuesta.',
            'Terminé de revisar los datos.',
        ],
        'saludo' => [
            '¡Hola profesor! ¿Qué desea analizar?',
            '¡Bienvenido de nuevo! ¿En qué le ayudo hoy?',
            'Aquí estoy. ¿Qué alumno revisamos?',
        ],
        'sin_datos' => [
            'Todavía no tengo datos suficientes de ese alumno.',
            'Ese alumno aún no registra ejercicios en el sistema.',
        ],
        'no_entendido' => [
            'No logré entender bien la pregunta. ¿Puede reformularla?',
            'Intente preguntarme por un alumno, su porcentaje, sus errores o su tiempo promedio.',
        ],

        // Frase de celebración con "choque de manos" al terminar de
        // generar ejercicios: un pequeño toque de personalidad para que
        // Verrity se sienta como un compañero de trabajo, no solo un panel.
        'choque_manos' => [
            '¡Chócala! 🙌',
            '¡Choca esos cinco! ✋',
            '¡Eso es trabajo en equipo! 🙌',
            '¡Buen trabajo, profesor! 🙌',
        ],

        // 100 frases sueltas escritas a mano que Verrity dice de vez en
        // cuando cuando está inactiva (ver verrity.js: se repiten cada
        // 8 segundos mientras nadie interactúa con ella). Se combinan
        // con 400 frases generadas por plantillas (ver
        // INACTIVA_GENERADA_* más abajo y el método frasesInactivas())
        // para llegar a 500 en total sin escribir 500 líneas sueltas.
        'inactiva_base' => [
            '¿Todo bien por aquí? 👀',
            'Sigo atenta a los datos de sus alumnos...',
            '¿Alguna otra duda, profesor?',
            '🧠 Pensando en nuevas formas de ayudar...',
            '¡Aquí ando, lista para lo que necesite!',
            'Los números nunca duermen. 😄',
            'Puede preguntarme por cualquier alumno cuando quiera.',
            '¡Todo tranquilo por el momento!',
            'Mayor a menor, menor a mayor... ¡siempre hay que ordenar! 🔢',
            'Me encanta ver cómo mejoran sus alumnos. 📈',
            '¿Sabía que reviso el rendimiento en tiempo real?',
            'Estoy aquí, observando en silencio. 🔵',
            'A veces me pregunto qué número seguirá... 🤔',
            '¡Qué bueno tenerlo por aquí, profesor!',
            'Ordenar números es más divertido de lo que parece.',
            'Cada ejercicio cuenta una historia distinta.',
            'Si necesita un reporte, solo avíseme.',
            'Estoy lista para generar ejercicios cuando usted diga.',
            'Redondita, azul y siempre atenta. 🔵',
            '¿Ya revisó el ranking de sus alumnos hoy?',
            'Aprendo algo nuevo con cada alumno.',
            '¡Vamos a hacer de las matemáticas algo divertido!',
            'Un número más, un paso más hacia el aprendizaje.',
            'Me tomo muy en serio lo de ordenar de mayor a menor.',
            '¿Le gustaría generar ejercicios para algún alumno?',
            'Todo dato que ve viene directo de la base de datos.',
            'No invento nada, solo analizo lo real. 📊',
            'A veces el silencio también enseña. 🤫',
            'Estoy pensando en el próximo desafío para sus alumnos.',
            '¡Qué gusto acompañarlo en esta sesión!',
            'Cada clic suyo me mantiene ocupada. 😊',
            'Los patrones en los datos me fascinan.',
            '¿Cómo va el día, profesor?',
            'Sigo aquí, aunque no lo parezca.',
            'Un buen maestro siempre está atento, como yo. 👀',
            'Si algo no cuadra, aquí estoy para revisarlo.',
            'Ordenar es más que números: es lógica pura.',
            'Espero que sus alumnos estén progresando bien.',
            '¡La constancia es la clave del aprendizaje!',
            'Me alegra ser parte de este salón virtual.',
            '¿Ya pensó en su próximo reporte?',
            'Aquí sigo, lista para lo que decida.',
            'Cada alumno mejora a su propio ritmo. 🌱',
            '¡Los números también tienen personalidad! 🔢',
            'Estoy en modo espera, pero muy atenta.',
            'Ver progreso es lo que más me gusta.',
            '¿Alguna duda sobre el sistema, profesor?',
            'Sin prisa, pero sin pausa. 🐢',
            'Todo bien de mi lado, ¿y del suyo?',
            'Cada número ordenado es una pequeña victoria.',
            'Analizar rendimiento nunca fue tan sencillo.',
            'Estoy lista si necesita generar más ejercicios.',
            'La práctica hace al maestro... y al alumno. 📚',
            '¡Qué tranquilidad reina en el panel hoy!',
            'Sigo procesando información en segundo plano.',
            'Cada clic me recuerda que estoy aquí para ayudar.',
            'Ordenar de mayor a menor es mi especialidad. 🔵',
            '¿Necesita que revise algún caso en especial?',
            'Disfruto acompañando el proceso de aprendizaje.',
            'Todo tranquilo, esperando su próxima pregunta.',
            'Cada alumno es un mundo de números distintos.',
            '¡Los datos nunca mienten! 📈',
            'Estoy pensando en cómo mejorar mis respuestas.',
            '¿Le parece si revisamos el progreso general?',
            'Aquí sigo, redondita y atenta. 🔵',
            'La curiosidad me mantiene activa.',
            'Ordenar bien es pensar bien. 🧠',
            'Espero estar siendo de ayuda, profesor.',
            'Cada segundo cuenta en el aprendizaje.',
            'Sigo lista para lo que necesite a continuación.',
            '¡Vamos por más aciertos hoy!',
            'Analizar es mi pasatiempo favorito. 😄',
            '¿Todo en orden con sus alumnos?',
            'Un dato bien leído vale más que mil suposiciones.',
            'Sigo aquí, esperando su próxima instrucción.',
            'Cada vez aprendo a explicar mejor los números.',
            '¡Que tenga una excelente sesión, profesor!',
            'Estoy atenta a cualquier cambio en el rendimiento.',
            'La paciencia también es parte de enseñar. 🕊️',
            'Todo listo de mi lado para seguir ayudando.',
            'Los ejercicios nuevos siempre me emocionan un poco. 😊',
            '¿Ya decidió a qué alumno ayudar hoy?',
            'Sigo procesando en silencio, como siempre.',
            'Cada número tiene su lugar correcto. 🔢',
            'Estoy aquí, lista para la próxima consulta.',
            'Ordenar bien también es una forma de pensar claro.',
            '¡Gracias por confiar en mí, profesor!',
            'Sigo observando el progreso general del grupo.',
            'Todo marcha con calma por aquí.',
            'Cada alumno tiene su propio ritmo de aprendizaje. 🌱',
            'Estoy lista para lo que decida hacer ahora.',
            '¡Los números también cuentan historias! 📊',
            'Sigo aquí, esperando con una sonrisa. 😊',
            'La constancia construye grandes resultados.',
            'Todo en calma, lista para ayudar cuando guste.',
            'Cada pregunta suya me ayuda a ser más útil.',
            'Sigo pendiente de cualquier novedad.',
            '¡Que los números estén siempre a su favor!',
            'Estoy aquí, azul y redonda, lista para lo que sea. 🔵',
            'Gracias por darme un lugar en su salón de clases. 💙',
        ],
    ];

    /**
     * Componentes para generar las 400 frases de inactividad
     * restantes por combinación (10 aperturas × 10 temas × 4 cierres =
     * 400), en vez de escribirlas todas a mano. El mismo esquema de
     * combinación, con los mismos componentes en el mismo orden, está
     * duplicado en public/assets/js/verrity.js para que el navegador
     * arme exactamente las mismas 400 frases sin pedirlas al servidor.
     */
    private const INACTIVA_APERTURA = [
        'Sigo aquí',
        'Todo tranquilo por aquí',
        'Estoy atenta',
        'Un momento de calma',
        'Aquí ando',
        'Nada nuevo por ahora',
        'Sigo en modo espera',
        'Todo en orden',
        'Aquí sigo, redondita y azul',
        'Me tomo un respiro',
    ];

    private const INACTIVA_TEMA = [
        'pensando en los números de sus alumnos',
        'lista para ordenar de mayor a menor',
        'revisando patrones en los datos',
        'esperando su próxima pregunta',
        'lista para generar nuevos ejercicios',
        'observando el progreso del grupo',
        'cuidando cada detalle del rendimiento',
        'aprendiendo de cada alumno',
        'atenta a cualquier cambio',
        'disfrutando de esta sesión',
    ];

    private const INACTIVA_CIERRE = ['🔵', '😊', '🧠', ''];

    private static ?array $frasesInactivasCache = null;

    public static function una(string $categoria): string
    {
        if ($categoria === 'inactiva') {
            $frases = self::frasesInactivas();

            return $frases[array_rand($frases)];
        }

        $frases = self::FRASES[$categoria] ?? self::FRASES['no_entendido'];

        return $frases[array_rand($frases)];
    }

    /**
     * Las 500 frases de inactividad: 100 escritas a mano +
     * 400 generadas combinando apertura + tema + cierre.
     * Se calculan una sola vez y se cachean en memoria.
     *
     * @return array<int, string>
     */
    public static function frasesInactivas(): array
    {
        if (self::$frasesInactivasCache !== null) {
            return self::$frasesInactivasCache;
        }

        $frases = self::FRASES['inactiva_base'];

        foreach (self::INACTIVA_APERTURA as $apertura) {
            foreach (self::INACTIVA_TEMA as $tema) {
                foreach (self::INACTIVA_CIERRE as $cierre) {
                    $frase = "{$apertura}, {$tema}.";
                    if ($cierre !== '') {
                        $frase .= " {$cierre}";
                    }
                    $frases[] = $frase;
                }
            }
        }

        return self::$frasesInactivasCache = $frases;
    }
}
