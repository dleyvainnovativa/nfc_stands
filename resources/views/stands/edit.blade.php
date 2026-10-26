@extends('layouts.app')
@section('title', 'Editar stand · NFC Stands')
@section('page-title', 'Editar stand')

@section('content')
<div class="mb-3">
    <a href="{{ route('stands.show', $stand) }}" class="text-muted-2" style="font-size:.9rem">
        <i class="fa-solid fa-arrow-left me-1"></i> Volver al stand
    </a>
</div>

<div class="card" style="max-width:720px">
    <div class="card-body">
        <h1 class="h5 fw-bold mb-1">Editar stand</h1>
        <p class="text-muted-2 mb-4" style="font-size:.9rem">
            Código <code>{{ $stand->code }}</code> · los cambios aplican en el siguiente escaneo.
        </p>
        <form method="POST" action="{{ route('stands.update', $stand) }}">
            @csrf @method('PUT')
            @include('stands._form')
            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-primary">Guardar cambios</button>
                <a href="{{ route('stands.show', $stand) }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
