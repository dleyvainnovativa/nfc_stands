@extends('layouts.app')
@section('title', 'Páginas · NFC Stands')
@section('page-title', 'Páginas')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-bold mb-1">Páginas</h1>
        <p class="text-muted-2 mb-0" style="font-size:.9rem">Páginas tipo linktree para menú, redes y más.</p>
    </div>
    @if (auth()->user()->isSuperAdmin())
        <a href="{{ route('pages.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i> Nueva página
        </a>
    @endif
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-cards align-middle mb-0">
            <thead>
                <tr class="text-muted-2" style="font-size:.8rem">
                    <th class="ps-3">Página</th>
                    <th class="text-end">Vistas</th>
                    <th class="text-end">Clics</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pages as $p)
                    <tr>
                        <td class="cell-title ps-3">
                            <div class="fw-semibold">{{ $p->name }}</div>
                            @php($cls = $p->is_active ? 'success' : 'warning')
                            <span class="badge-role" style="background:var(--{{ $cls }}-soft);color:var(--{{ $cls }})">{{ $p->statusLabel() }}</span>
                        </td>
                        <td class="text-end mono" data-label="Vistas">{{ number_format($p->views) }}</td>
                        <td class="text-end mono" data-label="Clics">{{ number_format($p->link_clicks_count) }}</td>
                        <td class="cell-actions text-end pe-3">
                            <a href="{{ route('pages.edit', $p) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                            <a href="{{ route('page.public', $p->code) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">Ver</a>
                            @if (auth()->user()->isSuperAdmin())
                                <form method="POST" action="{{ route('pages.toggle', $p) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-secondary" type="submit">{{ $p->is_active ? 'Ocultar' : 'Publicar' }}</button>
                                </form>
                                <form method="POST" action="{{ route('pages.destroy', $p) }}" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar esta página? Esta acción no se puede deshacer.')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-secondary" type="submit" style="color:var(--danger)"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted-2 py-5">
                            @if (auth()->user()->isSuperAdmin())
                                Aún no hay páginas. <a href="{{ route('pages.create') }}">Crea la primera</a>.
                            @else
                                Aún no hay páginas. Pide al administrador que cree una.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $pages->links() }}</div>
@endsection
