@php
    $map = [
        'pendiente_pago' => ['Pendiente de pago', 'secondary'],
        'enviada'        => ['Espera V°B°', 'info'],
        'en_revision'    => ['En revisión', 'info'],
        'observada'      => ['Observada', 'warning'],
        'aprobada'       => ['Aprobada', 'primary'],
        'derivada'       => ['Derivada / Por atender', 'primary'],
        'atendida'       => ['Atendida', 'success'],
        'rechazada'      => ['Rechazada', 'danger'],
    ];
    [$txt, $color] = $map[$estado] ?? [ucfirst($estado), 'secondary'];
@endphp
<span class="badge bg-{{ $color }}">{{ $txt }}</span>
