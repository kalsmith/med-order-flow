<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="sidebar-sticky">
        <ul class="nav flex-column">
            {{-- Dashboard General --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-grid-1x2-fill"></i> Dashboard
                </a>
            </li>

            {{-- MODULO: GESTIÓN MÉDICA (Solo Admin y Director Técnico) --}}
            @role('admin|director_tecnico')
            <h6 class="sidebar-heading">Gestión Médica</h6>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.specialties.*') ? 'active' : '' }}" href="{{ route('admin.specialties.index') }}">
                    <i class="bi bi-clipboard2-pulse-fill"></i> Especialidades
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.doctors.*') ? 'active' : '' }}" href="{{ route('admin.doctors.index') }}">
                    <i class="bi bi-person-badge-fill"></i> Doctores
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.exam-types.*') ? 'active' : '' }}" href="{{ route('admin.exam-types.index') }}">
                    <i class="bi bi-microscope-fill"></i> Catálogo Exámenes
                </a>
            </li>
            @endrole

            {{-- MODULO: OPERACIONES (Diferenciado por Rol) --}}
            <h6 class="sidebar-heading">Operaciones</h6>

            {{-- Si es Médico: Acceso directo a su panel de firma --}}
            @role('doctor')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.doctor.panel') ? 'active' : '' }}" href="{{ route('admin.doctor.panel') }}">
                    <i class="bi bi-pen-fill"></i> Panel de Firma
                </a>
            </li>
            @endrole

            {{-- Si es Admin o Director Técnico: Gestión global de órdenes --}}
            @role('admin|director_tecnico')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
                    <i class="bi bi-file-earmark-text-fill"></i> Órdenes Médicas
                </a>
            </li>
            @endrole

            {{-- MODULO: FINANZAS (Contable y Admin) --}}
            @role('contable|admin')
            <h6 class="sidebar-heading">Finanzas</h6>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.reports') ? 'active' : '' }}" href="{{ route('admin.reports') }}">
                    <i class="bi bi-graph-up-arrow"></i> Reportes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.accounting.*') ? 'active' : '' }}" href="{{ route('admin.accounting.index') }}">
                    <i class="bi bi-wallet2"></i> Contabilidad
                </a>
            </li>
            @endrole
        </ul>

        {{-- Logout móvil o acceso rápido --}}
        <div class="d-md-none p-3 border-top mt-auto">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100 btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Salir
                </button>
            </form>
        </div>
    </div>
</nav>
