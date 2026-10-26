@php
    use App\Models\SurveyQuestion;
    $typeOpts = SurveyQuestion::typeOptions();
    $name = "q[{$idx}]";
    $qType = $q->type ?? 'single';
    $qPrompt = $q->prompt ?? '';
    $qRequired = $q?->required ?? false;
    $qOptions = $q && is_array($q->options) ? implode("\n", $q->options) : '';
    $qMax = $q?->ratingMax() ?? 5;
    $isChoice = in_array($qType, ['single','multi'], true);
@endphp
<div class="card q-row">
    <div class="card-body">
        <div class="d-flex align-items-center gap-2 mb-3">
            <i class="fa-solid fa-grip-vertical text-subtle"></i>
            <select name="{{ $name }}[type]" data-role="type" class="form-select form-select-sm" style="max-width:190px">
                @foreach ($typeOpts as $v => $l)
                    <option value="{{ $v }}" @selected($qType === $v)>{{ $l }}</option>
                @endforeach
            </select>
            <div class="form-check ms-2 mb-0">
                <input type="checkbox" class="form-check-input" id="{{ $idx }}_req"
                       name="{{ $name }}[required]" value="1" @checked($qRequired)>
                <label class="form-check-label text-muted-2" for="{{ $idx }}_req" style="font-size:.85rem">Obligatoria</label>
            </div>
            <div class="ms-auto d-flex gap-1">
                <button type="button" class="icon-btn" data-act="up" title="Mover arriba"><i class="fa-solid fa-chevron-up"></i></button>
                <button type="button" class="icon-btn" data-act="down" title="Mover abajo"><i class="fa-solid fa-chevron-down"></i></button>
                <button type="button" class="icon-btn" data-act="remove" title="Eliminar" style="color:var(--danger)"><i class="fa-solid fa-trash"></i></button>
            </div>
        </div>

        @if ($q)
            <input type="hidden" name="{{ $name }}[id]" value="{{ $q->id }}">
        @endif

        <input type="text" name="{{ $name }}[prompt]" class="form-control mb-2"
               placeholder="Enunciado de la pregunta" maxlength="255" value="{{ $qPrompt }}">

        {{-- Choice options --}}
        <div data-for="options" class="{{ $isChoice ? '' : 'd-none' }}">
            <label class="form-label mt-1">Opciones <span class="text-subtle">(una por línea, mín. 2)</span></label>
            <textarea name="{{ $name }}[options]" class="form-control" rows="3" placeholder="Opción A&#10;Opción B">{{ $qOptions }}</textarea>
        </div>

        {{-- Rating max --}}
        <div data-for="rating" class="{{ $qType === 'rating' ? '' : 'd-none' }}" style="max-width:160px">
            <label class="form-label mt-1">Calificación máxima</label>
            <input type="number" name="{{ $name }}[rating_max]" class="form-control" min="2" max="10" value="{{ $qMax }}">
        </div>
    </div>
</div>
