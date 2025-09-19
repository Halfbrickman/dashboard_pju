<nav id="sidebar" class="sidebar js-sidebar">
    <div class="sidebar-content js-simplebar">
        <a class="sidebar-brand" href="index.html">
            <span class="align-middle">DASHBOARD PJU</span>
        </a>
        <ul class="sidebar-nav">
            <li class="sidebar-header">
                Pages
            </li>
            <li class="sidebar-item <?= (strpos(current_url(), '/dashboard') !== false) ? 'active' : '' ?>">
                <a class="sidebar-link" href="<?= base_url('/dashboard') ?>">
                    <i class="align-middle" data-feather="sliders"></i> <span class="align-middle">Dashboard</span>
                </a>
            </li>
            <li class="sidebar-item <?= (strpos(current_url(), '/maps') !== false) ? 'active' : '' ?>">
                <a class="sidebar-link" href="<?= base_url('/maps') ?>">
                    <i class="align-middle" data-feather="map-pin"></i> <span class="align-middle">Peta Data</span>
                </a>
            </li>

            <?php if (session()->get('isLoggedIn')): ?>
            <li class="sidebar-header">
                Master Data
            </li>
            <li class="sidebar-item <?= (strpos(current_url(), 'koordinat') !== false) ? 'active' : '' ?>">
                <a class="sidebar-link" href="<?= base_url('koordinat') ?>">
                    <i class="align-middle" data-feather="map"></i> <span class="align-middle">Data Koordinat</span>
                </a>
            </li>
            <?php if (session()->get('role_id') == 1): ?>
            <li class="sidebar-item <?= (strpos(current_url(), 'sumberdata') !== false) ? 'active' : '' ?>">
                <a class="sidebar-link" href="<?= base_url('sumberdata') ?>">
                    <i class="align-middle" data-feather="folder"></i> <span class="align-middle">Sumber Data</span>
                </a>
            </li>
            <li class="sidebar-item <?= (strpos(current_url(), 'judul-keterangan') !== false) ? 'active' : '' ?>">
                <a class="sidebar-link" href="<?= base_url('judul-keterangan') ?>">
                    <i class="align-middle" data-feather="info"></i> <span class="align-middle">Master Keterangan</span>
                </a>
            </li>
            <li class="sidebar-item <?= (strpos(current_url(), 'galeri') !== false) ? 'active' : '' ?>">
                <a class="sidebar-link" href="<?= base_url('galeri') ?>">
                    <i class="align-middle" data-feather="image"></i> <span class="align-middle">Galeri Foto</span>
                </a>
            </li>
            <?php endif; ?>
            <?php endif; ?>
        </ul>

    </div>
</nav>

<div class="main">
    <nav class="navbar navbar-expand navbar-light navbar-bg">
        <a class="sidebar-toggle js-sidebar-toggle">
            <i class="hamburger align-self-center"></i>
        </a>

        <div class="navbar-collapse collapse">
            <ul class="navbar-nav navbar-align">
                <li class="nav-item dropdown">
                    <a class="nav-icon dropdown-toggle d-inline-block d-sm-none" href="#"
                        data-bs-toggle="dropdown">
                        <i class="align-middle" data-feather="settings"></i>
                    </a>

                    <a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="#"
                        data-bs-toggle="dropdown">
                        <span class="text-dark"><?= session()->get('username'); ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="pages-profile.html"><i class="align-middle me-1"
                                data-feather="user"></i> Profile</a>
                        <a class="dropdown-item" href="#"><i class="align-middle me-1"
                                data-feather="pie-chart"></i> Analytics</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="index.html"><i class="align-middle me-1"
                                data-feather="settings"></i> Settings & Privacy</a>
                        <a class="dropdown-item" href="#"><i class="align-middle me-1"
                                data-feather="help-circle"></i> Help Center</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= base_url('logout') ?>">Log out</a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>