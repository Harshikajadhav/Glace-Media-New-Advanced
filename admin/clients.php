<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db_connection.php';

$search = trim($_GET['search'] ?? '');
$searchLike = '%' . $search . '%';

$brands = [];
$influencers = [];

$brandNameColumn = $conn->query("SHOW COLUMNS FROM brands LIKE 'brand_name'");
if ($brandNameColumn instanceof mysqli_result && $brandNameColumn->num_rows === 0) {
    $conn->query("ALTER TABLE brands ADD COLUMN brand_name VARCHAR(255) NOT NULL DEFAULT '' AFTER name");
}

$brandQuery = $conn->prepare(
    'SELECT id, name, brand_name, email, phone, service, message
     FROM brands
     WHERE name LIKE ? OR brand_name LIKE ? OR email LIKE ? OR phone LIKE ? OR service LIKE ? OR message LIKE ?
     ORDER BY id DESC'
);
    $brandQuery->bind_param('ssssss', $searchLike, $searchLike, $searchLike, $searchLike, $searchLike, $searchLike);
$brandQuery->execute();
$brandResult = $brandQuery->get_result();
while ($row = $brandResult->fetch_assoc()) {
    $brands[] = $row;
}
$brandQuery->close();

$influencerQuery = $conn->prepare(
    'SELECT id, name, email, phone, genre, message
     FROM influencers
     WHERE name LIKE ? OR email LIKE ? OR phone LIKE ? OR genre LIKE ? OR message LIKE ?
     ORDER BY id DESC'
);
$influencerQuery->bind_param('sssss', $searchLike, $searchLike, $searchLike, $searchLike, $searchLike);
$influencerQuery->execute();
$influencerResult = $influencerQuery->get_result();
while ($row = $influencerResult->fetch_assoc()) {
    $influencers[] = $row;
}
$influencerQuery->close();

function clientValue(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function initials(?string $name): string
{
    $parts = preg_split('/\s+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY);
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }
    return $initials ?: '--';
}
?>
<?php
$adminAssetPrefix = '';
$adminPageTitle = 'Clients';
$adminSiteHref = '../index.php';
include __DIR__ . '/header.php';
include __DIR__ . '/sidebar.php';
?>
<style>
    .clients-page { max-width: 1500px; }
    .client-tabs { display: flex; gap: 8px; margin-bottom: 16px; }
    .client-tab { padding: 10px 16px; border: 1px solid var(--border); border-radius: 8px; background: var(--surface); color: var(--muted); cursor: pointer; font-size: 12px; transition: .2s ease; }
    .client-tab:hover, .client-tab.active { border-color: rgba(20, 164, 154, .4); background: rgba(20, 164, 154, .12); color: var(--primary); }
    .client-table-panel { display: none; }
    .client-table-panel.active { display: block; }
    .client-table { min-width: 940px; }
    .client-table th:first-child, .client-table td:first-child { width: 55px; color: var(--muted-2); }
    .client-table td { vertical-align: top; }
    .client-table .person-cell { min-width: 145px; }
    .client-table .message-cell { min-width: 220px; max-width: 330px; color: var(--muted); line-height: 1.5; white-space: normal; }
    .client-count { color: var(--muted); font-size: 10px; }
    .empty-row { padding: 35px 18px !important; color: var(--muted); text-align: center; }
    @media (max-width: 700px) { .client-tabs { overflow-x: auto; } .client-tab { white-space: nowrap; } }
</style>
<section class="content-area clients-page">
            <div class="page-header">
                <div><span class="eyebrow">CLIENTS</span><h1>Client enquiries<span>.</span></h1><p>Review brand and influencer enquiries received through the website.</p></div>
            </div>

            <form class="filter-bar" method="get">
                <div class="search-box"><i class="fa-solid fa-magnifying-glass"></i><input type="search" name="search" value="<?= clientValue($search) ?>" placeholder="Search clients..."></div>
                <button class="filter-button" type="submit"><i class="fa-solid fa-magnifying-glass"></i>Search</button>
            </form>

            <div class="client-tabs" role="tablist" aria-label="Client types">
                <button class="client-tab active" type="button" data-tab="brands">Brands <span class="client-count">(<?= count($brands) ?>)</span></button>
                <button class="client-tab" type="button" data-tab="influencers">Influencers <span class="client-count">(<?= count($influencers) ?>)</span></button>
            </div>

            <section class="panel client-table-panel active" id="brands-panel">
                <div class="panel-header"><div><h2>Brands</h2><p><?= count($brands) ?> brand enquiries</p></div></div>
                <div class="table-container">
                    <table class="client-table">
                        <thead><tr><th>Sr.</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Brand Name</th><th>Service Selected</th><th>Message</th></tr></thead>
                        <tbody>
                        <?php if (!$brands): ?><tr><td class="empty-row" colspan="7">No brand enquiries found.</td></tr><?php endif; ?>
                        <?php foreach ($brands as $index => $brand): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><div class="person-cell"><div class="person-avatar"><?= clientValue(initials($brand['name'])) ?></div><div><strong><?= clientValue($brand['name']) ?></strong></div></div></td>
                                <td><?= clientValue($brand['email']) ?></td><td><?= clientValue($brand['phone']) ?></td>
                                <td><?= clientValue($brand['brand_name']) ?></td><td><span class="service-tag"><?= clientValue($brand['service']) ?></span></td><td class="message-cell"><?= clientValue($brand['message']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel client-table-panel" id="influencers-panel">
                <div class="panel-header"><div><h2>Influencers</h2><p><?= count($influencers) ?> influencer enquiries</p></div></div>
                <div class="table-container">
                    <table class="client-table">
                        <thead><tr><th>Sr.</th><th>Full Name</th><th>Email ID</th><th>Phone</th><th>Creator / Handle</th><th>Service</th><th>Message</th></tr></thead>
                        <tbody>
                        <?php if (!$influencers): ?><tr><td class="empty-row" colspan="7">No influencer enquiries found.</td></tr><?php endif; ?>
                        <?php foreach ($influencers as $index => $influencer): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><div class="person-cell"><div class="person-avatar"><?= clientValue(initials($influencer['name'])) ?></div><div><strong><?= clientValue($influencer['name']) ?></strong></div></div></td>
                                <td><?= clientValue($influencer['email']) ?></td><td><?= clientValue($influencer['phone']) ?></td>
                                <td><?= clientValue($influencer['genre']) ?></td><td><span class="service-tag">Influencer</span></td><td class="message-cell"><?= clientValue($influencer['message']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </section>
    </main>
</div>
<script>
    document.querySelectorAll('.client-tab').forEach((tab) => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.client-tab').forEach((item) => item.classList.remove('active'));
            document.querySelectorAll('.client-table-panel').forEach((panel) => panel.classList.remove('active'));
            tab.classList.add('active');
            document.getElementById(`${tab.dataset.tab}-panel`).classList.add('active');
        });
    });
</script>
</body>
</html>

