@extends('layouts.app')
@section('title', 'Nuevo stand · NFC Stands')
@section('page-title', 'Nuevo stand')

@section('content')
<div class="mb-3">
    <a href="{{ route('stands.index') }}" class="text-muted-2" style="font-size:.9rem">
        <i class="fa-solid fa-arrow-left me-1"></i> Stands
    </a>
</div>

<div class="card" style="max-width:720px">
    <div class="card-body">
        <h1 class="h5 fw-bold mb-4">Crear stand</h1>
        <form method="POST" action="{{ route('stands.store') }}">
            @csrf
            @include('stands._form')
            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-primary">Crear stand</button>
                <a href="{{ route('stands.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
