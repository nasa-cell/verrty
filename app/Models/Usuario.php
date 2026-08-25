<?php

namespace App\Models;

use Database\Factories\UsuarioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Usuario del sistema ORDENIA IA. Sustituye al modelo "User" por defecto
 * de Laravel para reflejar el diseño real de la base de datos (roles
 * alumno/profesor/administrador, tabla "usuarios").
 */
class Usuario extends Authenticatable
{
    /** @use HasFactory<UsuarioFactory> */
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'password',
        'rol',
        'estado',
        'ultimo_acceso',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'ultimo_acceso' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->apellido}");
    }

    public function esProfesor(): bool
    {
        return $this->rol === 'profesor';
    }

    public function esAlumno(): bool
    {
        return $this->rol === 'alumno';
    }

    public function esAdministrador(): bool
    {
        return $this->rol === 'administrador';
    }

    public function rendimiento()
    {
        return $this->hasOne(RendimientoAlumno::class, 'alumno_id');
    }

    public function progreso()
    {
        return $this->hasOne(Progreso::class, 'alumno_id');
    }

    public function ejercicios()
    {
        return $this->hasMany(Ejercicio::class, 'alumno_id');
    }

    public function respuestas()
    {
        return $this->hasMany(Respuesta::class, 'alumno_id');
    }

    public function partidas()
    {
        return $this->hasMany(Partida::class, 'alumno_id');
    }

    public function logros()
    {
        return $this->belongsToMany(Logro::class, 'usuario_logros', 'alumno_id', 'logro_id')
            ->withPivot('fecha_obtenido');
    }
}
