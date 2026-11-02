<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Código de recuperación para profesores
    |--------------------------------------------------------------------------
    |
    | ORDENIA IA no envía correos reales (MAIL_MAILER=log), así que la
    | recuperación de contraseña del profesor no usa un link por email:
    | usa un código fijo del sistema. El código en sí no cambia nada por
    | su cuenta — solo habilita, si es correcto, el formulario para que
    | el profesor elija una contraseña nueva.
    |
    */

    'codigo_profesor' => env('RECUPERACION_CODIGO_PROFESOR', '2026'),

];
