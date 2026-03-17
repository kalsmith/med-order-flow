<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="sidebar-sticky">
        <ul class="nav flex-column">
            {{-- Panel Principal --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.panel') ? 'active' : '' }}" href="{{ route('admin.panel') }}">
                    <i class="bi bi-grid-1x2-fill"></i> Panel Principal
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
                    <i class="bi bi-people-fill"></i> Médicos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.exam-types.*') ? 'active' : '' }}" href="{{ route('admin.exam-types.index') }}">
                    <i class="bi bi-prescription2 fs-5"></i> Catálogo Exámenes
                </a>
            </li>

            <li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.exam-types.*') ? 'active' : '' }}" href="{{ route('admin.exam-types.index') }}">
        <i class="bi bi-flask fs-5 me-2"></i> 1. Flask (Frasco)
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="#">
        <i class="bi bi-microscope fs-5 me-2"></i> 2. Microscope (Microscopio)
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="#">
        <i class="bi bi-prescription2 fs-5 me-2"></i> 3. Prescription (Receta)
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="#">
        <i class="bi bi-clipboard2-pulse fs-5 me-2"></i> 4. Clipboard (Diagnóstico)
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="#">
        <i class="bi bi-droplet-half fs-5 me-2"></i> 5. Droplet (Gota/Muestras)
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="#">
        <i class="bi bi-eyedropper fs-5 me-2"></i> 6. Eyedropper (Pipeta)
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="#">
        <i class="bi bi-virus fs-5 me-2"></i> 7. Virus (Patología)
    </a>
</li>

            {{-- CONTENIDOS / FAQ --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}" href="{{ route('admin.faqs.index') }}">
                    <i class="bi bi-info-circle-fill"></i> Contenidos / FAQ
                </a>
            </li>
            @endrole

            {{-- MODULO: OPERACIONES --}}
            <h6 class="sidebar-heading">Operaciones</h6>

            {{-- Rol Médico --}}
            @role('doctor')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.doctor.panel') ? 'active' : '' }}" href="{{ route('admin.doctor.panel') }}">
                    <i class="bi bi-pen-fill"></i> Panel de Firma
                </a>
            </li>
            {{-- NUEVO: Billetera del Médico --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.payouts.wallet') ? 'active' : '' }}" href="{{ route('admin.payouts.wallet') }}">
                    <i class="bi bi-cash-stack"></i> Mi Billetera
                </a>
            </li>
            @endrole

            {{-- Rol Admin o DT --}}
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
                    <i class="bi bi-graph-up-arrow"></i> Reportes de Gestión
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.accounting.index') ? 'active' : '' }}" href="{{ route('admin.accounting.index') }}">
                    <i class="bi bi-wallet2"></i> Contabilidad / Pagos
                </a>
            </li>
            {{-- NUEVO: Gestión de Retiros para Admin --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.payouts.index') ? 'active' : '' }}" href="{{ route('admin.payouts.index') }}">
                    <i class="bi bi-bank"></i> Pagos a Médicos
                </a>
            </li>
            @endrole
        </ul>

        {{-- Logout móvil --}}
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
