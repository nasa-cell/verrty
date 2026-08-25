<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SesionUsuario extends Model
{
    public $timestamps = false;

    protected $table = 'sesiones_usuarios';

    protected $fillable = ['usuario_id', 'token', 'ip', 'user_agent', 'inicio', 'ultimo_acceso', 'cierre', 'activa'];

    protected function casts(): array
    {
        return [
            'inicio' => 'datetime',
            'ultimo_acceso' => 'datetime',
            'cierre' => 'datetime',
            'activa' => 'boolean',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
