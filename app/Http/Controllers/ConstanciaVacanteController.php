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

            // ✅ VALIDACIÓN: Verificar si ya existe una constancia de vacante para este estudiante en este ciclo
            $constanciaExistente = DB::table('constancias_generadas')
                ->where('tipo', 'vacante')
                ->where('estudiante_id', $inscripcion->estudiante_id)
                ->where('inscripcion_id', $inscripcion->id)
                ->first();

            if ($constanciaExistente) {
                return back()->with('error', 'Ya existe una constancia de vacante generada para este ciclo académico. Solo se permite una constancia de vacante por ciclo.');
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

            // ✅ Configurar fecha en español con primera letra mayúscula
            Carbon::setLocale('es');
            setlocale(LC_TIME, 'es_PE.UTF-8', 'es_ES.UTF-8', 'Spanish');
            $fecha = ucfirst(Carbon::now()->translatedFormat('d \\d\\e F \\d\\e Y'));

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
                'fecha' => $fecha // 👈 fecha traducida y capitalizada
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

            $filename = 'constancia_vacante_' . $numeroConstancia . '.pdf';
            
            // Retornar PDF para visualización inline en navegador
            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al generar constancia de vacante: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
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
     * Ver constancia generada (PDF)
     */
    public function verConstancia($constanciaId)
    {
        try {
            $user = Auth::user();

            // Buscar la constancia
            $constancia = DB::table('constancias_generadas')
                ->where('id', $constanciaId)
                ->where('tipo', 'vacante')
                ->first();

            if (!$constancia) {
                abort(404, 'Constancia no encontrada');
            }

            // Verificar permisos (más permisivo, similar a ConstanciaEstudiosController)
            if ($user->id !== $constancia->estudiante_id && 
                $user->id !== $constancia->generado_por && 
                !$user->hasRole('admin') && 
                !$user->hasPermission('constancias.view') &&
                !$user->hasPermission('constancias.generar-vacante')) {
                abort(403, 'No tienes permiso para ver esta constancia');
            }

            // Obtener la inscripción
            $inscripcion = Inscripcion::with(['estudiante', 'ciclo', 'carrera', 'turno'])
                ->findOrFail($constancia->inscripcion_id);

            // Generar el PDF usando los datos almacenados
            $datos = json_decode($constancia->datos, true);

            Carbon::setLocale('es');
            $fecha = ucfirst(Carbon::parse($constancia->created_at)->translatedFormat('d \\d\\e F \\d\\e Y'));

            $pdf = PDF::loadView('pdf.constancia-vacante', [
                'inscripcion' => $inscripcion,
                'estudiante' => $inscripcion->estudiante,
                'ciclo' => $inscripcion->ciclo,
                'carrera' => $inscripcion->carrera,
                'turno' => $inscripcion->turno,
                'numero_constancia' => $constancia->numero_constancia,
                'codigo_verificacion' => $constancia->codigo_verificacion,
                'qr_code' => $datos['qr_code'] ?? '',
                'fecha_generacion' => Carbon::parse($constancia->created_at)->format('d/m/Y H:i'),
                'fecha' => $fecha,
                'lugar' => $datos['lugar'] ?? 'Puerto Maldonado',
            ]);

            return $pdf->stream('constancia-vacante-' . $constancia->numero_constancia . '.pdf');

        } catch (\Exception $e) {
            \Log::error('Error al ver constancia de vacante: ' . $e->getMessage());
            return back()->with('error', 'Error al mostrar la constancia');
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
