@php($u = auth()->user())
@php($acting = \App\Support\CurrentTenant::isActingAs())
<header class="topbar">
    <button class="icon-btn" id="sidebarToggle" type="button" aria-label="Abrir menú">
        <i class="fa-solid fa-bars"></i>
    </button>

    <div class="page-title d-none d-md-block">@yield('page-title', 'Dashboard')</div>

    <div class="spacer"></div>

    @if($u->isSuperAdmin() && $acting)
        @php($actingTenant = \App\Support\CurrentTenant::model())
        <span class="badge-role acting-badge" style="background:var(--warning-soft);color:var(--warning)">
            <i class="fa-solid fa-eye"></i>
            <span class="acting-name">{{ $actingTenant?->name }}</span>
        </span>
        <form method="POST" action="{{ route('tenant.exit') }}" class="d-inline">
            @csrf
            <button class="btn btn-sm btn-outline-secondary" type="submit">Salir</button>
        </form>
    @endif

    <button class="icon-btn" data-theme-toggle type="button" aria-label="Cambiar tema" title="Cambiar claro/oscuro">
        <i class="fa-solid fa-circle-half-stroke"></i>
    </button>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="btn btn-sm btn-outline-secondary" type="submit" title="Cerrar sesión">
            <i class="fa-solid fa-arrow-right-from-bracket"></i><span class="d-none d-sm-inline ms-1">Cerrar sesión</span>
        </button>
    </form>
</header>
