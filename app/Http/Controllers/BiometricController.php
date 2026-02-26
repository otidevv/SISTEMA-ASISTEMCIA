<?php

namespace App\Http\Controllers;

use App\Models\BiometricDevice;
use App\Models\BiometricCommand;
use App\Models\User;
use App\Models\Ciclo;
use App\Models\Inscripcion;
use App\Models\Carrera;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BiometricController extends Controller
{
    public function index()
    {
        $devices = BiometricDevice::all();
        $ciclos = Ciclo::orderBy('fecha_inicio', 'desc')->get();
        $carreras = Carrera::activas()->orderBy('nombre')->get();
        return view('biometria.index', compact('devices', 'ciclos', 'carreras'));
    }

    public function listDevices()
    {
        $devices = BiometricDevice::query();
        return DataTables::of($devices)
            ->editColumn('last_seen', function($device) {
                return $device->last_seen ? $device->last_seen->diffForHumans() : 'Nunca';
            })
            ->addColumn('status_html', function($device) {
                $online = $device->last_seen && $device->last_seen->diffInMinutes(now()) < 5;
                if ($online) {
                    return '<span class="badge bg-success">Online</span>';
                }
                return '<span class="badge bg-danger">Offline</span>';
            })
            ->rawColumns(['status_html'])
            ->make(true);
    }

    public function listUsers(Request $request)
    {
        $query = User::query()->with(['roles']);

        // Filtrar por Ciclo Académico
        if ($request->filled('ciclo_id')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('inscripciones', function($sq) use ($request) {
                    $sq->where('ciclo_id', $request->ciclo_id);
                })->orWhereHas('horarios', function($sq) use ($request) {
                    $sq->where('ciclo_id', $request->ciclo_id);
                });
            });
        } else {
            // Por defecto solo mostrar usuarios con roles relevantes
            $query->whereHas('roles', function($q) {
                $q->whereIn('nombre', ['estudiante', 'docente', 'profesor']);
            });
        }

        // Filtrar por Carrera
        if ($request->filled('carrera_id')) {
            $query->whereHas('inscripciones', function($q) use ($request) {
                $q->where('carrera_id', $request->carrera_id);
            });
        }

        // Filtrar por Estado Biométrico
        if ($request->filled('biometric_status')) {
            $status = $request->biometric_status;
            if ($status === 'fingerprint_pending') {
                $query->where('has_fingerprint', false);
            } elseif ($status === 'fingerprint_ok') {
                $query->where('has_fingerprint', true);
            } elseif ($status === 'face_pending') {
                $query->where('has_face', false);
            } elseif ($status === 'face_ok') {
                $query->where('has_face', true);
            } elseif ($status === 'both_pending') {
                $query->where('has_fingerprint', false)->where('has_face', false);
            }
        }
        
        return DataTables::of($query)
            ->addColumn('nombre_completo', function($user) {
                return $user->nombre . ' ' . $user->apellido_paterno . ' ' . $user->apellido_materno;
            })
            ->addColumn('rol', function($user) {
                return $user->roles->pluck('nombre')->map(function($rol) {
                    return ucfirst($rol);
                })->implode(', ');
            })
            ->editColumn('has_fingerprint', function($user) {
                return $user->has_fingerprint;
            })
            ->editColumn('has_face', function($user) {
                return $user->has_face;
            })
            ->make(true);
    }

    public function enroll(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'device_sn' => 'required|exists:biometric_devices,sn',
            'type' => 'required|in:FP,FACE'
        ]);

        $user = User::findOrFail($request->user_id);
        
        $command = BiometricCommand::create([
            'device_sn' => $request->device_sn,
            'command' => 'ENROLL_' . $request->type,
            'payload' => $user->numero_documento,
            'status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Orden de registro enviada al equipo. Por favor, acerque el ' . ($request->type == 'FP' ? 'dedo' : 'rostro') . ' al dispositivo.',
            'command_id' => $command->id
        ]);
    }

    public function checkCommandStatus($id)
    {
        $command = BiometricCommand::findOrFail($id);
        return response()->json([
            'status' => $command->status,
            'response' => $command->response_data
        ]);
    }
}
