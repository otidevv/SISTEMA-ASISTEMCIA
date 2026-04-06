{{-- resources/views/partials/reforzamiento-modal.blade.php --}}
@php
    $cicloActivoRef = \App\Models\Ciclo::where('es_activo', true)
                        ->where('nombre', 'like', '%REFORZAMIENTO%')
                        ->first() ?? \App\Models\Ciclo::where('es_activo', true)->first();
@endphp

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">

<style>
    :root {
        --rf-magenta: #ec008c;
        --rf-magenta-dark: #d4007d;
        --rf-magenta-glow: rgba(236, 0, 140, 0.25);
        --rf-cyan: #00aeef;
        --rf-cyan-glow: rgba(0, 174, 239, 0.2);
        --rf-navy: #0c1e2f;
        --rf-navy-light: #1a3a5a;
        --rf-green: #10b981;
        --rf-green-glow: rgba(16, 185, 129, 0.2);
        --rf-red: #ef4444;
        --rf-text-dark: #0c1e2f;
        --rf-text-muted: #64748b;
        --rf-bg-light: #f8fafc;
        --rf-font-titles: 'Outfit', sans-serif;
        --rf-font-body: 'Inter', sans-serif;
        --rf-transition: cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* === FIX: Override theme's global white text on inputs === */
    #reforzamientoModal input,
    #reforzamientoModal input:focus,
    #reforzamientoModal input::placeholder,
    #reforzamientoModal select,
    #reforzamientoModal select:focus,
    #reforzamientoModal textarea,
    #reforzamientoModal textarea:focus {
        color: var(--rf-text-dark) !important;
    }
    #reforzamientoModal input::placeholder {
        color: #94a3b8 !important;
        opacity: 1 !important;
    }
    #reforzamientoModal input[type="file"] {
        color: transparent !important;
    }

    /* ====== MODAL OVERLAY ====== */
    #reforzamientoModal {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(12, 30, 47, 0.65);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        z-index: 10001;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: all 0.4s var(--rf-transition);
    }
    #reforzamientoModal.modal-open {
        opacity: 1;
        visibility: visible;
    }

    /* ====== CONTAINER ====== */
    .rf-container {
        width: 95%;
        max-width: 1300px;
        min-height: 800px;
        max-height: 98vh;
        background: #fff;
        border-radius: 2rem;
        overflow: hidden;
        display: flex;
        box-shadow: 0 40px 100px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.1);
        transform: scale(0.92) translateY(30px);
        transition: all 0.6s var(--rf-transition);
    }
    #reforzamientoModal.modal-open .rf-container {
        transform: scale(1) translateY(0);
    }
    .rf-container.rf-container-success {
        max-width: 650px !important;
        min-height: auto !important;
        transition: all 0.5s var(--rf-transition);
        margin: auto;
    }

    /* ====== SIDEBAR ====== */
    .rf-sidebar {
        width: 320px;
        background: linear-gradient(165deg, var(--rf-navy) 0%, #0f2b45 50%, var(--rf-navy-light) 100%);
        color: #fff;
        padding: 2.5rem 2rem;
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
        position: relative;
        overflow: hidden;
    }
    .rf-sidebar::before {
        content: '';
        position: absolute;
        top: -50%; right: -50%;
        width: 200%; height: 200%;
        background: radial-gradient(circle at 80% 20%, rgba(236, 0, 140, 0.06) 0%, transparent 50%);
        pointer-events: none;
    }
    .rf-sidebar::after {
        content: '';
        position: absolute;
        top: 0; right: 0; bottom: 0;
        width: 1px;
        background: linear-gradient(to bottom, transparent, rgba(236, 0, 140, 0.3), rgba(0, 174, 239, 0.2), transparent);
    }

    .rf-logo {
        font-family: var(--rf-font-titles);
        font-size: 1.5rem;
        font-weight: 900;
        color: var(--rf-magenta);
        margin-bottom: 3rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        position: relative;
        z-index: 2;
    }

    /* === PROGRESS BAR VERTICAL === */
    .rf-nav {
        display: flex;
        flex-direction: column;
        gap: 0;
        position: relative;
        z-index: 2;
    }

    .rf-progress-track {
        position: absolute;
        left: 18px;
        top: 18px;
        bottom: 18px;
        width: 3px;
        background: rgba(255,255,255,0.1);
        border-radius: 3px;
        z-index: 0;
    }
    .rf-progress-fill {
        position: absolute;
        left: 18px;
        top: 18px;
        width: 3px;
        height: 0%;
        background: linear-gradient(to bottom, var(--rf-magenta), var(--rf-cyan));
        border-radius: 3px;
        transition: height 0.6s var(--rf-transition);
        z-index: 1;
        box-shadow: 0 0 12px var(--rf-magenta-glow);
    }

    .rf-nav-item {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        opacity: 0.35;
        transition: all 0.4s ease;
        padding: 1rem 0;
        position: relative;
        z-index: 2;
        cursor: default;
    }
    .rf-nav-item.active { opacity: 1; }
    .rf-nav-item.completed { opacity: 1; }

    .rf-step-num {
        width: 38px;
        height: 38px;
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--rf-font-titles);
        font-weight: 700;
        font-size: 0.85rem;
        transition: all 0.4s var(--rf-transition);
        flex-shrink: 0;
        background: rgba(255,255,255,0.03);
    }

    .rf-nav-item.active .rf-step-num {
        background: var(--rf-magenta);
        border-color: var(--rf-magenta);
        box-shadow: 0 0 25px var(--rf-magenta-glow), 0 0 50px rgba(236, 0, 140, 0.1);
        transform: scale(1.15);
    }

    .rf-nav-item.completed .rf-step-num {
        background: var(--rf-green);
        border-color: var(--rf-green);
        color: #fff;
        box-shadow: 0 0 15px var(--rf-green-glow);
    }

    .rf-step-text {
        line-height: 1.3;
    }
    .rf-step-text .rf-step-title {
        font-weight: 800;
        font-size: 0.9rem;
        margin: 0;
    }
    .rf-step-text .rf-step-sub {
        font-size: 0.72rem;
        opacity: 0.55;
    }

    /* === SIDEBAR FOOTER === */
    .rf-sidebar-footer {
        margin-top: auto;
        background: rgba(255,255,255,0.04);
        padding: 1.25rem;
        border-radius: 1.25rem;
        border: 1px solid rgba(255,255,255,0.08);
        position: relative;
        z-index: 2;
    }

    /* ====== MAIN CONTENT ====== */
    .rf-main {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        background: var(--rf-bg-light);
        position: relative;
        overflow: hidden;
    }

    .rf-header {
        padding: 2rem 3rem;
        background: #fff;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
    }

    .rf-title-box h2 {
        font-family: var(--rf-font-titles);
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--rf-text-dark);
        margin: 0;
    }
    .rf-title-box p {
        color: var(--rf-text-muted);
        font-size: 0.9rem;
        margin: 0.2rem 0 0 0;
        font-weight: 500;
    }

    .rf-close-btn {
        width: 44px; height: 44px;
        border-radius: 12px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
        color: var(--rf-navy);
        border: none;
    }
    .rf-close-btn:hover {
        background: var(--rf-red);
        color: #fff;
        transform: rotate(90deg);
    }

    /* ====== BODY / PANELS ====== */
    .rf-body {
        flex-grow: 1;
        padding: 2rem 3rem;
        overflow-y: auto;
        overflow-x: hidden;
        scrollbar-width: thin;
        scrollbar-color: #e2e8f0 transparent;
        position: relative;
    }
    .rf-body::-webkit-scrollbar { width: 6px; }
    .rf-body::-webkit-scrollbar-track { background: transparent; }
    .rf-body::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 3px; }

    .rf-panel {
        display: none;
        position: relative;
    }
    .rf-panel.active {
        display: block;
        animation: rfSlideIn 0.5s var(--rf-transition) both;
    }
    .rf-panel.slide-out-left {
        animation: rfSlideOutLeft 0.35s ease both;
    }
    .rf-panel.slide-out-right {
        animation: rfSlideOutRight 0.35s ease both;
    }

    @keyframes rfSlideIn {
        from { opacity: 0; transform: translateX(30px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes rfSlideInReverse {
        from { opacity: 0; transform: translateX(-30px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes rfSlideOutLeft {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(-30px); }
    }
    @keyframes rfSlideOutRight {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(30px); }
    }

    /* ====== STEP HEADER ====== */
    .rf-step-header {
        margin-bottom: 1.75rem;
    }
    .rf-step-header h3 {
        font-family: var(--rf-font-titles);
        font-weight: 800;
        font-size: 1.25rem;
        color: var(--rf-text-dark);
        margin: 0 0 0.25rem 0;
    }
    .rf-step-header p {
        color: var(--rf-text-muted);
        font-size: 0.88rem;
        margin: 0;
    }

    /* ====== FORM ELEMENTS ====== */
    .rf-label {
        display: block;
        font-weight: 700;
        font-size: 0.78rem;
        color: var(--rf-text-dark);
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-family: var(--rf-font-body);
    }

    .rf-input, .rf-select {
        width: 100%;
        height: 3.25rem;
        background: #fff;
        border: 2px solid #e2e8f0;
        border-radius: 0.875rem;
        padding: 0 1.25rem;
        font-family: var(--rf-font-body);
        font-size: 0.95rem;
        color: var(--rf-text-dark);
        transition: all 0.3s ease;
        outline: none;
    }
    .rf-input:hover, .rf-select:hover {
        border-color: #cbd5e1;
    }
    .rf-input:focus, .rf-select:focus {
        border-color: var(--rf-magenta);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(236, 0, 140, 0.08), 0 4px 12px rgba(0,0,0,0.04);
    }
    .rf-input.is-valid { border-color: var(--rf-green); }
    .rf-input.is-valid:focus { box-shadow: 0 0 0 4px var(--rf-green-glow); }
    .rf-input.is-invalid { border-color: var(--rf-red); }
    .rf-input.is-invalid:focus { box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1); }

    /* ====== NATIVE SELECT PREMIUM STYLING ====== */
    #reforzamientoModal select.rf-select {
        display: block !important; /* Force native visibility */
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2364748b'%3E%3Cpath d='M7 10l5 5 5-5H7z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 1.5rem;
        padding-right: 2.5rem;
        cursor: pointer;
        background-color: #fff;
    }
    
    /* Kill NiceSelect visually in this modal */
    #reforzamientoModal .nice-select {
        display: none !important;
    }
    
    #reforzamientoModal select.rf-select:disabled {
        background-color: #f1f5f9;
        cursor: not-allowed;
        opacity: 0.6;
    }
    #reforzamientoModal select.rf-select option {
        padding: 10px;
        background: #fff;
        color: var(--rf-text-dark);
    }
    
    .rf-input-lg {
        height: 3.75rem;
        font-size: 1.3rem;
        font-weight: 700;
        border-radius: 1rem;
        border: 2px solid #e2e8f0;
        transition: all 0.3s;
        font-family: var(--rf-font-titles);
        letter-spacing: 2px;
        text-align: center;
    }
    .rf-input-lg:focus {
        border-color: var(--rf-cyan);
        box-shadow: 0 0 0 4px var(--rf-cyan-glow);
    }

    .rf-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; min-width: 0; }
    .rf-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem; min-width: 0; }
    
    .rf-form-group {
        margin-bottom: 1.5rem;
        position: relative;
        min-width: 0;
    }
    .rf-form-group .rf-field-icon {
        position: absolute;
        right: 1rem;
        top: 2.6rem;
        font-size: 1.2rem;
        opacity: 0;
        transition: all 0.3s var(--rf-transition);
        z-index: 2;
    }
    .rf-form-group .rf-field-icon.show { opacity: 1; }
    .rf-form-group .rf-field-icon.valid { color: var(--rf-green); }
    .rf-form-group .rf-field-icon.invalid { color: var(--rf-red); }

    /* ====== BUTTONS ====== */
    .rf-btn {
        height: 3.25rem;
        border-radius: 1rem;
        padding: 0 1.75rem;
        font-family: var(--rf-font-titles);
        font-weight: 800;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        cursor: pointer;
        transition: all 0.3s var(--rf-transition);
        border: none;
        text-decoration: none;
    }

    .rf-btn-magenta {
        background: linear-gradient(135deg, var(--rf-magenta) 0%, #d4007d 100%);
        color: #fff;
        box-shadow: 0 6px 20px var(--rf-magenta-glow);
    }
    .rf-btn-magenta:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 12px 30px rgba(236, 0, 140, 0.35);
    }
    .rf-btn-magenta:active:not(:disabled) {
        transform: translateY(0);
    }
    .rf-btn-magenta:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        filter: grayscale(0.5);
    }
    .rf-btn-magenta.rf-pulse {
        animation: rfPulseBtn 2s ease-in-out infinite;
    }
    @keyframes rfPulseBtn {
        0%, 100% { box-shadow: 0 6px 20px var(--rf-magenta-glow); }
        50% { box-shadow: 0 6px 35px rgba(236, 0, 140, 0.5); }
    }

    .rf-btn-outline {
        background: #fff;
        color: var(--rf-text-dark);
        border: 2px solid #e2e8f0;
    }
    .rf-btn-outline:hover {
        border-color: var(--rf-navy);
        background: var(--rf-bg-light);
    }

    .rf-btn-search {
        background: linear-gradient(135deg, var(--rf-cyan), #0093ca);
        color: #fff;
        border: none;
        border-radius: 1rem;
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s var(--rf-transition);
        box-shadow: 0 4px 15px var(--rf-cyan-glow);
        flex-shrink: 0;
    }
    .rf-btn-search:hover {
        transform: scale(1.08) rotate(5deg);
        box-shadow: 0 8px 25px rgba(0, 174, 239, 0.4);
    }

    /* ====== CARDS ====== */
    .rf-card {
        background: #fff;
        border-radius: 1.25rem;
        padding: 1.5rem;
        border: 1px solid #e2e8f0;
        margin-bottom: 1.25rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        transition: all 0.3s ease;
    }
    .rf-card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.06);
        border-color: #cbd5e1;
    }

    .rf-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
    }

    /* ====== ALERTS ====== */
    .rf-alert {
        padding: 1rem 1.25rem;
        border-radius: 1rem;
        font-size: 0.9rem;
        line-height: 1.5;
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
        margin-bottom: 1.25rem;
    }
    .rf-alert-success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #064e3b; }
    .rf-alert-warning { background: #fffbeb; border: 1px solid #fde68a; color: #78350f; }
    .rf-alert-info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }

    /* ====== BADGE ====== */
    .rf-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.35rem 0.9rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .rf-badge-magenta { background: rgba(236,0,140,0.1); color: var(--rf-magenta); }
    .rf-badge-cyan { background: rgba(0,174,239,0.1); color: var(--rf-cyan); }

    /* ====== DNI SEARCH BOX ====== */
    .rf-dni-box {
        background: linear-gradient(135deg, rgba(0,174,239,0.04), rgba(236,0,140,0.02));
        border: 2px dashed rgba(0,174,239,0.3);
        border-radius: 1.25rem;
        padding: 1.5rem;
        margin-bottom: 1.75rem;
        transition: all 0.3s;
    }
    .rf-dni-box:focus-within {
        border-color: var(--rf-cyan);
        box-shadow: 0 0 0 4px var(--rf-cyan-glow);
    }
    .rf-dni-search-container {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }

    /* ====== DRAG & DROP FILE ZONES ====== */
    .rf-dropzone {
        border: 2px dashed #d1d5db;
        border-radius: 1rem;
        padding: 2rem 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #fafbfc;
        position: relative;
        overflow: hidden;
    }
    .rf-dropzone:hover {
        border-color: var(--rf-magenta);
        background: #fff;
        box-shadow: 0 10px 30px rgba(236,0,140,0.08);
    }
    .rf-dropzone.rf-dragover {
        border-color: var(--rf-cyan);
        background: rgba(0,174,239,0.02);
        transform: scale(1.01);
        box-shadow: 0 0 0 5px var(--rf-cyan-glow);
    }
    .rf-dropzone.rf-has-file {
        border-color: var(--rf-green);
        border-style: solid;
        background: #fff;
        box-shadow: 0 4px 20px rgba(16, 185, 129, 0.1);
    }
    .rf-dropzone-icon {
        font-size: 2.5rem;
        color: #94a3b8;
        margin-bottom: 0.75rem;
        transition: all 0.4s var(--rf-transition);
        display: inline-block;
    }
    .rf-dropzone:hover .rf-dropzone-icon { 
        color: var(--rf-magenta); 
        transform: scale(1.1) translateY(-5px);
    }
    .rf-dropzone.rf-has-file .rf-dropzone-icon { color: var(--rf-green); }
    .rf-dropzone-text {
        font-size: 0.82rem;
        color: var(--rf-text-muted);
        line-height: 1.4;
    }
    .rf-dropzone-text strong { color: var(--rf-navy); }
    .rf-dropzone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        z-index: 5;
    }
    .rf-file-preview {
        display: none;
        width: 100%;
        margin-top: 0.5rem;
        position: relative;
        z-index: 10;
        border-top: 1px dashed #e2e8f0;
        padding-top: 1rem;
    }
    .rf-file-preview img {
        width: 48px;
        height: 48px;
        border-radius: 0.5rem;
        object-fit: cover;
        border: 2px solid #e2e8f0;
    }
    .rf-file-preview .rf-file-name {
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--rf-text-dark);
    }
    .rf-file-preview .rf-file-size {
        font-size: 0.75rem;
        color: var(--rf-text-muted);
    }

    /* ====== BENEFICIOS TAGS ====== */
    .rf-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        background: #fff;
        border: 1px solid #e2e8f0;
        padding: 0.3rem 0.75rem;
        border-radius: 0.75rem;
        font-size: 0.72rem;
        font-weight: 700;
        color: var(--rf-navy);
        transition: all 0.2s;
    }
    .rf-tag:hover { border-color: var(--rf-magenta); color: var(--rf-magenta); }
    .rf-tag-accent {
        background: var(--rf-cyan);
        color: #fff;
        border-color: var(--rf-cyan);
    }

    /* ====== FOOTER ====== */
    .rf-footer {
        padding: 1.5rem 3rem;
        background: #fff;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
    }

    /* ====== SUGERENCIAS COLEGIOS ====== */
    #sugerencias-colegios {
        position: absolute;
        width: 100%;
        max-height: 320px;
        overflow-y: auto;
        background: white;
        z-index: 10000 !important;
        border-radius: 1.25rem;
        box-shadow: 
            0 25px 60px -12px rgba(0, 0, 0, 0.25), 
            0 0 0 1px rgba(0, 174, 239, 0.15);
        border: none;
        margin-top: 0.6rem;
    }
    .rf-search-item {
        padding: 0.85rem 1.25rem;
        cursor: pointer;
        transition: all 0.2s;
        border-bottom: 1px solid #f8fafc;
    }
    .rf-search-item:last-child { border-bottom: none; }
    .rf-search-item:hover {
        background: linear-gradient(135deg, rgba(236,0,140,0.03), rgba(0,174,239,0.03));
        padding-left: 1.5rem;
    }

    /* ====== SHIMMER / SKELETON ====== */
    .rf-shimmer {
        background: linear-gradient(90deg, #f0f0f0 25%, #e8e8e8 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: rfShimmer 1.5s ease-in-out infinite;
        border-radius: 0.5rem;
    }
    @keyframes rfShimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    /* ====== UTILITY CLASSES ====== */
    .rf-spin { animation: rfRotate 1s linear infinite; }
    @keyframes rfRotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    .hidden { display: none !important; }

    .rf-btn-icon-del {
        cursor: pointer;
        color: var(--rf-red);
        padding: 0.3rem;
        border-radius: 0.5rem;
        transition: all 0.2s;
    }
    .rf-btn-icon-del:hover {
        background: rgba(239, 68, 68, 0.1);
    }

    /* SweetAlert2 Z-Index */
    .swal2-container { z-index: 20000 !important; }

    /* ====== SUMMARY PREMIUM CARD ====== */
    .summary-premium-card {
        background: #fff;
        border-radius: 1.5rem;
        padding: 2rem;
        border: 2px solid #e2e8f0;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }
    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 1rem 0;
        border-bottom: 1px dashed #e2e8f0;
    }
    .summary-item:last-child { border-bottom: none; }
    .summary-item .label {
        font-weight: 700;
        color: var(--rf-text-muted);
        font-size: 0.85rem;
        text-transform: uppercase;
    }
    .summary-item .value {
        font-weight: 800;
        color: var(--rf-navy);
        font-size: 1rem;
    }
    .summary-status {
        margin-top: 1.5rem;
        padding: 1rem;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 700;
    }
    .summary-status.success { background: #ecfdf5; color: #064e3b; border: 1px solid #a7f3d0; }
    .summary-status.pending { background: #fffbeb; color: #78350f; border: 1px solid #fde68a; }
    .text-cyan { color: var(--rf-cyan) !important; }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 900px) {
        .rf-container { flex-direction: column; height: auto; max-height: 95vh; }
        .rf-sidebar { width: 100%; padding: 1.5rem; flex-direction: row; align-items: center; gap: 1rem; }
        .rf-nav { flex-direction: row; gap: 0.5rem; overflow-x: auto; }
        .rf-summary-layout { grid-template-columns: 1fr !important; }
        .rf-nav-item { padding: 0.5rem 0; }
        .rf-step-text { display: none; }
        .rf-progress-track, .rf-progress-fill { display: none; }
        .rf-sidebar-footer { display: none; }
        .rf-header { padding: 1.5rem; }
        .rf-body { padding: 1.5rem; }
        .rf-footer { padding: 1rem 1.5rem; }
        .rf-grid-2, .rf-grid-3 { grid-template-columns: 1fr; }
    }
</style>

<div id="reforzamientoModal">
    <div class="rf-container">
        <aside class="rf-sidebar">
            <div class="rf-logo">
                <span class="material-icons-round" style="font-size: 2.2rem; color: #fff;">school</span>
                <div style="line-height: 1.1;">
                    <span style="display:block; font-size: 0.7rem; opacity: 0.6; font-weight: 600; letter-spacing: 1px;">CEPRE UNAMAD</span>
                    <span style="display:block; color: var(--rf-magenta); font-weight: 900;">REFORZAMIENTO</span>
                </div>
            </div>

            <nav class="rf-nav">
                <div class="rf-progress-track"></div>
                <div class="rf-progress-fill" id="rfProgressFill"></div>

                <div class="rf-nav-item active" data-step-nav="1">
                    <div class="rf-step-num">01</div>
                    <div class="rf-step-text">
                        <div class="rf-step-title">Identificación</div>
                        <div class="rf-step-sub">DNI y Datos Personales</div>
                    </div>
                </div>
                <div class="rf-nav-item" data-step-nav="2">
                    <div class="rf-step-num">02</div>
                    <div class="rf-step-text">
                        <div class="rf-step-title">Apoderados</div>
                        <div class="rf-step-sub">Padres y/o Tutores</div>
                    </div>
                </div>
                <div class="rf-nav-item" data-step-nav="3">
                    <div class="rf-step-num">03</div>
                    <div class="rf-step-text">
                        <div class="rf-step-title">Académico</div>
                        <div class="rf-step-sub">Grado y Colegio</div>
                    </div>
                </div>
                <div class="rf-nav-item" data-step-nav="4">
                    <div class="rf-step-num">04</div>
                    <div class="rf-step-text">
                        <div class="rf-step-title">Documentos</div>
                        <div class="rf-step-sub">Fotos y Archivos</div>
                    </div>
                </div>
                <div class="rf-nav-item" data-step-nav="5">
                    <div class="rf-step-num">05</div>
                    <div class="rf-step-text">
                        <div class="rf-step-title">Resumen</div>
                        <div class="rf-step-sub">Confirmar y Enviar</div>
                    </div>
                </div>
            </nav>
            
            <!-- DETALLES DEL CICLO ACTIVO (DINÁMICO) -->
            <div id="sideCicloInfo" style="margin-top: 2rem; padding: 1.25rem; background: rgba(255,255,255,0.03); border-radius: 1.25rem; border: 1px solid rgba(255,255,255,0.08);">
                <div style="font-size: 0.65rem; color: var(--rf-magenta); font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem;">
                    <i class="material-icons-round" style="font-size: 0.9rem;">event_available</i> Periodo del Ciclo
                </div>
                <div id="sideCicloNombre" style="color: #fff; font-weight: 800; font-size: 0.85rem; line-height: 1.2; margin-bottom: 0.75rem;">{{ $cicloActivoRef->nombre ?? '---' }}</div>
                
                <div style="display: flex; gap: 0.75rem; align-items: center;">
                    <div style="flex: 1;">
                        <div style="font-size: 0.6rem; color: rgba(255,255,255,0.4); text-transform: uppercase; font-weight: 700;">Inicio</div>
                        <div id="sideCicloInicio" style="color: #fff; font-size: 0.75rem; font-weight: 700;">{{ $cicloActivoRef && $cicloActivoRef->fecha_inicio ? $cicloActivoRef->fecha_inicio->format('d/m/Y') : '--/--/--' }}</div>
                    </div>
                    <div style="width: 1px; height: 20px; background: rgba(255,255,255,0.1);"></div>
                    <div style="flex: 1;">
                        <div style="font-size: 0.6rem; color: rgba(255,255,255,0.4); text-transform: uppercase; font-weight: 700;">Término</div>
                        <div id="sideCicloFin" style="color: #fff; font-size: 0.75rem; font-weight: 700;">{{ $cicloActivoRef && $cicloActivoRef->fecha_fin ? $cicloActivoRef->fecha_fin->format('d/m/Y') : '--/--/--' }}</div>
                    </div>
                </div>
            </div>

            <div class="rf-sidebar-footer">
                <div style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem; color: var(--rf-cyan);">Asesoría y Consultas</div>
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.4rem;">
                    <span class="material-icons-round" style="font-size: 1.1rem;">phone</span>
                    <span style="font-weight: 700; font-size: 0.85rem;">993 110 927 / 993 111 037</span>
                </div>
                <div style="font-size: 0.68rem; opacity: 0.7; display: flex; align-items: flex-start; gap: 0.4rem;">
                    <span class="material-icons-round" style="font-size: 0.9rem;">location_on</span>
                    <span>Av. Dos de Mayo N° 960, 2do Piso</span>
                </div>
            </div>
        </aside>

        <main class="rf-main">
            <header class="rf-header">
                <div class="rf-title-box">
                    <h2 id="rfHeaderTitle">Inscripción de Reforzamiento</h2>
                    <p id="rfHeaderDesc">Completa los pasos para asegurar tu vacante escolar.</p>
                </div>
                <button class="rf-close-btn" onclick="closeReforzamientoModal()" type="button">
                    <span class="material-icons-round">close</span>
                </button>
            </header>

            <div class="rf-body">
                <form id="reforzamientoForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="ciclo_id" id="ref_ciclo_id" value="{{ $cicloActivoRef->id ?? '' }}">
                    <input type="hidden" name="es_manual" id="ref_es_manual" value="1">
                    <input type="hidden" name="pago_api_serial" id="ref_pago_api_serial">
                    <input type="hidden" id="ref_pago_api_fecha">
                    <input type="hidden" name="dni" id="ref_dni_hidden">

                    {{-- ==================== PASO 1: IDENTIFICACIÓN ==================== --}}
                    <div class="rf-panel active" data-step="1">
                        <div class="rf-step-header">
                            <h3><span class="material-icons-round" style="vertical-align: middle; color: var(--rf-cyan); margin-right: 0.3rem;">badge</span> Datos del Estudiante</h3>
                            <p>Ingresa el DNI para autocompletar los datos vía RENIEC o completa manualmente.</p>
                        </div>

                        {{-- DNI Search Box --}}
                        <div class="rf-dni-box">
                            <label class="rf-label" style="color: var(--rf-cyan); font-family: var(--rf-font-titles);">
                                <span class="material-icons-round" style="font-size: 1rem; vertical-align: middle;">search</span>
                                Identificación Rápida con DNI
                            </label>
                            <div class="rf-dni-search-container">
                                <input type="text" id="ref_dni" class="rf-input rf-input-lg" placeholder="_ _ _ _ _ _ _ _" maxlength="8" inputmode="numeric" style="flex:1;">
                                <button type="button" id="btnVerifyDni" class="rf-btn-search">
                                    <span class="material-icons-round" style="font-size:1.8rem;">search</span>
                                </button>
                            </div>
                            <div id="dniVerifyResult" style="margin-top: 0.75rem;"></div>
                        </div>

                        {{-- Datos Personales --}}
                        <div id="studentPublicInfo">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem;">
                                <span class="material-icons-round" style="color: var(--rf-magenta);">person</span>
                                <h4 style="font-family: var(--rf-font-titles); font-weight: 800; margin: 0; color: var(--rf-navy);">Información Personal</h4>
                            </div>

                            <div class="rf-form-group">
                                <label class="rf-label">Nombres Completos <span style="color:var(--rf-red);">*</span></label>
                                <input type="text" id="ref_nombre" name="nombre" class="rf-input" placeholder="Ej: Juan Carlos" required>
                                <span class="rf-field-icon material-icons-round"></span>
                            </div>
                            <div class="rf-grid-2">
                                <div class="rf-form-group">
                                    <label class="rf-label">Apellido Paterno <span style="color:var(--rf-red);">*</span></label>
                                    <input type="text" id="ref_apellido_paterno" name="apellido_paterno" class="rf-input" required>
                                    <span class="rf-field-icon material-icons-round"></span>
                                </div>
                                <div class="rf-form-group">
                                    <label class="rf-label">Apellido Materno <span style="color:var(--rf-red);">*</span></label>
                                    <input type="text" id="ref_apellido_materno" name="apellido_materno" class="rf-input" required>
                                    <span class="rf-field-icon material-icons-round"></span>
                                </div>
                            </div>
                            <div class="rf-grid-2">
                                <div class="rf-form-group">
                                    <label class="rf-label">Teléfono / Celular <span style="color:var(--rf-red);">*</span></label>
                                    <input type="text" id="ref_telefono" name="telefono" class="rf-input" placeholder="987 654 321" maxlength="9" inputmode="tel" required>
                                    <span class="rf-field-icon material-icons-round"></span>
                                </div>
                                <div class="rf-form-group">
                                    <label class="rf-label">Correo Electrónico</label>
                                    <input type="email" id="ref_email" name="email" class="rf-input" placeholder="estudiante@email.com">
                                    <span class="rf-field-icon material-icons-round"></span>
                                </div>
                            </div>

                            <div class="rf-form-group">
                                <label class="rf-label">Dirección / Domicilio <span style="color:var(--rf-red);">*</span></label>
                                <input type="text" id="ref_direccion" name="direccion" class="rf-input" placeholder="Ej: Av. 2 de Mayo 123" required>
                                <span class="rf-field-icon material-icons-round"></span>
                            </div>

                            <div class="rf-grid-2">
                                <div class="rf-form-group">
                                    <label class="rf-label">Fecha de Nacimiento <span style="color:var(--rf-red);">*</span></label>
                                    <input type="date" id="ref_fecha_nacimiento" name="fecha_nacimiento" class="rf-input" required>
                                    <span class="rf-field-icon material-icons-round"></span>
                                </div>
                                <div class="rf-form-group">
                                    <label class="rf-label">Género <span style="color:var(--rf-red);">*</span></label>
                                    <select id="ref_genero" name="genero" class="rf-select" required>
                                        <option value="">-- Seleccionar --</option>
                                        <option value="MASCULINO">MASCULINO</option>
                                        <option value="FEMENINO">FEMENINO</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Beneficios del Programa --}}
                            <div style="margin-top: 1.25rem; padding: 1.25rem; background: linear-gradient(135deg, rgba(0,174,239,0.04), rgba(236,0,140,0.02)); border-radius: 1.25rem; border: 1px solid #e2e8f0;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                                    <h5 style="margin:0; font-family:var(--rf-font-titles); font-weight:800; color:var(--rf-navy); font-size:0.9rem;">
                                        <span class="material-icons-round" style="font-size: 1.1rem; vertical-align: middle; color: var(--rf-cyan);">auto_awesome</span>
                                        Programa de Reforzamiento
                                    </h5>
                                    <span class="rf-badge rf-badge-magenta" style="font-size: 0.85rem; padding: 0.4rem 1rem;">
                                        <span class="material-icons-round" style="font-size:1rem;">payments</span>
                                        S/. 200.00 /mes
                                    </span>
                                    <div id="ref_ciclo_badge" style="margin-top:0.5rem; display: {{ $cicloActivoRef ? 'block' : 'none' }};">
                                        <span class="rf-badge rf-badge-cyan" style="font-size: 0.75rem; background: rgba(0, 174, 239, 0.1); color: var(--rf-navy); border: 1px solid rgba(0, 174, 239, 0.2);">
                                            <i class="material-icons-round" style="font-size:1rem; vertical-align:middle;">event</i>
                                            Ciclo: <strong id="ref_ciclo_nombre_display">{{ $cicloActivoRef->nombre ?? '---' }}</strong>
                                        </span>
                                    </div>
                                </div>
                                <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                                    <span class="rf-tag">Raz. Matemático</span>
                                    <span class="rf-tag">Álgebra</span>
                                    <span class="rf-tag">Aritmética</span>
                                    <span class="rf-tag">Trigonometría</span>
                                    <span class="rf-tag">Geometría</span>
                                    <span class="rf-tag">Comprensión Lectora</span>
                                    <span class="rf-tag rf-tag-accent">+ Asesoría de Tareas</span>
                                </div>
                            </div>
                        </div>

                        <div id="planBBox" class="rf-alert rf-alert-info" style="display:none; margin-top:1rem;">
                            <span class="material-icons-round">payment</span>
                            <div style="flex:1;">
                                <strong>¿Ya pagaste y no aparece?</strong> Si tu pago no se valida automáticamente, puedes adjuntar el voucher manualmente más adelante.
                                <button type="button" id="btnPlanB" class="rf-btn rf-btn-outline" style="margin-top:0.5rem; height:2.5rem; font-size:0.8rem;">
                                    <span class="material-icons-round" style="font-size:1rem;">receipt_long</span>
                                    Activar Validación por Voucher
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- ==================== PASO 2: APODERADOS ==================== --}}
                    <div class="rf-panel" data-step="2">
                        <div class="rf-step-header">
                            <h3><span class="material-icons-round" style="vertical-align: middle; color: var(--rf-magenta); margin-right: 0.3rem;">family_restroom</span> Datos de Padres o Tutores</h3>
                            <p>Registra al menos un apoderado. Ingresa su DNI para autocompletar con RENIEC.</p>
                        </div>
                        <div style="display:flex; justify-content:flex-end; margin-bottom:1.25rem;">
                            <button type="button" id="btnAddApoderado" class="rf-btn rf-btn-magenta" style="height:2.75rem; padding:0 1.25rem; font-size:0.8rem;">
                                <span class="material-icons-round" style="font-size:1.2rem;">person_add</span>
                                Agregar Apoderado
                            </button>
                        </div>
                        <div id="apoderadosContainer"></div>
                    </div>

                    {{-- ==================== PASO 3: ACADÉMICO ==================== --}}
                    <div class="rf-panel" data-step="3">
                        <div class="rf-step-header">
                            <h3><span class="material-icons-round" style="vertical-align: middle; color: var(--rf-cyan); margin-right: 0.3rem;">menu_book</span> Información Escolar</h3>
                            <p>Selecciona tu grado, turno de preferencia y busca tu colegio.</p>
                        </div>

                        <div class="rf-card">
                            <div class="rf-grid-2" style="margin-bottom: 1.25rem;">
                                <div class="rf-form-group" style="margin-bottom:0;">
                                    <label class="rf-label">Grado a Reforzar <span style="color:var(--rf-red);">*</span></label>
                                    <select name="grado" class="rf-select" required>
                                        <option value="1">1° DE SECUNDARIA</option>
                                        <option value="2">2° DE SECUNDARIA</option>
                                        <option value="3">3° DE SECUNDARIA</option>
                                        <option value="4">4° DE SECUNDARIA</option>
                                        <option value="5">5° DE SECUNDARIA</option>
                                    </select>
                                </div>
                                <div class="rf-form-group" style="margin-bottom:0;">
                                    <label class="rf-label">Turno de Preferencia <span style="color:var(--rf-red);">*</span></label>
                                    <select name="seccion" class="rf-select" required>
                                        <option value="MAÑANA">🌅 MAÑANA (08:00 - 11:00 AM)</option>
                                        <option value="TARDE">🌇 TARDE (04:00 - 07:00 PM)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="rf-card">
                            <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:1.25rem;">
                                <span class="material-icons-round" style="color: var(--rf-cyan);">domain</span>
                                <h4 style="font-family:var(--rf-font-titles); font-weight:800; margin:0; font-size:1rem;">Buscador de Institución Educativa</h4>
                            </div>

                            <div class="rf-grid-3" style="margin-bottom:1.25rem;">
                                <div class="rf-form-group" style="margin:0;">
                                    <label class="rf-label" style="font-size:0.7rem;">Departamento</label>
                                    <select id="ref_dep" class="rf-select"><option value="">Cargando...</option></select>
                                </div>
                                <div class="rf-form-group" style="margin:0;">
                                    <label class="rf-label" style="font-size:0.7rem;">Provincia</label>
                                    <select id="ref_prov" class="rf-select" disabled><option value="">Provincia</option></select>
                                </div>
                                <div class="rf-form-group" style="margin:0;">
                                    <label class="rf-label" style="font-size:0.7rem;">Distrito</label>
                                    <select id="ref_dist" class="rf-select" disabled><option value="">Distrito</option></select>
                                </div>
                            </div>

                            <div class="rf-form-group" style="position:relative; margin-bottom: 0;">
                                <label class="rf-label">Nombre del Colegio / IE</label>
                                <div style="display:flex; gap:0.75rem;">
                                    <input type="text" id="ref_search_colegio" class="rf-input" placeholder="Ej: Augusto Bouroncle Acuña" disabled style="flex:1;">
                                    <button type="button" id="btnBuscarColegio" class="rf-btn rf-btn-outline" style="flex-shrink:0;" disabled>
                                        <span class="material-icons-round">search</span>
                                    </button>
                                </div>
                                <div id="sugerencias-colegios" style="display:none;"></div>
                                <input type="hidden" name="colegio_id" id="ref_colegio_id_hidden">
                                <input type="hidden" name="colegio_nombre_manual" id="ref_colegio_nombre_manual">
                            </div>

                            <div id="colegio_seleccionado_box" style="display:none; margin-top:1rem;">
                                <div class="rf-alert rf-alert-success" style="margin:0;">
                                    <span class="material-icons-round">school</span>
                                    <div style="flex:1;">
                                        <div style="font-weight:800; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.5px; color: #047857;">Colegio Seleccionado</div>
                                        <div id="nombre_colegio_sel" style="font-size:0.9rem; font-weight:600;">...</div>
                                    </div>
                                    <span class="material-icons-round rf-btn-icon-del" onclick="clearColegio()" style="cursor:pointer;">close</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ==================== PASO 4: DOCUMENTOS ==================== --}}
                    <div class="rf-panel" data-step="4">
                        <div class="rf-step-header">
                            <h3><span class="material-icons-round" style="vertical-align: middle; color: var(--rf-magenta); margin-right: 0.3rem;">upload_file</span> Documentos Requeridos</h3>
                            <p>Sube tus documentos oficiales en formato JPG, PNG o PDF (máx. 5MB cada uno).</p>
                        </div>

                        {{-- CARD PREMIUM PARA DESCARGA DE FORMATOS --}}
                        <div class="rf-card" style="background: linear-gradient(135deg, var(--rf-navy) 0%, var(--rf-navy-light) 100%); color: #fff; border: none; margin-bottom: 2rem; padding: 1.75rem;">
                            <div style="display: flex; align-items: center; gap: 1.25rem; flex-wrap: wrap;">
                                <div style="width: 56px; height: 56px; border-radius: 14px; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i class="material-icons-round" style="font-size: 2.2rem; color: var(--rf-magenta);">picture_as_pdf</i>
                                </div>
                                <div style="flex: 1; min-width: 250px;">
                                    <h4 style="margin: 0; font-family: var(--rf-font-titles); font-weight: 800; font-size: 1.05rem; color: #fff; letter-spacing: 0.5px;">PACK DE INSCRIPCIÓN PRE-LLENADO</h4>
                                    <p style="margin: 0.3rem 0 0 0; font-size: 0.82rem; opacity: 0.85; line-height: 1.4;">
                                        Hemos preparado tu <strong>Carta de Compromiso y Declaraciones</strong> con tus datos (Nombres, DNI, Ciclo). 
                                        ¡Solo descárgalo, firma, pon tu huella y súbelo aquí mismo!
                                    </p>
                                </div>
                                <button type="button" id="btnDownloadPack" class="rf-btn rf-btn-magenta" style="height: 3.25rem; box-shadow: 0 10px 25px rgba(236, 0, 140, 0.4); min-width: 220px;">
                                    <span class="material-icons-round">download</span> DESCARGAR FORMATOS
                                </button>
                            </div>
                        </div>

                        <div class="rf-grid-2">
                            {{-- Foto del estudiante --}}
                            <div class="rf-form-group">
                                <label class="rf-label">
                                    <span class="material-icons-round" style="font-size:1rem; vertical-align:middle; color:var(--rf-magenta);">photo_camera</span>
                                    FOTO DEL ESTUDIANTE (ROSTRO) <span style="color:var(--rf-red);">*</span>
                                </label>
                                <div class="rf-dropzone" id="dropzone_foto">
                                    <input type="file" name="foto" accept="image/*" required>
                                    <div class="rf-dropzone-content" style="flex-direction: column; gap: 0.5rem;">
                                        <i class="material-icons-round" style="font-size: 2.5rem; color: #cbd5e1;">add_a_photo</i>
                                        <strong style="font-size: 0.9rem; color: var(--rf-navy);">Arrastra tu foto aqui</strong>
                                        <p style="margin: 0; font-size: 0.75rem; color: var(--rf-text-muted);">o haz clic para seleccionar</p>
                                    </div>
                                    <div class="rf-file-preview"></div>
                                </div>
                            </div>
                            {{-- Copia de DNI Estudiante --}}
                            <div class="rf-form-group">
                                <label class="rf-label">
                                    <span class="material-icons-round" style="font-size:1rem; vertical-align:middle; color:var(--rf-cyan);">badge</span>
                                    COPIA DE DNI <span style="color:var(--rf-red);">*</span>
                                </label>
                                <div class="rf-dropzone" id="dropzone_dni">
                                    <input type="file" name="dni_file" accept=".pdf,image/*" required>
                                    <div class="rf-dropzone-content" style="flex-direction: column; gap: 0.5rem;">
                                        <i class="material-icons-round" style="font-size: 2.5rem; color: #cbd5e1;">badge</i>
                                        <strong style="font-size: 0.9rem; color: var(--rf-navy);">Arrastra tu DNI escaneado</strong>
                                        <p style="margin: 0; font-size: 0.75rem; color: var(--rf-text-muted);">PDF, JPG o PNG</p>
                                    </div>
                                    <div class="rf-file-preview"></div>
                                </div>
                            </div>
                        </div>

                        <div class="rf-grid-2" style="margin-top: 1rem;">
                            {{-- Copia de DNI Apoderado --}}
                            <div class="rf-form-group">
                                <label class="rf-label">
                                    <span class="material-icons-round" style="font-size:1rem; vertical-align:middle; color:var(--rf-magenta);">family_restroom</span>
                                    DNI DEL APODERADO <span style="color:var(--rf-red);">*</span>
                                </label>
                                <div class="rf-dropzone" id="dropzone_dni_apoderado">
                                    <input type="file" name="dni_apoderado_file" accept=".pdf,image/*" required>
                                    <div class="rf-dropzone-content" style="flex-direction: column; gap: 0.5rem;">
                                        <i class="material-icons-round" style="font-size: 2.5rem; color: #cbd5e1;">how_to_reg</i>
                                        <strong style="font-size: 0.9rem; color: var(--rf-navy);">Arrastra DNI del Apoderado</strong>
                                        <p style="margin: 0; font-size: 0.75rem; color: var(--rf-text-muted);">PDF, JPG o PNG</p>
                                    </div>
                                    <div class="rf-file-preview"></div>
                                </div>
                            </div>
                            {{-- Voucher de Pago Original --}}
                            <div class="rf-form-group">
                                <label class="rf-label">
                                    <span class="material-icons-round" style="font-size:1rem; vertical-align:middle; color:var(--rf-green);">receipt_long</span>
                                    VOUCHER DE PAGO ORIGINAL <span style="color:var(--rf-red);">*</span>
                                </label>
                                <div class="rf-dropzone" id="dropzone_voucher" style="border-color: var(--rf-magenta);">
                                    <input type="file" name="voucher_file" accept=".pdf,image/*" required>
                                    <div class="rf-dropzone-content" style="flex-direction: column; gap: 0.5rem;">
                                        <i class="material-icons-round" style="font-size: 2.5rem; color: var(--rf-magenta);">monetization_on</i>
                                        <strong style="font-size: 0.9rem; color: var(--rf-navy);">Arrastra tu Voucher aquí</strong>
                                        <p style="margin: 0; font-size: 0.75rem; color: var(--rf-magenta); font-weight:800;">Obligatorio para validación</p>
                                    </div>
                                    <div class="rf-file-preview"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Certificado o Constancia --}}
                        <div class="rf-grid-2" style="margin-top: 1.5rem;">
                            <div class="rf-form-group">
                                <label class="rf-label">
                                    <span class="material-icons-round" style="font-size:1.1rem; vertical-align:middle; color:var(--rf-navy);">article</span>
                                    CERTIFICADO / CONSTANCIA (OPCIONAL)
                                </label>
                                <div class="rf-dropzone" id="dropzone_certificado" style="height: auto; padding: 1.5rem; min-height: 110px;">
                                    <input type="file" name="certificado_file" accept=".pdf,image/*">
                                    <div class="rf-dropzone-content" style="flex-direction: column; gap: 0.4rem; justify-content: center;">
                                        <i class="material-icons-round" style="font-size: 2.2rem; color: #cbd5e1;">cloud_upload</i>
                                        <p style="margin: 0; font-size: 0.75rem; color: var(--rf-text-muted);">Certificado de Estudios</p>
                                    </div>
                                    <div class="rf-file-preview"></div>
                                </div>
                            </div>

                            {{-- Carta de Compromiso --}}
                            <div class="rf-form-group">
                                <label class="rf-label">
                                    <span class="material-icons-round" style="font-size:1.1rem; vertical-align:middle; color:var(--rf-magenta);">draw</span>
                                    CARTA DE COMPROMISO FIRMADA <span style="color:var(--rf-red);">*</span>
                                </label>
                                <div class="rf-dropzone" id="dropzone_compromiso" style="height: auto; padding: 1.5rem; border-color: var(--rf-navy); min-height: 110px;">
                                    <input type="file" name="compromiso_file" accept=".pdf,image/*" required>
                                    <div class="rf-dropzone-content" style="flex-direction: column; gap: 0.4rem; justify-content: center;">
                                        <i class="material-icons-round" style="font-size: 2.2rem; color: var(--rf-navy); opacity: 0.3;">description</i>
                                        <p style="margin: 0; font-size: 0.75rem; color: var(--rf-text-muted);">Sube el PDF firmado</p>
                                    </div>
                                    <div class="rf-file-preview"></div>
                                </div>
                            </div>
                        </div>

                        <div class="rf-alert rf-alert-info" style="margin-top: 1.25rem;">
                            <span class="material-icons-round" style="color: var(--rf-cyan);">info</span>
                            <div style="flex: 1; font-size: 0.85rem; color: var(--rf-navy);">
                                <strong>Requisito Físico:</strong> Deberás entregar 02 fotos tamaño carnet y una mica transparente en nuestra oficina (Av. Dos de Mayo 960) para completar tu expediente.
                            </div>
                        </div>
                    </div>

                    {{-- ==================== PASO 5: RESUMEN ==================== --}}
                    <div class="rf-panel" data-step="5">
                        <div class="rf-step-header">
                            <h3><span class="material-icons-round" style="vertical-align: middle; color: var(--rf-green); margin-right: 0.3rem;">fact_check</span> Confirmación de Datos</h3>
                            <p>Revisa toda la información antes de enviar tu inscripción.</p>
                        </div>
                        <div id="reforzamientoResumen"></div>
                        <div class="rf-alert rf-alert-warning" style="margin-top:1.5rem;">
                            <span class="material-icons-round">privacy_tip</span>
                            <div style="font-size:0.85rem;">Al hacer clic en <strong>Finalizar Inscripción</strong>, declaras que toda la información proporcionada es verdadera y aceptas las políticas institucionales del CEPRE UNAMAD.</div>
                        </div>

                        <!-- CAMPOS OCULTOS DE CONTROL -->
                        <input type="hidden" id="ref_dni" name="dni">
                        <input type="hidden" id="ref_pago_api_serial" name="pago_api_serial">
                        <input type="hidden" id="ref_pago_api_fecha" name="pago_api_fecha">
                        <input type="hidden" id="ref_es_manual" name="es_manual" value="1">
                        <input type="hidden" id="ref_ciclo_id" name="ciclo_id">
                    </div>
                </form>
            </div>

            <footer class="rf-footer">
                <button type="button" id="btnPrev" class="rf-btn rf-btn-outline hidden">
                    <span class="material-icons-round">west</span> Anterior
                </button>
                <div id="rf-pagination-info" style="font-weight:700; color:var(--rf-text-muted); font-size:0.85rem; display:flex; align-items:center; gap:0.5rem;">
                    <span class="material-icons-round" style="font-size:1rem;">linear_scale</span>
                    Paso <span id="currentStepText" style="color:var(--rf-magenta); font-size:1rem;">1</span> de 5
                </div>
                <div>
                    <button type="button" id="btnNext" class="rf-btn rf-btn-magenta">
                        Siguiente <span class="material-icons-round">east</span>
                    </button>
                    <button type="submit" form="reforzamientoForm" id="btnSubmit" class="rf-btn rf-btn-magenta hidden" style="background: linear-gradient(135deg, var(--rf-navy) 0%, var(--rf-navy-light) 100%);">
                        <span class="material-icons-round">verified</span> Finalizar Inscripción
                    </button>
                </div>
            </footer>
        </main>
    </div>
</div>