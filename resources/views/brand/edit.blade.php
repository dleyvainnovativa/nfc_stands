@extends('layouts.app')
@section('title', 'Marca · NFC Stands')
@section('page-title', 'Marca')

@php
    $primary = old('primary_color', $tenant->primary_color ?: \App\Models\Tenant::DEFAULT_PRIMARY);
    $secondary = old('secondary_color', $tenant->secondary_color ?: \App\Models\Tenant::DEFAULT_SECONDARY);
@endphp

@section('content')
<div class="mb-4">
    <h1 class="h4 fw-bold mb-1">Marca de {{ $tenant->name }}</h1>
    <p class="text-muted-2 mb-0" style="font-size:.9rem">
        Personaliza el color y el logotipo que ven tus clientes en las encuestas públicas.
    </p>
</div>

<div class="row g-4" style="max-width:920px">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('brand.update') }}" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label">Color primario</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" id="primaryPicker" value="{{ $primary }}"
                                       style="width:44px;height:38px;padding:2px;border:1px solid var(--border-strong);border-radius:var(--radius);background:var(--bg-elevated)">
                                <input type="text" name="primary_color" id="primaryText" value="{{ $primary }}"
                                       class="form-control mono @error('primary_color') is-invalid @enderror"
                                       maxlength="7" placeholder="#2e6be6">
                            </div>
                            @error('primary_color') <div class="text-danger mt-1" style="font-size:.82rem">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Color secundario</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" id="secondaryPicker" value="{{ $secondary }}"
                                       style="width:44px;height:38px;padding:2px;border:1px solid var(--border-strong);border-radius:var(--radius);background:var(--bg-elevated)">
                                <input type="text" name="secondary_color" id="secondaryText" value="{{ $secondary }}"
                                       class="form-control mono @error('secondary_color') is-invalid @enderror"
                                       maxlength="7" placeholder="#1b34c4">
                            </div>
                            @error('secondary_color') <div class="text-danger mt-1" style="font-size:.82rem">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <hr class="hr-token my-4">

                    <label class="form-label">Logotipo</label>
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div id="logoPreview" style="width:72px;height:72px;border-radius:var(--radius);border:1px solid var(--border);
                             display:grid;place-items:center;background:var(--bg-sunken);overflow:hidden">
                            @if ($tenant->logoUrl())
                                <img src="{{ $tenant->logoUrl() }}" alt="Logo" style="max-width:100%;max-height:100%;object-fit:contain">
                            @else
                                <i class="fa-solid fa-image text-subtle"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <input type="file" name="logo" id="logoInput" accept="image/png,image/jpeg,image/webp"
                                   class="form-control @error('logo') is-invalid @enderror">
                            <div class="form-text text-muted-2">PNG, JPG o WEBP · máx. 1 MB.</div>
                            @error('logo') <div class="text-danger mt-1" style="font-size:.82rem">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    @if ($tenant->logoUrl())
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="remove_logo" name="remove_logo" value="1">
                            <label class="form-check-label text-muted-2" for="remove_logo" style="font-size:.88rem">Quitar el logotipo actual</label>
                        </div>
                    @endif

                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary">Guardar marca</button>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Live preview --}}
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">Vista previa</div>
            <div class="card-body">
                <div id="brandPreview" style="--bp:{{ $primary }};--bs:{{ $secondary }};
                     border-radius:var(--radius-lg);padding:1.5rem;text-align:center;
                     background:linear-gradient(150deg, color-mix(in srgb, var(--bp) 10%, var(--surface)), var(--surface));
                     border:1px solid var(--border)">
                    <div id="previewLogo" style="margin-bottom:1rem">
                        @if ($tenant->logoUrl())
                            <img src="{{ $tenant->logoUrl() }}" alt="Logo" style="max-height:52px;max-width:160px;object-fit:contain">
                        @endif
                    </div>
                    <div class="fw-bold mb-1">¿Cómo fue tu experiencia?</div>
                    <div class="text-muted-2 mb-3" style="font-size:.86rem">Encuesta de ejemplo</div>
                    <div style="display:flex;gap:.4rem;justify-content:center;margin-bottom:1rem">
                        @for ($i = 1; $i <= 5; $i++)
                            <span class="prev-chip" style="min-width:34px;padding:.4rem;border-radius:var(--radius);
                                  border:1px solid var(--border-strong);font-weight:600;font-size:.85rem;
                                  {{ $i === 5 ? 'background:var(--bp);border-color:var(--bp);color:#fff' : 'color:var(--text-muted)' }}">{{ $i }}</span>
                        @endfor
                    </div>
                    <button type="button" id="previewBtn"
                            style="width:100%;border:none;border-radius:var(--radius);padding:.6rem;font-weight:600;
                                   color:#fff;background:var(--bp)">Enviar</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const pPick = document.getElementById('primaryPicker');
    const pText = document.getElementById('primaryText');
    const sPick = document.getElementById('secondaryPicker');
    const sText = document.getElementById('secondaryText');
    const preview = document.getElementById('brandPreview');
    const hex = /^#([0-9a-fA-F]{6})$/;

    function apply() {
        if (hex.test(pText.value)) preview.style.setProperty('--bp', pText.value);
        if (hex.test(sText.value)) preview.style.setProperty('--bs', sText.value);
    }
    pPick.addEventListener('input', () => { pText.value = pPick.value; apply(); });
    sPick.addEventListener('input', () => { sText.value = sPick.value; apply(); });
    pText.addEventListener('input', () => { if (hex.test(pText.value)) pPick.value = pText.value; apply(); });
    sText.addEventListener('input', () => { if (hex.test(sText.value)) sPick.value = sText.value; apply(); });

    // Live logo preview on file pick
    const logoInput = document.getElementById('logoInput');
    const previewLogo = document.getElementById('previewLogo');
    logoInput?.addEventListener('change', () => {
        const f = logoInput.files?.[0];
        if (!f) return;
        const url = URL.createObjectURL(f);
        previewLogo.innerHTML = '<img src="' + url + '" alt="Logo" style="max-height:52px;max-width:160px;object-fit:contain">';
        document.getElementById('logoPreview').innerHTML = '<img src="' + url + '" style="max-width:100%;max-height:100%;object-fit:contain">';
    });
})();
</script>
@endpush
@endsection
