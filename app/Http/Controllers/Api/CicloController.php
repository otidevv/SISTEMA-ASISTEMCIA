<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ciclo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CicloController extends Controller
{
    public function index()
    {
        $ciclos = Ciclo::with(['creadoPor', 'actualizadoPor'])
            ->withCount('inscripciones')
            ->get();

        $data = $ciclos->map(function ($ciclo) {
            // Actualizar porcentaje de avance
            $porcentajeCalculado = $ciclo->calcularPorcentajeAvance();
            if ($porcentajeCalculado != $ciclo->porcentaje_avance) {
                $ciclo->porcentaje_avance = $porcentajeCalculado;
                $ciclo->save();
            }

            return [
                'id' => $ciclo->id,
                'codigo' => $ciclo->codigo,
                'nombre' => $ciclo->nombre,
                'descripcion' => $ciclo->descripcion,
                'fecha_inicio' => $ciclo->fecha_inicio->format('Y-m-d'),
                'fecha_fin' => $ciclo->fecha_fin->format('Y-m-d'),
                'porcentaje_amonestacion' => $ciclo->porcentaje_amonestacion,
                'porcentaje_inhabilitacion' => $ciclo->porcentaje_inhabilitacion,
                'fecha_primer_examen' => $ciclo->fecha_primer_examen ? $ciclo->fecha_primer_examen->format('Y-m-d') : null,
                'fecha_segundo_examen' => $ciclo->fecha_segundo_examen ? $ciclo->fecha_segundo_examen->format('Y-m-d') : null,
                'fecha_tercer_examen' => $ciclo->fecha_tercer_examen ? $ciclo->fecha_tercer_examen->format('Y-m-d') : null,
                'proximo_examen' => $ciclo->getProximoExamen(),
                'dias_habiles' => $ciclo->getTotalDiasHabiles(),
                'limite_faltas_amonestacion' => $ciclo->getLimiteFaltasAmonestacion(),
                'limite_faltas_inhabilitacion' => $ciclo->getLimiteFaltasInhabilitacion(),
                'porcentaje_avance' => $ciclo->porcentaje_avance,
                'es_activo' => $ciclo->es_activo,
                'estado' => $ciclo->estado,
                'inscripciones_count' => $ciclo->inscripciones_count,
                'creado_por' => $ciclo->creadoPor ? $ciclo->creadoPor->nombre . ' ' . $ciclo->creadoPor->apellido_paterno : null,
                'actions' => $this->getActionButtons($ciclo)
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    private function getActionButtons($ciclo)
    {
        $buttons = '';

        if (auth()->user()->hasPermission('ciclos.edit')) {
            $buttons .= '<a href="javascript:void(0)" class="btn btn-sm btn-primary edit-ciclo" data-id="' . $ciclo->id . '" title="Editar"><i class="uil uil-edit"></i></a> ';
        }

        if (auth()->user()->hasPermission('ciclos.activate')) {
            if (!$ciclo->es_activo) {
                $buttons .= '<a href="javascript:void(0)" class="btn btn-sm btn-success activate-ciclo" data-id="' . $ciclo->id . '" title="Activar"><i class="uil uil-check-circle"></i></a> ';
            } else {
                $buttons .= '<a href="javascript:void(0)" class="btn btn-sm btn-warning deactivate-ciclo" data-id="' . $ciclo->id . '" title="Desactivar"><i class="uil uil-times-circle"></i></a> ';
            }
        }

        if (auth()->user()->hasPermission('ciclos.delete')) {
            $buttons .= '<a href="javascript:void(0)" class="btn btn-sm btn-danger delete-ciclo" data-id="' . $ciclo->id . '" title="Eliminar"><i class="uil uil-trash-alt"></i></a>';
        }

        return $buttons;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo' => 'required|string|max:20|unique:ciclos',
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'porcentaje_amonestacion' => 'nullable|numeric|min:0|max:100',
            'porcentaje_inhabilitacion' => 'nullable|numeric|min:0|max:100',
            'fecha_primer_examen' => 'nullable|date|after_or_equal:fecha_inicio',
            'fecha_segundo_examen' => 'nullable|date|after:fecha_primer_examen',
            'fecha_tercer_examen' => 'nullable|date|after:fecha_segundo_examen',
            'estado' => 'required|in:planificado,en_curso,finalizado,cancelado',
            'correlativo_inicial' => 'nullable|integer|min:1',
            // Horarios de receso
            'receso_manana_inicio' => 'nullable|date_format:H:i',
            'receso_manana_fin' => 'nullable|date_format:H:i',
            'receso_tarde_inicio' => 'nullable|date_format:H:i',
            'receso_tarde_fin' => 'nullable|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['creado_por'] = auth()->id();
            $data['porcentaje_avance'] = 0;
            $data['es_activo'] = false;

            // Valores por defecto para porcentajes si no se envían
            $data['porcentaje_amonestacion'] = $data['porcentaje_amonestacion'] ?? 20.00;
            $data['porcentaje_inhabilitacion'] = $data['porcentaje_inhabilitacion'] ?? 30.00;
            $data['correlativo_inicial'] = $data['correlativo_inicial'] ?? 1;

            $ciclo = Ciclo::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ciclo académico creado exitosamente',
                'data' => $ciclo
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el ciclo académico'
            ], 500);
        }
    }

    public function show($id)
    {
        $ciclo = Ciclo::with(['creadoPor', 'actualizadoPor', 'inscripciones'])
            ->find($id);

        if (!$ciclo) {
            return response()->json([
                'success' => false,
                'message' => 'Ciclo académico no encontrado'
            ], 404);
        }

        // Formatear las fechas para evitar problemas de timezone
        $cicloData = $ciclo->toArray();
        $cicloData['fecha_inicio'] = $ciclo->fecha_inicio ? $ciclo->fecha_inicio->format('Y-m-d') : null;
        $cicloData['fecha_fin'] = $ciclo->fecha_fin ? $ciclo->fecha_fin->format('Y-m-d') : null;
        $cicloData['fecha_primer_examen'] = $ciclo->fecha_primer_examen ? $ciclo->fecha_primer_examen->format('Y-m-d') : null;
        $cicloData['fecha_segundo_examen'] = $ciclo->fecha_segundo_examen ? $ciclo->fecha_segundo_examen->format('Y-m-d') : null;
        $cicloData['fecha_tercer_examen'] = $ciclo->fecha_tercer_examen ? $ciclo->fecha_tercer_examen->format('Y-m-d') : null;
        $cicloData['correlativo_inicial'] = $ciclo->correlativo_inicial ?? 1;
        // Agregar campos de receso
        $cicloData['receso_manana_inicio'] = $ciclo->receso_manana_inicio;
        $cicloData['receso_manana_fin'] = $ciclo->receso_manana_fin;
        $cicloData['receso_tarde_inicio'] = $ciclo->receso_tarde_inicio;
        $cicloData['receso_tarde_fin'] = $ciclo->receso_tarde_fin;

        return response()->json([
            'success' => true,
            'data' => $cicloData
        ]);
    }

    public function update(Request $request, $id)
    {
        $ciclo = Ciclo::find($id);

        if (!$ciclo) {
            return response()->json([
                'success' => false,
                'message' => 'Ciclo académico no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'codigo' => 'required|string|max:20|unique:ciclos,codigo,' . $id,
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'porcentaje_amonestacion' => 'nullable|numeric|min:0|max:100',
            'porcentaje_inhabilitacion' => 'nullable|numeric|min:0|max:100',
            'fecha_primer_examen' => 'nullable|date|after_or_equal:fecha_inicio',
            'fecha_segundo_examen' => 'nullable|date|after:fecha_primer_examen',
            'fecha_tercer_examen' => 'nullable|date|after:fecha_segundo_examen',
            'estado' => 'required|in:planificado,en_curso,finalizado,cancelado',
            'correlativo_inicial' => 'nullable|integer|min:1',
            // Horarios de receso
            'receso_manana_inicio' => 'nullable|date_format:H:i',
            'receso_manana_fin' => 'nullable|date_format:H:i',
            'receso_tarde_inicio' => 'nullable|date_format:H:i',
            'receso_tarde_fin' => 'nullable|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['actualizado_por'] = auth()->id();
            $data['porcentaje_avance'] = $ciclo->calcularPorcentajeAvance();

            $ciclo->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ciclo académico actualizado exitosamente',
                'data' => $ciclo
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el ciclo académico'
            ], 500);
        }
    }

    public function destroy($id)
    {
        $ciclo = Ciclo::find($id);

        if (!$ciclo) {
            return response()->json([
                'success' => false,
                'message' => 'Ciclo académico no encontrado'
            ], 404);
        }

        // Verificar si tiene inscripciones
        if ($ciclo->inscripciones()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el ciclo porque tiene inscripciones asociadas'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $ciclo->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ciclo académico eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el ciclo académico'
            ], 500);
        }
    }

    public function activar(Request $request, $id)
    {
        $ciclo = Ciclo::find($id);

        if (!$ciclo) {
            return response()->json([
                'success' => false,
                'message' => 'Ciclo académico no encontrado'
            ], 404);
        }

        DB::beginTransaction();
        try {
            // Por defecto no desactivamos otros, permitiendo múltiples ciclos activos
            $deactivateOthers = $request->input('deactivate_others', false);
            $ciclo->activar($deactivateOthers);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ciclo académico activado exitosamente',
                'data' => $ciclo
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al activar el ciclo académico'
            ], 500);
        }
    }

    public function desactivar($id)
    {
        $ciclo = Ciclo::find($id);

        if (!$ciclo) {
            return response()->json([
                'success' => false,
                'message' => 'Ciclo académico no encontrado'
            ], 404);
        }

        DB::beginTransaction();
        try {
            $ciclo->desactivar();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ciclo académico desactivado exitosamente',
                'data' => $ciclo
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al desactivar el ciclo académico'
            ], 500);
        }
    }

    public function cicloActivo()
    {
        // Si hay varios activos, devolvemos el más reciente por ahora
        // pero incluimos información de que hay múltiples si es el caso
        $ciclos = Ciclo::activo()->orderBy('fecha_inicio', 'desc')->get();

        if ($ciclos->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay un ciclo activo'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $ciclos->first(),
            'total_activos' => $ciclos->count(),
            'todos_activos' => $ciclos
        ]);
    }
}
