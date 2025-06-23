<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoDocente extends Model
{
    use HasFactory;

    protected $table = 'pagos_docentes'; // Asegúrate que coincida con tu tabla

    protected $fillable = [
        'docente_id',
        'tarifa_por_hora',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $dates = [
        'fecha_inicio',
        'fecha_fin',
    ];

    // Relación con el docente
    public function docente()
    {
        return $this->belongsTo(User::class, 'docente_id');
    }
}