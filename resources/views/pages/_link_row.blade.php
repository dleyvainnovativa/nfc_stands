@php($hasCustom = !empty($link['bg'] ?? null) || !empty($link['text'] ?? null))
<div class="link-row" style="border:1px solid var(--border);border-radius:var(--radius);padding:.6rem .7rem">
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <select name="link_icon[]" class="form-select" style="max-width:150px">
            @foreach (\App\Models\Page::iconOptions() as $v => $l)
                <option value="{{ $v }}" @selected(($link['icon'] ?? '') === $v)>{{ $l }}</option>
            @endforeach
        </select>
        <input type="text" name="link_label[]" class="form-control" style="min-width:110px;flex:1 1 110px"
               placeholder="Texto" value="{{ $link['label'] ?? '' }}" maxlength="80">
        <input type="url" name="link_url[]" class="form-control" style="min-width:150px;flex:2 1 150px"
               placeholder="https://…" value="{{ $link['url'] ?? '' }}" maxlength="2048">
        <button type="button" class="btn btn-outline-secondary remove-row flex-shrink-0"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <div class="d-flex gap-3 align-items-center flex-wrap mt-2" style="font-size:.82rem">
        <label class="text-muted-2 d-inline-flex align-items-center gap-1 mb-0" style="cursor:pointer">
            <input type="checkbox" class="form-check-input link-custom mt-0" @checked($hasCustom)>
            Color propio
        </label>
        <div class="link-colors d-flex gap-2 align-items-center {{ $hasCustom ? '' : 'd-none' }}">
            <span class="text-muted-2">Fondo</span>
            <input type="color" class="link-bg-pick" value="{{ $link['bg'] ?? '#2e6be6' }}"
                   style="width:34px;height:30px;padding:1px;border:1px solid var(--border-strong);border-radius:6px;background:var(--bg-elevated)">
            <span class="text-muted-2">Texto</span>
            <input type="color" class="link-text-pick" value="{{ $link['text'] ?? '#ffffff' }}"
                   style="width:34px;height:30px;padding:1px;border:1px solid var(--border-strong);border-radius:6px;background:var(--bg-elevated)">
        </div>
        <input type="hidden" name="link_bg[]" class="link-bg-val" value="{{ $link['bg'] ?? '' }}">
        <input type="hidden" name="link_text[]" class="link-text-val" value="{{ $link['text'] ?? '' }}">
    </div>
</div>
