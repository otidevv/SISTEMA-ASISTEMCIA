<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Turno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TurnoController extends Controller
{
    public function index()
    {
        $turnos = Turno::withCount(['inscripciones' => function ($query) {
            $query->where('estado_inscripcion', 'activo');
        }])
            ->orderBy('orden')
            ->get();

        $data = $turnos->map(function ($turno) {
            return [
                'id' => $turno->id,
                'codigo' => $turno->codigo,
                'nombre' => $turno->nombre,
                'hora_inicio' => substr($turno->hora_inicio, 0, 5), // HH:MM
                'hora_fin' => substr($turno->hora_fin, 0, 5), // HH:MM
                'duracion' => $turno->getDuracionHoras() . ' horas',
                'dias_semana' => $turno->dias_semana,
                'descripcion' => $turno->descripcion,
                'estudiantes_activos' => $turno->inscripciones_count,
                'estado' => $turno->estado,
                'orden' => $turno->orden,
                'fecha_creacion' => $turno->created_at->format('d/m/Y'),
                'actions' => $this->getActionButtons($turno)
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    private function getActionButtons($turno)
    {
        $buttons = '';

        if (auth()->user()->hasPermission('turnos.edit')) {
            $buttons .= '<a href="javascript:void(0)" class="btn btn-sm btn-primary edit-turno" data-id="' . $turno->id . '" title="Editar"><i class="uil uil-edit"></i></a> ';
        }

        if (auth()->user()->hasPermission('turnos.change_status')) {
            $statusIcon = $turno->estado ? 'uil-ban' : 'uil-check';
            $statusTitle = $turno->estado ? 'Desactivar' : 'Activar';
            $statusClass = $turno->estado ? 'warning' : 'success';

            $buttons .= '<a href="javascript:void(0)" class="btn btn-sm btn-' . $statusClass . ' change-status" data-id="' . $turno->id . '" title="' . $statusTitle . '"><i class="uil ' . $statusIcon . '"></i></a> ';
        }

        if (auth()->user()->hasPermission('turnos.delete')) {
            $buttons .= '<a href="javascript:void(0)" class="btn btn-sm btn-danger delete-turno" data-id="' . $turno->id . '" title="Eliminar"><i class="uil uil-trash-alt"></i></a>';
        }

        return $buttons;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo' => 'required|string|max:20|unique:turnos',
            'nombre' => 'required|string|max:50',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'dias_semana' => 'nullable|string|max:50',
            'descripcion' => 'nullable|string',
            'orden' => 'nullable|integer|min:0',
            'estado' => 'boolean'
        ], [
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio',
            'hora_inicio.date_format' => 'El formato de hora debe ser HH:MM',
            'hora_fin.date_format' => 'El formato de hora debe ser HH:MM'
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

            // Convertir formato de hora si viene con segundos
            if (strlen($data['hora_inicio']) == 5) {
                $data['hora_inicio'] .= ':00';
            }
            if (strlen($data['hora_fin']) == 5) {
                $data['hora_fin'] .= ':00';
            }

            $data['estado'] = $request->estado ?? true;
            $data['orden'] = $request->orden ?? $this->getNextOrden();

            $turno = Turno::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Turno creado exitosamente',
                'data' => $turno
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el turno: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getNextOrden()
    {
        return Turno::max('orden') + 1 ?? 1;
    }

    public function show($id)
    {
        $turno = Turno::find($id);

        if (!$turno) {
            return response()->json([
                'success' => false,
                'message' => 'Turno no encontrado'
            ], 404);
        }

        // Formatear horas para el formulario
        $turnoData = $turno->toArray();
        $turnoData['hora_inicio'] = substr($turno->hora_inicio, 0, 5);
        $turnoData['hora_fin'] = substr($turno->hora_fin, 0, 5);

        return response()->json([
            'success' => true,
            'data' => $turnoData
        ]);
    }

    public function update(Request $request, $id)
    {
        $turno = Turno::find($id);

        if (!$turno) {
            return response()->json([
                'success' => false,
                'message' => 'Turno no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'codigo' => 'required|string|max:20|unique:turnos,codigo,' . $id,
            'nombre' => 'required|string|max:50',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'dias_semana' => 'nullable|string|max:50',
            'descripcion' => 'nullable|string',
            'orden' => 'nullable|integer|min:0',
            'estado' => 'boolean'
        ], [
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio',
            'hora_inicio.date_format' => 'El formato de hora debe ser HH:MM',
            'hora_fin.date_format' => 'El formato de hora debe ser HH:MM'
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

            // Convertir formato de hora si viene con segundos
            if (strlen($data['hora_inicio']) == 5) {
                $data['hora_inicio'] .= ':00';
            }
            if (strlen($data['hora_fin']) == 5) {
                $data['hora_fin'] .= ':00';
            }

            $turno->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Turno actualizado exitosamente',
                'data' => $turno
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el turno'
            ], 500);
        }
    }

    public function destroy($id)
    {
        $turno = Turno::find($id);

        if (!$turno) {
            return response()->json([
                'success' => false,
                'message' => 'Turno no encontrado'
            ], 404);
        }

        // Verificar si tiene inscripciones
        if ($turno->inscripciones()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el turno porque tiene inscripciones asociadas'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $turno->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Turno eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el turno'
            ], 500);
        }
    }

    public function changeStatus($id)
    {
        $turno = Turno::find($id);

        if (!$turno) {
            return response()->json([
                'success' => false,
                'message' => 'Turno no encontrado'
            ], 404);
        }

        $turno->estado = !$turno->estado;
        $turno->save();

        $status = $turno->estado ? 'activado' : 'desactivado';

        return response()->json([
            'success' => true,
            'message' => "Turno {$status} exitosamente",
            'data' => $turno
        ]);
    }

    public function porCarrera(Request $request)
    {
        $carreraId = $request->get('carrera_id');
        $cicloId = $request->get('ciclo_id');
        
        // Por ahora retornar todos los turnos activos
        // En el futuro podrías filtrar por carrera si hay relación
        $turnos = Turno::where('estado', true)
            ->orderBy('orden')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $turnos
        ]);
    }
    
    public function listaActivos()
    {
        $turnos = Turno::activos()
            ->ordenados()
            ->select('id', 'codigo', 'nombre', 'hora_inicio', 'hora_fin')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $turnos
        ]);
    }
}
