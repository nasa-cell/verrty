<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logro extends Model
{
    public $timestamps = false;

    protected $table = 'logros';

    protected $fillable = ['nombre', 'descripcion', 'requisito', 'icono', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function alumnos()
    {
        return $this->belongsToMany(Usuario::class, 'usuario_logros', 'logro_id', 'alumno_id')
            ->withPivot('fecha_obtenido');
    }
}
