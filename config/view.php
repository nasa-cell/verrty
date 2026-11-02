<?php

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    |
    | Most templating systems load templates from disk. Here you may specify
    | an array of paths that should be checked for your views. Of course
    | the usual Laravel view path has already been registered for you.
    |
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | This option determines where all the compiled Blade templates will be
    | stored for your application. Typically, this is within the storage
    | directory. However, as usual, you are free to change this value.
    |
    | No usamos realpath() aca (a diferencia del default de Laravel): en
    | hosting compartido (InfinityFree) la carpeta storage/framework/views
    | a veces no sobrevive la descompresion del zip, y realpath() sobre una
    | ruta inexistente devuelve false en vez de la ruta, lo que rompe la
    | app entera con "Please provide a valid cache path.". Con storage_path()
    | a secas, Laravel crea la carpeta sola la primera vez que compila una
    | vista.
    |
    */

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        storage_path('framework/views')
    ),

];
