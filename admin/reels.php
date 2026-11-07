<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db_connection.php';

$reelsTableSql = 'CREATE TABLE IF NOT EXISTS reels (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        video_path VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
;

$requiredReelColumns = ['id', 'name', 'video_path', 'created_at'];
$existingReelColumns = [];
$columnsResult = $conn->query('SHOW COLUMNS FROM reels');
if ($columnsResult instanceof mysqli_result) {
    while ($column = $columnsResult->fetch_assoc()) {
        $existingReelColumns[] = $column['Field'];
    }
}

if ($existingReelColumns && array_diff($requiredReelColumns, $existingReelColumns)) {
    $conn->query('DROP TABLE IF EXISTS reels');
}

$conn->query($reelsTableSql);

$uploadDirectory = dirname(__DIR__) . '/videos/reels/';
$uploadWebPath = '../videos/reels/';
$message = '';
$error = '';
$editingReel = null;

if (!is_dir($uploadDirectory)) {
    mkdir($uploadDirectory, 0755, true);
}

function reelValue(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $reelId = (int) ($_POST['reel_id'] ?? 0);

    if ($action === 'delete' && $reelId > 0) {
        $query = $conn->prepare('SELECT video_path FROM reels WHERE id = ?');
        $query->bind_param('i', $reelId);
        $query->execute();
        $oldReel = $query->get_result()->fetch_assoc();
        $query->close();

        if ($oldReel) {
            $deleteQuery = $conn->prepare('DELETE FROM reels WHERE id = ?');
            $deleteQuery->bind_param('i', $reelId);
            $deleteQuery->execute();
            $deleteQuery->close();

            $oldFile = dirname(__DIR__) . '/' . ltrim($oldReel['video_path'], '/');
            if (is_file($oldFile)) {
                unlink($oldFile);
            }
            $message = 'Reel deleted successfully.';
        }
    }

    if ($action === 'save') {
        $name = trim($_POST['name'] ?? '');
        $existingPath = trim($_POST['existing_video_path'] ?? '');
        $file = $_FILES['video'] ?? null;

        if ($name === '') {
            $error = 'Enter a name for the reel.';
        } elseif (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
            if ($reelId > 0 && $existingPath !== '') {
                $query = $conn->prepare('UPDATE reels SET name = ? WHERE id = ?');
                $query->bind_param('si', $name, $reelId);
                $query->execute();
                $query->close();
                $message = 'Reel updated successfully.';
            } else {
                $error = 'Choose an MP4 video to upload.';
            }
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'The video upload failed. Please try again.';
        } else {
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['mp4', 'webm', 'mov'];
            $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
            $allowedMimeTypes = ['video/mp4', 'video/webm', 'video/quicktime'];

            if (!in_array($extension, $allowedExtensions, true) || !in_array($mimeType, $allowedMimeTypes, true)) {
                $error = 'Only MP4, WebM, or MOV video files are allowed.';
            } else {
                $fileName = bin2hex(random_bytes(12)) . '.' . $extension;
                $targetPath = $uploadDirectory . $fileName;
                $relativePath = 'videos/reels/' . $fileName;

                if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $error = 'The video could not be saved.';
                } elseif ($reelId > 0) {
                    $query = $conn->prepare('UPDATE reels SET name = ?, video_path = ? WHERE id = ?');
                    $query->bind_param('ssi', $name, $relativePath, $reelId);
                    $query->execute();
                    $query->close();

                    if ($existingPath !== '') {
                        $oldFile = dirname(__DIR__) . '/' . ltrim($existingPath, '/');
                        if (is_file($oldFile)) {
                            unlink($oldFile);
                        }
                    }
                    $message = 'Reel updated successfully.';
                } else {
                    $query = $conn->prepare('INSERT INTO reels (name, video_path) VALUES (?, ?)');
                    $query->bind_param('ss', $name, $relativePath);
                    $query->execute();
                    $query->close();
                    $message = 'Reel uploaded successfully.';
                }
            }
        }
    }
}

if (isset($_GET['edit'])) {
    $reelId = (int) $_GET['edit'];
    $query = $conn->prepare('SELECT id, name, video_path FROM reels WHERE id = ?');
    $query->bind_param('i', $reelId);
    $query->execute();
    $editingReel = $query->get_result()->fetch_assoc();
    $query->close();
}

$reels = [];
$result = $conn->query('SELECT id, name, video_path, created_at FROM reels ORDER BY id DESC');
while ($row = $result->fetch_assoc()) {
    $reels[] = $row;
}

$adminAssetPrefix = '';
$adminPageTitle = 'Reels';
$adminSiteHref = '../index.php';
include __DIR__ . '/header.php';
include __DIR__ . '/sidebar.php';
?>
<style>
    .reels-page { max-width: 1500px; }
    .reel-upload-panel { margin-bottom: 16px; }
    .reel-form { display: grid; grid-template-columns: minmax(180px, .7fr) minmax(260px, 1fr) auto; align-items: end; gap: 16px; padding: 20px; }
    .reel-form label { display: block; margin-bottom: 7px; color: var(--muted); font-size: 10px; text-transform: uppercase; letter-spacing: 1px; }
    .reel-form input[type="text"], .reel-form input[type="file"] { width: 100%; padding: 11px 12px; border: 1px solid var(--border); border-radius: 8px; background: var(--surface-2); color: var(--text); font-size: 12px; }
    .reel-form input:focus { border-color: var(--primary); }
    .reel-form input[type="file"] { padding: 9px; }
    .reel-actions { display: flex; gap: 8px; align-items: center; }
    .table-actions form { display: inline; }
    .video-button { display: inline-flex; align-items: center; gap: 6px; color: var(--primary); font-size: 10px; font-weight: 700; text-decoration: none; }
    .video-button:hover { color: var(--cream); }
    .reel-notice { margin-bottom: 16px; padding: 12px 15px; border: 1px solid rgba(20,164,154,.28); border-radius: 8px; background: rgba(20,164,154,.08); color: var(--primary); font-size: 12px; }
    .reel-error { border-color: rgba(229,107,111,.28); background: rgba(229,107,111,.08); color: var(--danger); }
    @media (max-width: 800px) { .reel-form { grid-template-columns: 1fr; } .reel-actions { align-items: stretch; } }
</style>
<section class="content-area reels-page">
    <div class="page-header">
        <div><span class="eyebrow">WEBSITE</span><h1>Reels<span>.</span></h1><p>Upload and manage the videos displayed across your website.</p></div>
    </div>

    <?php if ($message): ?><div class="reel-notice"><?= reelValue($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="reel-notice reel-error"><?= reelValue($error) ?></div><?php endif; ?>

    <section class="panel reel-upload-panel">
        <div class="panel-header"><div><h2><?= $editingReel ? 'Edit reel' : 'Upload reel' ?></h2><p><?= $editingReel ? 'Update the reel name or replace its video.' : 'Add a video reel to your website library.' ?></p></div></div>
        <form class="reel-form" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="reel_id" value="<?= (int) ($editingReel['id'] ?? 0) ?>">
            <input type="hidden" name="existing_video_path" value="<?= reelValue($editingReel['video_path'] ?? '') ?>">
            <div><label for="reelName">Name</label><input type="text" id="reelName" name="name" value="<?= reelValue($editingReel['name'] ?? '') ?>" placeholder="Reel name" required></div>
            <div><label for="reelVideo">Video uploaded</label><input type="file" id="reelVideo" name="video" accept="video/mp4,video/webm,video/quicktime" <?= $editingReel ? '' : 'required' ?>></div>
            <div class="reel-actions"><button class="primary-button" type="submit"><i class="fa-solid fa-upload"></i><?= $editingReel ? 'Save changes' : 'Upload reel' ?></button><?php if ($editingReel): ?><a class="secondary-button" href="reels.php">Cancel</a><?php endif; ?></div>
        </form>
    </section>

    <section class="panel">
        <div class="panel-header"><div><h2>Uploaded reels</h2><p><?= count($reels) ?> reel<?= count($reels) === 1 ? '' : 's' ?> currently available.</p></div></div>
        <div class="table-container">
            <table>
                <thead><tr><th>Sr. No.</th><th>Name</th><th>Video Uploaded</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (!$reels): ?><tr><td colspan="4" class="empty-row">No reels uploaded yet.</td></tr><?php endif; ?>
                <?php foreach ($reels as $index => $reel): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><strong><?= reelValue($reel['name']) ?></strong></td>
                        <td><a class="video-button" href="../<?= reelValue($reel['video_path']) ?>" target="_blank" rel="noopener"><i class="fa-regular fa-circle-play"></i>View reel</a></td>
                        <td><div class="table-actions"><a href="reels.php?edit=<?= (int) $reel['id'] ?>" title="Edit reel"><i class="fa-regular fa-pen-to-square"></i></a><form method="post" onsubmit="return confirm('Delete this reel?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="reel_id" value="<?= (int) $reel['id'] ?>"><button class="delete-button" type="submit" title="Delete reel"><i class="fa-regular fa-trash-can"></i></button></form></div></td>
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
