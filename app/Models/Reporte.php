<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    public $timestamps = false;

    protected $table = 'reportes';

    protected $fillable = ['profesor_id', 'alumno_id', 'tipo', 'titulo', 'contenido', 'fecha_generacion'];

    protected function casts(): array
    {
        return [
            'contenido' => 'array',
            'fecha_generacion' => 'datetime',
        ];
    }

    public function profesor()
    {
        return $this->belongsTo(Usuario::class, 'profesor_id');
    }

    public function alumno()
    {
        return $this->belongsTo(Usuario::class, 'alumno_id');
    }
}
