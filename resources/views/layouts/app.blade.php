<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Fleet Management') - Fleet Dashboard</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="{{ asset('css/purity.css') }}">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-logo">
                <!-- Fleet Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124l-.321-5.128a2.25 2.25 0 0 0-2.25-2.112h-2.25M9.75 8.25h1.5a.75.75 0 0 1 .75.75v.75H8.25V9a.75.75 0 0 1 .75-.75Zm0 0V4.5a.75.75 0 0 1 .75-.75h3.75a.75.75 0 0 1 .75.75v3.75M9 11.25V14.25m6-3v3M3.375 14.25h17.25c.621 0 1.125-.504 1.125-1.125v-1.5a1.125 1.125 0 0 0-1.125-1.125H3.375A1.125 1.125 0 0 0 2.25 12v1.5c0 .621.504 1.125 1.125 1.125Z" />
                </svg>
                <span>Fleet Management</span>
            </div>
            
            <ul class="sidebar-menu">

                {{-- DASHBOARD --}}
                @cannot('super-admin-only')
                <li class="menu-item {{ Request::is('/') || Request::is('dashboard') ? 'active' : '' }}">
                    <a href="{{ url('/') }}">
                        <div class="icon-box"><i data-lucide="layout-dashboard"></i></div>
                        <span>Dashboard</span>
                    </a>
                </li>
                @endcannot

                {{-- FLEET OPERATIONS --}}
                @cannot('super-admin-only')
                @canany(['vehicles.view', 'daily-data.view', 'maintenance.view', 'store.view'])
                <li class="sidebar-section-label">Fleet Operations</li>
                @can('vehicles.view')
                <li class="menu-item {{ Request::is('vehicles*') ? 'active' : '' }}">
                    <a href="{{ route('vehicles.index') }}">
                        <div class="icon-box"><i data-lucide="truck"></i></div>
                        <span>Vehicles</span>
                    </a>
                </li>
                @endcan
                @can('daily-data.view')
                <li class="menu-item {{ Request::is('daily-data*') ? 'active' : '' }}">
                    <a href="{{ route('daily-data.index') }}">
                        <div class="icon-box"><i data-lucide="calendar-days"></i></div>
                        <span>Daily Data</span>
                    </a>
                </li>
                @endcan
                @can('maintenance.view')
                <li class="menu-item {{ Request::is('maintenances*') ? 'active' : '' }}">
                    <a href="{{ route('maintenances.index') }}">
                        <div class="icon-box"><i data-lucide="wrench"></i></div>
                        <span>Maintenance</span>
                    </a>
                </li>
                @endcan
                @can('store.view')
                <li class="menu-item {{ Request::is('store-items*') ? 'active' : '' }}">
                    <a href="{{ route('store-items.index') }}">
                        <div class="icon-box"><i data-lucide="shopping-bag"></i></div>
                        <span>Store / Inventory</span>
                    </a>
                </li>
                @endcan
                @endcanany
                @endcannot

                {{-- FINANCIALS --}}
                @cannot('super-admin-only')
                @can('finance.view')
                <li class="sidebar-section-label">Financials</li>
                <li class="menu-item {{ Request::is('incomes*') ? 'active' : '' }}">
                    <a href="{{ route('incomes.index') }}">
                        <div class="icon-box"><i data-lucide="trending-up"></i></div>
                        <span>Income</span>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('expenses*') ? 'active' : '' }}">
                    <a href="{{ route('expenses.index') }}">
                        <div class="icon-box"><i data-lucide="credit-card"></i></div>
                        <span>Expenses</span>
                    </a>
                </li>
                @endcan
                @endcannot

                {{-- PAYROLL --}}
                @cannot('super-admin-only')
                @canany(['payroll.view', 'advances.view'])
                <li class="sidebar-section-label">Payroll</li>
                @can('payroll.view')
                <li class="menu-item {{ Request::is('driver-salaries*') ? 'active' : '' }}">
                    <a href="{{ route('driver-salaries.index') }}">
                        <div class="icon-box"><i data-lucide="dollar-sign"></i></div>
                        <span>Driver Salaries</span>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('employee-salaries*') ? 'active' : '' }}">
                    <a href="{{ route('employee-salaries.index') }}">
                        <div class="icon-box"><i data-lucide="banknote"></i></div>
                        <span>Employee Salaries</span>
                    </a>
                </li>
                @endcan
                @can('advances.view')
                <li class="menu-item {{ Request::is('pasgi-advances*') ? 'active' : '' }}">
                    <a href="{{ route('pasgi-advances.index') }}">
                        <div class="icon-box"><i data-lucide="landmark"></i></div>
                        <span>Pasgi Advances</span>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('employee-advances*') ? 'active' : '' }}">
                    <a href="{{ route('employee-advances.index') }}">
                        <div class="icon-box"><i data-lucide="hand-coins"></i></div>
                        <span>Staff Advances</span>
                    </a>
                </li>
                @endcan
                @endcanany
                @endcannot

                {{-- PEOPLE --}}
                @cannot('super-admin-only')
                @canany(['manage-drivers', 'manage-employees'])
                <li class="sidebar-section-label">People</li>
                @endcanany
                @can('manage-drivers')
                <li class="menu-item {{ Request::is('drivers*') ? 'active' : '' }}">
                    <a href="{{ route('drivers.index') }}">
                        <div class="icon-box"><i data-lucide="users"></i></div>
                        <span>Drivers</span>
                    </a>
                </li>
                @endcan
                @can('manage-employees')
                <li class="menu-item {{ Request::is('employees*') ? 'active' : '' }}">
                    <a href="{{ route('employees.index') }}">
                        <div class="icon-box"><i data-lucide="briefcase"></i></div>
                        <span>Employees</span>
                    </a>
                </li>
                @endcan
                @endcannot

                {{-- ADMINISTRATION --}}
                @canany(['manage-users', 'manage-settings'])
                {{-- REPORTS --}}
                @cannot('super-admin-only')
                @can('view-financials')
                <li class="sidebar-section-label">Reports</li>
                <li class="menu-item {{ Request::is('reports*') ? 'active' : '' }}">
                    <a href="{{ route('reports.index') }}">
                        <div class="icon-box"><i data-lucide="bar-chart-2"></i></div>
                        <span>Reports</span>
                    </a>
                </li>
                @endcan
                <li class="sidebar-section-label">Administration</li>
                @endcannot
                @endcanany
                @cannot('super-admin-only')
                @can('manage-settings')
                <li class="menu-item {{ Request::is('categories*') ? 'active' : '' }}">
                    <a href="{{ route('categories.index') }}">
                        <div class="icon-box"><i data-lucide="tag"></i></div>
                        <span>Categories</span>
                    </a>
                </li>
                @endcan
                @endcannot
                @can('super-admin-only')
                {{-- Super Admin Expandable Sub-Menu --}}
                <li class="sidebar-section-label" style="display:flex;align-items:center;gap:6px;">
                    <i data-lucide="shield-check" style="width:12px;height:12px;"></i> Super Admin
                </li>
                <li class="menu-item {{ Request::is('admin') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}">
                        <div class="icon-box"><i data-lucide="layout-dashboard"></i></div>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('admin/users*') ? 'active' : '' }}">
                    <a href="{{ route('admin.users.index') }}">
                        <div class="icon-box"><i data-lucide="users"></i></div>
                        <span>User Management</span>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('admin/roles*') ? 'active' : '' }}">
                    <a href="{{ route('admin.roles.index') }}">
                        <div class="icon-box"><i data-lucide="shield"></i></div>
                        <span>Roles & Permissions</span>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('admin/permissions*') ? 'active' : '' }}">
                    <a href="{{ route('admin.permissions.index') }}">
                        <div class="icon-box"><i data-lucide="key"></i></div>
                        <span>Permission Builder</span>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('admin/logs*') ? 'active' : '' }}">
                    <a href="{{ route('admin.logs.index') }}">
                        <div class="icon-box"><i data-lucide="file-text"></i></div>
                        <span>Activity Logs</span>
                    </a>
                </li>
                @endcan
                @cannot('super-admin-only')
                @can('manage-users')
                <li class="menu-item {{ Request::is('users*') ? 'active' : '' }}">
                    <a href="{{ route('users.index') }}">
                        <div class="icon-box"><i data-lucide="user-cog"></i></div>
                        <span>Users</span>
                    </a>
                </li>
                @endcan
                @endcannot
                @cannot('super-admin-only')
                @can('manage-settings')
                <li class="menu-item {{ Request::is('settings*') ? 'active' : '' }}">
                    <a href="{{ route('settings.index') }}">
                        <div class="icon-box"><i data-lucide="settings"></i></div>
                        <span>Settings</span>
                    </a>
                </li>
                @endcan
                @endcannot
                @cannot('super-admin-only')
                @can('manage-users')
                <li class="menu-item {{ Request::is('activity-logs*') ? 'active' : '' }}">
                    <a href="{{ route('activity-logs.index') }}">
                        <div class="icon-box"><i data-lucide="file-text"></i></div>
                        <span>Activity Logs</span>
                    </a>
                </li>
                @endcan
                @endcannot

            </ul>

            <!-- Sidebar Logout -->
            @auth
            <div class="sidebar-logout">
                <div class="sidebar-user-info">
                    <div class="sidebar-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <div class="sidebar-user-meta">
                        <span class="sidebar-user-name">{{ auth()->user()->name }}</span>
                        <span class="sidebar-user-role">{{ ucfirst(str_replace('_', ' ', auth()->user()->user_type ?? 'user')) }}</span>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="sidebar-logout-btn" title="Log Out">
                        <i data-lucide="log-out"></i>
                    </button>
                </form>
            </div>
            @endauth

        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Header / Navbar -->
            <nav class="navbar">
                <div class="navbar-breadcrumbs">
                    <div class="breadcrumbs-trail">
                        <a href="{{ url('/') }}">Pages</a>
                        <span>/</span>
                        <a href="{{ Request::url() }}">@yield('breadcrumbs', 'Dashboard')</a>
                    </div>
                    <h1 class="navbar-title">@yield('page_title', 'Dashboard')</h1>
                </div>

                <div class="navbar-actions">

                    @auth
                    {{-- Profile / Settings Dropdown --}}
                    <div class="profile-dropdown" id="profileDropdown">
                        <button class="profile-trigger" id="profileTrigger" type="button">
                            <div class="profile-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                            <span class="profile-name">{{ auth()->user()->name }}</span>
                            <i data-lucide="chevron-down" style="width: 14px; height: 14px;"></i>
                        </button>

                        <div class="profile-menu" id="profileMenu">
                            <div class="profile-menu-header">
                                <div class="profile-menu-name">{{ auth()->user()->name }}</div>
                                <div class="profile-menu-role">{{ ucwords(str_replace('_', ' ', auth()->user()->user_type ?? 'user')) }}</div>
                            </div>
                            <div class="profile-menu-divider"></div>
                            <button type="button" class="profile-menu-item" onclick="openPasswordModal()">
                                <i data-lucide="key-round" style="width: 14px; height: 14px;"></i>
                                Change Password
                            </button>
                            <div class="profile-menu-divider"></div>
                            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit" class="profile-menu-item profile-menu-logout">
                                    <i data-lucide="log-out" style="width: 14px; height: 14px;"></i>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Hidden logout form (kept for compatibility) --}}
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
                    @endauth

                    {{-- Mobile Menu Toggle --}}
                    <button class="navbar-btn navbar-menu-toggle" id="menuToggleBtn">
                        <i data-lucide="menu"></i>
                    </button>
                </div>
            </nav>

            {{-- ===== CHANGE PASSWORD MODAL ===== --}}
            @auth
            <div id="passwordModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:9999; align-items:center; justify-content:center;">
                <div style="background:var(--card-bg); border-radius:16px; border:1px solid var(--border-color); padding:32px; width:100%; max-width:420px; box-shadow:0 20px 60px rgba(0,0,0,0.3); position:relative;">
                    <button type="button" onclick="closePasswordModal()" style="position:absolute; top:16px; right:16px; background:none; border:none; cursor:pointer; color:var(--text-muted); padding:4px;">
                        <i data-lucide="x" style="width:18px; height:18px;"></i>
                    </button>

                    <div style="margin-bottom:22px;">
                        <h2 style="font-size:17px; font-weight:800; color:var(--text-color); margin:0 0 4px;">Change Password</h2>
                        <p style="font-size:12px; color:var(--text-muted); margin:0;">Update your account password below</p>
                    </div>

                    <form action="{{ route('profile.password') }}" method="POST" class="auth-form" style="max-width:100%; gap:16px;">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" name="current_password" id="current_password" class="form-input" placeholder="Enter current password" required>
                            @error('current_password') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <div style="display:flex; gap:8px; align-items:center;">
                                <input type="text" name="new_password" id="new_password" class="form-input" placeholder="Min 8 characters" required style="flex:1;">
                                <button type="button" onclick="generateModalPassword()" style="height:48px; padding:0 18px; background:rgba(79,209,197,0.1); border:1px solid rgba(79,209,197,0.3); border-radius:10px; color:var(--primary); font-weight:700; font-size:12px; cursor:pointer; white-space:nowrap; transition:all 0.2s;" onmouseover="this.style.background='var(--primary)';this.style.color='#fff';" onmouseout="this.style.background='rgba(79,209,197,0.1)';this.style.color='var(--primary)';">
                                    Generate
                                </button>
                            </div>
                            @error('new_password') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="new_password_confirmation">Confirm New Password</label>
                            <input type="text" name="new_password_confirmation" id="new_password_confirmation" class="form-input" placeholder="Repeat new password" required>
                        </div>

                        <div style="display:flex; gap:12px; margin-top:6px;">
                            <button type="submit" class="btn-signin" style="margin:0; width:auto; padding:12px 24px;">Update Password</button>
                            <button type="button" onclick="closePasswordModal()" class="btn-signin" style="margin:0; width:auto; padding:12px 24px; background:#718096; box-shadow:none;">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            @endauth

            <!-- Yield Main Page Content -->
            @yield('content')
        </main>
    </div>

    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // Responsive Sidebar Toggle
        const menuToggleBtn = document.getElementById('menuToggleBtn');
        const sidebar = document.getElementById('sidebar');

        menuToggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (event) => {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickInsideToggleBtn = menuToggleBtn.contains(event.target);
            if (!isClickInsideSidebar && !isClickInsideToggleBtn && sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
            }

            // Close profile dropdown when clicking outside
            const profileDropdown = document.getElementById('profileDropdown');
            const profileTrigger  = document.getElementById('profileTrigger');
            const profileMenu     = document.getElementById('profileMenu');
            if (profileDropdown && profileMenu && !profileDropdown.contains(event.target)) {
                profileMenu.classList.remove('open');
                profileTrigger?.classList.remove('active');
            }
        });

        // Profile dropdown toggle
        const profileTrigger = document.getElementById('profileTrigger');
        const profileMenu    = document.getElementById('profileMenu');
        if (profileTrigger && profileMenu) {
            profileTrigger.addEventListener('click', (e) => {
                e.stopPropagation();
                profileMenu.classList.toggle('open');
                profileTrigger.classList.toggle('active');
            });
        }

        // Password Modal helpers
        function openPasswordModal() {
            const modal = document.getElementById('passwordModal');
            modal.style.display = 'flex';
            if (profileMenu) { profileMenu.classList.remove('open'); profileTrigger?.classList.remove('active'); }
            lucide.createIcons(); // re-init icons inside modal
        }
        function closePasswordModal() {
            document.getElementById('passwordModal').style.display = 'none';
        }
        // Close modal on backdrop click
        document.getElementById('passwordModal')?.addEventListener('click', function(e) {
            if (e.target === this) closePasswordModal();
        });

        // Password generator for modal
        function generateModalPassword() {
            const chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789@#$!';
            let pass = '';
            for (let i = 0; i < 12; i++) pass += chars.charAt(Math.floor(Math.random() * chars.length));
            const np = document.getElementById('new_password');
            const nc = document.getElementById('new_password_confirmation');
            if (np) np.value = pass;
            if (nc) nc.value = pass;
        }

        // Global SweetAlert Toast Configuration
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // Global Delete Form Confirm Dialog
        document.addEventListener('click', function(e) {
            const deleteBtn = e.target.closest('.delete-btn');
            if (deleteBtn) {
                e.preventDefault();
                const form = deleteBtn.closest('form');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#319795',
                    cancelButtonColor: '#E53E3E',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    background: '#FFFFFF',
                    color: '#2D3748'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });
    </script>

    @if(session('success'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Toast.fire({
                icon: 'success',
                title: "{{ session('success') }}"
            });
        });
    </script>
    @endif

    @if(session('error'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Toast.fire({
                icon: 'error',
                title: "{{ session('error') }}"
            });
        });
    </script>
    @endif

    @if ($errors->has('current_password') || $errors->has('new_password'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            openPasswordModal();
        });
    </script>
    @endif

    <!-- Attachment Live Preview JS -->
    <script>
        function previewAttachment(input) {
            const container = document.getElementById('attachment-preview-container');
            if (!container) return;

            const imgWrapper = document.getElementById('attachment-preview-img-wrapper');
            const img = document.getElementById('attachment-preview-img');
            const docWrapper = document.getElementById('attachment-preview-file-icon-wrapper');
            const filenameLabel = document.getElementById('attachment-preview-filename');
            const filesizeLabel = document.getElementById('attachment-preview-filesize');

            if (input.files && input.files[0]) {
                const file = input.files[0];
                filenameLabel.textContent = file.name;
                filesizeLabel.textContent = (file.size / 1024).toFixed(1) + ' KB';
                container.style.display = 'flex';
                
                if (file.type.startsWith('image/')) {
                    img.src = URL.createObjectURL(file);
                    imgWrapper.style.display = 'block';
                    docWrapper.style.display = 'none';
                } else {
                    let extension = file.name.split('.').pop().toUpperCase();
                    docWrapper.textContent = extension;
                    docWrapper.style.display = 'flex';
                    imgWrapper.style.display = 'none';
                }
            } else {
                container.style.display = 'none';
            }
        }

        function removeSelectedAttachment() {
            const input = document.getElementById('attachment');
            if (input) {
                input.value = '';
                previewAttachment(input);
            }
        }
    </script>

    @yield('scripts')
</body>
</html>
