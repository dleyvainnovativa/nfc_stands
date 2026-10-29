@php($u = auth()->user())
<aside class="sidebar">
    <div class="brand">
        <span class="logo-mark"><i class="fa-solid fa-wifi"></i></span>
        <span>NFC Stands</span>
    </div>

    <nav class="flex-grow-1 overflow-auto">
        <div class="nav-section">General</div>
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fa-solid fa-gauge-high"></i> Panel
        </a>
        <a href="{{ route('stands.index') }}" class="nav-link {{ request()->routeIs('stands.index','stands.show','stands.create','stands.edit') ? 'active' : '' }}">
            <i class="fa-solid fa-wifi"></i> Stands
        </a>
        <a href="{{ route('surveys.index') }}" class="nav-link {{ request()->routeIs('surveys.*') ? 'active' : '' }}">
            <i class="fa-solid fa-clipboard-list"></i> Encuestas
        </a>
        <a href="{{ route('pages.index') }}" class="nav-link {{ request()->routeIs('pages.*') ? 'active' : '' }}">
            <i class="fa-solid fa-sitemap"></i> Páginas
        </a>
        <a href="{{ route('google.index') }}" class="nav-link {{ request()->routeIs('google.*') ? 'active' : '' }}">
            <i class="fa-brands fa-google"></i> Reseñas de Google
        </a>
        <a href="{{ route('brand.edit') }}" class="nav-link {{ request()->routeIs('brand.*') ? 'active' : '' }}">
            <i class="fa-solid fa-palette"></i> Marca
        </a>
        @if($u->isSuperAdmin())
            <a href="{{ route('stands.claim') }}" class="nav-link {{ request()->routeIs('stands.claim') ? 'active' : '' }}">
                <i class="fa-solid fa-plus-circle"></i> Reclamar etiqueta
            </a>
        @endif

        @if($u->isSuperAdmin())
            <div class="nav-section">Plataforma</div>
            <a href="{{ route('admin.tags.index') }}" class="nav-link {{ request()->routeIs('admin.tags.*') ? 'active' : '' }}">
                <i class="fa-solid fa-tags"></i> Etiquetas en blanco
            </a>
            <a href="{{ route('admin.tenants.index') }}" class="nav-link {{ request()->routeIs('admin.tenants.*') ? 'active' : '' }}">
                <i class="fa-solid fa-building"></i> Clientes
            </a>
        @endif
    </nav>

    <div class="p-3 border-top border-token">
        <div class="d-flex align-items-center gap-2">
            <div class="avatar">{{ $u->initials() }}</div>
            <div class="min-w-0">
                <div class="fw-semibold text-truncate" style="font-size:.9rem">{{ $u->name }}</div>
                <div class="text-subtle text-truncate" style="font-size:.78rem">{{ $u->roleLabel() }}</div>
            </div>
        </div>
    </div>
</aside>
