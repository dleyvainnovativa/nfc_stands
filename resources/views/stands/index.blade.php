@extends('layouts.app')
@section('title', 'Stands · NFC Stands')
@section('page-title', 'Stands')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <h1 class="h4 fw-bold mb-0">Stands</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('stands.claim') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-plus-circle me-1"></i> Reclamar etiqueta
        </a>
        <a href="{{ route('stands.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i> Nuevo stand
        </a>
    </div>
</div>

<form method="GET" class="row g-2 mb-3" style="max-width:560px">
    <div class="col">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
               placeholder="Buscar etiqueta o código…">
    </div>
    <div class="col-auto">
        <select name="status" class="form-select form-select-sm">
            <option value="">Todos los estados</option>
            @foreach (['active' => 'Activo', 'unassigned' => 'Sin asignar', 'disabled' => 'Deshabilitado'] as $v => $l)
                <option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary btn-sm">Filtrar</button>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-cards align-middle mb-0">
            <thead>
                <tr class="text-muted-2" style="font-size:.8rem">
                    <th class="ps-3">Etiqueta</th>
                    <th>Código</th>
                    <th>Destino</th>
                    <th>Estado</th>
                    <th class="text-end">Escaneos</th>
                    <th>Último escaneo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($stands as $stand)
                    <tr>
                        <td class="cell-title ps-3 fw-semibold">{{ $stand->label ?: '—' }}</td>
                        <td data-label="Código"><code>{{ $stand->code }}</code></td>
                        <td class="text-muted-2" data-label="Destino">{{ \App\Models\Stand::typeOptions()[$stand->destination_type] ?? $stand->destination_type }}</td>
                        <td data-label="Estado">
                            @php($cls = match($stand->status){ 'active' => 'success', 'disabled' => 'danger', default => 'warning' })
                            <span class="badge-role" style="background:var(--{{ $cls }}-soft);color:var(--{{ $cls }})">
                                {{ $stand->statusLabel() }}
                            </span>
                        </td>
                        <td class="text-end mono" data-label="Escaneos">{{ number_format($stand->scans_total) }}</td>
                        <td class="text-muted-2" style="font-size:.86rem" data-label="Último escaneo">
                            {{ $stand->last_scan_at?->diffForHumans() ?? '—' }}
                        </td>
                        <td class="cell-actions text-end pe-3">
                            <a href="{{ route('stands.show', $stand) }}" class="btn btn-sm btn-outline-secondary">Abrir</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted-2 py-5">
                            Aún no hay stands. <a href="{{ route('stands.create') }}">Crea uno</a> o
                            <a href="{{ route('stands.claim') }}">reclama una etiqueta</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $stands->links() }}</div>
@endsection
