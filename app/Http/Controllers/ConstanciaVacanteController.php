<?php

namespace App\Http\Controllers;

use App\Models\Inscripcion;
use App\Models\CicloCarreraVacante;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ConstanciaVacanteController extends Controller
{
    /**
     * Generar constancia de vacante en PDF
     */
    public function generarConstancia($inscripcionId)
    {
        try {
            // Validar que el ID sea numérico
            if (!is_numeric($inscripcionId)) {
                return back()->with('error', 'ID de inscripción inválido');
            }

            $inscripcion = Inscripcion::with(['estudiante', 'ciclo', 'carrera', 'turno'])
                ->findOrFail($inscripcionId);

            // Verificar que la inscripción esté activa
            if ($inscripcion->estado_inscripcion !== 'activo') {
                abort(403, 'La inscripción no está activa');
            }

            // Verificar permisos
            $user = Auth::user();
            if ($user->id !== $inscripcion->estudiante_id && !$user->hasRole('admin') && !$user->hasPermission('constancias.generar-vacante')) {
                abort(403, 'No tienes permiso para generar esta constancia');
            }

            // Generar número de constancia único
            $numeroConstancia = $this->generarNumeroConstancia();

            // Código de verificación único
            $codigoVerificacion = 'VAC-' . $numeroConstancia . '-' . md5($inscripcion->id . now()->timestamp);

            // Generar QR code
            $urlValidacion = route('constancias.validar', $codigoVerificacion);
            try {
                $qrCode = base64_encode(QrCode::format('png')->size(150)->generate($urlValidacion));
            } catch (\Exception $e) {
                $qrCode = ''; // Fallback si QR falla
            }

            // Preparar datos para la vista
            $data = [
                'inscripcion' => $inscripcion,
                'estudiante' => $inscripcion->estudiante,
                'ciclo' => $inscripcion->ciclo,
                'carrera' => $inscripcion->carrera,
                'turno' => $inscripcion->turno,
                'numero_constancia' => $numeroConstancia,
                'codigo_verificacion' => $codigoVerificacion,
                'qr_code' => $qrCode,
                'fecha_generacion' => Carbon::now()->format('d/m/Y H:i'),
                'lugar' => 'Puerto Maldonado',
                'fecha' => Carbon::now()->format('d \d\e F \d\e Y')
            ];

            // Registrar en base de datos
            DB::table('constancias_generadas')->insert([
                'tipo' => 'vacante',
                'codigo_verificacion' => $codigoVerificacion,
                'numero_constancia' => $numeroConstancia,
                'inscripcion_id' => $inscripcion->id,
                'estudiante_id' => $inscripcion->estudiante_id,
                'datos' => json_encode($data),
                'generado_por' => $user->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Generar PDF
            $pdf = PDF::loadView('pdf.constancia-vacante', $data);
            $pdf->setPaper('A4', 'portrait');

            // Descargar el PDF
            return $pdf->download('constancia_vacante_' . $numeroConstancia . '.pdf');

        } catch (\Exception $e) {
            \Log::error('Error al generar constancia de vacante: ' . $e->getMessage());
            return back()->with('error', 'Error al generar la constancia: ' . $e->getMessage());
        }
    }

    /**
     * Subir constancia firmada
     */
    public function subirConstanciaFirmada(Request $request, $inscripcionId)
    {
        try {
            $inscripcion = Inscripcion::with(['estudiante'])
                ->findOrFail($inscripcionId);

            // Verificar permisos
            $user = Auth::user();
            if ($user->id !== $inscripcion->estudiante_id && !$user->hasRole('admin')) {
                abort(403, 'No tienes permiso para subir constancias para esta inscripción');
            }

            $request->validate([
                'constancia_firmada' => 'required|file|mimes:pdf|max:5120', // 5MB máximo
            ]);

            $file = $request->file('constancia_firmada');
            $filename = 'constancia_vacante_firmada_' . $inscripcion->id . '_' . time() . '.pdf';
            $path = $file->storeAs('constancias/firmadas', $filename, 'public');

            // Actualizar registro en base de datos
            DB::table('constancias_generadas')
                ->where('inscripcion_id', $inscripcion->id)
                ->where('tipo', 'vacante')
                ->update([
                    'constancia_firmada_path' => $path,
                    'estado_firma' => 'firmada',
                    'updated_at' => now()
                ]);

            return back()->with('success', 'Constancia firmada subida correctamente');

        } catch (\Exception $e) {
            \Log::error('Error al subir constancia firmada: ' . $e->getMessage());
            return back()->with('error', 'Error al subir la constancia: ' . $e->getMessage());
        }
    }

    /**
     * Generar número de constancia único
     */
    private function generarNumeroConstancia()
    {
        $año = date('Y');
        $ultimo = DB::table('constancias_generadas')
            ->where('tipo', 'vacante')
            ->whereYear('created_at', $año)
            ->count() + 1;

        return sprintf('%s-%04d', $año, $ultimo);
    }
}
