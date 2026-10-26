@extends('layouts.app')
@section('title', 'Clientes · NFC Stands')
@section('page-title', 'Clientes')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-bold mb-1">Clientes</h1>
        <p class="text-muted-2 mb-0" style="font-size:.9rem">
            Monitorea la actividad de cada cliente y entra a su panel con «Ver como».
        </p>
    </div>
    <button class="btn btn-primary btn-sm" type="button" onclick="App.modal.show('#newTenant')">
        <i class="fa-solid fa-plus me-1"></i> Nuevo cliente
    </button>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-cards align-middle mb-0">
            <thead>
                <tr class="text-muted-2" style="font-size:.8rem">
                    <th class="ps-3">Cliente</th>
                    <th class="text-end">Stands</th>
                    <th class="text-end">Escaneos (30d)</th>
                    <th class="text-end">Encuestas</th>
                    <th class="text-end">Respuestas</th>
                    <th class="text-end">Calif. prom.</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tenants as $t)
                    <tr>
                        <td class="cell-title ps-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar" style="border-radius:var(--radius)">{{ $t->initial() }}</span>
                                <div class="min-w-0">
                                    <div class="fw-semibold text-truncate">{{ $t->name }}</div>
                                    <div class="text-subtle text-truncate" style="font-size:.78rem">
                                        {{ $t->contact_email ?: $t->slug }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="text-end mono" data-label="Stands">
                            {{ number_format($t->active_stands_count) }}<span class="text-subtle">/{{ number_format($t->stands_count) }}</span>
                        </td>
                        <td class="text-end mono" data-label="Escaneos (30d)">{{ number_format($scans30[$t->id] ?? 0) }}</td>
                        <td class="text-end mono" data-label="Encuestas">{{ number_format($t->surveys_count) }}</td>
                        <td class="text-end mono" data-label="Respuestas">{{ number_format($t->survey_responses_count) }}</td>
                        <td class="text-end mono" data-label="Calif. prom.">
                            @if (isset($ratings[$t->id]))
                                {{ number_format($ratings[$t->id], 1) }}
                                <i class="fa-solid fa-star" style="color:var(--brand-gold);font-size:.8rem"></i>
                            @else
                                <span class="text-subtle">—</span>
                            @endif
                        </td>
                        <td class="cell-actions text-end pe-3">
                            <form method="POST" action="{{ route('tenant.enter', $t) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary" type="submit">
                                    <i class="fa-solid fa-eye me-1"></i> Ver como
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted-2 py-5">
                            Aún no hay clientes. <a href="#" onclick="App.modal.show('#newTenant');return false;">Crea el primero</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Create client modal --}}
<div class="modal fade" id="newTenant" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.tenants.store') }}">
                @csrf
                <div class="modal-header">
                    <h2 class="h6 fw-bold mb-0">Nuevo cliente</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre del cliente</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" maxlength="160" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-sm-7">
                            <label class="form-label">Correo de contacto <span class="text-subtle">(opcional)</span></label>
                            <input type="email" name="contact_email" value="{{ old('contact_email') }}" class="form-control" maxlength="190">
                        </div>
                        <div class="col-sm-5">
                            <label class="form-label">Teléfono <span class="text-subtle">(opcional)</span></label>
                            <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" class="form-control" maxlength="40">
                        </div>
                    </div>

                    <hr class="hr-token my-3">
                    <div class="fw-semibold mb-2" style="font-size:.85rem">Administrador del cliente</div>

                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="admin_name" value="{{ old('admin_name') }}" class="form-control" maxlength="160" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-sm-7">
                            <label class="form-label">Correo (acceso)</label>
                            <input type="email" name="admin_email" value="{{ old('admin_email') }}" class="form-control" maxlength="190" required>
                        </div>
                        <div class="col-sm-5">
                            <label class="form-label">Contraseña</label>
                            <input type="text" name="admin_password" class="form-control" minlength="8" required>
                        </div>
                    </div>
                    <div class="form-text text-muted-2 mt-2">La contraseña debe tener al menos 8 caracteres. Compártela con el cliente.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($errors->any())
    @push('scripts')
    <script>document.addEventListener('DOMContentLoaded', () => App.modal.show('#newTenant'));</script>
    @endpush
@endif
@endsection
