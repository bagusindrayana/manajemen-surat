<nav id="sidebarMenu" class="sidebar d-lg-block bg-gray-800 text-white collapse" data-simplebar>
    <div class="sidebar-inner px-4 pt-3">
        <div
            class="user-card d-flex d-md-none align-items-center justify-content-between justify-content-md-center pb-4">
            <div class="d-flex align-items-center">
                <div class="avatar-lg me-4">
                    <img src="{{ Avatar::create(auth()->user()->nama)->toBase64() }}"
                        class="card-img-top rounded-circle border-white" alt="Bonnie Green">
                </div>
                <div class="d-block">
                    <h2 class="h5 mb-3">{{ auth()->user()->name }}</h2>
                    <a href="#" onclick="document.getElementById('logout-form').submit()"
                        class="btn btn-secondary btn-sm d-inline-flex align-items-center">
                        <svg class="icon icon-xxs me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                        Sign Out
                    </a>
                </div>
            </div>
            <div class="collapse-close d-md-none">
                <a href="#sidebarMenu" data-bs-toggle="collapse" data-bs-target="#sidebarMenu"
                    aria-controls="sidebarMenu" aria-expanded="true" aria-label="Toggle navigation">
                    <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd"></path>
                    </svg>
                </a>
            </div>
        </div>
        <ul class="nav flex-column pt-3 pt-md-0">
            <li class="nav-item">
                <a href="{{ url('/') }}/index.html" class="nav-link d-flex align-items-center">

                    <span class="mt-1 ms-1 sidebar-text">{{ env('APP_NAME') }}</span>
                </a>
            </li>
            <li class="nav-item ">
                <a href="{{ url('/') }}" class="nav-link">
                    <span class="sidebar-icon">
                        <i class="fas fa-house me-2 icon"></i>
                    </span>

                    <span class="sidebar-text">Dashboard</span>
                </a>
            </li>
            @canany(['View Cloud Storage', 'View Role', 'View User'])
                <li class="nav-item">
                    <span class="nav-link  d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
                        data-bs-target="#submenu-components">
                        <span>
                            <span class="sidebar-icon">
                                <svg class="icon icon-xs me-2" fill="currentColor" viewBox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z"></path>
                                    <path fill-rule="evenodd"
                                        d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </span>
                            <span class="sidebar-text">Master Data</span>
                        </span>
                        <span class="link-arrow">
                            <svg class="icon icon-sm" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </span>
                    </span>
                    <div class="multi-level collapse  {{ request()->is('local-storage*') || request()->is('cloud-storage*') || request()->is('user*') ? 'show' : '' }} "
                        role="list" id="submenu-components" aria-expanded="false">
                        <ul class="flex-column nav">
                            @can('View Cloud Storage')
                                <li class="nav-item  {{ request()->is('local-storage*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('local-storage.index') }}">
                                        <span class="sidebar-text">Local Storage</span>
                                    </a>
                                </li>
                                <li class="nav-item  {{ request()->is('cloud-storage*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('cloud-storage.index') }}">
                                        <span class="sidebar-text">Cloud Storage</span>
                                    </a>
                                </li>
                            @endcan

                            @can('View Role')
                                <li class="nav-item  {{ request()->is('role*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('role.index') }}">
                                        <span class="sidebar-text">Role</span>
                                    </a>
                                </li>
                            @endcan
                            @can('View User')
                                <li class="nav-item  {{ request()->is('user*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('user.index') }}">
                                        <span class="sidebar-text">User</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </li>
            @endcanany

            @canany(['View Surat'])
                <li class="nav-item {{ request()->is('surat*') ? 'active' : '' }}">
                    <a href="{{ route('surat.index') }}" class="nav-link ">
                        <span class="sidebar-icon">
                            <i class="fas fa-mail-bulk me-2 icon"></i>
                        </span>

                        <span class="sidebar-text">Surat</span>
                    </a>
                </li>
            @endcanany
            @if (auth()->user()->hasRole('Lurah'))
                <li class="nav-item {{ request()->is('disposisi-surat*') ? 'active' : '' }}">
                    <a href="{{ route('disposisi-surat.index') }}" class="nav-link ">
                        <span class="sidebar-icon">
                            <i class="fas fa-mail-bulk me-2 icon"></i>
                        </span>

                        <span class="sidebar-text">Disposisi Surat <small class="badge bg-danger">{{ NotificationHelper::jumlahSuratPerluDiperiksa() }}</small></span>
                    </a>
                </li>
            @endif
            <li role="separator" class="dropdown-divider mt-4 mb-3 border-gray-700"></li>
        </ul>
    </div>
</nav>
