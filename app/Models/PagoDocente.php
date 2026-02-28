<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PagoDocente extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'pagos_docentes'; // Asegúrate que coincida con tu tabla

    protected $fillable = [
        'docente_id',
        'ciclo_id',
        'tarifa_por_hora',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $dates = [
        'fecha_inicio',
        'fecha_fin',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['tarifa_por_hora', 'fecha_inicio', 'fecha_fin', 'docente_id', 'ciclo_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Pago docente {$eventName}");
    }

    // Relación con el docente
    public function docente()
    {
        return $this->belongsTo(User::class, 'docente_id');
    }

    // Relación con el ciclo
    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class, 'ciclo_id');
    }
}