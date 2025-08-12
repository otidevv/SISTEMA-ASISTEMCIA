<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CentroEducativo extends Model
{
    use HasFactory;

    // Usar la conexión externa
    protected $connection = 'mysql_centros';
    
    protected $table = 'centros_educativos';
    
    // Desactivar timestamps ya que la tabla externa no los tiene
    public $timestamps = false;

    protected $fillable = [
        'id',
        'd_dpto',
        'd_prov',
        'd_dist',
        'cen_edu',
        'd_niv_mod',
        'dir_cen'
    ];

    protected $casts = [];

    // Scopes
    public function scopeActivos($query)
    {
        // Como no hay campo estado, devolver todos
        return $query;
    }

    public function scopePorDepartamento($query, $departamento)
    {
        return $query->where('d_dpto', $departamento);
    }

    public function scopePorProvincia($query, $provincia)
    {
        return $query->where('d_prov', $provincia);
    }

    public function scopePorDistrito($query, $distrito)
    {
        return $query->where('d_dist', $distrito);
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where('cen_edu', 'LIKE', '%' . $termino . '%');
    }

    // Métodos para obtener listas únicas
    public static function getDepartamentos()
    {
        return self::select('d_dpto')
            ->distinct()
            ->orderBy('d_dpto')
            ->pluck('d_dpto');
    }

    public static function getProvincias($departamento)
    {
        return self::where('d_dpto', $departamento)
            ->select('d_prov')
            ->distinct()
            ->orderBy('d_prov')
            ->pluck('d_prov');
    }

    public static function getDistritos($departamento, $provincia)
    {
        return self::where('d_dpto', $departamento)
            ->where('d_prov', $provincia)
            ->select('d_dist')
            ->distinct()
            ->orderBy('d_dist')
            ->pluck('d_dist');
    }

    public static function buscarColegios($departamento, $provincia, $distrito, $termino = null)
    {
        $query = self::where('d_dpto', $departamento)
            ->where('d_prov', $provincia)
            ->where('d_dist', $distrito);
        
        if ($termino) {
            $query->where('cen_edu', 'LIKE', '%' . $termino . '%');
        }
        
        return $query->select('id', 'cen_edu', 'd_niv_mod', 'dir_cen')
            ->orderBy('cen_edu')
            ->get();
    }
}