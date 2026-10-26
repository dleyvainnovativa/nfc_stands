@extends('layouts.app')
@section('title', 'Encuestas · NFC Stands')
@section('page-title', 'Encuestas')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <h1 class="h4 fw-bold mb-0">Encuestas</h1>
    <a href="{{ route('surveys.create') }}" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-plus me-1"></i> Nueva encuesta
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-cards align-middle mb-0">
            <thead>
                <tr class="text-muted-2" style="font-size:.8rem">
                    <th class="ps-3">Título</th><th>Estado</th><th>Preguntas</th>
                    <th class="text-end">Respuestas</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($surveys as $survey)
                    <tr>
                        <td class="cell-title ps-3 fw-semibold">{{ $survey->title }}</td>
                        <td data-label="Estado">
                            @php($cls = match($survey->status){ 'active' => 'success', 'closed' => 'danger', default => 'warning' })
                            <span class="badge-role" style="background:var(--{{ $cls }}-soft);color:var(--{{ $cls }})">
                                {{ $survey->statusLabel() }}
                            </span>
                        </td>
                        <td class="text-muted-2" data-label="Preguntas">{{ $survey->questions_count }}</td>
                        <td class="text-end mono" data-label="Respuestas">{{ number_format($survey->responses_count) }}</td>
                        <td class="cell-actions text-end pe-3">
                            <a href="{{ route('surveys.show', $survey) }}" class="btn btn-sm btn-outline-secondary">Resultados</a>
                            <a href="{{ route('surveys.edit', $survey) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted-2 py-5">
                        Aún no hay encuestas. <a href="{{ route('surveys.create') }}">Crea una</a>.
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $surveys->links() }}</div>
@endsection
