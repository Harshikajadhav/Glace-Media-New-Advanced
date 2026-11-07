<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db_connection.php';

$conn->query(
    'CREATE TABLE IF NOT EXISTS client_icons (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
);

$uploadDirectory = __DIR__ . '/uploads/client-icons/';
$message = '';
$error = '';
$editingIcon = null;

if (!is_dir($uploadDirectory)) {
    mkdir($uploadDirectory, 0755, true);
}

function iconValue(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function saveClientIcon(array $file, string $uploadDirectory): string|false
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'];
    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);

    if (!in_array($extension, $allowedExtensions, true) || !in_array($mimeType, $allowedMimeTypes, true)) {
        return false;
    }

    $fileName = bin2hex(random_bytes(12)) . '.' . $extension;
    return move_uploaded_file($file['tmp_name'], $uploadDirectory . $fileName) ? $fileName : false;
}

function removeClientIcon(string $fileName, string $uploadDirectory): void
{
    $safeFileName = basename($fileName);
    $filePath = $uploadDirectory . $safeFileName;
    if (is_file($filePath)) {
        unlink($filePath);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $iconId = (int) ($_POST['icon_id'] ?? 0);

    if ($action === 'delete' && $iconId > 0) {
        $query = $conn->prepare('SELECT image_path FROM client_icons WHERE id = ?');
        $query->bind_param('i', $iconId);
        $query->execute();
        $oldIcon = $query->get_result()->fetch_assoc();
        $query->close();

        if ($oldIcon) {
            $deleteQuery = $conn->prepare('DELETE FROM client_icons WHERE id = ?');
            $deleteQuery->bind_param('i', $iconId);
            $deleteQuery->execute();
            $deleteQuery->close();
            removeClientIcon($oldIcon['image_path'], $uploadDirectory);
            $message = 'Client icon deleted successfully.';
        }
    }

    if ($action === 'save') {
        $name = trim($_POST['name'] ?? '');
        $existingImage = trim($_POST['existing_image_path'] ?? '');
        $image = $_FILES['image'] ?? null;

        if ($name === '') {
            $error = 'Enter a name for the client icon.';
        } elseif ($iconId === 0 && (!$image || $image['error'] === UPLOAD_ERR_NO_FILE)) {
            $error = 'Choose an image to upload.';
        } else {
            $newImage = $existingImage;
            if ($image && $image['error'] !== UPLOAD_ERR_NO_FILE) {
                $newImage = saveClientIcon($image, $uploadDirectory);
            }

            if ($newImage === false || $newImage === '') {
                $error = 'Only valid JPG, PNG, WebP, GIF, or SVG images are allowed.';
            } elseif ($iconId > 0) {
                $query = $conn->prepare('UPDATE client_icons SET name = ?, image_path = ? WHERE id = ?');
                $query->bind_param('ssi', $name, $newImage, $iconId);
                $query->execute();
                $query->close();
                if ($newImage !== $existingImage && $existingImage !== '') {
                    removeClientIcon($existingImage, $uploadDirectory);
                }
                $message = 'Client icon updated successfully.';
            } else {
                $query = $conn->prepare('INSERT INTO client_icons (name, image_path) VALUES (?, ?)');
                $query->bind_param('ss', $name, $newImage);
                $query->execute();
                $query->close();
                $message = 'Client icon uploaded successfully.';
            }
        }
    }
}

if (isset($_GET['edit'])) {
    $iconId = (int) $_GET['edit'];
    $query = $conn->prepare('SELECT id, name, image_path FROM client_icons WHERE id = ?');
    $query->bind_param('i', $iconId);
    $query->execute();
    $editingIcon = $query->get_result()->fetch_assoc();
    $query->close();
}

$clientIcons = [];
$result = $conn->query('SELECT id, name, image_path, created_at FROM client_icons ORDER BY id ASC');
while ($row = $result->fetch_assoc()) {
    $clientIcons[] = $row;
}

$adminAssetPrefix = '';
$adminPageTitle = 'Client Icons';
$adminSiteHref = '../index.php';
include __DIR__ . '/header.php';
include __DIR__ . '/sidebar.php';
?>
<style>
    .client-icons-page { max-width: 1500px; }
    .icon-form-panel { margin-bottom: 16px; }
    .icon-form { display: grid; grid-template-columns: minmax(180px, .7fr) minmax(260px, 1fr) auto; align-items: end; gap: 16px; padding: 20px; }
    .icon-form label { display: block; margin-bottom: 7px; color: var(--muted); font-size: 10px; letter-spacing: 1px; text-transform: uppercase; }
    .icon-form input { width: 100%; padding: 11px 12px; border: 1px solid var(--border); border-radius: 8px; background: var(--surface-2); color: var(--text); font-size: 12px; }
    .icon-form input:focus { border-color: var(--primary); }
    .icon-actions { display: flex; gap: 8px; align-items: center; }
    .icon-notice { margin-bottom: 16px; padding: 12px 15px; border: 1px solid rgba(20,164,154,.28); border-radius: 8px; background: rgba(20,164,154,.08); color: var(--primary); font-size: 12px; }
    .icon-error { border-color: rgba(229,107,111,.28); background: rgba(229,107,111,.08); color: var(--danger); }
    .icon-preview { width: 74px; height: 42px; object-fit: contain; border-radius: 5px; background: #101514; }
    .table-actions form { display: inline; }
    @media (max-width: 800px) { .icon-form { grid-template-columns: 1fr; } .icon-actions { align-items: stretch; } }
</style>
<section class="content-area client-icons-page">
    <div class="page-header">
        <div><span class="eyebrow">HOMEPAGE</span><h1>Client Icons<span>.</span></h1><p>Manage the client logos displayed in the homepage brand section.</p></div>
    </div>

    <?php if ($message): ?><div class="icon-notice"><?= iconValue($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="icon-notice icon-error"><?= iconValue($error) ?></div><?php endif; ?>

    <section class="panel icon-form-panel">
        <div class="panel-header"><div><h2><?= $editingIcon ? 'Edit client icon' : 'Upload client icon' ?></h2><p><?= $editingIcon ? 'Update the name or replace the selected icon.' : 'Add a logo to the homepage brand marquee.' ?></p></div></div>
        <form class="icon-form" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="icon_id" value="<?= (int) ($editingIcon['id'] ?? 0) ?>">
            <input type="hidden" name="existing_image_path" value="<?= iconValue($editingIcon['image_path'] ?? '') ?>">
            <div><label for="iconName">Name</label><input type="text" id="iconName" name="name" value="<?= iconValue($editingIcon['name'] ?? '') ?>" placeholder="Client name" required></div>
            <div><label for="iconImage">Icon image</label><input type="file" id="iconImage" name="image" accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml" <?= $editingIcon ? '' : 'required' ?>></div>
            <div class="icon-actions"><button class="primary-button" type="submit"><i class="fa-solid fa-upload"></i><?= $editingIcon ? 'Save changes' : 'Upload icon' ?></button><?php if ($editingIcon): ?><a class="secondary-button" href="client-icons.php">Cancel</a><?php endif; ?></div>
        </form>
    </section>

    <section class="panel">
        <div class="panel-header"><div><h2>Uploaded client icons</h2><p><?= count($clientIcons) ?> icon<?= count($clientIcons) === 1 ? '' : 's' ?> currently available.</p></div></div>
        <div class="table-container"><table><thead><tr><th>Sr. No.</th><th>Preview</th><th>Name</th><th>Actions</th></tr></thead><tbody>
            <?php if (!$clientIcons): ?><tr><td colspan="4" class="empty-row">No client icons uploaded yet.</td></tr><?php endif; ?>
            <?php foreach ($clientIcons as $index => $icon): ?><tr><td><?= $index + 1 ?></td><td><img class="icon-preview" src="uploads/client-icons/<?= iconValue($icon['image_path']) ?>" alt="<?= iconValue($icon['name']) ?>"></td><td><strong><?= iconValue($icon['name']) ?></strong></td><td><div class="table-actions"><a href="client-icons.php?edit=<?= (int) $icon['id'] ?>" title="Edit client icon"><i class="fa-regular fa-pen-to-square"></i></a><form method="post" onsubmit="return confirm('Delete this client icon?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="icon_id" value="<?= (int) $icon['id'] ?>"><button class="delete-button" type="submit" title="Delete client icon"><i class="fa-regular fa-trash-can"></i></button></form></div></td></tr><?php endforeach; ?>
        </tbody></table></div>
    </section>
</section>
</main>
</div>
</body>
</html>
