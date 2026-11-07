<?php
require_once __DIR__ . '/db_connection.php';

$conn->query(
    'CREATE TABLE IF NOT EXISTS brands (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        brand_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(80) NOT NULL DEFAULT "",
        service VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
);

$conn->query(
    'CREATE TABLE IF NOT EXISTS influencers (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(80) NOT NULL DEFAULT "",
        genre VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
);

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

$conn->query(
    'INSERT IGNORE INTO contact_page_settings (id, visual_image_path, background_media_path, background_media_type, email, instagram)
     VALUES (1, "https://images.unsplash.com/photo-1531058020387-3be344556be6?auto=format&fit=crop&w=1200&q=85", "videos/bg5.mp4", "video", "hello@youragency.com", "@youragency")'
);

$contactPageSettings = [
    'visual_image_path' => 'https://images.unsplash.com/photo-1531058020387-3be344556be6?auto=format&fit=crop&w=1200&q=85',
    'background_media_path' => 'videos/bg5.mp4',
    'background_media_type' => 'video',
    'email' => 'hello@youragency.com',
    'instagram' => '@youragency',
];
$contactPageSettingsResult = $conn->query('SELECT visual_image_path, background_media_path, background_media_type, email, instagram FROM contact_page_settings WHERE id = 1');
if ($contactPageSettingsResult instanceof mysqli_result && ($savedContactPageSettings = $contactPageSettingsResult->fetch_assoc())) {
    $contactPageSettings = array_merge($contactPageSettings, $savedContactPageSettings);
}
$instagramValue = trim($contactPageSettings['instagram']);
$instagramHref = filter_var($instagramValue, FILTER_VALIDATE_URL)
    ? $instagramValue
    : 'https://instagram.com/' . ltrim($instagramValue, '@');

$brandNameColumn = $conn->query("SHOW COLUMNS FROM brands LIKE 'brand_name'");
if ($brandNameColumn instanceof mysqli_result && $brandNameColumn->num_rows === 0) {
    $conn->query("ALTER TABLE brands ADD COLUMN brand_name VARCHAR(255) NOT NULL DEFAULT '' AFTER name");
}

$contactMessage = '';
$contactError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'contact_enquiry') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $service = trim($_POST['services'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $formType = $_POST['enquiry_type'] ?? 'brand';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $service === '') {
        $contactError = 'Please enter your name, a valid email, and select a service.';
    } elseif ($formType === 'influencer') {
        $genre = trim($_POST['creatorHandle'] ?? '');
        $query = $conn->prepare('INSERT INTO influencers (name, email, phone, genre, message) VALUES (?, ?, ?, ?, ?)');
        $query->bind_param('sssss', $name, $email, $phone, $genre, $message);
        $query->execute();
        $query->close();
        $contactMessage = 'Thanks! Your enquiry has been received. We will be in touch soon.';
    } else {
        $brandName = trim($_POST['brandName'] ?? '');
        $query = $conn->prepare('INSERT INTO brands (name, brand_name, email, phone, service, message) VALUES (?, ?, ?, ?, ?, ?)');
        $query->bind_param('ssssss', $name, $brandName, $email, $phone, $service, $message);
        $query->execute();
        $query->close();
        $contactMessage = 'Thanks! Your enquiry has been received. We will be in touch soon.';
    }
}

include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact | Glace Media</title>
    <style>
        :root {
            --teal: #14a49a;
            --cream: #ebe1d3;
            --black: #0c0d0d;
            --dark: #111313;
            --dark-2: #171a19;
            --muted: #8d9491;
            --border: rgba(235, 225, 211, .13);
        }

        .contact-page,
        .contact-page * { box-sizing: border-box; }

        .contact-page {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            padding: 145px 28px 28px;
            color: var(--cream);
            font-family: Helvetica, Arial, sans-serif;
        }

        .footer {
            position: relative;
            z-index: 2;
        }

        .contact-background-video,
        .contact-background-image,
        .contact-background-overlay {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .contact-background-video {
            z-index: 0;
            object-fit: cover;
        }

        .contact-background-image {
            z-index: 0;
            object-fit: cover;
        }

        .contact-background-overlay {
            z-index: 0;
            background: rgba(12, 13, 13, .58);
        }

        .contact-wrapper {
            display: grid;
            grid-template-columns: .95fr 1.05fr;
            width: min(1450px, 100%);
            min-height: calc(100vh - 173px);
            margin: 0 auto;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 28px;
            background: rgba(17, 19, 19, .93);
        }

        .visual-panel {
            position: relative;
            min-height: 720px;
            overflow: hidden;
            background: #191c1b;
        }

        .visual-panel img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: saturate(.8) contrast(1.05);
        }

        .visual-panel::after {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(12, 13, 13, .05) 20%, rgba(12, 13, 13, .92) 100%);
            content: "";
        }

        .visual-content {
            position: absolute;
            right: 45px;
            bottom: 45px;
            left: 45px;
            z-index: 1;
        }

        .eyebrow {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
            color: var(--teal);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .eyebrow span { width: 30px; height: 1px; background: var(--teal); }

        .visual-content h1 {
            max-width: 600px;
            margin: 0 0 25px;
            font-size: clamp(48px, 5vw, 82px);
            line-height: .95;
            letter-spacing: -4px;
        }

        .visual-content h1 em {
            color: var(--teal);
            font-family: Georgia, serif;
            font-weight: 500;
        }

        .visual-content p {
            max-width: 460px;
            margin: 0;
            color: rgba(235, 225, 211, .72);
            font-size: 15px;
            line-height: 1.7;
        }

        .image-badge {
            position: absolute;
            top: 35px;
            right: 35px;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 92px;
            height: 92px;
            border: 1px solid rgba(235, 225, 211, .4);
            border-radius: 50%;
            background: rgba(12, 13, 13, .35);
            color: var(--cream);
            font-size: 10px;
            line-height: 1.4;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
            backdrop-filter: blur(10px);
        }

        .image-badge::before {
            position: absolute;
            inset: 8px;
            border: 1px dashed var(--teal);
            border-radius: 50%;
            content: "";
        }

        .form-panel {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 65px clamp(35px, 5vw, 85px);
            zoom: .75;
        }

        .form-header { margin-bottom: 40px; }
        .form-header h2 { margin: 0 0 15px; font-size: clamp(36px, 4vw, 56px); line-height: 1; letter-spacing: -2.5px; }
        .form-header p { max-width: 650px; margin: 0; color: var(--muted); font-size: 20px; line-height: 1.6; }

        .type-switch {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5px;
            padding: 5px;
            margin-bottom: 32px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--dark-2);
        }

        .type-btn {
            font-size: 20px;
            padding: 10px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            font: 600 13px inherit;
            transition: background .25s ease, color .25s ease;
        }

        .type-btn.active { background: var(--teal); color: #07100f; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .field { display: flex; flex-direction: column; gap: 8px; }
        .field.full { grid-column: 1 / -1; }
        .field label { color: rgba(235, 225, 211, .68); font-size: 18px; letter-spacing: 1.3px; text-transform: uppercase; }

        .field input,
        .field select,
        .field textarea {
            width: 100%;
            padding: 13px 0;
            border: 0;
            border-bottom: 1px solid var(--border);
            outline: 0;
            background: transparent;
            color: var(--cream);
            font-size: 18px;
            font-family: inherit;
            transition: border-color .25s ease;
        }

        .field input::placeholder,
        .field textarea::placeholder {
            color: #666d69;
            font-size: 18px;
            font-family: inherit;
        }
        .field input:focus,
        .field select:focus,
        .field textarea:focus { border-color: var(--teal); }
        .field select { appearance: none; cursor: pointer; }
        .field select option {
            background: #111312;
            color: var(--cream);
            font-size: 18px;
            font-family: inherit;
            border: 1px solid #14A49A;
            color-scheme: dark;
        }
        .field textarea { min-height: 100px; resize: vertical; }

        .submit-row { display: flex; align-items: center; justify-content: space-between; gap: 20px; margin-top: 30px; }
        .submit-row small { max-width: 800px; color: #666d69; font-size: 18px; line-height: 1.5; }

        .submit-btn {
            min-width: 150px;
            padding: 15px 25px;
            border: 0;
            border-radius: 100px;
            background: var(--cream);
            color: var(--black);
            cursor: pointer;
            font-size: 18px;
            font-family: inherit;
            font-weight: 700;
            transition: transform .25s ease, background .25s ease;
        }

        .submit-btn:hover { background: var(--teal); transform: translateY(-2px); }

        .success-message {
            display: none;
            padding: 14px 16px;
            margin-top: 20px;
            border: 1px solid rgba(20, 164, 154, .35);
            border-radius: 10px;
            background: rgba(20, 164, 154, .08);
            color: var(--teal);
            font-size: 13px;
        }

        .success-message.show { display: block; }

        .contact-details {
            display: flex;
            gap: 35px;
            padding-top: 22px;
            margin-top: 50px;
            border-top: 1px solid var(--border);
        }

        .contact-detail span { display: block; margin-bottom: 7px; color: #666d69; font-size: 18px; letter-spacing: 1.5px; text-transform: uppercase; }
        .contact-detail a { color: var(--cream); font-size: 18px; text-decoration: none; }
        .contact-detail a:hover { color: var(--teal); }

        @media (max-width: 1000px) {
            .contact-wrapper { grid-template-columns: 1fr; }
            .visual-panel { min-height: 620px; }
            .form-panel { padding: 55px 40px; zoom: 1; }
        }

        @media (max-width: 767px) {
            .contact-wrapper {
                min-height: auto;
            }

            .visual-panel {
                display: none;
            }

            .form-panel {
                justify-content: flex-start;
                padding: 20px 25px 45px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-header h2 {
                font-size: 28px;
                line-height: 1.05;
            }

            .form-header p {
                font-size: 16px;
                line-height: 1.5;
            }

            .type-btn {
                font-size: 14px;
                line-height: 1.3;
            }

            .field label {
                font-size: 14px;
            }

            .field input,
            .field select,
            .field textarea {
                font-size: 15px;
                line-height: 1.4;
            }

            .field input::placeholder,
            .field textarea::placeholder {
                font-size: 14px;
            }

            .field select option {
                font-size: 15px;
            }

            .submit-row small {
                font-size: 13px;
                line-height: 1.45;
            }

            .submit-btn {
                font-size: 15px;
            }

            .contact-detail span,
            .contact-detail a {
                font-size: 14px;
            }
        }

        @media (max-width: 600px) {
            .contact-page { padding: 105px 12px 12px; }
            .contact-wrapper { border-radius: 18px; }
            .form-panel { padding: 20px 25px 45px; }
            .submit-row { flex-direction: column; align-items: stretch; }
            .submit-btn { width: 100%; }
            .contact-details { flex-direction: column; gap: 18px; }
        }
    </style>
</head>
<body>
    <?php if ($contactPageSettings['background_media_type'] === 'image'): ?>
        <img class="contact-background-image" src="<?= htmlspecialchars($contactPageSettings['background_media_path'], ENT_QUOTES, 'UTF-8') ?>" alt="" aria-hidden="true">
    <?php else: ?>
        <video class="contact-background-video" autoplay muted loop playsinline aria-hidden="true">
            <source src="<?= htmlspecialchars($contactPageSettings['background_media_path'], ENT_QUOTES, 'UTF-8') ?>" type="video/mp4">
        </video>
    <?php endif; ?>
    <div class="contact-background-overlay" aria-hidden="true"></div>

    <main class="contact-page">
        <section class="contact-wrapper">
            <div class="visual-panel">
                <img src="<?= htmlspecialchars($contactPageSettings['visual_image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Creative social media campaign">
                <div class="image-badge">Let's<br>create</div>
                <div class="visual-content">
                    <div class="eyebrow"><span></span>Start a conversation</div>
                    <h1>Make your<br>brand <em>matter.</em></h1>
                    <p>Whether you're a brand looking to make noise or a creator ready to collaborate, tell us what you're building.</p>
                </div>
            </div>

            <div class="form-panel">
                <div class="form-header">
                    <h2>Let's talk.</h2>
                    <p>Choose what best describes you and we'll get the conversation started.</p>
                </div>

                <div class="type-switch">
                    <button type="button" class="type-btn active" data-type="brand">I'm a Brand</button>
                    <button type="button" class="type-btn" data-type="influencer">I'm an Influencer</button>
                </div>

                <form id="contactForm" method="post">
                    <input type="hidden" name="form_type" value="contact_enquiry">
                    <input type="hidden" id="enquiryType" name="enquiry_type" value="brand">
                    <div class="form-grid">
                        <div class="field"><label for="name">Full Name</label><input type="text" id="name" name="name" placeholder="Your name" required></div>
                        <div class="field"><label for="email">Email</label><input type="email" id="email" name="email" placeholder="hello@example.com" required></div>
                        <div class="field"><label for="phone">Phone</label><input type="tel" id="phone" name="phone" placeholder="+91 00000 00000"></div>
                        <div class="field"><label id="dynamicLabel" for="dynamicField">Brand Name</label><input type="text" id="dynamicField" name="brandName" placeholder="Your brand name"></div>
                        <div class="field"><label for="services">Services you're interested in</label><select id="services" name="services" required><option value="" disabled selected>Select a service</option><option>Influencer Marketing</option><option>Campaign Management</option><option>Videography</option><option>Social Media Marketing</option></select></div>
                        <div class="field"><label for="message">Tell us more</label><textarea id="message" name="message" placeholder="Tell us about your project, campaign or idea..."></textarea></div>
                    </div>
                    <div class="submit-row"><small>By submitting this form, you agree to be contacted regarding your enquiry.</small><button type="submit" class="submit-btn">Send enquiry &rarr;</button></div>
                    <?php if ($contactMessage): ?><div class="success-message show" id="successMessage"><?= htmlspecialchars($contactMessage, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    <?php if ($contactError): ?><div class="success-message show" id="successMessage"><?= htmlspecialchars($contactError, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                </form>

                <div class="contact-details">
                    <div class="contact-detail"><span>Email</span><a href="mailto:<?= htmlspecialchars($contactPageSettings['email'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($contactPageSettings['email'], ENT_QUOTES, 'UTF-8') ?></a></div>
                    <div class="contact-detail"><span>Instagram</span><a href="<?= htmlspecialchars($instagramHref, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"><?= htmlspecialchars($contactPageSettings['instagram'], ENT_QUOTES, 'UTF-8') ?></a></div>
                    <div class="contact-detail"><span>Based in</span><a href="#">Mumbai, India</a></div>
                </div>
            </div>
        </section>
    </main>

    <script>
        const typeButtons = document.querySelectorAll('.type-btn');
        const dynamicLabel = document.getElementById('dynamicLabel');
        const dynamicField = document.getElementById('dynamicField');
        const enquiryType = document.getElementById('enquiryType');
        const contactForm = document.getElementById('contactForm');
        const successMessage = document.getElementById('successMessage');

        typeButtons.forEach((button) => {
            button.addEventListener('click', () => {
                typeButtons.forEach((item) => item.classList.remove('active'));
                button.classList.add('active');

                const isBrand = button.dataset.type === 'brand';
                enquiryType.value = isBrand ? 'brand' : 'influencer';
                dynamicLabel.textContent = isBrand ? 'Brand Name' : 'Creator / Handle';
                dynamicField.placeholder = isBrand ? 'Your brand name' : '@yourinstagramhandle';
                dynamicField.name = isBrand ? 'brandName' : 'creatorHandle';
            });
        });

    </script>
    <?php include 'footer.php'; ?>
</body>
</html>
