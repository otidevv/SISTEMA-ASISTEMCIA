<?php

// app/Http/Controllers/Api/AulaController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aula;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AulaController extends Controller
{
    public function index()
    {
        $aulas = Aula::all();

        $data = $aulas->map(function ($aula) {
            return [
                'id' => $aula->id,
                'codigo' => $aula->codigo,
                'nombre' => $aula->nombre,
                'capacidad' => $aula->capacidad,
                'tipo' => $aula->tipo,
                'tipo_display' => $this->getTipoDisplay($aula->tipo),
                'edificio' => $aula->edificio,
                'piso' => $aula->piso,
                'descripcion' => $aula->descripcion,
                'equipamiento' => $aula->equipamiento,
                'tiene_proyector' => $aula->tiene_proyector,
                'tiene_aire_acondicionado' => $aula->tiene_aire_acondicionado,
                'accesible' => $aula->accesible,
                'caracteristicas' => $aula->caracteristicas,
                'estado' => $aula->estado,
                'fecha_creacion' => $aula->created_at->format('d/m/Y'),
                'actions' => $this->getActionButtons($aula)
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    private function getTipoDisplay($tipo)
    {
        $tipos = [
            'aula' => 'Aula',
            'laboratorio' => 'Laboratorio',
            'taller' => 'Taller',
            'auditorio' => 'Auditorio'
        ];

        return $tipos[$tipo] ?? $tipo;
    }

    private function getActionButtons($aula)
    {
        $buttons = '';

        if (auth()->user()->hasPermission('aulas.edit')) {
            $buttons .= '<a href="javascript:void(0)" class="btn btn-sm btn-primary edit-aula" data-id="' . $aula->id . '" title="Editar"><i class="uil uil-edit"></i></a> ';
        }

        if (auth()->user()->hasPermission('aulas.change_status')) {
            $statusIcon = $aula->estado ? 'uil-ban' : 'uil-check';
            $statusTitle = $aula->estado ? 'Desactivar' : 'Activar';
            $statusClass = $aula->estado ? 'warning' : 'success';

            $buttons .= '<a href="javascript:void(0)" class="btn btn-sm btn-' . $statusClass . ' change-status" data-id="' . $aula->id . '" title="' . $statusTitle . '"><i class="uil ' . $statusIcon . '"></i></a> ';
        }

        if (auth()->user()->hasPermission('aulas.delete')) {
            $buttons .= '<a href="javascript:void(0)" class="btn btn-sm btn-danger delete-aula" data-id="' . $aula->id . '" title="Eliminar"><i class="uil uil-trash-alt"></i></a>';
        }

        return $buttons;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo' => 'required|string|max:20|unique:aulas',
            'nombre' => 'required|string|max:100',
            'capacidad' => 'required|integer|min:1|max:1000',
            'tipo' => 'required|in:aula,laboratorio,taller,auditorio',
            'edificio' => 'nullable|string|max:100',
            'piso' => 'nullable|string|max:20',
            'descripcion' => 'nullable|string',
            'equipamiento' => 'nullable|string',
            'tiene_proyector' => 'boolean',
            'tiene_aire_acondicionado' => 'boolean',
            'accesible' => 'boolean',
            'estado' => 'boolean'
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

            // Valores por defecto para checkboxes
            $data['tiene_proyector'] = $request->tiene_proyector ?? false;
            $data['tiene_aire_acondicionado'] = $request->tiene_aire_acondicionado ?? false;
            $data['accesible'] = $request->accesible ?? true;
            $data['estado'] = $request->estado ?? true;

            $aula = Aula::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Aula creada exitosamente',
                'data' => $aula
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el aula: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $aula = Aula::find($id);

        if (!$aula) {
            return response()->json([
                'success' => false,
                'message' => 'Aula no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $aula
        ]);
    }

    public function update(Request $request, $id)
    {
        $aula = Aula::find($id);

        if (!$aula) {
            return response()->json([
                'success' => false,
                'message' => 'Aula no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'codigo' => 'required|string|max:20|unique:aulas,codigo,' . $id,
            'nombre' => 'required|string|max:100',
            'capacidad' => 'required|integer|min:1|max:1000',
            'tipo' => 'required|in:aula,laboratorio,taller,auditorio',
            'edificio' => 'nullable|string|max:100',
            'piso' => 'nullable|string|max:20',
            'descripcion' => 'nullable|string',
            'equipamiento' => 'nullable|string',
            'tiene_proyector' => 'boolean',
            'tiene_aire_acondicionado' => 'boolean',
            'accesible' => 'boolean',
            'estado' => 'boolean'
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

            // Manejar checkboxes
            $data['tiene_proyector'] = $request->has('tiene_proyector') ? $request->tiene_proyector : false;
            $data['tiene_aire_acondicionado'] = $request->has('tiene_aire_acondicionado') ? $request->tiene_aire_acondicionado : false;
            $data['accesible'] = $request->has('accesible') ? $request->accesible : false;

            $aula->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Aula actualizada exitosamente',
                'data' => $aula
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el aula'
            ], 500);
        }
    }

    public function destroy($id)
    {
        $aula = Aula::find($id);

        if (!$aula) {
            return response()->json([
                'success' => false,
                'message' => 'Aula no encontrada'
            ], 404);
        }

        DB::beginTransaction();
        try {
            $aula->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Aula eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el aula'
            ], 500);
        }
    }

    public function changeStatus($id)
    {
        $aula = Aula::find($id);

        if (!$aula) {
            return response()->json([
                'success' => false,
                'message' => 'Aula no encontrada'
            ], 404);
        }

        $aula->estado = !$aula->estado;
        $aula->save();

        $status = $aula->estado ? 'activada' : 'desactivada';

        return response()->json([
            'success' => true,
            'message' => "Aula {$status} exitosamente",
            'data' => $aula
        ]);
    }

    public function porCapacidad($capacidad)
    {
        $aulas = Aula::activas()
            ->conCapacidadMinima($capacidad)
            ->orderBy('capacidad')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $aulas
        ]);
    }

    public function porTipo($tipo)
    {
        $aulas = Aula::activas()
            ->porTipo($tipo)
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $aulas
        ]);
    }
}
