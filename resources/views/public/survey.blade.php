<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      data-theme="{{ request()->cookie('theme', 'light') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.theme-init')
    <title>{{ $survey->title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @if ($fontUrl = \App\Support\GoogleFont::cssUrl($survey->font_family))
        <link href="{{ $fontUrl }}" rel="stylesheet">
    @endif
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    @vite(['resources/css/main.css', 'resources/js/app.js'])
    <style>
        .scale { display:flex; flex-wrap:wrap; gap:.4rem; }
        .scale input { position:absolute; opacity:0; }
        .scale label {
            min-width:40px; text-align:center; padding:.5rem .4rem; cursor:pointer;
            border:1px solid var(--border-strong); border-radius:var(--radius);
            color:var(--text-muted); font-weight:600; user-select:none;
        }
        .scale input:checked + label { background:var(--accent); border-color:var(--accent); color:#fff; }
        .opt { display:flex; align-items:center; gap:.6rem; padding:.6rem .8rem;
               border:1px solid var(--border); border-radius:var(--radius); cursor:pointer; }
        .opt:hover { background:var(--surface-hover); }
        .brand-logo { text-align:center; margin-bottom:1.25rem; }
        .brand-logo img { max-height:64px; max-width:220px; object-fit:contain; }
        .survey-toggle {
            position:fixed; top:.75rem; right:.75rem; z-index:10;
            background:var(--surface); border:1px solid var(--border);
        }
        .survey-progress { height:6px; border-radius:999px; background:var(--bg-sunken); overflow:hidden; }
        .survey-progress > div { height:100%; width:0; transition:width .25s ease;
            background:linear-gradient(90deg, var(--accent), var(--accent-2)); }
        .q-card.step-invalid { border-color:var(--danger); }
        .site-footer { text-align:center; margin-top:1.75rem; font-size:.78rem; }
        .site-footer a { color:var(--text-subtle); text-decoration:none; }
        .site-footer a:hover { text-decoration:underline; }
    </style>
    @include('partials.brand-style')
    @if (\App\Support\GoogleFont::isValid($survey->font_family))
        <style>body { font-family: {{ \App\Support\GoogleFont::stack($survey->font_family) }}; }</style>
    @endif
</head>
<body>
<button class="icon-btn survey-toggle" data-theme-toggle type="button" aria-label="Cambiar tema" title="Cambiar tema">
    <i class="fa-solid fa-circle-half-stroke"></i>
</button>

<div style="min-height:100vh;display:grid;place-items:start center;padding:2rem 1rem;background:
    radial-gradient(900px 400px at 50% -10%, var(--accent-soft), transparent 60%), var(--bg);">
    <div style="width:100%;max-width:520px">
        @if (isset($brand) && $brand && $brand->logoUrl())
            <div class="brand-logo"><img src="{{ $brand->logoUrl() }}" alt="{{ $brand->name }}"></div>
        @endif
        <div class="mb-4">
            <h1 class="h4 fw-bold mb-1">{{ $survey->title }}</h1>
            @if ($survey->description)
                <p class="text-muted-2 mb-0">{{ $survey->description }}</p>
            @endif
        </div>

        <form method="POST" action="{{ route('survey.public.submit', $survey->code) }}" id="surveyForm">
            @csrf
            @if ($via)<input type="hidden" name="via" value="{{ $via }}">@endif

            <div id="surveySteps">
                @if ($survey->collect_contact)
                    <div class="survey-step">
                        <div class="card">
                            <div class="card-body">
                                <label class="fw-semibold d-block mb-3">Tus datos</label>

                                <div class="mb-3">
                                    <label class="form-label">Nombre <span style="color:var(--danger)">*</span></label>
                                    <input type="text" name="contact_name" value="{{ old('contact_name') }}" data-req="1"
                                           class="form-control @error('contact_name') is-invalid @enderror" maxlength="160">
                                    @error('contact_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="req-msg" style="display:none;color:var(--danger);font-size:.82rem;margin-top:.4rem">
                                        El nombre es obligatorio.
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Teléfono <span class="text-subtle">(opcional)</span></label>
                                    <input type="tel" name="contact_phone" value="{{ old('contact_phone') }}"
                                           class="form-control @error('contact_phone') is-invalid @enderror" maxlength="40">
                                    @error('contact_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">Correo <span class="text-subtle">(opcional)</span></label>
                                    <input type="email" name="contact_email" value="{{ old('contact_email') }}"
                                           class="form-control @error('contact_email') is-invalid @enderror" maxlength="190">
                                    @error('contact_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($survey->paginated)
                    @foreach ($survey->questions as $q)
                        <div class="survey-step">
                            @include('public._survey_question', ['q' => $q])
                        </div>
                    @endforeach
                @else
                    <div class="survey-step">
                        <div class="d-flex flex-column gap-3">
                            @foreach ($survey->questions as $q)
                                @include('public._survey_question', ['q' => $q])
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-4">
                <div class="survey-progress" id="surveyProgress" style="display:none"><div></div></div>
                <div class="d-flex gap-2 mt-3">
                    <button type="button" id="surveyPrev" class="btn btn-outline-secondary" style="display:none">
                        <i class="fa-solid fa-arrow-left me-1"></i> Atrás
                    </button>
                    <button type="button" id="surveyNext" class="btn btn-brand flex-grow-1" style="display:none">
                        Siguiente <i class="fa-solid fa-arrow-right ms-1"></i>
                    </button>
                    <button type="submit" id="surveySubmit" class="btn btn-brand flex-grow-1">Enviar</button>
                </div>
            </div>
        </form>

        <footer class="site-footer">
            <a href="{{ url('/') }}">Desarrollado por NFC</a>
        </footer>
    </div>
</div>

<script>
(function () {
    const steps = Array.from(document.querySelectorAll('#surveySteps .survey-step'));
    const prev = document.getElementById('surveyPrev');
    const next = document.getElementById('surveyNext');
    const submit = document.getElementById('surveySubmit');
    const progress = document.getElementById('surveyProgress');
    const fill = progress ? progress.firstElementChild : null;
    const multi = steps.length > 1;
    let i = 0;

    function clearErrors(step) {
        step.querySelectorAll('.q-card.step-invalid').forEach(c => c.classList.remove('step-invalid'));
        step.querySelectorAll('.req-msg').forEach(m => m.style.display = 'none');
        step.querySelectorAll('.is-invalid').forEach(x => x.classList.remove('is-invalid'));
    }

    function validateStep(step) {
        let ok = true;
        // Required single inputs (e.g. contact name)
        step.querySelectorAll('[data-req="1"]').forEach(inp => {
            if (!String(inp.value || '').trim()) {
                ok = false; inp.classList.add('is-invalid');
                const msg = inp.parentElement.querySelector('.req-msg');
                if (msg) msg.style.display = 'block';
            }
        });
        // Required questions (radio/checkbox/rating/nps/text)
        step.querySelectorAll('.q-card[data-required="1"]').forEach(card => {
            let answered = false;
            card.querySelectorAll('input, textarea').forEach(x => {
                if (x.type === 'radio' || x.type === 'checkbox') { if (x.checked) answered = true; }
                else if (String(x.value || '').trim()) answered = true;
            });
            if (!answered) {
                ok = false; card.classList.add('step-invalid');
                const msg = card.querySelector('.req-msg');
                if (msg) msg.style.display = 'block';
            }
        });
        return ok;
    }

    function render() {
        steps.forEach((s, idx) => s.style.display = idx === i ? '' : 'none');
        const last = i === steps.length - 1;
        if (multi) {
            prev.style.display = i > 0 ? '' : 'none';
            next.style.display = last ? 'none' : '';
            progress.style.display = '';
            if (fill) fill.style.width = ((i + 1) / steps.length * 100) + '%';
        }
        submit.style.display = last ? '' : 'none';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    if (multi) {
        next.addEventListener('click', () => {
            if (!validateStep(steps[i])) return;
            if (i < steps.length - 1) { i++; render(); }
        });
        prev.addEventListener('click', () => { if (i > 0) { i--; render(); } });
        render();
    }

    // Validate the visible step before the final submit. If a middle step is
    // somehow submitted (e.g. Enter on a text field), advance instead.
    document.getElementById('surveyForm').addEventListener('submit', (e) => {
        if (multi && i < steps.length - 1) {
            e.preventDefault();
            if (validateStep(steps[i])) { i++; render(); }
            return;
        }
        if (!validateStep(steps[i])) e.preventDefault();
    });

    // Clear a question's error as soon as it's answered.
    document.getElementById('surveySteps').addEventListener('input', (e) => {
        const card = e.target.closest('.q-card');
        if (card) { card.classList.remove('step-invalid'); const m = card.querySelector('.req-msg'); if (m) m.style.display = 'none'; }
        if (e.target.matches('[data-req="1"]')) {
            e.target.classList.remove('is-invalid');
            const m = e.target.parentElement.querySelector('.req-msg'); if (m) m.style.display = 'none';
        }
    });
})();
</script>
</body>
</html>
