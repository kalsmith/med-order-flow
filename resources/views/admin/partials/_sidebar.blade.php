<nav id="sidebarMenu" class="sidebar collapse d-md-block">
    <div class="sidebar-sticky">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.panel') ? 'active' : '' }}" href="{{ route('admin.panel') }}">
                    <i class="bi bi-grid-1x2-fill"></i> Panel Principal
                </a>
            </li>

            {{-- GESTIÓN MÉDICA Y OPERATIVA --}}
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
                    <i class="bi bi-eyedropper fs-5"></i> Catálogo
                </a>
            </li>
            @endrole

            {{-- SECCIÓN DE CONTENIDOS Y BLOG: Transversal excepto contador --}}
            @unlessrole('contable')
            <h6 class="sidebar-heading">Marketing y Contenidos</h6>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.posts.*') ? 'active' : '' }}" href="{{ route('admin.posts.index') }}">
                    <i class="bi bi-journal-richtext"></i> Blog Educativo
                </a>
            </li>
            @role('admin')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}" href="{{ route('admin.faqs.index') }}">
                    <i class="bi bi-info-circle-fill"></i> FAQ / Ayuda
                </a>
            </li>
            @endrole
            @endunlessrole

            <h6 class="sidebar-heading">Operaciones</h6>

            {{-- SOLO DOCTOR --}}
            @role('doctor')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.doctor.panel') ? 'active' : '' }}" href="{{ route('admin.doctor.panel') }}">
                    <i class="bi bi-pen-fill"></i> Panel de Firma
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.payouts.wallet') ? 'active' : '' }}" href="{{ route('admin.payouts.wallet') }}">
                    <i class="bi bi-cash-stack"></i> Mi Billetera
                </a>
            </li>
            @endrole

            {{-- SOLO DIRECTOR TÉCNICO --}}
            @role('director_tecnico')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.reports.clinical') ? 'active' : '' }}" href="{{ route('admin.reports.clinical') }}">
                    <i class="bi bi-clipboard-check-fill"></i> Calidad Clínica
                </a>
            </li>
            @endrole

            {{-- FINANZAS: Admin y Contable --}}
            @role('contable|admin')
            <h6 class="sidebar-heading">Finanzas</h6>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.reports') ? 'active' : '' }}" href="{{ route('admin.reports') }}">
                    <i class="bi bi-graph-up-arrow"></i> Reportes Negocio
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.accounting.index') ? 'active' : '' }}" href="{{ route('admin.accounting.index') }}">
                    <i class="bi bi-wallet2"></i> Contabilidad
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.payouts.index') ? 'active' : '' }}" href="{{ route('admin.payouts.index') }}">
                    <i class="bi bi-bank"></i> Pagos Médicos
                </a>
            </li>
            @endrole

            {{-- CONFIGURACIÓN / ACCESO: Solo Admin --}}
            @role('admin')
            <h6 class="sidebar-heading">Sistema</h6>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                    <i class="bi bi-person-gear"></i> Gestión de Staff
                </a>
            </li>
            @endrole
        </ul>
    </div>
</nav>
