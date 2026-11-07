<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db_connection.php';

$uploadDirectory = __DIR__ . '/uploads/founders/';
$uploadWebPath = 'uploads/founders/';
$message = '';
$error = '';

if (!is_dir($uploadDirectory)) {
    mkdir($uploadDirectory, 0755, true);
}

$conn->query(
    'CREATE TABLE IF NOT EXISTS founders (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(120) NOT NULL,
        tagline TEXT NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        sort_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        INDEX (sort_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
);

$seedQuery = $conn->prepare('SELECT COUNT(*) AS total FROM founders');
$seedQuery->execute();
$seedCount = (int) $seedQuery->get_result()->fetch_assoc()['total'];
$seedQuery->close();

if ($seedCount === 0) {
    $defaultFounders = [
        ['Prekshi', 'Co-Founder / Strategy', 'image/Prekshi.jpeg', 1],
        ['Muskaan Kataria', "My mood varies from 'Lover' by Taylor Swift to 'Lover' by Diljit Dosanjh", 'image/Muskaan.JPG', 2],
        ['Asmi', 'Co-Founder / Marketing', 'image/Asmi.jpeg', 3],
    ];

    $insertSeed = $conn->prepare('INSERT INTO founders (name, tagline, image_path, sort_order) VALUES (?, ?, ?, ?)');
    foreach ($defaultFounders as $founder) {
        [$name, $tagline, $imagePath, $sortOrder] = $founder;
        $insertSeed->bind_param('sssi', $name, $tagline, $imagePath, $sortOrder);
        $insertSeed->execute();
    }
    $insertSeed->close();
}

function founderValue(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function saveFounderImage(array $file, string $uploadDirectory): string|false
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

function removeFounderImage(string $fileName, string $uploadDirectory): void
{
    $safeFileName = basename($fileName);
    $filePath = $uploadDirectory . $safeFileName;
    if (is_file($filePath)) {
        unlink($filePath);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_founder') {
        $founderId = (int) ($_POST['founder_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $tagline = trim($_POST['tagline'] ?? '');
        $existingImage = trim($_POST['existing_image'] ?? '');
        $imageFile = $_FILES['image'] ?? null;

        if ($founderId <= 0) {
            $error = 'Invalid founder selected.';
        } elseif ($name === '') {
            $error = 'Enter a founder name.';
        } elseif ($tagline === '') {
            $error = 'Enter a founder tagline.';
        } else {
            $newImage = $existingImage;

            if ($imageFile && $imageFile['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadedImage = saveFounderImage($imageFile, $uploadDirectory);
                if ($uploadedImage === false) {
                    $error = 'Only JPG, PNG, WebP, or GIF images are allowed.';
                } else {
                    $newImage = $uploadWebPath . $uploadedImage;
                }
            }

            if ($error === '') {
                if ($newImage === '') {
                    $error = 'Upload a founder image.';
                } else {
                    $query = $conn->prepare('UPDATE founders SET name = ?, tagline = ?, image_path = ? WHERE id = ?');
                    $query->bind_param('sssi', $name, $tagline, $newImage, $founderId);
                    $query->execute();
                    $query->close();

                    if (isset($uploadedImage) && $existingImage !== '' && $uploadedImage !== false) {
                        $oldPath = $uploadDirectory . basename($existingImage);
                        if ($existingImage !== $newImage && is_file($oldPath)) {
                            unlink($oldPath);
                        }
                    }

                    $message = 'Founder updated successfully.';
                }
            }
        }
    }
}

$founders = [];
$result = $conn->query('SELECT id, name, tagline, image_path, sort_order FROM founders ORDER BY sort_order ASC, id ASC');
while ($row = $result->fetch_assoc()) {
    $founders[] = $row;
}

$adminAssetPrefix = '';
$adminPageTitle = 'About';
$adminSiteHref = '../index.php';
include __DIR__ . '/header.php';
include __DIR__ . '/sidebar.php';
?>
<style>
    .about-admin-page { max-width: 1500px; }
    .founder-admin-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 18px; }
    .founder-edit-panel { padding: 0; overflow: hidden; }
    .founder-edit-form { padding: 20px; }
    .founder-preview { width: 100%; height: 230px; overflow: hidden; border-bottom: 1px solid var(--border); background: var(--surface-2); }
    .founder-preview img { width: 100%; height: 100%; object-fit: cover; }
    .form-grid { display: grid; gap: 14px; margin-top: 18px; }
    .founder-edit-form label { display: block; margin-bottom: 8px; font-size: 10px; letter-spacing: 1px; text-transform: uppercase; color: var(--muted); }
    .founder-edit-form input[type="text"],
    .founder-edit-form textarea,
    .founder-edit-form input[type="file"] { width: 100%; padding: 11px 12px; border: 1px solid var(--border); border-radius: 8px; background: var(--surface-2); color: var(--text); font-size: 12px; }
    .founder-edit-form textarea { min-height: 90px; resize: vertical; }
    .founder-edit-form input[type="file"] { padding: 9px; }
    .founder-edit-form .form-actions { display: flex; align-items: center; gap: 8px; margin-top: 18px; }
    .admin-notice { margin-bottom: 16px; padding: 12px 15px; border: 1px solid rgba(20,164,154,.28); border-radius: 8px; background: rgba(20,164,154,.08); color: var(--primary); font-size: 12px; }
    .admin-notice.error { border-color: rgba(229,107,111,.28); background: rgba(229,107,111,.08); color: var(--danger); }
    @media (max-width: 720px) { .founder-admin-grid { grid-template-columns: 1fr; } }
</style>

<section class="content-area about-admin-page">
    <div class="page-header">
        <div>
            <span class="eyebrow">PAGES</span>
            <h1>About Founders<span>.</span></h1>
            <p>Update the founders shown on the public About Us page.</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="admin-notice"><?= founderValue($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="admin-notice error"><?= founderValue($error) ?></div>
    <?php endif; ?>

    <div class="founder-admin-grid">
        <?php foreach ($founders as $founder): ?>
            <section class="panel founder-edit-panel">
                <?php if (!empty($founder['image_path'])): ?>
                    <div class="founder-preview">
                        <img src="<?= founderValue($founder['image_path']) ?>" alt="<?= founderValue($founder['name']) ?> preview">
                    </div>
                <?php endif; ?>

                <form class="founder-edit-form" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="save_founder">
                    <input type="hidden" name="founder_id" value="<?= (int) $founder['id'] ?>">
                    <input type="hidden" name="existing_image" value="<?= founderValue($founder['image_path']) ?>">

                    <div class="form-grid">
                        <div>
                            <label for="name_<?= (int) $founder['id'] ?>">Name</label>
                            <input id="name_<?= (int) $founder['id'] ?>" type="text" name="name" value="<?= founderValue($founder['name']) ?>" required>
                        </div>

                        <div>
                            <label for="tagline_<?= (int) $founder['id'] ?>">Tagline</label>
                            <textarea id="tagline_<?= (int) $founder['id'] ?>" name="tagline" required><?= founderValue($founder['tagline']) ?></textarea>
                        </div>

                        <div>
                            <label for="image_<?= (int) $founder['id'] ?>">Image</label>
                            <input id="image_<?= (int) $founder['id'] ?>" type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button class="primary-button" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save founder</button>
                    </div>
                </form>
            </section>
        <?php endforeach; ?>
    </div>
</section>
</main>
</div>
</body>
</html>
