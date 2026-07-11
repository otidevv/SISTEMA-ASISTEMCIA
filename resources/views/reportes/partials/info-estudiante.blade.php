@php
    // Función para limpiar emojis y caracteres raros no soportados por DomPDF
    $limpiarTexto = function($texto) {
        if (!$texto) return '';
        // Regex para eliminar emojis, símbolos y caracteres misceláneos
        $clean = preg_replace('/[\x{1F300}-\x{1F6FF}\x{1F900}-\x{1FAFF}\x{2600}-\x{27BF}\x{1F1E0}-\x{1F1FF}\x{2B00}-\x{2BFF}\x{2300}-\x{23FF}\x{2190}-\x{21FF}\x{25A0}-\x{25FF}\x{2000}-\x{3300}]/u', '', $texto);
        return trim($clean);
    };
@endphp

<table class="info-table" style="width: 100%; border-collapse: collapse; margin-bottom: 20px; background-color: #ffffff; border: 1.5px solid #000; border-radius: 4px; overflow: hidden;">
    <tr>
        <td style="width: 110px; padding: 12px; vertical-align: middle; background-color: #f8fafc; border-right: 1.5px solid #000; text-align: center;">
            @php
                $photoPath = public_path('storage/' . ($estudiante->foto_perfil ?? 'default-avatar.png'));
                if (!file_exists($photoPath) || !($estudiante->foto_perfil)) {
                    $photoData = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('assets/images/users/user-placeholder.png')));
                } else {
                    $photoData = 'data:image/' . pathinfo($photoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($photoPath));
                }
            @endphp
            <img src="{{ $photoData }}" alt="Foto Estudiante" style="width: 90px; height: 110px; object-fit: cover; border: 1.5px solid #000; border-radius: 3px; background-color: white;">
        </td>
        <td style="padding: 12px 16px; vertical-align: top;">
            <h3 style="font-size: 11px; color: #2b5a6f; margin: 0 0 10px 0; border-bottom: 1.5px solid #2b5a6f; padding-bottom: 4px; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px;">
                Información General del Estudiante
            </h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 50%; padding-bottom: 6px;">
                        <span style="font-size: 8px; font-weight: bold; color: #555; display: block; text-transform: uppercase;">Código de Inscripción:</span>
                        <span style="font-size: 11px; font-weight: bold; color: #000;">{{ $inscripcion->codigo_inscripcion }}</span>
                    </td>
                    <td style="width: 50%; padding-bottom: 6px;">
                        <span style="font-size: 8px; font-weight: bold; color: #555; display: block; text-transform: uppercase;">N° Documento (DNI):</span>
                        <span style="font-size: 11px; font-weight: bold; color: #000; font-family: monospace;">{{ $estudiante->numero_documento }}</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding-bottom: 8px; border-top: 1px solid #eee; padding-top: 6px;">
                        <span style="font-size: 8px; font-weight: bold; color: #555; display: block; text-transform: uppercase;">Apellidos y Nombres:</span>
                        <span style="font-size: 13px; font-weight: bold; color: #2b5a6f; text-transform: uppercase;">{{ $estudiante->nombre_completo }}</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding-top: 4px; border-top: 1px solid #eee;">
                        <span style="font-size: 8px; font-weight: bold; color: #555; display: block; text-transform: uppercase;">Carrera Profesional:</span>
                        <span style="font-size: 10px; font-weight: bold; color: #000;">{{ $limpiarTexto($carrera->nombre) }}</span>
                    </td>
                    <td style="padding-top: 4px; border-top: 1px solid #eee;">
                        <span style="font-size: 8px; font-weight: bold; color: #555; display: block; text-transform: uppercase;">Turno / Aula Asignada:</span>
                        <span style="font-size: 10px; font-weight: bold; color: #000;">{{ $limpiarTexto($turno->nombre) }} &mdash; {{ $limpiarTexto($aula->codigo) }}</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
