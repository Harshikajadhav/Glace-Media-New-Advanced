<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db_connection.php';

$conn->query(
    'CREATE TABLE IF NOT EXISTS contact_page_settings (
        id TINYINT UNSIGNED NOT NULL,
        visual_image_path VARCHAR(255) NOT NULL DEFAULT "",
        background_media_path VARCHAR(255) NOT NULL DEFAULT "videos/bg5.mp4",
        background_media_type ENUM("video", "image") NOT NULL DEFAULT "video",
        email VARCHAR(255) NOT NULL DEFAULT "hello@youragency.com",
        instagram VARCHAR(255) NOT NULL DEFAULT "@youragency",
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
);

$defaultSettings = [
    'visual_image_path' => 'https://images.unsplash.com/photo-1531058020387-3be344556be6?auto=format&fit=crop&w=1200&q=85',
    'background_media_path' => 'videos/bg5.mp4',
    'background_media_type' => 'video',
    'email' => 'hello@youragency.com',
    'instagram' => '@youragency',
];
$conn->query(
    'INSERT IGNORE INTO contact_page_settings (id, visual_image_path, background_media_path, background_media_type, email, instagram)
     VALUES (1, "' . $conn->real_escape_string($defaultSettings['visual_image_path']) . '", "videos/bg5.mp4", "video", "hello@youragency.com", "@youragency")'
);

$uploadDirectory = __DIR__ . '/uploads/contact/';
$uploadWebPrefix = 'admin/uploads/contact/';
$message = '';
$error = '';

if (!is_dir($uploadDirectory)) {
    mkdir($uploadDirectory, 0755, true);
}

function contactPageValue(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function contactPageUpload(array $file, string $directory, array $extensions, array $mimeTypes): string|false
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return false;
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!in_array($extension, $extensions, true) || !in_array($mimeType, $mimeTypes, true)) {
        return false;
    }

    $fileName = bin2hex(random_bytes(12)) . '.' . $extension;
    return move_uploaded_file($file['tmp_name'], $directory . $fileName) ? $fileName : false;
}

function contactPageRemoveUpload(string $path, string $directory): void
{
    $prefix = 'admin/uploads/contact/';
    if (strpos($path, $prefix) !== 0) {
        return;
    }

    $filePath = $directory . basename($path);
    if (is_file($filePath)) {
        unlink($filePath);
    }
}

$settingsResult = $conn->query('SELECT visual_image_path, background_media_path, background_media_type, email, instagram FROM contact_page_settings WHERE id = 1');
$settings = $settingsResult instanceof mysqli_result ? ($settingsResult->fetch_assoc() ?: $defaultSettings) : $defaultSettings;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentVisualPath = trim($settings['visual_image_path']);
    $currentBackgroundPath = trim($settings['background_media_path']);
    $visualPath = $currentVisualPath;
    $backgroundPath = $currentBackgroundPath;
    $backgroundType = ($_POST['background_media_type'] ?? $settings['background_media_type']) === 'image' ? 'image' : 'video';
    $email = trim($_POST['email'] ?? '');
    $instagram = trim($_POST['instagram'] ?? '');
    $visualFile = $_FILES['visual_image'] ?? null;
    $backgroundFile = $_FILES['background_media'] ?? null;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid contact email address.';
    } elseif ($instagram === '') {
        $error = 'Enter an Instagram handle or URL.';
    } else {
        if ($visualFile && ($visualFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $newVisualFile = contactPageUpload($visualFile, $uploadDirectory, ['jpg', 'jpeg', 'png', 'webp', 'gif'], ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
            if ($newVisualFile === false) {
                $error = 'The page image must be a valid JPG, PNG, WebP, or GIF file.';
            } else {
                $visualPath = $uploadWebPrefix . $newVisualFile;
            }
        }

        if ($error === '' && $backgroundFile && ($backgroundFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            if ($backgroundType === 'video') {
                $newBackgroundFile = contactPageUpload($backgroundFile, $uploadDirectory, ['mp4', 'webm', 'mov'], ['video/mp4', 'video/webm', 'video/quicktime']);
            } else {
                $newBackgroundFile = contactPageUpload($backgroundFile, $uploadDirectory, ['jpg', 'jpeg', 'png', 'webp', 'gif'], ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
            }

            if ($newBackgroundFile === false) {
                $error = $backgroundType === 'video'
                    ? 'The background must be a valid MP4, WebM, or MOV video.'
                    : 'The background must be a valid JPG, PNG, WebP, or GIF image.';
            } else {
                $backgroundPath = $uploadWebPrefix . $newBackgroundFile;
            }
        }

        if ($error === '') {
            $query = $conn->prepare('UPDATE contact_page_settings SET visual_image_path = ?, background_media_path = ?, background_media_type = ?, email = ?, instagram = ? WHERE id = 1');
            $query->bind_param('sssss', $visualPath, $backgroundPath, $backgroundType, $email, $instagram);
            $query->execute();
            $query->close();

            if ($visualPath !== $currentVisualPath) {
                contactPageRemoveUpload($currentVisualPath, $uploadDirectory);
            }
            if ($backgroundPath !== $currentBackgroundPath) {
                contactPageRemoveUpload($currentBackgroundPath, $uploadDirectory);
            }

            $settings = [
                'visual_image_path' => $visualPath,
                'background_media_path' => $backgroundPath,
                'background_media_type' => $backgroundType,
                'email' => $email,
                'instagram' => $instagram,
            ];
            $message = 'Contact page updated successfully.';
        }
    }
}

$adminAssetPrefix = '';
$adminPageTitle = 'Edit page';
$adminSiteHref = '../contact-us.php';
include __DIR__ . '/header.php';
include __DIR__ . '/sidebar.php';
?>
<style>
    .contact-page-editor { max-width: 1500px; }
    .contact-editor-panel { margin-bottom: 16px; }
    .contact-editor-form { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; padding: 24px; }
    .contact-editor-field { display: flex; flex-direction: column; gap: 8px; }
    .contact-editor-field.full { grid-column: 1 / -1; }
    .contact-editor-field label { color: var(--muted); font-size: 10px; letter-spacing: 1px; text-transform: uppercase; }
    .contact-editor-field input, .contact-editor-field select { width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; background: var(--surface-2); color: var(--text); font: inherit; }
    .contact-editor-field input[type="file"] { padding: 9px; }
    .contact-editor-field small { color: var(--muted); font-size: 11px; line-height: 1.5; }
    .contact-editor-actions { grid-column: 1 / -1; display: flex; justify-content: flex-end; gap: 10px; }
    .contact-editor-preview { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; padding: 0 24px 24px; }
    .contact-editor-preview figure { margin: 0; overflow: hidden; border: 1px solid var(--border); border-radius: 8px; background: var(--surface-2); }
    .contact-editor-preview img, .contact-editor-preview video { display: block; width: 100%; height: 180px; object-fit: cover; }
    .contact-editor-preview figcaption { padding: 10px 12px; color: var(--muted); font-size: 11px; }
    .contact-editor-notice { margin-bottom: 16px; padding: 12px 15px; border: 1px solid rgba(20,164,154,.28); border-radius: 8px; background: rgba(20,164,154,.08); color: var(--primary); font-size: 12px; }
    .contact-editor-notice.error { border-color: rgba(229,107,111,.28); background: rgba(229,107,111,.08); color: var(--danger); }
    @media (max-width: 700px) { .contact-editor-form, .contact-editor-preview { grid-template-columns: 1fr; } .contact-editor-field.full, .contact-editor-actions { grid-column: auto; } }
</style>
<section class="content-area contact-page-editor">
    <div class="page-header">
        <div><span class="eyebrow">CONTACT US</span><h1>Edit page<span>.</span></h1><p>Update the contact page media and contact details.</p></div>
    </div>

    <?php if ($message): ?><div class="contact-editor-notice"><?= contactPageValue($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="contact-editor-notice error"><?= contactPageValue($error) ?></div><?php endif; ?>

    <section class="panel contact-editor-panel">
        <div class="panel-header"><div><h2>Contact page settings</h2><p>Changes appear on the public contact page immediately after saving.</p></div></div>
        <form class="contact-editor-form" method="post" enctype="multipart/form-data">
            <div class="contact-editor-field"><label for="visualImage">Page image</label><input type="file" id="visualImage" name="visual_image" accept="image/jpeg,image/png,image/webp,image/gif"><small>Displayed in the left visual panel. Leave empty to keep the current image.</small></div>
            <div class="contact-editor-field"><label for="backgroundType">Background type</label><select id="backgroundType" name="background_media_type"><option value="video" <?= $settings['background_media_type'] === 'video' ? 'selected' : '' ?>>Video</option><option value="image" <?= $settings['background_media_type'] === 'image' ? 'selected' : '' ?>>Image</option></select></div>
            <div class="contact-editor-field full"><label for="backgroundMedia">Background media</label><input type="file" id="backgroundMedia" name="background_media" accept="video/mp4,video/webm,video/quicktime,image/jpeg,image/png,image/webp,image/gif"><small>Choose a video or image matching the selected background type. Leave empty to keep the current background.</small></div>
            <div class="contact-editor-field"><label for="contactEmail">Email</label><input type="email" id="contactEmail" name="email" value="<?= contactPageValue($settings['email']) ?>" required></div>
            <div class="contact-editor-field"><label for="instagram">Instagram</label><input type="text" id="instagram" name="instagram" value="<?= contactPageValue($settings['instagram']) ?>" placeholder="@youragency or full URL" required></div>
            <div class="contact-editor-actions"><button class="primary-button" type="submit"><i class="fa-regular fa-floppy-disk"></i>Save changes</button></div>
        </form>
        <div class="contact-editor-preview">
            <figure><img src="<?= contactPageValue($settings['visual_image_path']) ?>" alt="Current contact page image"><figcaption>Current page image</figcaption></figure>
            <?php if ($settings['background_media_type'] === 'video'): ?><figure><video src="<?= contactPageValue($settings['background_media_path']) ?>" controls muted></video><figcaption>Current background video</figcaption></figure><?php else: ?><figure><img src="<?= contactPageValue($settings['background_media_path']) ?>" alt="Current contact background"><figcaption>Current background image</figcaption></figure><?php endif; ?>
        </div>
    </section>
</section>
</main>
</div>
</body>
</html>
