<table class="info-table" style="width: 100%; border-collapse: collapse; margin-bottom: 20px; background-color: #f8f9fa; border: 1px solid #dee2e6;">
    <tr>
        <td style="width: 120px; padding: 15px; vertical-align: top;">
            @php
                $photoPath = public_path('storage/' . ($estudiante->foto_perfil ?? 'default-avatar.png'));
                if (!file_exists($photoPath) || !($estudiante->foto_perfil)) {
                    $photoData = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('assets/images/users/user-placeholder.png')));
                } else {
                    $photoData = 'data:image/' . pathinfo($photoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($photoPath));
                }
            @endphp
            <img src="{{ $photoData }}" alt="Foto" style="width: 100px; height: 120px; object-fit: cover; border: 2px solid #2c3e50; border-radius: 4px; background-color: white;">
        </td>
        <td style="padding: 15px; vertical-align: top;">
            <h3 style="font-size: 14px; color: #2c3e50; margin-bottom: 10px; border-bottom: 1px solid #bdc3c7; padding-bottom: 5px; text-transform: uppercase; font-weight: 800;">
                Datos del Estudiante
            </h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 50%; padding-bottom: 8px;">
                        <span style="font-size: 9px; font-weight: 700; color: #555; display: block;">CÓDIGO:</span>
                        <span style="font-size: 11px; font-weight: 600;">{{ $inscripcion->codigo_inscripcion }}</span>
                    </td>
                    <td style="width: 50%; padding-bottom: 8px;">
                        <span style="font-size: 9px; font-weight: 700; color: #555; display: block;">DOCUMENTO:</span>
                        <span style="font-size: 11px; font-weight: 600;">{{ $estudiante->numero_documento }}</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding-bottom: 8px;">
                        <span style="font-size: 9px; font-weight: 700; color: #555; display: block;">ESTUDIANTE:</span>
                        <span style="font-size: 14px; font-weight: 800; color: #2c3e50;">{{ $estudiante->nombre_completo }}</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding-bottom: 8px;">
                        <span style="font-size: 9px; font-weight: 700; color: #555; display: block;">CARRERA:</span>
                        <span style="font-size: 11px; font-weight: 600;">{{ $carrera->nombre }}</span>
                    </td>
                    <td style="padding-bottom: 8px;">
                        <span style="font-size: 9px; font-weight: 700; color: #555; display: block;">TURNO / AULA:</span>
                        <span style="font-size: 11px; font-weight: 600;">{{ $turno->nombre }} - {{ $aula->codigo }}</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
