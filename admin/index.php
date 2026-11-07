<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db_connection.php';
date_default_timezone_set('Asia/Kolkata');

function dashboardCount(mysqli $conn, string $table): int
{
    $allowedTables = ['brands', 'influencers', 'reels', 'homepage_work', 'services'];
    if (!in_array($table, $allowedTables, true)) {
        return 0;
    }

    $result = $conn->query("SELECT COUNT(*) AS total FROM `$table`");
    return $result ? (int) $result->fetch_assoc()['total'] : 0;
}

$clientEnquiryCount = dashboardCount($conn, 'brands') + dashboardCount($conn, 'influencers');
$reelCount = dashboardCount($conn, 'reels');
$workCount = dashboardCount($conn, 'homepage_work');
$serviceCount = dashboardCount($conn, 'services');
$currentDate = date('F j, Y');
$recentEnquiries = [];
$recentQuery = $conn->query(
    "SELECT name, email, COALESCE(NULLIF(brand_name, ''), 'Brand enquiry') AS company, service, id
     FROM brands
     UNION ALL
     SELECT name, email, 'Influencer enquiry' AS company, genre AS service, id
     FROM influencers
     ORDER BY id DESC
     LIMIT 3"
);
if ($recentQuery) {
    while ($enquiry = $recentQuery->fetch_assoc()) {
        $recentEnquiries[] = $enquiry;
    }
}

function dashboardInitials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY);
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }
    return $initials ?: '--';
}

$adminAssetPrefix = '';
$adminPageTitle = 'Dashboard';
$adminSiteHref = '../index.php';
include 'header.php';
include 'sidebar.php';
?>

    <!-- ================= CONTENT ================= -->

        <section class="content-area">


            <!-- =========================================
                 DASHBOARD
            ========================================== -->

            <div class="page active-page" id="page-dashboard">

                <div class="page-header">

                    <div>
                        <span class="eyebrow">OVERVIEW</span>

                        <h1>
                            Good evening, John<span>.</span>
                        </h1>

                        <p>
                            Here's what's happening across your website.
                        </p>
                    </div>

                    <div class="header-date">
                        <i class="fa-regular fa-calendar"></i>
                        <?= htmlspecialchars($currentDate, ENT_QUOTES, 'UTF-8') ?>
                    </div>

                </div>


                <!-- STATS -->

                <div class="stats-grid">

                    <div class="stat-card">

                        <div class="stat-top">
                            <div class="stat-icon">
                                <i class="fa-regular fa-building"></i>
                            </div>

                            <span class="stat-growth">
                                Live
                            </span>
                        </div>

                        <div class="stat-value">
                            <?= number_format($clientEnquiryCount) ?>
                        </div>

                        <div class="stat-label">
                            Client Enquiries
                        </div>

                    </div>


                    <div class="stat-card">

                        <div class="stat-top">
                            <div class="stat-icon">
                                <i class="fa-solid fa-film"></i>
                            </div>

                            <span class="stat-growth">
                                Live
                            </span>
                        </div>

                        <div class="stat-value">
                            <?= number_format($reelCount) ?>
                        </div>

                        <div class="stat-label">
                            Published Reels
                        </div>

                    </div>


                    <div class="stat-card">

                        <div class="stat-top">
                            <div class="stat-icon">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>

                            <span class="stat-growth">
                                Live
                            </span>
                        </div>

                        <div class="stat-value">
                            <?= number_format($workCount) ?>
                        </div>

                        <div class="stat-label">
                            Work Projects
                        </div>

                    </div>


                    <div class="stat-card">

                        <div class="stat-top">
                            <div class="stat-icon">
                                <i class="fa-regular fa-envelope"></i>
                            </div>

                            <span class="stat-new">
                                Live
                            </span>
                        </div>

                        <div class="stat-value">
                            <?= number_format($serviceCount) ?>
                        </div>

                        <div class="stat-label">
                            Services
                        </div>

                    </div>

                </div>


                <div class="dashboard-grid">


                    <!-- RECENT CONTACTS -->

                    <div class="panel large-panel">

                        <div class="panel-header">

                            <div>
                                <h2>Recent enquiries</h2>
                                <p>Latest messages from your website.</p>
                            </div>

                            <a class="text-button" href="/glace-new/admin/clients.php">
                                View all
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>

                        </div>


                        <div class="table-container">

                            <table>

                                <thead>

                                <tr>
                                    <th>Name</th>
                                    <th>Company</th>
                                    <th>Service</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>

                                </thead>

                                <tbody>

                                <?php if ($recentEnquiries): ?>
                                    <?php foreach ($recentEnquiries as $enquiry): ?>
                                        <tr>
                                            <td>
                                                <div class="person-cell">
                                                    <div class="person-avatar"><?= htmlspecialchars(dashboardInitials($enquiry['name']), ENT_QUOTES, 'UTF-8') ?></div>
                                                    <div>
                                                        <strong><?= htmlspecialchars($enquiry['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                                        <span><?= htmlspecialchars($enquiry['email'], ENT_QUOTES, 'UTF-8') ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($enquiry['company'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($enquiry['service'] ?: 'General enquiry', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>Recent</td>
                                            <td><span class="status new">New</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="empty-row">No enquiries yet.</td></tr>
                                <?php endif; ?>

                                </tbody>

                            </table>

                        </div>

                    </div>


                    <!-- QUICK ACTIONS -->

                    <div class="panel">

                        <div class="panel-header">

                            <div>
                                <h2>Quick actions</h2>
                                <p>Manage your content faster.</p>
                            </div>

                        </div>


                        <div class="quick-actions">

                            <a class="quick-action" href="/glace-new/admin/client-icons.php">
                                <div class="quick-icon">
                                    <i class="fa-regular fa-building"></i>
                                </div>

                                <div>
                                    <strong>Add Client</strong>
                                    <span>Upload a client logo</span>
                                </div>

                                <i class="fa-solid fa-arrow-right"></i>
                            </a>


                            <a class="quick-action" href="/glace-new/admin/reels.php">
                                <div class="quick-icon">
                                    <i class="fa-solid fa-film"></i>
                                </div>

                                <div>
                                    <strong>Add Reel</strong>
                                    <span>Publish a new reel</span>
                                </div>

                                <i class="fa-solid fa-arrow-right"></i>
                            </a>


                            <a class="quick-action" href="/glace-new/admin/work.php">
                                <div class="quick-icon">
                                    <i class="fa-solid fa-plus"></i>
                                </div>

                                <div>
                                    <strong>Add Work</strong>
                                    <span>Create a portfolio item</span>
                                </div>

                                <i class="fa-solid fa-arrow-right"></i>
                            </a>

                        </div>

                    </div>

                </div>


            </div>


            <!-- =========================================
                 CLIENTS
            ========================================== -->

            <div class="page" id="page-clients">

                <div class="page-header">

                    <div>
                        <span class="eyebrow">HOMEPAGE</span>

                        <h1>Clients<span>.</span></h1>

                        <p>
                            Manage the client logos displayed on your homepage.
                        </p>
                    </div>

                    <button
                        class="primary-button"
                        onclick="openModal('clientModal')"
                    >
                        <i class="fa-solid fa-plus"></i>
                        Add Client
                    </button>

                </div>


                <div class="filter-bar">

                    <div class="search-box">

                        <i class="fa-solid fa-magnifying-glass"></i>

                        <input
                            type="text"
                            placeholder="Search clients..."
                        >

                    </div>

                    <button class="filter-button">
                        <i class="fa-solid fa-filter"></i>
                        Filter
                    </button>

                </div>


                <div class="panel">

                    <div class="panel-header">

                        <div>
                            <h2>All clients</h2>
                            <p>24 clients currently displayed.</p>
                        </div>

                        <button class="icon-button">
                            <i class="fa-solid fa-arrow-down-wide-short"></i>
                        </button>

                    </div>


                    <div class="client-grid">


                        <div class="client-card">

                            <div class="client-image">
                                <div class="fake-logo">NIKE</div>
                            </div>

                            <div class="client-card-bottom">

                                <div>
                                    <strong>Nike</strong>
                                    <span>Position 01</span>
                                </div>

                                <div class="card-actions">
                                    <button onclick="openModal('clientModal')">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </button>

                                    <button class="danger-action">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </div>

                            </div>

                        </div>


                        <div class="client-card">

                            <div class="client-image">
                                <div class="fake-logo">adidas</div>
                            </div>

                            <div class="client-card-bottom">

                                <div>
                                    <strong>Adidas</strong>
                                    <span>Position 02</span>
                                </div>

                                <div class="card-actions">
                                    <button onclick="openModal('clientModal')">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </button>

                                    <button class="danger-action">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </div>

                            </div>

                        </div>


                        <div class="client-card">

                            <div class="client-image">
                                <div class="fake-logo">SONY</div>
                            </div>

                            <div class="client-card-bottom">

                                <div>
                                    <strong>Sony</strong>
                                    <span>Position 03</span>
                                </div>

                                <div class="card-actions">
                                    <button onclick="openModal('clientModal')">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </button>

                                    <button class="danger-action">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </div>

                            </div>

                        </div>


                        <div class="client-card">

                            <div class="client-image">
                                <div class="fake-logo">LEGO</div>
                            </div>

                            <div class="client-card-bottom">

                                <div>
                                    <strong>Lego</strong>
                                    <span>Position 04</span>
                                </div>

                                <div class="card-actions">
                                    <button onclick="openModal('clientModal')">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </button>

                                    <button class="danger-action">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </div>

                            </div>

                        </div>


                    </div>

                </div>

            </div>


            <!-- =========================================
                 REELS
            ========================================== -->

            <div class="page" id="page-reels">

                <div class="page-header">

                    <div>
                        <span class="eyebrow">HOMEPAGE</span>

                        <h1>Reels<span>.</span></h1>

                        <p>
                            Manage the reels displayed across your website.
                        </p>
                    </div>

                    <button
                        class="primary-button"
                        onclick="openModal('reelModal')"
                    >
                        <i class="fa-solid fa-plus"></i>
                        Add Reel
                    </button>

                </div>


                <div class="reel-grid">


                    <div class="reel-card">

                        <div class="reel-thumbnail">

                            <div class="play-button">
                                <i class="fa-solid fa-play"></i>
                            </div>

                            <span class="reel-status">
                                Published
                            </span>

                        </div>

                        <div class="reel-info">

                            <div>
                                <strong>Summer Campaign</strong>
                                <span>Position 01</span>
                            </div>

                            <button class="more-button">
                                <i class="fa-solid fa-ellipsis"></i>
                            </button>

                        </div>

                    </div>


                    <div class="reel-card">

                        <div class="reel-thumbnail reel-two">

                            <div class="play-button">
                                <i class="fa-solid fa-play"></i>
                            </div>

                            <span class="reel-status">
                                Published
                            </span>

                        </div>

                        <div class="reel-info">

                            <div>
                                <strong>Brand Film</strong>
                                <span>Position 02</span>
                            </div>

                            <button class="more-button">
                                <i class="fa-solid fa-ellipsis"></i>
                            </button>

                        </div>

                    </div>


                    <div class="reel-card">

                        <div class="reel-thumbnail reel-three">

                            <div class="play-button">
                                <i class="fa-solid fa-play"></i>
                            </div>

                            <span class="reel-status draft">
                                Draft
                            </span>

                        </div>

                        <div class="reel-info">

                            <div>
                                <strong>Product Shoot</strong>
                                <span>Position 03</span>
                            </div>

                            <button class="more-button">
                                <i class="fa-solid fa-ellipsis"></i>
                            </button>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =========================================
                 HOMEPAGE CONTENT
            ========================================== -->

            <div class="page" id="page-content">

                <div class="page-header">

                    <div>
                        <span class="eyebrow">HOMEPAGE</span>

                        <h1>Content<span>.</span></h1>

                        <p>
                            Edit the text and calls-to-action on your homepage.
                        </p>
                    </div>

                    <button class="primary-button">
                        <i class="fa-regular fa-floppy-disk"></i>
                        Save Changes
                    </button>

                </div>


                <div class="editor-layout">

                    <div class="panel editor-panel">

                        <div class="section-heading">
                            <span class="section-number">01</span>

                            <div>
                                <h2>Hero section</h2>
                                <p>The first thing visitors see.</p>
                            </div>
                        </div>


                        <div class="form-group">

                            <label>Headline</label>

                            <input
                                class="input"
                                value="We create brands that people remember."
                            >

                        </div>


                        <div class="form-group">

                            <label>Supporting text</label>

                            <textarea
                                class="textarea"
                                rows="4"
                            >A creative media agency built for brands that want to stand out, connect deeply and grow fearlessly.</textarea>

                        </div>


                        <div class="form-row">

                            <div class="form-group">

                                <label>Primary CTA</label>

                                <input
                                    class="input"
                                    value="Let's Work Together"
                                >

                            </div>


                            <div class="form-group">

                                <label>CTA Link</label>

                                <input
                                    class="input"
                                    value="/contact"
                                >

                            </div>

                        </div>

                    </div>


                    <div class="panel editor-preview">

                        <div class="panel-header">

                            <div>
                                <h2>Preview</h2>
                                <p>Homepage hero preview.</p>
                            </div>

                            <span class="preview-label">
                                LIVE PREVIEW
                            </span>

                        </div>


                        <div class="hero-preview">

                            <span>CREATIVE MEDIA AGENCY</span>

                            <h3>
                                We create brands
                                <em>that people remember.</em>
                            </h3>

                            <p>
                                A creative media agency built for brands
                                that want to stand out.
                            </p>

                            <button>
                                Let's Work Together
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </button>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =========================================
                 OUR WORK
            ========================================== -->

            <div class="page" id="page-work">

                <div class="page-header">

                    <div>
                        <span class="eyebrow">HOMEPAGE</span>

                        <h1>Our Work<span>.</span></h1>

                        <p>
                            Manage your portfolio and featured projects.
                        </p>
                    </div>

                    <button
                        class="primary-button"
                        onclick="openModal('workModal')"
                    >
                        <i class="fa-solid fa-plus"></i>
                        Add Work
                    </button>

                </div>


                <div class="work-grid">


                    <div class="work-card">

                        <div class="work-image">
                            <span>01</span>
                        </div>

                        <div class="work-card-info">

                            <div>
                                <span>BRANDING</span>
                                <h3>Future Forward</h3>
                            </div>

                            <button onclick="openModal('workModal')">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>

                        </div>

                    </div>


                    <div class="work-card">

                        <div class="work-image work-image-two">
                            <span>02</span>
                        </div>

                        <div class="work-card-info">

                            <div>
                                <span>CAMPAIGN</span>
                                <h3>Made For More</h3>
                            </div>

                            <button onclick="openModal('workModal')">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>

                        </div>

                    </div>


                    <div class="work-card">

                        <div class="work-image work-image-three">
                            <span>03</span>
                        </div>

                        <div class="work-card-info">

                            <div>
                                <span>PRODUCTION</span>
                                <h3>After Hours</h3>
                            </div>

                            <button onclick="openModal('workModal')">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =========================================
                 DID YOU KNOW
            ========================================== -->

            <div class="page" id="page-didyouknow">

                <div class="page-header">

                    <div>
                        <span class="eyebrow">HOMEPAGE</span>

                        <h1>Did You Know<span>?</span></h1>

                        <p>
                            Manage the two lines of your infinite scrolling section.
                        </p>
                    </div>

                    <button class="primary-button">
                        <i class="fa-regular fa-floppy-disk"></i>
                        Save Changes
                    </button>

                </div>


                <div class="panel scroller-editor">

                    <div class="scroller-line">

                        <div class="line-header">

                            <div class="line-number">
                                01
                            </div>

                            <div>
                                <h2>First scrolling line</h2>
                                <p>
                                    This line moves from left to right.
                                </p>
                            </div>

                        </div>


                        <textarea class="large-textarea">WE TURN IDEAS INTO CULTURE</textarea>

                        <div class="character-count">
                            29 characters
                        </div>

                    </div>


                    <div class="divider"></div>


                    <div class="scroller-line">

                        <div class="line-header">

                            <div class="line-number">
                                02
                            </div>

                            <div>
                                <h2>Second scrolling line</h2>
                                <p>
                                    This line moves from right to left.
                                </p>
                            </div>

                        </div>


                        <textarea class="large-textarea">STRATEGY Ã— CREATIVITY Ã— CULTURE</textarea>

                        <div class="character-count">
                            31 characters
                        </div>

                    </div>

                </div>


                <div class="panel scroller-preview">

                    <div class="panel-header">

                        <div>
                            <h2>Live preview</h2>
                            <p>This is how the infinite scroller appears.</p>
                        </div>

                    </div>


                    <div class="infinite-preview">

                        <div class="preview-line">
                            WE TURN IDEAS INTO CULTURE
                            <span>âœ¦</span>
                            WE TURN IDEAS INTO CULTURE
                            <span>âœ¦</span>
                        </div>

                        <div class="preview-line reverse">
                            STRATEGY Ã— CREATIVITY Ã— CULTURE
                            <span>âœ¦</span>
                            STRATEGY Ã— CREATIVITY Ã— CULTURE
                            <span>âœ¦</span>
                        </div>

                    </div>

                </div>

            </div>


            <!-- =========================================
                 CONTACTS
            ========================================== -->

            <div class="page" id="page-contacts">

                <div class="page-header">

                    <div>
                        <span class="eyebrow">ENQUIRIES</span>

                        <h1>Contact Submissions<span>.</span></h1>

                        <p>
                            View and manage messages received from your website.
                        </p>
                    </div>

                    <div class="contact-count">
                        <strong>47</strong>
                        <span>Total submissions</span>
                    </div>

                </div>


                <div class="filter-bar">

                    <div class="search-box">

                        <i class="fa-solid fa-magnifying-glass"></i>

                        <input
                            type="text"
                            placeholder="Search by name, email or company..."
                        >

                    </div>


                    <select class="select-filter">

                        <option>All Status</option>
                        <option>New</option>
                        <option>Read</option>
                        <option>Contacted</option>

                    </select>


                    <select class="select-filter">

                        <option>All Services</option>
                        <option>Branding</option>
                        <option>Social Media</option>
                        <option>Production</option>
                        <option>Web Design</option>

                    </select>

                </div>


                <div class="panel">

                    <div class="table-container contact-table">

                        <table>

                            <thead>

                            <tr>
                                <th>CONTACT</th>
                                <th>COMPANY</th>
                                <th>SERVICE</th>
                                <th>MESSAGE</th>
                                <th>DATE</th>
                                <th>STATUS</th>
                                <th>ACTIONS</th>
                            </tr>

                            </thead>


                            <tbody>


                            <tr>

                                <td>

                                    <div class="person-cell">

                                        <div class="person-avatar">
                                            RS
                                        </div>

                                        <div>
                                            <strong>Rahul Sharma</strong>
                                            <span>rahul@email.com</span>
                                        </div>

                                    </div>

                                </td>

                                <td>ABC Studios</td>

                                <td>
                                    <span class="service-tag">
                                        Social Media
                                    </span>
                                </td>

                                <td>
                                    <span class="message-preview">
                                        Looking for a long-term social...
                                    </span>
                                </td>

                                <td>14 Sep 2026</td>

                                <td>
                                    <span class="status new">
                                        New
                                    </span>
                                </td>

                                <td>

                                    <div class="table-actions">

                                        <button
                                            title="View"
                                            onclick="openModal('contactModal')"
                                        >
                                            <i class="fa-regular fa-eye"></i>
                                        </button>

                                        <button
                                            title="Edit"
                                            onclick="openModal('contactModal')"
                                        >
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>

                                        <button
                                            class="delete-button"
                                            title="Delete"
                                        >
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>

                                    </div>

                                </td>

                            </tr>


                            <tr>

                                <td>

                                    <div class="person-cell">

                                        <div class="person-avatar">
                                            SK
                                        </div>

                                        <div>
                                            <strong>Sarah Khan</strong>
                                            <span>sarah@email.com</span>
                                        </div>

                                    </div>

                                </td>

                                <td>Studio 21</td>

                                <td>
                                    <span class="service-tag">
                                        Branding
                                    </span>
                                </td>

                                <td>
                                    <span class="message-preview">
                                        We'd love to discuss our...
                                    </span>
                                </td>

                                <td>13 Sep 2026</td>

                                <td>
                                    <span class="status read">
                                        Read
                                    </span>
                                </td>

                                <td>

                                    <div class="table-actions">

                                        <button onclick="openModal('contactModal')">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>

                                        <button onclick="openModal('contactModal')">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>

                                        <button class="delete-button">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>

                                    </div>

                                </td>

                            </tr>


                            <tr>

                                <td>

                                    <div class="person-cell">

                                        <div class="person-avatar">
                                            AM
                                        </div>

                                        <div>
                                            <strong>Arjun Mehta</strong>
                                            <span>arjun@email.com</span>
                                        </div>

                                    </div>

                                </td>

                                <td>Orbit</td>

                                <td>
                                    <span class="service-tag">
                                        Production
                                    </span>
                                </td>

                                <td>
                                    <span class="message-preview">
                                        Need a production partner for...
                                    </span>
                                </td>

                                <td>12 Sep 2026</td>

                                <td>
                                    <span class="status contacted">
                                        Contacted
                                    </span>
                                </td>

                                <td>

                                    <div class="table-actions">

                                        <button onclick="openModal('contactModal')">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>

                                        <button onclick="openModal('contactModal')">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>

                                        <button class="delete-button">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>

                                    </div>

                                </td>

                            </tr>


                            </tbody>

                        </table>

                    </div>


                    <div class="pagination">

                        <span>
                            Showing 1â€“10 of 47
                        </span>

                        <div>

                            <button disabled>
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>

                            <button class="active">1</button>
                            <button>2</button>
                            <button>3</button>
                            <button>4</button>
                            <button>5</button>

                            <button>
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =========================================
                 ABOUT
            ========================================== -->

            <div class="page" id="page-about">

                <div class="page-header">

                    <div>
                        <span class="eyebrow">PAGES</span>

                        <h1>About<span>.</span></h1>

                        <p>
                            Keep your About page simple and easy to update.
                        </p>
                    </div>

                    <button class="primary-button">
                        <i class="fa-regular fa-floppy-disk"></i>
                        Save Changes
                    </button>

                </div>


                <div class="panel">

                    <div class="form-group">

                        <label>Page Heading</label>

                        <input
                            class="input"
                            value="We are a creative force."
                        >

                    </div>


                    <div class="form-group">

                        <label>About Description</label>

                        <textarea
                            class="textarea"
                            rows="12"
                        >We are an independent media agency creating meaningful work for ambitious brands.

Our approach combines strategy, creativity and culture to create work that people remember.</textarea>

                    </div>

                </div>

            </div>


            <!-- =========================================
                 SERVICES
            ========================================== -->

            <div class="page" id="page-services">

                <div class="page-header">

                    <div>
                        <span class="eyebrow">PAGES</span>

                        <h1>Services<span>.</span></h1>

                        <p>
                            Update the imagery associated with each service.
                        </p>
                    </div>

                </div>


                <div class="services-list">


                    <div class="service-card">

                        <div class="service-preview service-one">

                            <span>01</span>

                        </div>

                        <div class="service-details">

                            <span class="service-index">
                                01
                            </span>

                            <div>

                                <h2>Brand Strategy</h2>

                                <p>
                                    Strategy, identity and positioning.
                                </p>

                            </div>

                        </div>

                        <button
                            class="secondary-button"
                            onclick="openModal('serviceModal')"
                        >
                            <i class="fa-regular fa-image"></i>
                            Edit Image
                        </button>

                    </div>


                    <div class="service-card">

                        <div class="service-preview service-two">

                            <span>02</span>

                        </div>

                        <div class="service-details">

                            <span class="service-index">
                                02
                            </span>

                            <div>

                                <h2>Social Media</h2>

                                <p>
                                    Social strategy, content and campaigns.
                                </p>

                            </div>

                        </div>

                        <button
                            class="secondary-button"
                            onclick="openModal('serviceModal')"
                        >
                            <i class="fa-regular fa-image"></i>
                            Edit Image
                        </button>

                    </div>


                    <div class="service-card">

                        <div class="service-preview service-three">

                            <span>03</span>

                        </div>

                        <div class="service-details">

                            <span class="service-index">
                                03
                            </span>

                            <div>

                                <h2>Production</h2>

                                <p>
                                    Film, photography and creative production.
                                </p>

                            </div>

                        </div>

                        <button
                            class="secondary-button"
                            onclick="openModal('serviceModal')"
                        >
                            <i class="fa-regular fa-image"></i>
                            Edit Image
                        </button>

                    </div>


                </div>

            </div>


        </section>

    </main>

</div>


<!-- ==================================================
     CLIENT MODAL
=================================================== -->

<div class="modal-overlay" id="clientModal">

    <div class="modal">

        <div class="modal-header">

            <div>
                <span class="eyebrow">CLIENT</span>
                <h2>Add Client</h2>
            </div>

            <button
                class="modal-close"
                onclick="closeModal('clientModal')"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>

        </div>


        <div class="modal-body">

            <div class="form-group">

                <label>Client Name</label>

                <input
                    class="input"
                    placeholder="e.g. Nike"
                >

            </div>


            <div class="form-group">

                <label>Client Logo</label>

                <div class="upload-area">

                    <i class="fa-solid fa-cloud-arrow-up"></i>

                    <strong>
                        Upload client logo
                    </strong>

                    <span>
                        PNG, JPG, SVG up to 5MB
                    </span>

                    <button class="upload-button">
                        Browse Files
                    </button>

                </div>

            </div>


            <div class="form-group">

                <label>Display Order</label>

                <input
                    class="input"
                    type="number"
                    value="1"
                >

            </div>


            <div class="toggle-row">

                <div>
                    <strong>Published</strong>
                    <span>Show this client on the website.</span>
                </div>

                <label class="switch">

                    <input type="checkbox" checked>

                    <span></span>

                </label>

            </div>

        </div>


        <div class="modal-footer">

            <button
                class="secondary-button"
                onclick="closeModal('clientModal')"
            >
                Cancel
            </button>

            <button
                class="primary-button"
                onclick="saveAndClose('clientModal')"
            >
                Save Client
            </button>

        </div>

    </div>

</div>


<!-- ==================================================
     REEL MODAL
=================================================== -->

<div class="modal-overlay" id="reelModal">

    <div class="modal">

        <div class="modal-header">

            <div>
                <span class="eyebrow">HOMEPAGE</span>
                <h2>Add Reel</h2>
            </div>

            <button
                class="modal-close"
                onclick="closeModal('reelModal')"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>

        </div>


        <div class="modal-body">

            <div class="form-group">

                <label>Reel Title</label>

                <input
                    class="input"
                    placeholder="e.g. Summer Campaign"
                >

            </div>


            <div class="form-group">

                <label>Reel URL</label>

                <input
                    class="input"
                    placeholder="https://instagram.com/reel/..."
                >

                <small class="field-help">
                    Use an external reel URL or replace this with your
                    storage upload when connecting the backend.
                </small>

            </div>


            <div class="form-group">

                <label>Thumbnail</label>

                <div class="upload-area small">

                    <i class="fa-regular fa-image"></i>

                    <strong>
                        Upload thumbnail
                    </strong>

                    <span>
                        Recommended 1080 Ã— 1920
                    </span>

                    <button class="upload-button">
                        Browse Files
                    </button>

                </div>

            </div>


            <div class="form-row">

                <div class="form-group">

                    <label>Position</label>

                    <input
                        class="input"
                        type="number"
                        value="1"
                    >

                </div>


                <div class="form-group">

                    <label>Status</label>

                    <select class="input">

                        <option>Published</option>
                        <option>Draft</option>

                    </select>

                </div>

            </div>

        </div>


        <div class="modal-footer">

            <button
                class="secondary-button"
                onclick="closeModal('reelModal')"
            >
                Cancel
            </button>

            <button
                class="primary-button"
                onclick="saveAndClose('reelModal')"
            >
                Add Reel
            </button>

        </div>

    </div>

</div>


<!-- ==================================================
     WORK MODAL
=================================================== -->

<div class="modal-overlay" id="workModal">

    <div class="modal modal-large">

        <div class="modal-header">

            <div>
                <span class="eyebrow">PORTFOLIO</span>
                <h2>Add Work</h2>
            </div>

            <button
                class="modal-close"
                onclick="closeModal('workModal')"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>

        </div>


        <div class="modal-body">

            <div class="form-row">

                <div class="form-group">

                    <label>Project Name</label>

                    <input
                        class="input"
                        placeholder="e.g. Future Forward"
                    >

                </div>


                <div class="form-group">

                    <label>Category</label>

                    <select class="input">

                        <option>Branding</option>
                        <option>Campaign</option>
                        <option>Production</option>
                        <option>Social Media</option>

                    </select>

                </div>

            </div>


            <div class="form-group">

                <label>Project Image</label>

                <div class="upload-area">

                    <i class="fa-regular fa-image"></i>

                    <strong>
                        Upload project image
                    </strong>

                    <span>
                        Recommended 1600 Ã— 1200
                    </span>

                    <button class="upload-button">
                        Browse Files
                    </button>

                </div>

            </div>


            <div class="form-group">

                <label>Description</label>

                <textarea
                    class="textarea"
                    rows="5"
                    placeholder="Tell the story behind this project..."
                ></textarea>

            </div>


            <div class="form-row">

                <div class="form-group">

                    <label>Display Order</label>

                    <input
                        class="input"
                        type="number"
                        value="1"
                    >

                </div>


                <div class="form-group">

                    <label>Project URL</label>

                    <input
                        class="input"
                        placeholder="https://..."
                    >

                </div>

            </div>

        </div>


        <div class="modal-footer">

            <button
                class="secondary-button"
                onclick="closeModal('workModal')"
            >
                Cancel
            </button>

            <button
                class="primary-button"
                onclick="saveAndClose('workModal')"
            >
                Save Work
            </button>

        </div>

    </div>

</div>


<!-- ==================================================
     CONTACT MODAL
=================================================== -->

<div class="modal-overlay" id="contactModal">

    <div class="modal modal-large">

        <div class="modal-header">

            <div>
                <span class="eyebrow">CONTACT ENQUIRY</span>
                <h2>Contact Details</h2>
            </div>

            <button
                class="modal-close"
                onclick="closeModal('contactModal')"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>

        </div>


        <div class="modal-body">

            <div class="contact-detail-head">

                <div class="person-avatar large">
                    RS
                </div>

                <div>

                    <h3>Rahul Sharma</h3>

                    <span>
                        Received September 14, 2026
                    </span>

                </div>

                <span class="status new">
                    New
                </span>

            </div>


            <div class="detail-grid">

                <div class="detail-item">

                    <label>Email</label>

                    <strong>
                        rahul@email.com
                    </strong>

                </div>


                <div class="detail-item">

                    <label>Phone</label>

                    <strong>
                        +91 98765 43210
                    </strong>

                </div>


                <div class="detail-item">

                    <label>Company</label>

                    <strong>
                        ABC Studios
                    </strong>

                </div>


                <div class="detail-item">

                    <label>Service</label>

                    <strong>
                        Social Media
                    </strong>

                </div>

            </div>


            <div class="message-box">

                <label>Message</label>

                <p>
                    Hi, we're looking for a creative partner to help
                    us manage our social media presence and create
                    monthly campaign content. We'd love to discuss
                    the possibility of working together.
                </p>

            </div>

        </div>


        <div class="modal-footer">

            <button
                class="danger-button"
                onclick="closeModal('contactModal')"
            >
                <i class="fa-regular fa-trash-can"></i>
                Delete
            </button>

            <div>

                <button
                    class="secondary-button"
                    onclick="closeModal('contactModal')"
                >
                    Close
                </button>

                <button class="primary-button">
                    Mark as Contacted
                </button>

            </div>

        </div>

    </div>

</div>


<!-- ==================================================
     SERVICE MODAL
=================================================== -->

<div class="modal-overlay" id="serviceModal">

    <div class="modal">

        <div class="modal-header">

            <div>
                <span class="eyebrow">SERVICES</span>
                <h2>Edit Service Image</h2>
            </div>

            <button
                class="modal-close"
                onclick="closeModal('serviceModal')"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>

        </div>


        <div class="modal-body">

            <div class="form-group">

                <label>Service</label>

                <input
                    class="input"
                    value="Brand Strategy"
                    readonly
                >

            </div>


            <div class="form-group">

                <label>Current Image</label>

                <div class="current-image">

                    <span>
                        BRAND STRATEGY
                    </span>

                </div>

            </div>


            <div class="form-group">

                <label>Replace Image</label>

                <div class="upload-area">

                    <i class="fa-solid fa-cloud-arrow-up"></i>

                    <strong>
                        Upload new image
                    </strong>

                    <span>
                        JPG, PNG or WebP up to 10MB
                    </span>

                    <button class="upload-button">
                        Browse Files
                    </button>

                </div>

            </div>

        </div>


        <div class="modal-footer">

            <button
                class="secondary-button"
                onclick="closeModal('serviceModal')"
            >
                Cancel
            </button>

            <button
                class="primary-button"
                onclick="saveAndClose('serviceModal')"
            >
                Update Image
            </button>

        </div>

    </div>

</div>


<!-- TOAST -->

<div class="toast" id="toast">

    <div class="toast-icon">
        <i class="fa-solid fa-check"></i>
    </div>

    <div>
        <strong>Changes saved</strong>
        <span>Your content has been updated.</span>
    </div>

</div>


<script src="app.js"></script>

</body>
</html>

