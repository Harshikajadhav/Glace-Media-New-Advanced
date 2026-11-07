<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db_connection.php';

$uploadDirectory = __DIR__ . '/uploads/';
$uploadWebPath = 'uploads/';
$message = '';
$error = '';
$editingWork = null;

$conn->query(
    'CREATE TABLE IF NOT EXISTS homepage_work (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        main_img VARCHAR(255) NOT NULL,
        thumbnail_img VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
);

if (!is_dir($uploadDirectory)) {
    mkdir($uploadDirectory, 0755, true);
}

function workValue(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function saveWorkImage(array $file, string $uploadDirectory): string|false
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);

    if (!in_array($extension, $allowedExtensions, true) || !in_array($mimeType, $allowedMimeTypes, true)) {
        return false;
    }

    $fileName = bin2hex(random_bytes(12)) . '.' . $extension;
    return move_uploaded_file($file['tmp_name'], $uploadDirectory . $fileName) ? $fileName : false;
}

function removeWorkImage(string $fileName, string $uploadDirectory): void
{
    $safeFileName = basename($fileName);
    $filePath = $uploadDirectory . $safeFileName;
    if (is_file($filePath)) {
        unlink($filePath);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $workId = (int) ($_POST['work_id'] ?? 0);

    if ($action === 'delete' && $workId > 0) {
        $query = $conn->prepare('SELECT main_img, thumbnail_img FROM homepage_work WHERE id = ?');
        $query->bind_param('i', $workId);
        $query->execute();
        $oldWork = $query->get_result()->fetch_assoc();
        $query->close();

        if ($oldWork) {
            $deleteQuery = $conn->prepare('DELETE FROM homepage_work WHERE id = ?');
            $deleteQuery->bind_param('i', $workId);
            $deleteQuery->execute();
            $deleteQuery->close();
            removeWorkImage($oldWork['main_img'], $uploadDirectory);
            removeWorkImage($oldWork['thumbnail_img'], $uploadDirectory);
            $message = 'Work item deleted successfully.';
        }
    }

    if ($action === 'save') {
        $existingMainImage = trim($_POST['existing_main_img'] ?? '');
        $existingLogo = trim($_POST['existing_thumbnail_img'] ?? '');
        $mainImage = $_FILES['main_image'] ?? null;
        $logo = $_FILES['logo'] ?? null;

        if ($workId === 0 && (!$mainImage || $mainImage['error'] === UPLOAD_ERR_NO_FILE || !$logo || $logo['error'] === UPLOAD_ERR_NO_FILE)) {
            $error = 'Upload both a logo and a main image.';
        } else {
            $newMainImage = $existingMainImage;
            $newLogo = $existingLogo;

            if ($mainImage && $mainImage['error'] !== UPLOAD_ERR_NO_FILE) {
                $newMainImage = saveWorkImage($mainImage, $uploadDirectory);
            }
            if ($logo && $logo['error'] !== UPLOAD_ERR_NO_FILE) {
                $newLogo = saveWorkImage($logo, $uploadDirectory);
            }

            if ($newMainImage === false || $newLogo === false || $newMainImage === '' || $newLogo === '') {
                $error = 'Only valid JPG, PNG, WebP, or GIF images are allowed.';
            } elseif ($workId > 0) {
                $query = $conn->prepare('UPDATE homepage_work SET main_img = ?, thumbnail_img = ? WHERE id = ?');
                $query->bind_param('ssi', $newMainImage, $newLogo, $workId);
                $query->execute();
                $query->close();

                if ($newMainImage !== $existingMainImage && $existingMainImage !== '') {
                    removeWorkImage($existingMainImage, $uploadDirectory);
                }
                if ($newLogo !== $existingLogo && $existingLogo !== '') {
                    removeWorkImage($existingLogo, $uploadDirectory);
                }
                $message = 'Work item updated successfully.';
            } else {
                $query = $conn->prepare('INSERT INTO homepage_work (main_img, thumbnail_img) VALUES (?, ?)');
                $query->bind_param('ss', $newMainImage, $newLogo);
                $query->execute();
                $query->close();
                $message = 'Work item added successfully.';
            }
        }
    }
}

if (isset($_GET['edit'])) {
    $workId = (int) $_GET['edit'];
    $query = $conn->prepare('SELECT id, main_img, thumbnail_img FROM homepage_work WHERE id = ?');
    $query->bind_param('i', $workId);
    $query->execute();
    $editingWork = $query->get_result()->fetch_assoc();
    $query->close();
}

$workItems = [];
$result = $conn->query('SELECT id, main_img, thumbnail_img FROM homepage_work ORDER BY id DESC');
while ($row = $result->fetch_assoc()) {
    $workItems[] = $row;
}

$adminAssetPrefix = '';
$adminPageTitle = 'Work';
$adminSiteHref = '../index.php';
include __DIR__ . '/header.php';
include __DIR__ . '/sidebar.php';
?>
<style>
    .work-page { max-width: 1500px; }
    .work-upload-panel { margin-bottom: 16px; }
    .work-form { display: grid; grid-template-columns: minmax(180px, .7fr) minmax(220px, 1fr) minmax(220px, 1fr) auto; align-items: end; gap: 16px; padding: 20px; }
    .work-form label { display: block; margin-bottom: 7px; color: var(--muted); font-size: 10px; letter-spacing: 1px; text-transform: uppercase; }
    .work-form input[type="text"], .work-form input[type="file"] { width: 100%; padding: 11px 12px; border: 1px solid var(--border); border-radius: 8px; background: var(--surface-2); color: var(--text); font-size: 12px; }
    .work-form input[type="file"] { padding: 9px; }
    .work-actions { display: flex; align-items: center; gap: 8px; }
    .work-notice { margin-bottom: 16px; padding: 12px 15px; border: 1px solid rgba(20,164,154,.28); border-radius: 8px; background: rgba(20,164,154,.08); color: var(--primary); font-size: 12px; }
    .work-error { border-color: rgba(229,107,111,.28); background: rgba(229,107,111,.08); color: var(--danger); }
    .work-table { min-width: 760px; }
    .work-table td { vertical-align: middle; }
    .work-thumbnail { display: block; width: 72px; height: 48px; overflow: hidden; border: 1px solid var(--border); border-radius: 6px; background: var(--surface-2); object-fit: cover; }
    .work-logo { width: 58px; height: 42px; object-fit: contain; }
    .work-table .table-actions a, .work-table .table-actions button { color: var(--muted); }
    .work-table .table-actions a:hover, .work-table .table-actions button:hover { color: var(--primary); }
    .empty-row { padding: 35px 18px !important; color: var(--muted); text-align: center; }
    @media (max-width: 950px) { .work-form { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 600px) { .work-form { grid-template-columns: 1fr; } }
</style>
<section class="content-area work-page">
    <div class="page-header">
        <div><span class="eyebrow">HOMEPAGE</span><h1>Work Thumbnails<span>.</span></h1><p>Manage the logos and main images displayed in your work section.</p></div>
    </div>

    <?php if ($message): ?><div class="work-notice"><?= workValue($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="work-notice work-error"><?= workValue($error) ?></div><?php endif; ?>

    <section class="panel work-upload-panel">
        <div class="panel-header"><div><h2><?= $editingWork ? 'Edit work item' : 'Add work item' ?></h2><p><?= $editingWork ? 'Replace either image for this homepage work item.' : 'Upload a logo and main image for a homepage work item.' ?></p></div></div>
        <form class="work-form" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="work_id" value="<?= (int) ($editingWork['id'] ?? 0) ?>">
            <input type="hidden" name="existing_main_img" value="<?= workValue($editingWork['main_img'] ?? '') ?>">
            <input type="hidden" name="existing_thumbnail_img" value="<?= workValue($editingWork['thumbnail_img'] ?? '') ?>">
            <div><label for="workLogo">Logo</label><input type="file" id="workLogo" name="logo" accept="image/jpeg,image/png,image/webp,image/gif" <?= $editingWork ? '' : 'required' ?>></div>
            <div><label for="mainImage">Main image</label><input type="file" id="mainImage" name="main_image" accept="image/jpeg,image/png,image/webp,image/gif" <?= $editingWork ? '' : 'required' ?>></div>
            <div class="work-actions"><button class="primary-button" type="submit"><i class="fa-solid fa-upload"></i><?= $editingWork ? 'Save changes' : 'Add work' ?></button><?php if ($editingWork): ?><a class="secondary-button" href="work.php">Cancel</a><?php endif; ?></div>
        </form>
    </section>

    <section class="panel">
        <div class="panel-header"><div><h2>Homepage work items</h2><p><?= count($workItems) ?> item<?= count($workItems) === 1 ? '' : 's' ?> currently available.</p></div></div>
        <div class="table-container">
            <table class="work-table">
                <thead><tr><th>Sr. No.</th><th>Logo</th><th>Main Image</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (!$workItems): ?><tr><td colspan="4" class="empty-row">No work items found.</td></tr><?php endif; ?>
                <?php foreach ($workItems as $index => $work): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><a href="<?= $uploadWebPath . workValue($work['thumbnail_img']) ?>" target="_blank" rel="noopener"><img class="work-thumbnail work-logo" src="<?= $uploadWebPath . workValue($work['thumbnail_img']) ?>" alt="Thumbnail image"></a></td>
                        <td><a href="<?= $uploadWebPath . workValue($work['main_img']) ?>" target="_blank" rel="noopener"><img class="work-thumbnail" src="<?= $uploadWebPath . workValue($work['main_img']) ?>" alt="Main image"></a></td>
                        <td><div class="table-actions"><a href="work.php?edit=<?= (int) $work['id'] ?>" title="Edit work"><i class="fa-regular fa-pen-to-square"></i></a><form method="post" onsubmit="return confirm('Delete this work item?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="work_id" value="<?= (int) $work['id'] ?>"><button class="delete-button" type="submit" title="Delete work"><i class="fa-regular fa-trash-can"></i></button></form></div></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</section>
</main>
</div>
</body>
</html>
