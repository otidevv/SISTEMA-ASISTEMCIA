<?php

namespace App\Http\Controllers;

use App\Models\Inscripcion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ConstanciaEstudiosController extends Controller
{
    /**
     * Generar constancia de estudios en PDF
     */
    public function generarConstancia($inscripcionId)
    {
        try {
            // Validar que el ID sea numérico
            if (!is_numeric($inscripcionId)) {
                return back()->with('error', 'ID de inscripción inválido');
            }

            $inscripcion = Inscripcion::with(['estudiante', 'ciclo', 'carrera', 'turno', 'aula'])
                ->findOrFail($inscripcionId);

            // Verificar que la inscripción esté activa
            if ($inscripcion->estado_inscripcion !== 'activo') {
                abort(403, 'La inscripción no está activa');
            }

            // Verificar permisos
            $user = Auth::user();
            if ($user->id !== $inscripcion->estudiante_id && !$user->hasRole('admin') && !$user->hasPermission('constancias.generar-estudios')) {
                abort(403, 'No tienes permiso para generar esta constancia');
            }

            // Generar número de constancia único
            $numeroConstancia = $this->generarNumeroConstancia();

            // Código de verificación único
            $codigoVerificacion = 'EST-' . $numeroConstancia . '-' . md5($inscripcion->id . now()->timestamp);

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
            $fecha = ucfirst(Carbon::now()->translatedFormat('d \d\e F \d\e Y'));

            // Preparar datos para la vista
            $data = [
                'inscripcion' => $inscripcion,
                'estudiante' => $inscripcion->estudiante,
                'ciclo' => $inscripcion->ciclo,
                'carrera' => $inscripcion->carrera,
                'turno' => $inscripcion->turno,
                'aula' => $inscripcion->aula,
                'numero_constancia' => $numeroConstancia,
                'codigo_verificacion' => $codigoVerificacion,
                'qr_code' => $qrCode,
                'fecha_generacion' => Carbon::now()->format('d/m/Y H:i'),
                'lugar' => 'Puerto Maldonado',
                'fecha' => $fecha, // 👈 fecha traducida y capitalizada
                'pie_linea1' => 'UNAMAD: Parque científico Tecnológico sostenible con Investigación e Innovación',
                'pie_linea2' => 'Av. Dos de Mayo N° 960 — Puerto Maldonado — CEL: 917061893 — 975844977',
                'pie_linea3' => 'CEPRE-UNAMAD CEL: 993110927'
            ];

            // Registrar en base de datos
            DB::table('constancias_generadas')->insert([
                'tipo' => 'estudios',
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
            $pdf = PDF::loadView('pdf.constancia-estudios', $data);
            $pdf->setPaper('A4', 'portrait');

            // Descargar el PDF
            return $pdf->download('constancia_estudios_' . $numeroConstancia . '.pdf');

        } catch (\Exception $e) {
            \Log::error('Error al generar constancia de estudios: ' . $e->getMessage());
            return back()->with('error', 'Error al generar la constancia: ' . $e->getMessage());
        }
    }

    /**
     * Subir constancia firmada
     */
    public function subirConstanciaFirmada(Request $request, $constanciaId)
    {
        try {
            // Buscar la constancia generada
            $constancia = DB::table('constancias_generadas')
                ->where('id', $constanciaId)
                ->where('tipo', 'estudios')
                ->first();

            if (!$constancia) {
                return back()->with('error', 'Constancia no encontrada');
            }

            // Verificar permisos
            $user = Auth::user();
            if ($user->id !== $constancia->estudiante_id && !$user->hasRole('admin') && !$user->hasPermission('constancias.subir-firmada')) {
                abort(403, 'No tienes permiso para subir constancias para esta inscripción');
            }

            $request->validate([
                'constancia_firmada' => 'required|file|mimes:pdf|max:5120', // 5MB máximo
            ]);

            $file = $request->file('constancia_firmada');
            $filename = 'constancia_estudios_firmada_' . $constancia->id . '_' . time() . '.pdf';
            $path = $file->storeAs('constancias/firmadas', $filename, 'public');

            // Actualizar registro en base de datos
            DB::table('constancias_generadas')
                ->where('id', $constanciaId)
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
     * Ver constancia firmada
     */
    public function verConstanciaFirmada($inscripcionId)
    {
        try {
            $inscripcion = Inscripcion::with(['estudiante'])
                ->findOrFail($inscripcionId);

            // Verificar permisos
            $user = Auth::user();
            if ($user->id !== $inscripcion->estudiante_id && !$user->hasRole('admin')) {
                abort(403, 'No tienes permiso para ver esta constancia');
            }

            $constancia = DB::table('constancias_generadas')
                ->where('inscripcion_id', $inscripcion->id)
                ->where('tipo', 'estudios')
                ->whereNotNull('constancia_firmada_path')
                ->first();

            if (!$constancia) {
                return back()->with('error', 'No se encontró la constancia firmada');
            }

            return response()->file(storage_path('app/public/' . $constancia->constancia_firmada_path));

        } catch (\Exception $e) {
            \Log::error('Error al ver constancia firmada: ' . $e->getMessage());
            return back()->with('error', 'Error al mostrar la constancia');
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
                ->where('tipo', 'estudios')
                ->first();

            if (!$constancia) {
                abort(404, 'Constancia no encontrada');
            }

            // Verificar permisos
            if ($user->id !== $constancia->estudiante_id && $user->id !== $constancia->generado_por && !$user->hasRole('admin') && !$user->hasPermission('constancias.generar-estudios')) {
                abort(403, 'No tienes permiso para ver esta constancia');
            }

            // Obtener la inscripción
            $inscripcion = Inscripcion::with(['estudiante', 'ciclo', 'carrera', 'turno', 'aula'])
                ->findOrFail($constancia->inscripcion_id);

            // Obtener el usuario que generó la constancia
            $generador = User::find($constancia->generado_por);

            // Generar el PDF usando los datos almacenados
            $datos = json_decode($constancia->datos, true);

            Carbon::setLocale('es');
            $fecha = ucfirst(Carbon::parse($constancia->created_at)->translatedFormat('d \d\e F \d\e Y'));

            $pdf = PDF::loadView('pdf.constancia-estudios', [
                'inscripcion' => $inscripcion,
                'estudiante' => $inscripcion->estudiante,
                'ciclo' => $inscripcion->ciclo,
                'carrera' => $inscripcion->carrera,
                'turno' => $inscripcion->turno,
                'aula' => $inscripcion->aula,
                'numero_constancia' => $constancia->numero_constancia,
                'codigo_verificacion' => $constancia->codigo_verificacion,
                'qr_code' => $datos['qr_code'] ?? '',
                'fecha_generacion' => Carbon::parse($constancia->created_at)->format('d/m/Y H:i'),
                'fecha' => $fecha,
                'lugar' => $datos['lugar'] ?? 'Puerto Maldonado',
                'generador' => $generador, // Pass the generator user to the view
            ]);

            return $pdf->stream('constancia-estudios-' . $constancia->numero_constancia . '.pdf');

        } catch (\Exception $e) {
            \Log::error('Error al ver constancia: ' . $e->getMessage());
            return back()->with('error', 'Error al mostrar la constancia');
        }
    }

    /**
     * Página de validación pública
     */
    public function validarConstancia($codigoVerificacion)
    {
        try {
            $constancia = DB::table('constancias_generadas')
                ->where('codigo_verificacion', $codigoVerificacion)
                ->where('tipo', 'estudios')
                ->first();

            if (!$constancia) {
                return view('constancias.validacion', [
                    'valida' => false,
                    'mensaje' => 'Código de verificación no encontrado o inválido'
                ]);
            }

            $datos = json_decode($constancia->datos, true);

            return view('constancias.validacion', [
                'valida' => true,
                'tipo' => 'estudios',
                'datos' => $datos,
                'fecha_generacion' => $constancia->created_at,
                'constancia_firmada' => $constancia->constancia_firmada_path ?? null
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al validar constancia: ' . $e->getMessage());
            return view('constancias.validacion', [
                'valida' => false,
                'mensaje' => 'Error al validar la constancia'
            ]);
        }
    }

    /**
     * Generar número de constancia único
     */
    private function generarNumeroConstancia()
    {
        $año = date('Y');
        $ultimo = DB::table('constancias_generadas')
            ->where('tipo', 'estudios')
            ->whereYear('created_at', $año)
            ->count() + 1;

        return sprintf('%s-%04d', $año, $ultimo);
    }
}