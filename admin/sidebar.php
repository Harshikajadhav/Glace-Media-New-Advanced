<?php
$adminBasePath = $adminBasePath ?? '';
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-top">
        <div class="brand">
            <div class="brand-mark">M</div>
            <div class="brand-text"><span>MEDIA</span><small>ADMIN</small></div>
        </div>
        <button class="sidebar-close" id="sidebarClose" type="button"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <nav class="navigation">
        <div class="nav-label">OVERVIEW</div>
        <a class="nav-item<?= $adminPageTitle === 'Dashboard' ? ' active' : '' ?>" href="<?= $adminBasePath ?>index.php" data-page="dashboard"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></a>

        <div class="nav-label">HOMEPAGE</div>
        <a class="nav-item<?= $adminPageTitle === 'Header' ? ' active' : '' ?>" href="<?= $adminBasePath ?>header-content.php" data-page="header"><i class="fa-regular fa-heading"></i><span>Header</span></a>
        <a class="nav-item<?= $adminPageTitle === 'Client Icons' ? ' active' : '' ?>" href="<?= $adminBasePath ?>client-icons.php" data-page="client-icons"><i class="fa-regular fa-image"></i><span>Client Icons</span></a>
        <a class="nav-item<?= $adminPageTitle === 'Reels' ? ' active' : '' ?>" href="<?= $adminBasePath ?>reels.php" data-page="reels"><i class="fa-solid fa-film"></i><span>Reels</span></a>
        <a class="nav-item<?= $adminPageTitle === 'Work' ? ' active' : '' ?>" href="<?= $adminBasePath ?>work.php" data-page="work"><i class="fa-solid fa-layer-group"></i><span>Work Thumbnails</span></a>
        <a class="nav-item<?= $adminPageTitle === 'Did You Know' ? ' active' : '' ?>" href="<?= $adminBasePath ?>didyouknow.php" data-page="didyouknow"><i class="fa-solid fa-wand-magic-sparkles"></i><span>Did You Know</span></a>

        <div class="nav-label">ABOUT</div>
        <a class="nav-item<?= $adminPageTitle === 'About' ? ' active' : '' ?>" href="<?= $adminBasePath ?>about.php" data-page="about"><i class="fa-regular fa-address-card"></i><span>About</span></a>

        <div class="nav-label">SERVICES</div>
        <a class="nav-item<?= $adminPageTitle === 'Services' ? ' active' : '' ?>" href="<?= $adminBasePath ?>services.php" data-page="services"><i class="fa-solid fa-photo-film"></i><span>Services</span></a>

        <div class="nav-label">CONTACT US</div>
        <a class="nav-item<?= $adminPageTitle === 'Edit page' ? ' active' : '' ?>" href="<?= $adminBasePath ?>contact-page.php" data-page="contact-page"><i class="fa-regular fa-pen-to-square"></i><span>Edit page</span></a>
        <a class="nav-item<?= $adminPageTitle === 'Clients' ? ' active' : '' ?>" href="<?= $adminBasePath ?>clients.php" data-page="clients"><i class="fa-regular fa-building"></i><span>Clients</span></a>
    </nav>

    <div class="sidebar-bottom">
        <button class="sidebar-setting" type="button"><i class="fa-solid fa-gear"></i>Settings</button>
        <div class="admin-mini">
            <div class="admin-avatar">JD</div>
            <div class="admin-details"><strong>John Doe</strong><span>Administrator</span></div>
            <details class="admin-menu">
                <summary aria-label="Open account menu"><i class="fa-solid fa-ellipsis"></i></summary>
                <div class="admin-menu-popover">
                    <a href="<?= $adminBasePath ?>logout.php"><i class="fa-solid fa-right-from-bracket"></i>Logout</a>
                </div>
            </details>
        </div>
    </div>
</aside>
