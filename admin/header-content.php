<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db_connection.php';

$conn->query(
    'CREATE TABLE IF NOT EXISTS news (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        news_content TEXT NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
);

$message = '';
$error = '';
$editingNews = null;

function newsValue(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $newsId = (int) ($_POST['news_id'] ?? 0);
    $content = trim($_POST['news_content'] ?? '');

    if ($action === 'delete' && $newsId > 0) {
        $query = $conn->prepare('DELETE FROM news WHERE id = ?');
        $query->bind_param('i', $newsId);
        $query->execute();
        $query->close();
        $message = 'Announcement deleted successfully.';
    }

    if ($action === 'save') {
        if ($content === '') {
            $error = 'Enter announcement text.';
        } elseif ($newsId > 0) {
            $query = $conn->prepare('UPDATE news SET news_content = ? WHERE id = ?');
            $query->bind_param('si', $content, $newsId);
            $query->execute();
            $query->close();
            $message = 'Announcement updated successfully.';
        } else {
            $query = $conn->prepare('INSERT INTO news (news_content) VALUES (?)');
            $query->bind_param('s', $content);
            $query->execute();
            $query->close();
            $message = 'Announcement added successfully.';
        }
    }
}

if (isset($_GET['edit'])) {
    $newsId = (int) $_GET['edit'];
    $query = $conn->prepare('SELECT id, news_content FROM news WHERE id = ?');
    $query->bind_param('i', $newsId);
    $query->execute();
    $editingNews = $query->get_result()->fetch_assoc();
    $query->close();
}

$announcements = [];
$result = $conn->query('SELECT id, news_content FROM news ORDER BY id ASC');
while ($row = $result->fetch_assoc()) {
    $announcements[] = $row;
}

$adminAssetPrefix = '';
$adminPageTitle = 'Header';
$adminSiteHref = '../index.php';
include __DIR__ . '/header.php';
include __DIR__ . '/sidebar.php';
?>
<style>
    .header-content-page { max-width: 1500px; }
    .announcement-form-panel { margin-bottom: 16px; }
    .announcement-form { display: grid; grid-template-columns: minmax(300px, 1fr) auto; align-items: end; gap: 16px; padding: 20px; }
    .announcement-form label { display: block; margin-bottom: 7px; color: var(--muted); font-size: 10px; letter-spacing: 1px; text-transform: uppercase; }
    .announcement-form textarea { width: 100%; min-height: 46px; resize: vertical; padding: 11px 12px; border: 1px solid var(--border); border-radius: 8px; outline: none; background: var(--surface-2); color: var(--text); font-size: 12px; }
    .announcement-form textarea:focus { border-color: var(--primary); }
    .announcement-actions { display: flex; gap: 8px; align-items: center; }
    .announcement-notice { margin-bottom: 16px; padding: 12px 15px; border: 1px solid rgba(20,164,154,.28); border-radius: 8px; background: rgba(20,164,154,.08); color: var(--primary); font-size: 12px; }
    .announcement-error { border-color: rgba(229,107,111,.28); background: rgba(229,107,111,.08); color: var(--danger); }
    .announcement-text { max-width: 850px; color: var(--muted); line-height: 1.5; }
    .table-actions form { display: inline; }
    @media (max-width: 800px) { .announcement-form { grid-template-columns: 1fr; } .announcement-actions { align-items: stretch; } }
</style>
<section class="content-area header-content-page">
    <div class="page-header">
        <div><span class="eyebrow">HOMEPAGE</span><h1>Header<span>.</span></h1><p>Manage the announcement text displayed in the website header.</p></div>
    </div>

    <?php if ($message): ?><div class="announcement-notice"><?= newsValue($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="announcement-notice announcement-error"><?= newsValue($error) ?></div><?php endif; ?>

    <section class="panel announcement-form-panel">
        <div class="panel-header"><div><h2><?= $editingNews ? 'Edit announcement' : 'Add announcement' ?></h2><p><?= $editingNews ? 'Update the selected header announcement.' : 'Add text to the homepage announcement bar.' ?></p></div></div>
        <form class="announcement-form" method="post">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="news_id" value="<?= (int) ($editingNews['id'] ?? 0) ?>">
            <div><label for="announcementText">Announcement text</label><textarea id="announcementText" name="news_content" placeholder="Write an announcement..." required><?= newsValue($editingNews['news_content'] ?? '') ?></textarea></div>
            <div class="announcement-actions"><button class="primary-button" type="submit"><i class="fa-regular fa-floppy-disk"></i><?= $editingNews ? 'Save changes' : 'Add announcement' ?></button><?php if ($editingNews): ?><a class="secondary-button" href="header-content.php">Cancel</a><?php endif; ?></div>
        </form>
    </section>

    <section class="panel">
        <div class="panel-header"><div><h2>Header announcements</h2><p><?= count($announcements) ?> announcement<?= count($announcements) === 1 ? '' : 's' ?> currently available.</p></div></div>
        <div class="table-container"><table><thead><tr><th>Sr. No.</th><th>Announcement</th><th>Actions</th></tr></thead><tbody>
            <?php if (!$announcements): ?><tr><td colspan="3" class="empty-row">No announcements added yet.</td></tr><?php endif; ?>
            <?php foreach ($announcements as $index => $announcement): ?><tr><td><?= $index + 1 ?></td><td class="announcement-text"><?= newsValue($announcement['news_content']) ?></td><td><div class="table-actions"><a href="header-content.php?edit=<?= (int) $announcement['id'] ?>" title="Edit announcement"><i class="fa-regular fa-pen-to-square"></i></a><form method="post" onsubmit="return confirm('Delete this announcement?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="news_id" value="<?= (int) $announcement['id'] ?>"><button class="delete-button" type="submit" title="Delete announcement"><i class="fa-regular fa-trash-can"></i></button></form></div></td></tr><?php endforeach; ?>
        </tbody></table></div>
    </section>
</section>
</main>
</div>
</body>
</html>
