<div class="info-section">
    <div class="info-container">
        <div class="student-photo-container">
            @php
                $photoPath = public_path('storage/' . ($estudiante->foto_perfil ?? 'default-avatar.png'));
                if (!file_exists($photoPath) || !($estudiante->foto_perfil)) {
                    // Fallback to a placeholder if not found
                    $photoData = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('assets/images/users/user-placeholder.png')));
                } else {
                    $photoData = 'data:image/' . pathinfo($photoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($photoPath));
                }
            @endphp
            <img src="{{ $photoData }}" alt="Foto del Estudiante" class="student-photo">
        </div>
        
        <div class="student-details">
            <h3>DATOS DEL ESTUDIANTE</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">CÓDIGO:</span>
                    <span class="info-value">{{ $inscripcion->codigo_inscripcion }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">DOCUMENTO:</span>
                    <span class="info-value">{{ $estudiante->numero_documento }}</span>
                </div>
                <div class="info-item full-width">
                    <span class="info-label">ESTUDIANTE:</span>
                    <span class="info-value highlight">{{ $estudiante->nombre_completo }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">CARRERA:</span>
                    <span class="info-value">{{ $carrera->nombre }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">TURNO:</span>
                    <span class="info-value">{{ $turno->nombre }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">AULA:</span>
                    <span class="info-value">{{ $aula->codigo }} - {{ $aula->nombre }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
