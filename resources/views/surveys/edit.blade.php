@extends('layouts.app')
@section('title', 'Editar encuesta · NFC Stands')
@section('page-title', 'Editar encuesta')

@php
    use App\Models\SurveyQuestion;
    $typeOpts = SurveyQuestion::typeOptions();
@endphp

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <a href="{{ route('surveys.index') }}" class="text-muted-2" style="font-size:.9rem">
        <i class="fa-solid fa-arrow-left me-1"></i> Encuestas
    </a>
    <div class="d-flex align-items-center gap-2" style="min-width:0">
        <code class="text-muted-2 text-truncate d-inline-block" style="font-size:.82rem;max-width:min(48vw,300px)" title="{{ $survey->publicUrl() }}">{{ $survey->publicUrl() }}</code>
        <button type="button" class="btn btn-outline-secondary btn-sm flex-shrink-0" id="copySurveyUrl" data-url="{{ $survey->publicUrl() }}" title="Copiar enlace">
            <i class="fa-regular fa-copy"></i>
        </button>
        <a href="{{ route('surveys.show', $survey) }}" class="btn btn-outline-secondary btn-sm flex-shrink-0">Resultados</a>
    </div>
</div>

<form method="POST" action="{{ route('surveys.update', $survey) }}">
    @csrf @method('PUT')

    {{-- Meta --}}
    <div class="card mb-4" style="max-width:820px">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Título</label>
                    <input type="text" name="title" value="{{ old('title', $survey->title) }}" class="form-control" maxlength="160" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        @foreach (['draft'=>'Borrador','active'=>'Activa','closed'=>'Cerrada'] as $v=>$l)
                            <option value="{{ $v }}" @selected(old('status',$survey->status)===$v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Descripción <span class="text-subtle">(opcional)</span></label>
                    <textarea name="description" class="form-control" rows="2" maxlength="1000">{{ old('description', $survey->description) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Mensaje de agradecimiento <span class="text-subtle">(opcional)</span></label>
                    <input type="text" name="thank_you_message" value="{{ old('thank_you_message', $survey->thank_you_message) }}" class="form-control" maxlength="300" placeholder="¡Gracias por tus comentarios!">
                </div>

                <div class="col-md-6">
                    <label class="form-label">URL de regreso <span class="text-subtle">(opcional)</span></label>
                    <input type="url" name="return_url" value="{{ old('return_url', $survey->return_url) }}" class="form-control" maxlength="2048" placeholder="https://instagram.com/tu-negocio">
                    <div class="form-text text-muted-2">Botón que se muestra al terminar (redes sociales, sitio web…).</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Texto del botón <span class="text-subtle">(opcional)</span></label>
                    <input type="text" name="return_label" value="{{ old('return_label', $survey->return_label) }}" class="form-control" maxlength="60" placeholder="Síguenos / Volver al sitio">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tipografía</label>
                    <select name="font_family" class="form-select">
                        @foreach (\App\Support\GoogleFont::options() as $v => $l)
                            <option value="{{ $v }}" @selected(old('font_family', $survey->font_family ?? '') === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                    <div class="form-text text-muted-2">Se aplica a la encuesta pública.</div>
                </div>

                <div class="col-12 d-flex flex-column gap-2 mt-1">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="paginated" name="paginated" value="1" @checked(old('paginated', $survey->paginated))>
                        <label class="form-check-label" for="paginated">Mostrar una pregunta por página</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="collect_contact" name="collect_contact" value="1" @checked(old('collect_contact', $survey->collect_contact))>
                        <label class="form-check-label" for="collect_contact">
                            Pedir datos de contacto al inicio
                            <span class="text-subtle">(nombre obligatorio; teléfono/correo opcionales)</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Questions --}}
    <div class="d-flex align-items-center justify-content-between mb-2" style="max-width:820px">
        <h2 class="h6 fw-bold mb-0">Preguntas</h2>
    </div>

    <div id="questions" class="d-flex flex-column gap-3" style="max-width:820px">
        @foreach ($survey->questions as $i => $q)
            @include('surveys._question_row', [
                'idx' => $i,
                'q' => $q,
            ])
        @endforeach
    </div>

    <div class="mt-3" style="max-width:820px">
        <button type="button" id="addQuestion" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-plus me-1"></i> Agregar pregunta
        </button>
    </div>

    <div class="d-flex gap-2 mt-4" style="max-width:820px">
        <button class="btn btn-primary">Guardar encuesta</button>
        <a href="{{ route('surveys.index') }}" class="btn btn-outline-secondary">Listo</a>
    </div>
</form>

{{-- Template for new question rows --}}
<template id="qTemplate">
    @include('surveys._question_row', ['idx' => '__IDX__', 'q' => null])
</template>

@push('scripts')
<script>
(function () {
    const wrap = document.getElementById('questions');
    const tpl = document.getElementById('qTemplate').innerHTML;
    let counter = {{ $survey->questions->count() }};

    function wire(row) {
        const typeSel = row.querySelector('[data-role="type"]');
        const toggle = () => {
            const t = typeSel.value;
            row.querySelector('[data-for="options"]').classList.toggle('d-none', !(t === 'single' || t === 'multi'));
            row.querySelector('[data-for="rating"]').classList.toggle('d-none', t !== 'rating');
        };
        typeSel.addEventListener('change', toggle); toggle();

        row.querySelector('[data-act="remove"]').addEventListener('click', () => row.remove());
        row.querySelector('[data-act="up"]').addEventListener('click', () => {
            const prev = row.previousElementSibling; if (prev) wrap.insertBefore(row, prev);
        });
        row.querySelector('[data-act="down"]').addEventListener('click', () => {
            const next = row.nextElementSibling; if (next) wrap.insertBefore(next, row);
        });
    }

    wrap.querySelectorAll('.q-row').forEach(wire);

    document.getElementById('addQuestion').addEventListener('click', () => {
        const html = tpl.replaceAll('__IDX__', 'n' + (counter++));
        const tmp = document.createElement('div');
        tmp.innerHTML = html.trim();
        const row = tmp.firstElementChild;
        wrap.appendChild(row);
        wire(row);
    });
})();
</script>
@endpush

@push('scripts')
<script>
  document.getElementById('copySurveyUrl')?.addEventListener('click', async (e) => {
    const btn = e.currentTarget;
    try {
      await navigator.clipboard.writeText(btn.dataset.url);
      window.App?.toast('Enlace copiado', 'success');
    } catch (err) { window.App?.toast('No se pudo copiar', 'error'); }
  });
</script>
@endpush
@endsection
