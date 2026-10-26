@extends('layouts.app')
@section('title', 'Etiquetas en blanco · NFC Stands')
@section('page-title', 'Etiquetas en blanco')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-bold mb-1">Etiquetas en blanco</h1>
        <p class="text-muted-2 mb-0" style="font-size:.9rem">
            Genera códigos por adelantado para grabar en calcomanías NFC físicas. Los clientes las reclaman por código.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.tags.qr_sheet') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-qrcode me-1"></i> Imprimir hoja QR
        </a>
        <a href="{{ route('admin.tags.export') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-file-csv me-1"></i> Exportar todo (CSV)
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h2 class="h6 fw-bold mb-3">Generar</h2>
                <form method="POST" action="{{ route('admin.tags.store') }}">
                    @csrf
                    <label class="form-label">¿Cuántas?</label>
                    <input type="number" name="count" min="1" max="500" value="25"
                           class="form-control @error('count') is-invalid @enderror">
                    @error('count') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text text-muted-2 mb-3">Hasta 500 por lote.</div>
                    <button class="btn btn-primary w-100">Generar etiquetas</button>
                </form>

                @if (session('generated_codes'))
                    <hr class="hr-token my-3">
                    <div class="fw-semibold mb-2" style="font-size:.85rem">Recién generadas</div>
                    <div class="mono" style="font-size:.8rem;max-height:160px;overflow:auto">
                        @foreach (session('generated_codes') as $c)
                            <div>{{ $c }}</div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Etiquetas sin reclamar</span>
                <span class="text-muted-2" style="font-size:.85rem">{{ $blanks->total() }} en total</span>
            </div>
            <div class="table-responsive">
                <table class="table table-cards align-middle mb-0">
                    <thead>
                        <tr class="text-muted-2" style="font-size:.8rem">
                            <th class="ps-3">Código</th><th>URL pública</th><th>Creada</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($blanks as $b)
                            <tr>
                                <td class="cell-title ps-3 mono">{{ $b->code }}</td>
                                <td class="text-muted-2" style="font-size:.85rem" data-label="URL pública">{{ url('/t/' . $b->code) }}</td>
                                <td class="text-muted-2" style="font-size:.85rem" data-label="Creada">{{ $b->created_at?->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted-2 py-4">No hay etiquetas en blanco. Genera un lote para empezar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $blanks->links() }}</div>
    </div>
</div>
@endsection
