{{-- Requiere: $roles. Campos para derivar a un rol y/o a una persona específica. --}}
<div class="mb-2">
    <label class="form-label">Acción</label>
    <select name="accion" class="form-select form-select-sm">
        @foreach(\App\Models\SolicitudDerivacion::ACCIONES as $k => $label)
            <option value="{{ $k }}">{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="mb-2">
    <label class="form-label">Derivar a (área / rol)</label>
    <select name="rol_destino_id" class="form-select form-select-sm">
        <option value="">— Ninguno —</option>
        @foreach($roles as $r)
            <option value="{{ $r->id }}">{{ $r->nombre }}</option>
        @endforeach
    </select>
</div>

<div class="mb-2 position-relative">
    <label class="form-label">…o a una persona específica</label>
    <input type="text" class="form-control form-control-sm persona-search" placeholder="Buscar administrativo por nombre o DNI..." autocomplete="off">
    <input type="hidden" name="user_destino_id" class="persona-id">
    <div class="list-group position-absolute w-100 shadow-sm persona-results" style="z-index:1060; max-height:200px; overflow:auto; display:none;"></div>
    <small class="text-muted persona-elegido"></small>
</div>

<div class="mb-2">
    <label class="form-label">Observación</label>
    <textarea name="observacion" class="form-control form-control-sm" rows="2" placeholder="Indicaciones para quien atiende..."></textarea>
</div>
