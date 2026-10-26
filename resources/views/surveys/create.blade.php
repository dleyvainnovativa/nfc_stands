@extends('layouts.app')
@section('title', 'Nueva encuesta · NFC Stands')
@section('page-title', 'Nueva encuesta')

@section('content')
<div class="mb-3">
    <a href="{{ route('surveys.index') }}" class="text-muted-2" style="font-size:.9rem">
        <i class="fa-solid fa-arrow-left me-1"></i> Encuestas
    </a>
</div>

<div class="card" style="max-width:640px">
    <div class="card-body">
        <h1 class="h5 fw-bold mb-4">Crear encuesta</h1>
        <form method="POST" action="{{ route('surveys.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Título</label>
                <input type="text" name="title" value="{{ old('title') }}" class="form-control" maxlength="160" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Descripción <span class="text-subtle">(opcional)</span></label>
                <textarea name="description" class="form-control" rows="2" maxlength="1000">{{ old('description') }}</textarea>
            </div>
            <div class="mb-3" style="max-width:220px">
                <label class="form-label">Estado</label>
                <select name="status" class="form-select">
                    <option value="draft" selected>Borrador</option>
                    <option value="active">Activa</option>
                    <option value="closed">Cerrada</option>
                </select>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-primary">Crear y agregar preguntas</button>
                <a href="{{ route('surveys.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
