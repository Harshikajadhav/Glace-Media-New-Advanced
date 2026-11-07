<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db_connection.php';

$uploadDirectory = __DIR__ . '/uploads/services/';
$uploadWebPath = 'uploads/services/';
$message = '';
$error = '';

if (!is_dir($uploadDirectory)) {
    mkdir($uploadDirectory, 0755, true);
}

$conn->query(
    'CREATE TABLE IF NOT EXISTS services (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        title VARCHAR(120) NOT NULL,
        description TEXT NOT NULL,
        icon VARCHAR(30) NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        sort_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        INDEX (sort_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
);

$seedQuery = $conn->prepare('SELECT COUNT(*) AS total FROM services');
$seedQuery->execute();
$seedCount = (int) $seedQuery->get_result()->fetch_assoc()['total'];
$seedQuery->close();

if ($seedCount === 0) {
    $defaultServices = [
        ['Influencer Marketing', 'We find the right creators for your brand and build partnerships that drive trust, reach, and meaningful engagement.', '♡', 'image/influencermarketing.png', 1],
        ['Social Media Marketing', 'We create content and strategies that grow your online presence, engage your audience, and build a brand people remember.', '◎', 'image/socialmediamarketing.png', 2],
        ['Campaign Planning', 'From planning to execution, we create campaigns that grab attention, connect with your audience, and deliver real results.', '◉', 'image/campaignplanning.png', 3],
        ['Video Production', 'From scripting and creative direction to filming, editing, and final delivery, we handle every step of the video production process.', '▶', 'image/videoproduction.png', 4],
    ];

    $seedInsert = $conn->prepare('INSERT INTO services (title, description, icon, image_path, sort_order) VALUES (?, ?, ?, ?, ?)');
    foreach ($defaultServices as $service) {
        [$title, $description, $icon, $imagePath, $sortOrder] = $service;
        $seedInsert->bind_param('ssssi', $title, $description, $icon, $imagePath, $sortOrder);
        $seedInsert->execute();
    }
    $seedInsert->close();
}

function serviceValue(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function saveServiceImage(array $file, string $uploadDirectory): string|false
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

function removeServiceImage(string $fileName, string $uploadDirectory): void
{
    $safeFileName = basename($fileName);
    $filePath = $uploadDirectory . $safeFileName;
    if (is_file($filePath)) {
        unlink($filePath);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_service') {
        $serviceId = (int) ($_POST['service_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? '');
        $existingImage = trim($_POST['existing_image'] ?? '');
        $imageFile = $_FILES['image'] ?? null;

        if ($serviceId <= 0) {
            $error = 'Invalid service selected.';
        } elseif ($title === '') {
            $error = 'Enter a service title.';
        } elseif ($description === '') {
            $error = 'Add a description for the service.';
        } elseif ($icon === '') {
            $error = 'Enter an icon symbol or short label.';
        } else {
            $newImage = $existingImage;
            $uploadedImage = null;

            if ($imageFile && $imageFile['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadedImage = saveServiceImage($imageFile, $uploadDirectory);
                if ($uploadedImage === false) {
                    $error = 'Only JPG, PNG, WebP, or GIF images are allowed.';
                } else {
                    $newImage = $uploadWebPath . $uploadedImage;
                }
            }

            if ($error === '') {
                if ($newImage === '') {
                    $error = 'Upload a service image.';
                } else {
                    $query = $conn->prepare('UPDATE services SET title = ?, description = ?, icon = ?, image_path = ? WHERE id = ?');
                    $query->bind_param('ssssi', $title, $description, $icon, $newImage, $serviceId);
                    $query->execute();
                    $query->close();

                    if ($uploadedImage !== null && $existingImage !== '' && $uploadedImage !== false) {
                        $oldPath = $uploadDirectory . basename($existingImage);
                        if (is_file($oldPath)) {
                            unlink($oldPath);
                        }
                    }

                    $message = 'Service updated successfully.';
                }
            }
        }
    }
}

$services = [];
$result = $conn->query('SELECT id, title, description, icon, image_path FROM services ORDER BY sort_order ASC, id ASC');
while ($row = $result->fetch_assoc()) {
    $services[] = $row;
}

$adminAssetPrefix = '';
$adminPageTitle = 'Services';
$adminSiteHref = '../index.php';
include __DIR__ . '/header.php';
include __DIR__ . '/sidebar.php';
?>
<style>
    .services-admin-page { max-width: 1500px; }
    .service-admin-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(330px, 1fr)); gap: 18px; }
    .service-admin-card { padding: 0; overflow: hidden; }
    .service-admin-preview { width: 100%; height: 200px; overflow: hidden; border-bottom: 1px solid var(--border); background: var(--surface-2); }
    .service-admin-preview img { width: 100%; height: 100%; object-fit: cover; }
    .service-admin-form { padding: 20px; }
    .service-form-grid { display: grid; gap: 14px; }
    .service-admin-form label { display: block; margin-bottom: 8px; font-size: 10px; letter-spacing: 1px; text-transform: uppercase; color: var(--muted); }
    .service-admin-form input[type="text"],
    .service-admin-form textarea,
    .service-admin-form input[type="file"] { width: 100%; padding: 11px 12px; border: 1px solid var(--border); border-radius: 8px; background: var(--surface-2); color: var(--text); font-size: 12px; }
    .service-admin-form textarea { min-height: 110px; resize: vertical; }
    .service-admin-form input[type="file"] { padding: 9px; }
    .service-admin-form .form-actions { display: flex; align-items: center; gap: 8px; margin-top: 18px; }
    .admin-notice { margin-bottom: 16px; padding: 12px 15px; border: 1px solid rgba(20,164,154,.28); border-radius: 8px; background: rgba(20,164,154,.08); color: var(--primary); font-size: 12px; }
    .admin-notice.error { border-color: rgba(229,107,111,.28); background: rgba(229,107,111,.08); color: var(--danger); }
    @media (max-width: 720px) { .service-admin-grid { grid-template-columns: 1fr; } }
</style>

<section class="content-area services-admin-page">
    <div class="page-header">
        <div>
            <span class="eyebrow">PAGES</span>
            <h1>Services<span>.</span></h1>
            <p>Update each service card title, text, icon, and image.</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="admin-notice"><?= serviceValue($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="admin-notice error"><?= serviceValue($error) ?></div>
    <?php endif; ?>

    <div class="service-admin-grid">
        <?php foreach ($services as $service): ?>
            <section class="panel service-admin-card">
                <div class="service-admin-preview">
                    <img src="<?= serviceValue($service['image_path']) ?>" alt="<?= serviceValue($service['title']) ?> preview">
                </div>

                <form class="service-admin-form" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="save_service">
                    <input type="hidden" name="service_id" value="<?= (int) $service['id'] ?>">
                    <input type="hidden" name="existing_image" value="<?= serviceValue($service['image_path']) ?>">

                    <div class="service-form-grid">
                        <div>
                            <label for="title_<?= (int) $service['id'] ?>">Title</label>
                            <input id="title_<?= (int) $service['id'] ?>" type="text" name="title" value="<?= serviceValue($service['title']) ?>" required>
                        </div>

                        <div>
                            <label for="icon_<?= (int) $service['id'] ?>">Icon</label>
                            <input id="icon_<?= (int) $service['id'] ?>" type="text" name="icon" value="<?= serviceValue($service['icon']) ?>" required>
                        </div>

                        <div>
                            <label for="description_<?= (int) $service['id'] ?>">Description</label>
                            <textarea id="description_<?= (int) $service['id'] ?>" name="description" required><?= serviceValue($service['description']) ?></textarea>
                        </div>

                        <div>
                            <label for="image_<?= (int) $service['id'] ?>">Image</label>
                            <input id="image_<?= (int) $service['id'] ?>" type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button class="primary-button" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save service</button>
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
