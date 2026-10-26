@extends('layouts.app')
@section('title', $survey->title . ' · Resultados')
@section('page-title', 'Resultados de la encuesta')

@php use App\Models\SurveyQuestion; @endphp

@section('content')
<div class="mb-3">
    <a href="{{ route('surveys.index') }}" class="text-muted-2" style="font-size:.9rem">
        <i class="fa-solid fa-arrow-left me-1"></i> Encuestas
    </a>
</div>

<div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 class="h4 fw-bold mb-1">{{ $survey->title }}</h1>
        @php($cls = match($survey->status){ 'active' => 'success', 'closed' => 'danger', default => 'warning' })
        <span class="badge-role" style="background:var(--{{ $cls }}-soft);color:var(--{{ $cls }})">{{ $survey->statusLabel() }}</span>
        <span class="text-muted-2 ms-2" style="font-size:.9rem">{{ number_format($survey->responses_count) }} respuestas</span>
    </div>
    <a href="{{ route('surveys.edit', $survey) }}" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-pen me-1"></i> Editar
    </a>
</div>

@if (empty($results))
    <div class="card"><div class="card-body text-center text-muted-2 py-5">
        Aún no hay preguntas. <a href="{{ route('surveys.edit', $survey) }}">Agrega algunas</a>.
    </div></div>
@endif

@foreach ($results as $r)
    @php($q = $r['question'])
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3 gap-2">
                <div>
                    <div class="fw-semibold">{{ $q->prompt }}</div>
                    <div class="text-subtle" style="font-size:.8rem">
                        {{ SurveyQuestion::typeOptions()[$q->type] ?? $q->type }} · {{ $r['answered'] }} respondidas
                    </div>
                </div>
            </div>

            @switch($q->type)
                @case('single')
                @case('multi')
                    <div class="d-flex flex-column gap-2">
                        @foreach ($r['data']['rows'] as $row)
                            <div>
                                <div class="d-flex justify-content-between" style="font-size:.88rem">
                                    <span>{{ $row['label'] }}</span>
                                    <span class="text-muted-2">{{ $row['count'] }} · {{ $row['pct'] }}%</span>
                                </div>
                                @include('surveys._bar', ['pct' => $row['pct']])
                            </div>
                        @endforeach
                    </div>
                    @break

                @case('rating')
                    <div class="mb-3">
                        <span class="h3 fw-bold">{{ $r['data']['average'] ?? '—' }}</span>
                        <span class="text-muted-2">/ {{ $r['data']['max'] }} promedio ({{ $r['data']['count'] }})</span>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        @foreach ($r['data']['rows'] as $row)
                            <div>
                                <div class="d-flex justify-content-between" style="font-size:.88rem">
                                    <span>{{ $row['label'] }} ★</span>
                                    <span class="text-muted-2">{{ $row['count'] }} · {{ $row['pct'] }}%</span>
                                </div>
                                @include('surveys._bar', ['pct' => $row['pct']])
                            </div>
                        @endforeach
                    </div>
                    @break

                @case('nps')
                    @php($d = $r['data'])
                    <div class="d-flex align-items-center gap-4 flex-wrap mb-3">
                        <div>
                            <div class="stat-label">NPS</div>
                            <div class="h2 fw-bold mb-0">{{ $d['score'] ?? '—' }}</div>
                        </div>
                        <div class="flex-grow-1" style="min-width:220px">
                            <div style="display:flex;height:12px;border-radius:999px;overflow:hidden;background:var(--bg-sunken)">
                                <div style="width:{{ $d['detractors_pct'] }}%;background:var(--danger)"></div>
                                <div style="width:{{ $d['passives_pct'] }}%;background:var(--warning)"></div>
                                <div style="width:{{ $d['promoters_pct'] }}%;background:var(--success)"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-2 text-muted-2" style="font-size:.8rem">
                                <span><i class="fa-solid fa-circle" style="color:var(--danger)"></i> Detractores {{ $d['detractors'] }}</span>
                                <span><i class="fa-solid fa-circle" style="color:var(--warning)"></i> Pasivos {{ $d['passives'] }}</span>
                                <span><i class="fa-solid fa-circle" style="color:var(--success)"></i> Promotores {{ $d['promoters'] }}</span>
                            </div>
                        </div>
                    </div>
                    @break

                @case('text')
                    @if (empty($r['data']['recent']))
                        <div class="text-muted-2" style="font-size:.9rem">Aún no hay respuestas de texto.</div>
                    @else
                        <div class="d-flex flex-column gap-2" style="max-height:320px;overflow:auto">
                            @foreach ($r['data']['recent'] as $txt)
                                <div class="p-2 rounded" style="background:var(--bg-sunken);font-size:.9rem">{{ $txt }}</div>
                            @endforeach
                        </div>
                    @endif
                    @break
            @endswitch
        </div>
    </div>
@endforeach

@if (isset($contacts) && $contacts->isNotEmpty())
    <div class="card mt-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Contactos</span>
            <span class="text-muted-2" style="font-size:.85rem">{{ $contacts->count() }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-cards align-middle mb-0">
                <thead>
                    <tr class="text-muted-2" style="font-size:.8rem">
                        <th class="ps-3">Nombre</th><th>Teléfono</th><th>Correo</th><th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($contacts as $c)
                        <tr>
                            <td class="cell-title ps-3 fw-semibold">{{ $c->contact_name }}</td>
                            <td data-label="Teléfono">
                                @if ($c->contact_phone)<a href="tel:{{ $c->contact_phone }}">{{ $c->contact_phone }}</a>@else <span class="text-subtle">—</span> @endif
                            </td>
                            <td data-label="Correo" class="text-truncate" style="max-width:220px">
                                @if ($c->contact_email)<a href="mailto:{{ $c->contact_email }}">{{ $c->contact_email }}</a>@else <span class="text-subtle">—</span> @endif
                            </td>
                            <td data-label="Fecha" class="text-muted-2" style="font-size:.85rem">{{ $c->submitted_at?->format('d M, H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
