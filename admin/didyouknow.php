<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db_connection.php';

$conn->query(
    'CREATE TABLE IF NOT EXISTS did_you_know (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        scroller TINYINT UNSIGNED NOT NULL,
        text_content TEXT NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        INDEX (scroller)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
);

$seedCountResult = $conn->query('SELECT COUNT(*) AS total FROM did_you_know');
$seedCount = (int) $seedCountResult->fetch_assoc()['total'];
if ($seedCount === 0) {
    $seedItems = [
        [1, "Instagram's original filters were named after alcoholic drinks."],
        [1, 'The first influencer was likely Cindy Margolis.'],
        [1, 'Stories transformed disappearing content into a daily habit.'],
        [2, 'The internet transformed brands into communities.'],
        [2, 'Great typography can change the personality of a website.'],
        [2, 'Motion gives interfaces a feeling of continuity.'],
        [2, 'Simple design often creates the strongest impression.'],
    ];
    $seedQuery = $conn->prepare('INSERT INTO did_you_know (scroller, text_content) VALUES (?, ?)');
    foreach ($seedItems as [$scroller, $text]) {
        $seedQuery->bind_param('is', $scroller, $text);
        $seedQuery->execute();
    }
    $seedQuery->close();
}

$message = '';
$error = '';
$editingFact = null;

function factValue(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $factId = (int) ($_POST['fact_id'] ?? 0);

    if ($action === 'delete' && $factId > 0) {
        $query = $conn->prepare('DELETE FROM did_you_know WHERE id = ?');
        $query->bind_param('i', $factId);
        $query->execute();
        $query->close();
        $message = 'Fact deleted successfully.';
    }

    if ($action === 'save') {
        $scroller = (int) ($_POST['scroller'] ?? 1);
        $text = trim($_POST['text_content'] ?? '');

        if (!in_array($scroller, [1, 2], true)) {
            $error = 'Choose a valid scroller.';
        } elseif ($text === '') {
            $error = 'Enter text for the scroller.';
        } elseif ($factId > 0) {
            $query = $conn->prepare('UPDATE did_you_know SET scroller = ?, text_content = ? WHERE id = ?');
            $query->bind_param('isi', $scroller, $text, $factId);
            $query->execute();
            $query->close();
            $message = 'Fact updated successfully.';
        } else {
            $query = $conn->prepare('INSERT INTO did_you_know (scroller, text_content) VALUES (?, ?)');
            $query->bind_param('is', $scroller, $text);
            $query->execute();
            $query->close();
            $message = 'Fact added successfully.';
        }
    }
}

if (isset($_GET['edit'])) {
    $factId = (int) $_GET['edit'];
    $query = $conn->prepare('SELECT id, scroller, text_content FROM did_you_know WHERE id = ?');
    $query->bind_param('i', $factId);
    $query->execute();
    $editingFact = $query->get_result()->fetch_assoc();
    $query->close();
}

$scrollerOne = [];
$scrollerTwo = [];
$result = $conn->query('SELECT id, scroller, text_content FROM did_you_know ORDER BY scroller ASC, id ASC');
while ($row = $result->fetch_assoc()) {
    if ((int) $row['scroller'] === 1) {
        $scrollerOne[] = $row;
    } else {
        $scrollerTwo[] = $row;
    }
}

$adminAssetPrefix = '';
$adminPageTitle = 'Did You Know';
$adminSiteHref = '../index.php';
include __DIR__ . '/header.php';
include __DIR__ . '/sidebar.php';
?>
<style>
    .facts-page { max-width: 1500px; }
    .fact-form-panel { margin-bottom: 16px; }
    .fact-form { display: grid; grid-template-columns: 220px minmax(300px, 1fr) auto; align-items: end; gap: 16px; padding: 20px; }
    .fact-form label { display: block; margin-bottom: 7px; color: var(--muted); font-size: 10px; letter-spacing: 1px; text-transform: uppercase; }
    .fact-form select, .fact-form textarea { width: 100%; padding: 11px 12px; border: 1px solid var(--border); border-radius: 8px; outline: none; background: var(--surface-2); color: var(--text); font-size: 12px; }
    .fact-form textarea { min-height: 42px; resize: vertical; }
    .fact-form select:focus, .fact-form textarea:focus { border-color: var(--primary); }
    .fact-actions { display: flex; align-items: center; gap: 8px; }
    .fact-notice { margin-bottom: 16px; padding: 12px 15px; border: 1px solid rgba(20,164,154,.28); border-radius: 8px; background: rgba(20,164,154,.08); color: var(--primary); font-size: 12px; }
    .fact-error { border-color: rgba(229,107,111,.28); background: rgba(229,107,111,.08); color: var(--danger); }
    .fact-table { min-width: 620px; }
    .fact-table td { vertical-align: top; }
    .fact-text { max-width: 760px; color: var(--muted); line-height: 1.5; white-space: normal; }
    .empty-row { padding: 35px 18px !important; color: var(--muted); text-align: center; }
    @media (max-width: 800px) { .fact-form { grid-template-columns: 1fr; } }
</style>
<section class="content-area facts-page">
    <div class="page-header">
        <div><span class="eyebrow">HOMEPAGE</span><h1>Did You Know<span>?</span></h1><p>Manage the text shown in both infinite scrollers on the homepage.</p></div>
    </div>

    <?php if ($message): ?><div class="fact-notice"><?= factValue($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="fact-notice fact-error"><?= factValue($error) ?></div><?php endif; ?>

    <section class="panel fact-form-panel">
        <div class="panel-header"><div><h2><?= $editingFact ? 'Edit fact' : 'Add fact' ?></h2><p><?= $editingFact ? 'Update the selected scroller text.' : 'Add text to either homepage infinite scroller.' ?></p></div></div>
        <form class="fact-form" method="post">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="fact_id" value="<?= (int) ($editingFact['id'] ?? 0) ?>">
            <div><label for="scroller">Scroller</label><select id="scroller" name="scroller"><option value="1" <?= ((int) ($editingFact['scroller'] ?? 1) === 1) ? 'selected' : '' ?>>Top infinite scroller</option><option value="2" <?= ((int) ($editingFact['scroller'] ?? 1) === 2) ? 'selected' : '' ?>>Second infinite scroller</option></select></div>
            <div><label for="factText">Text</label><textarea id="factText" name="text_content" placeholder="Write the scroller text..." required><?= factValue($editingFact['text_content'] ?? '') ?></textarea></div>
            <div class="fact-actions"><button class="primary-button" type="submit"><i class="fa-regular fa-floppy-disk"></i><?= $editingFact ? 'Save changes' : 'Add text' ?></button><?php if ($editingFact): ?><a class="secondary-button" href="didyouknow.php">Cancel</a><?php endif; ?></div>
        </form>
    </section>

    <section class="panel" style="margin-bottom: 16px;">
        <div class="panel-header"><div><h2>Top infinite scroller</h2><p><?= count($scrollerOne) ?> text item<?= count($scrollerOne) === 1 ? '' : 's' ?></p></div></div>
        <div class="table-container"><table class="fact-table"><thead><tr><th>Sr. No.</th><th>Text</th><th>Actions</th></tr></thead><tbody>
            <?php if (!$scrollerOne): ?><tr><td colspan="3" class="empty-row">No text items found.</td></tr><?php endif; ?>
            <?php foreach ($scrollerOne as $index => $fact): ?><tr><td><?= $index + 1 ?></td><td class="fact-text"><?= factValue($fact['text_content']) ?></td><td><div class="table-actions"><a href="didyouknow.php?edit=<?= (int) $fact['id'] ?>" title="Edit"><i class="fa-regular fa-pen-to-square"></i></a><form method="post" onsubmit="return confirm('Delete this text item?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="fact_id" value="<?= (int) $fact['id'] ?>"><button class="delete-button" type="submit" title="Delete"><i class="fa-regular fa-trash-can"></i></button></form></div></td></tr><?php endforeach; ?>
        </tbody></table></div>
    </section>

    <section class="panel">
        <div class="panel-header"><div><h2>Second infinite scroller</h2><p><?= count($scrollerTwo) ?> text item<?= count($scrollerTwo) === 1 ? '' : 's' ?></p></div></div>
        <div class="table-container"><table class="fact-table"><thead><tr><th>Sr. No.</th><th>Text</th><th>Actions</th></tr></thead><tbody>
            <?php if (!$scrollerTwo): ?><tr><td colspan="3" class="empty-row">No text items found.</td></tr><?php endif; ?>
            <?php foreach ($scrollerTwo as $index => $fact): ?><tr><td><?= $index + 1 ?></td><td class="fact-text"><?= factValue($fact['text_content']) ?></td><td><div class="table-actions"><a href="didyouknow.php?edit=<?= (int) $fact['id'] ?>" title="Edit"><i class="fa-regular fa-pen-to-square"></i></a><form method="post" onsubmit="return confirm('Delete this text item?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="fact_id" value="<?= (int) $fact['id'] ?>"><button class="delete-button" type="submit" title="Delete"><i class="fa-regular fa-trash-can"></i></button></form></div></td></tr><?php endforeach; ?>
        </tbody></table></div>
    </section>
</section>
</main>
</div>
</body>
</html>
