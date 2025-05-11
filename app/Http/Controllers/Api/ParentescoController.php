<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Parentesco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ParentescoController extends Controller
{
    /**
     * Muestra una lista de todos los parentescos para DataTables.
     */
    /**
     * Muestra una lista de todos los parentescos para DataTables.
     */
    public function index()
    {
        $parentescos = Parentesco::with(['estudiante', 'padre'])->get();

        // Formatear los datos para DataTables
        $data = $parentescos->map(function ($parentesco) {
            return [
                'id' => $parentesco->id,
                'estudiante' => $parentesco->estudiante->nombre . ' ' .
                    $parentesco->estudiante->apellido_paterno . ' ' .
                    $parentesco->estudiante->apellido_materno,
                'padre' => $parentesco->padre->nombre . ' ' .
                    $parentesco->padre->apellido_paterno . ' ' .
                    $parentesco->padre->apellido_materno,
                'tipo_parentesco' => ucfirst($parentesco->tipo_parentesco),
                'acceso_portal' => $parentesco->acceso_portal,
                'recibe_notificaciones' => $parentesco->recibe_notificaciones,
                'contacto_emergencia' => $parentesco->contacto_emergencia,
                'estado' => $parentesco->estado,
                'actions' => $this->getActionButtons($parentesco)
            ];
        });

        return response()->json([
            'draw' => request()->input('draw', 1),
            'recordsTotal' => $parentescos->count(),
            'recordsFiltered' => $parentescos->count(),
            'data' => $data
        ]);
    }

    /**
     * Genera los botones de acción para cada parentesco.
     */
    private function getActionButtons($parentesco)
    {
        $buttons = '';

        // Botón de editar
        if (Auth::user()->hasPermission('parentescos.edit')) {
            $buttons .= '<a href="javascript:void(0)" class="btn btn-sm btn-primary edit-parentesco" data-id="' . $parentesco->id . '" title="Editar"><i class="uil uil-edit"></i></a> ';
        }

        // Botón de cambiar estado
        $statusIcon = $parentesco->estado ? 'uil-ban' : 'uil-check';
        $statusTitle = $parentesco->estado ? 'Desactivar' : 'Activar';
        $statusClass = $parentesco->estado ? 'warning' : 'success';

        if (Auth::user()->hasPermission('parentescos.edit')) {
            $buttons .= '<a href="javascript:void(0)" class="btn btn-sm btn-' . $statusClass . ' change-status" data-id="' . $parentesco->id . '" title="' . $statusTitle . '"><i class="uil ' . $statusIcon . '"></i></a> ';
        }

        // Botón de eliminar
        if (Auth::user()->hasPermission('parentescos.delete')) {
            $buttons .= '<a href="javascript:void(0)" class="btn btn-sm btn-danger delete-parentesco" data-id="' . $parentesco->id . '" title="Eliminar"><i class="uil uil-trash-alt"></i></a>';
        }

        return $buttons;
    }

    /**
     * Almacena un nuevo parentesco.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'estudiante_id' => 'required|exists:users,id', // Cambiado a users
            'padre_id' => 'required|exists:users,id',
            'tipo_parentesco' => 'required|string|max:50',
            'acceso_portal' => 'boolean',
            'recibe_notificaciones' => 'boolean',
            'contacto_emergencia' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que no exista el mismo tipo de parentesco para el mismo estudiante y padre
        $existente = Parentesco::where('estudiante_id', $request->estudiante_id)
            ->where('padre_id', $request->padre_id)
            ->where('tipo_parentesco', $request->tipo_parentesco)
            ->first();

        if ($existente) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe este tipo de parentesco para el estudiante y padre seleccionados.',
                'errors' => [
                    'tipo_parentesco' => ['Ya existe este tipo de parentesco para el estudiante y padre seleccionados.']
                ]
            ], 422);
        }

        $parentesco = Parentesco::create([
            'estudiante_id' => $request->estudiante_id,
            'padre_id' => $request->padre_id,
            'tipo_parentesco' => $request->tipo_parentesco,
            'acceso_portal' => (bool)$request->acceso_portal,
            'recibe_notificaciones' => (bool)$request->recibe_notificaciones,
            'contacto_emergencia' => (bool)$request->contacto_emergencia,
            'estado' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Parentesco creado exitosamente',
            'data' => $parentesco
        ], 201);
    }
    /**
     * Muestra el parentesco especificado.
     */
    public function show($id)
    {
        $parentesco = Parentesco::with(['estudiante', 'padre'])->find($id);

        if (!$parentesco) {
            return response()->json([
                'success' => false,
                'message' => 'Parentesco no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $parentesco
        ]);
    }

    /**
     * Actualiza el parentesco especificado.
     */
    public function update(Request $request, $id)
    {
        // Encontrar el parentesco
        $parentesco = Parentesco::find($id);

        if (!$parentesco) {
            return response()->json([
                'success' => false,
                'message' => 'Parentesco no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'estudiante_id' => 'required|exists:users,id',
            'padre_id' => 'required|exists:users,id',
            'tipo_parentesco' => 'required|string|max:30',
            'acceso_portal' => 'boolean',
            'recibe_notificaciones' => 'boolean',
            'contacto_emergencia' => 'boolean',
            'estado' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que no exista el mismo tipo de parentesco para el mismo estudiante y padre (excepto el actual)
        $existente = Parentesco::where('estudiante_id', $request->estudiante_id)
            ->where('padre_id', $request->padre_id)
            ->where('tipo_parentesco', $request->tipo_parentesco)
            ->where('id', '!=', $id)
            ->first();

        if ($existente) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe este tipo de parentesco para el estudiante y padre seleccionados.',
                'errors' => [
                    'tipo_parentesco' => ['Ya existe este tipo de parentesco para el estudiante y padre seleccionados.']
                ]
            ], 422);
        }

        // Actualiza el parentesco con los valores del request
        // Nota: Ahora usamos directamente los valores enviados, ya que vienen como 0/1
        $parentesco->update([
            'estudiante_id' => $request->estudiante_id,
            'padre_id' => $request->padre_id,
            'tipo_parentesco' => $request->tipo_parentesco,
            'acceso_portal' => (bool)$request->acceso_portal,
            'recibe_notificaciones' => (bool)$request->recibe_notificaciones,
            'contacto_emergencia' => (bool)$request->contacto_emergencia,
            'estado' => (bool)$request->estado,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Parentesco actualizado exitosamente',
            'data' => $parentesco
        ]);
    }

    /**
     * Elimina el parentesco especificado.
     */
    public function destroy($id)
    {
        $parentesco = Parentesco::find($id);

        if (!$parentesco) {
            return response()->json([
                'success' => false,
                'message' => 'Parentesco no encontrado'
            ], 404);
        }

        $parentesco->delete();

        return response()->json([
            'success' => true,
            'message' => 'Parentesco eliminado exitosamente'
        ]);
    }

    /**
     * Cambia el estado del parentesco.
     */
    public function changeStatus($id)
    {
        $parentesco = Parentesco::find($id);

        if (!$parentesco) {
            return response()->json([
                'success' => false,
                'message' => 'Parentesco no encontrado'
            ], 404);
        }

        $parentesco->estado = !$parentesco->estado;
        $parentesco->save();

        $status = $parentesco->estado ? 'activado' : 'desactivado';

        return response()->json([
            'success' => true,
            'message' => "Parentesco {$status} exitosamente",
            'data' => $parentesco
        ]);
    }
}
