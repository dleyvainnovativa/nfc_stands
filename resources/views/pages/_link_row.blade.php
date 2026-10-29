<div class="link-row d-flex gap-2 align-items-center flex-wrap">
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
