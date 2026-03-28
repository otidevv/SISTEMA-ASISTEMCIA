<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApoderadoReforzamiento extends Model
{
    use HasFactory;

    protected $table = 'apoderados_reforzamiento';

    protected $fillable = [
        'inscripcion_id',
        'numero_documento',
        'nombres',
        'celular',
        'email',
        'parentesco',
    ];

    public function inscripcion()
    {
        return $this->belongsTo(InscripcionReforzamiento::class, 'inscripcion_id');
    }
}
