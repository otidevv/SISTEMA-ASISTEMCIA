@php
    // Función para limpiar emojis y caracteres raros no soportados por DomPDF
    $limpiarTexto = function($texto) {
        if (!$texto) return '';
        // Regex para eliminar emojis, símbolos y caracteres misceláneos
        $clean = preg_replace('/[\x{1F300}-\x{1F6FF}\x{1F900}-\x{1FAFF}\x{2600}-\x{27BF}\x{1F1E0}-\x{1F1FF}\x{2B00}-\x{2BFF}\x{2300}-\x{23FF}\x{2190}-\x{21FF}\x{25A0}-\x{25FF}\x{2000}-\x{3300}]/u', '', $texto);
        return trim($clean);
    };
@endphp

<div style="border: 1px solid #d1dde4; border-left: 5px solid #2b5a6f; border-radius: 8px; margin-bottom: 20px; overflow: hidden; background-color: #ffffff; box-shadow: 0 4px 10px rgba(0,0,0,0.03);">
    <table style="width: 100%; border-collapse: collapse; margin: 0;">
        <tr>
            {{-- Foto del Estudiante con Fondo Estilizado --}}
            <td style="width: 115px; padding: 15px; vertical-align: middle; background: #f8fafc; border-right: 1px solid #e2e8f0; text-align: center;">
                @php
                    $photoPath = public_path('storage/' . ($estudiante->foto_perfil ?? 'default-avatar.png'));
                    if (!file_exists($photoPath) || !($estudiante->foto_perfil)) {
                        $photoData = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('assets/images/users/user-placeholder.png')));
                    } else {
                        $photoData = 'data:image/' . pathinfo($photoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($photoPath));
                    }
                @endphp
                <div style="display: inline-block; padding: 3px; background: white; border: 1px solid #cbd5e1; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    <img src="{{ $photoData }}" alt="Foto Estudiante" style="width: 85px; height: 105px; object-fit: cover; border-radius: 4px; display: block;">
                </div>
            </td>
            
            {{-- Información Detallada con Diseño Dashboard --}}
            <td style="padding: 15px 20px; vertical-align: top;">
                {{-- Badge de Categoría --}}
                <div style="margin-bottom: 8px; display: block;">
                    <span style="background-color: #00aeef; color: white; padding: 2px 8px; border-radius: 3px; font-size: 7px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">Estudiante Matriculado</span>
                </div>
                
                {{-- Nombre Completo --}}
                <div style="font-size: 14px; font-weight: bold; color: #1e293b; text-transform: uppercase; margin-bottom: 12px; letter-spacing: -0.3px;">
                    {{ $estudiante->nombre_completo }}
                </div>
                
                {{-- Cuadrícula de Datos --}}
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 50%; padding-bottom: 8px; vertical-align: top;">
                            <span style="font-size: 7.5px; font-weight: 800; color: #64748b; display: block; text-transform: uppercase; letter-spacing: 0.3px;">Código Institucional</span>
                            <span style="font-size: 11px; font-weight: bold; color: #2b5a6f;">{{ $inscripcion->codigo_inscripcion }}</span>
                        </td>
                        <td style="width: 50%; padding-bottom: 8px; vertical-align: top;">
                            <span style="font-size: 7.5px; font-weight: 800; color: #64748b; display: block; text-transform: uppercase; letter-spacing: 0.3px;">Documento de Identidad</span>
                            <span style="font-size: 11px; font-weight: bold; color: #2b5a6f; font-family: monospace;">{{ $estudiante->numero_documento }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 4px; border-top: 1px solid #f1f5f9; vertical-align: top;">
                            <span style="font-size: 7.5px; font-weight: 800; color: #64748b; display: block; text-transform: uppercase; letter-spacing: 0.3px;">Carrera Profesional</span>
                            <span style="font-size: 9.5px; font-weight: bold; color: #0f172a;">{{ $limpiarTexto($carrera->nombre) }}</span>
                        </td>
                        <td style="padding-top: 4px; border-top: 1px solid #f1f5f9; vertical-align: top;">
                            <span style="font-size: 7.5px; font-weight: 800; color: #64748b; display: block; text-transform: uppercase; letter-spacing: 0.3px;">Aula &amp; Turno</span>
                            <span style="font-size: 9.5px; font-weight: bold; color: #0f172a;">{{ $limpiarTexto($turno->nombre) }} &mdash; {{ $limpiarTexto($aula->codigo) }}</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
