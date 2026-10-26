@php($err = $errors->first("answers.{$q->id}"))
<div class="card q-card" @if($q->required) data-required="1" @endif data-qid="{{ $q->id }}">
    <div class="card-body">
        <label class="fw-semibold d-block mb-2">
            {{ $q->prompt }}
            @if ($q->required)<span style="color:var(--danger)">*</span>@endif
        </label>

        @switch($q->type)
            @case('single')
                <div class="d-flex flex-column gap-2">
                    @foreach ($q->options as $opt)
                        <label class="opt">
                            <input type="radio" name="answers[{{ $q->id }}]" value="{{ $opt }}"
                                   @checked(old("answers.{$q->id}") === $opt)>
                            <span>{{ $opt }}</span>
                        </label>
                    @endforeach
                </div>
                @break

            @case('multi')
                <div class="d-flex flex-column gap-2">
                    @foreach ($q->options as $opt)
                        <label class="opt">
                            <input type="checkbox" name="answers[{{ $q->id }}][]" value="{{ $opt }}"
                                   @checked(in_array($opt, (array) old("answers.{$q->id}", [])))>
                            <span>{{ $opt }}</span>
                        </label>
                    @endforeach
                </div>
                @break

            @case('rating')
                <div class="scale">
                    @for ($n = 1; $n <= $q->ratingMax(); $n++)
                        <span style="position:relative">
                            <input type="radio" id="q{{ $q->id }}_{{ $n }}" name="answers[{{ $q->id }}]" value="{{ $n }}"
                                   @checked((string)old("answers.{$q->id}") === (string)$n)>
                            <label for="q{{ $q->id }}_{{ $n }}">{{ $n }}</label>
                        </span>
                    @endfor
                </div>
                @break

            @case('nps')
                <div class="scale">
                    @for ($n = 0; $n <= 10; $n++)
                        <span style="position:relative">
                            <input type="radio" id="q{{ $q->id }}_{{ $n }}" name="answers[{{ $q->id }}]" value="{{ $n }}"
                                   @checked((string)old("answers.{$q->id}") === (string)$n)>
                            <label for="q{{ $q->id }}_{{ $n }}">{{ $n }}</label>
                        </span>
                    @endfor
                </div>
                <div class="d-flex justify-content-between text-subtle mt-1" style="font-size:.78rem">
                    <span>Poco probable</span><span>Muy probable</span>
                </div>
                @break

            @case('text')
                <textarea name="answers[{{ $q->id }}]" class="form-control" rows="3" maxlength="2000"
                          placeholder="Tu respuesta">{{ old("answers.{$q->id}") }}</textarea>
                @break
        @endswitch

        @if ($err)
            <div style="color:var(--danger);font-size:.82rem;margin-top:.5rem">{{ $err }}</div>
        @endif
        <div class="req-msg" style="display:none;color:var(--danger);font-size:.82rem;margin-top:.5rem">
            Esta pregunta es obligatoria.
        </div>
    </div>
</div>
