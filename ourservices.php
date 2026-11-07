<?php
require_once __DIR__ . '/db_connection.php';

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

$services = [];
$result = $conn->query('SELECT title, description, icon, image_path FROM services ORDER BY sort_order ASC, id ASC');
while ($row = $result->fetch_assoc()) {
    $services[] = $row;
}

function serviceText(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function serviceImagePath(?string $value): string
{
    $path = trim($value ?? '');
    if ($path === '') {
        return '';
    }

    if (strpos($path, 'uploads/services/') === 0) {
        $path = 'admin/' . $path;
    } elseif (strpos($path, '/') === false) {
        $path = 'admin/uploads/services/' . basename($path);
    }

    return serviceText($path);
}

include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services Page</title>
    <!-- <link rel="stylesheet" href="ourservices.css">
    <link rel="stylesheet" href="mobile.css"> -->
</head>

<body>
  <!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Our Services</title>
<br><br><br>
<br><br>
<style>


* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    position: relative;
    min-height: 100vh;
    overflow-x: hidden;
    background:
        radial-gradient(
            circle at 75% 50%,
            rgba(20, 164, 154, .12),
            transparent 32%
        ),
        linear-gradient(
            90deg,
            #030908 0%,
            #040b0a 42%,
            #06100f 100%
        );
    font-family: "Poppins", Arial, sans-serif;
    color: white;
}

/* =========================================
   MAIN SERVICES SECTION
========================================= */

.services-section {
    width: 100%;
    max-width: 1500px;
    margin: auto;
    padding: 60px 50px 80px;
}

/* Heading */

.section-label {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;

    font-size: 14px;
    letter-spacing: 1.5px;
    margin-bottom: 10px;
    color: #ffffff;
}

.section-label::before,
.section-label::after {
    content: "";
    width: 90px;
    height: 1px;
    background: rgba(255,255,255,0.6);
}

.section-title {
    text-align: center;
    font-size: 36px;
    font-weight: 700;
    margin-bottom: 40px;
}

.section-title span {
    color: #25d9d0;
}

/* =========================================
   SERVICES GRID
========================================= */

.services-grid {
     display: grid;

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    /* Let row height follow card content so it doesn't overlap footer */
    grid-template-rows: auto;

    gap: 22px;

    width: 100%;

    transition:
        grid-template-columns
        0.65s
        cubic-bezier(0.22, 1, 0.36, 1);
}



/* =========================================
   ICON
========================================= */

.service-icon {

    width: 75px;
    height: 75px;

    border-radius: 50%;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 32px;

    border: 1px solid rgba(0,50,50,0.12);

    background: rgba(255,255,255,0.25);

    margin-bottom: 22px;

    transition:
        transform 0.4s ease,
        background 0.4s ease;
}


/* =========================================
   CARD CONTENT
========================================= */

.service-title {

    font-size: 25px;
    line-height: 1.2;

    font-weight: 700;

    margin-bottom: 18px;
}

.service-description {

    font-size: 15px;

    line-height: 1.6;

    max-width: 250px;

    color: #153b3b;
     transition:
        color 0.45s ease,
        max-width 0.5s ease,
        transform 0.5s cubic-bezier(0.22, 1, 0.36, 1),
        opacity 0.4s ease;

}


/* =========================================
   ARROW
========================================= */

.card-arrow {

    position: absolute;

    right: 22px;
    bottom: 22px;

    width: 40px;
    height: 40px;

    border-radius: 50%;

    background: #006b69;

    color: white;

    display: flex;

    align-items: center;
    justify-content: center;

    font-size: 20px;

    transition:
        transform 0.4s ease,
        background 0.4s ease;
}


/* =========================================
   HOVER EFFECT ON NORMAL CARD
========================================= */

.service-card:hover {

    transform: translateY(-5px);

    box-shadow:
        0 20px 45px rgba(0,0,0,0.25);
}

.service-card:hover .service-icon {

    transform: scale(1.05);
}

.service-card:hover .card-arrow {

    transform: translateX(4px);
}


/* =========================================
   EXPANDED CARD
========================================= */




/* Expanded card content */

.service-card.active .service-icon {

    width: 55px;
    height: 55px;

    font-size: 25px;

    margin-bottom: 12px;

    background: rgba(255,255,255,0.15);

    border-color: rgba(255,255,255,0.25);
}


.service-card.active .service-title {

    font-size: 36px;

    max-width: 360px;

    margin-bottom: 15px;
}


.service-card.active .service-description {

    color: white;

    font-size: 16px;

    max-width: 360px;
    transform: translateY(0);
}


/* =========================================
   EXPANDED CARD NUMBER
========================================= */

.service-number {

    display: none;
}

.service-card.active .service-number {

    display: flex;

    align-items: center;
    justify-content: center;

    width: 52px;
    height: 32px;

    border-radius: 20px;

    border: 1px solid rgba(255,255,255,0.8);

    font-size: 13px;

    margin-bottom: 15px;
}


/* =========================================
   LEARN MORE BUTTON
========================================= */

.learn-more {

    display: none;
}

.service-card.active .learn-more {

    display: inline-flex;

    align-items: center;
    gap: 15px;

    margin-top: 20px;

    padding: 11px 20px;

    border-radius: 30px;

    border: none;

    background: #35ddd4;

    color: #003b3a;

    font-size: 14px;

    font-weight: 600;

    cursor: pointer;

    transition: transform 0.3s ease;
}

.service-card.active .learn-more:hover {

    transform: translateX(5px);
}


/* =========================================
   EXPANDED CARD IMAGE
========================================= */

.service-image {

    display: none;
}

.service-card.active .service-image {

    display: block;

    position: absolute;

    right: 0;

    bottom: 0;

    width: 52%;

    height: 100%;

    background-size: cover;

    background-position: center;

    /*
       Replace these URLs with your own images
    */

    opacity: 0.95;

    -webkit-mask-image:
        linear-gradient(
            to right,
            transparent 0%,
            black 25%
        );

    mask-image:
        linear-gradient(
            to right,
            transparent 0%,
            black 25%
        );
}


/* =========================================
   DARK IMAGE OVERLAY
========================================= */

.service-card.active::after {

    content: "";

    position: absolute;

    inset: 0;

    pointer-events: none;

    background:
        linear-gradient(
            90deg,
            rgba(0,75,72,0.85) 0%,
            rgba(0,75,72,0.35) 50%,
            rgba(0,75,72,0.05) 100%
        );
}


/* Keep content above overlay */

.service-card.active > * {

    position: relative;

    z-index: 2;
}


/* =========================================
   IMAGE FOR EACH SERVICE
========================================= */

.service-card:nth-child(1) .service-image {

    background-image:
        url("https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?auto=format&fit=crop&w=1000&q=80");
}

.service-card:nth-child(2) .service-image {

    background-image:
        url("https://images.unsplash.com/photo-1611162617474-5b21e879e113?auto=format&fit=crop&w=1000&q=80");
}

.service-card:nth-child(3) .service-image {

    background-image:
        url("https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=1000&q=80");
}

.service-card:nth-child(4) .service-image {

    background-image:
        url("https://images.unsplash.com/photo-1485846234645-a62644f84728?auto=format&fit=crop&w=1000&q=80");
}





@media (max-width: 600px) {

    .services-section {
        padding: 40px 20px;
    }

    .section-title {
        font-size: 28px;
    }

    .services-grid {
        grid-template-columns: 1fr;
    }

    .service-card,
    .service-card.active {
        grid-column: span 1;

        min-height: 300px;
    }

    .service-card.active .service-image {
        width: 50%;
    }

    .service-card.active .service-title {
        font-size: 28px;
    }
}


/* =========================================
   SERVICES GRID
========================================= */

/* .services-grid {
    display: grid;

    grid-template-columns: repeat(4, 1fr);

    gap: 22px;

    width: 100%;

    transition: grid-template-columns 0.45s ease;
} */


/* =========================================
   HOVER CARDS
========================================= */

.services-grid.active-1 {
    grid-template-columns:
        2.4fr 0.6fr 0.6fr 0.6fr;
}

.services-grid.active-2 {
    grid-template-columns:
        0.6fr 2.4fr 0.6fr 0.6fr;
}

.services-grid.active-3 {
    grid-template-columns:
        0.6fr 0.6fr 2.4fr 0.6fr;
}

.services-grid.active-4 {
    grid-template-columns:
        0.6fr 0.6fr 0.6fr 2.4fr;
}



/* =========================================
   BASE CARD
========================================= */

.service-card {

      position: relative;

    min-width: 0;
    width: 100%;

    /* FIXED HEIGHT */
    height: 400px;
    min-height: 400px;
    max-height: 400px;

    /* padding: 28px 32px; */
        padding: 76px 32px;

    border-radius: 18px;

    overflow: hidden;

    cursor: pointer;

    color: #002727;

    transition:
        background 0.5s ease,
        box-shadow 0.5s ease,
        border-radius 0.5s ease,
        transform 0.45s ease;

    background:
        linear-gradient(
            135deg,
            #dffcf9,
            #c8f3ef
        );
}


.service-card:nth-child(even) {
    background: linear-gradient(
        135deg,
        #fffdf3,
        #f7f2df
    );
}

.service-card.active {
    grid-column: auto;

    width: 100%;
    min-width: 0;

    color: white;

    background:
        linear-gradient(
            105deg,
            #00645f,
            #008b84
        );

    box-shadow:
        0 25px 60px rgba(0, 0, 0, 0.30);

    transform: none;
}


/* =========================================
   ACTIVE / EXPANDED CARD
========================================= */

.service-card.active {

    grid-column: auto;

    width: 100%;
    min-width: 0;

    color: white;

    background:
        linear-gradient(
            105deg,
            #00645f,
            #008b84
        );

    box-shadow:
        0 25px 60px rgba(0, 0, 0, 0.30);
}

/* =========================================
   NORMAL CARD CONTENT
========================================= */

.service-title {
    font-size: 26px;
    line-height: 1.2;

    font-weight: 700;

    margin-bottom: 18px;
      transition:
        font-size 0.5s cubic-bezier(0.22, 1, 0.36, 1),
        transform 0.5s ease,
        opacity 0.4s ease;

    transform-origin: left center;
}

.service-description {
    font-size: 16px;

    line-height: 1.6;

    max-width: 250px;

    color: #153b3b;
}


/* =========================================
   ACTIVE CARD CONTENT
========================================= */

.service-card.active .service-title {

    font-size: 36px;

    max-width: 360px;

    margin-bottom: 15px;
       transform: translateX(0);
}

.service-card.active .service-description {

    color: white;

    font-size: 16px;

    max-width: 360px;
    transform: translateY(0);
}


/* =========================================
   NUMBER
========================================= */

.service-number {
    /* display: none; */
      opacity: 0;

    transform:
        translateY(10px)
        scale(0.9);

    transition:
        opacity 0.35s ease,
        transform 0.45s cubic-bezier(0.22, 1, 0.36, 1);
}

.service-card.active .service-number {

    display: flex;

    align-items: center;
    justify-content: center;

    width: 52px;
    height: 32px;

    border-radius: 20px;

    border: 1px solid rgba(255,255,255,0.8);

    font-size: 13px;

    margin-bottom: 15px;
     opacity: 1;

    transform:
        translateY(0)
        scale(1);

    transition-delay: 0.15s;
}


/* =========================================
   LEARN MORE
========================================= */

.learn-more {
    /* display: none; */
     opacity: 0;

    transform:
        translateY(15px);

    pointer-events: none;

    transition:
        opacity 0.4s ease,
        transform 0.5s cubic-bezier(0.22, 1, 0.36, 1);
}

.service-card.active .learn-more {

    display: inline-flex;

    align-items: center;

    gap: 15px;

    margin-top: 20px;

    padding: 11px 20px;

    border-radius: 30px;

    border: none;

    background: #35ddd4;

    color: #003b3a;

    font-size: 14px;

    font-weight: 600;

    cursor: pointer;
    display: inline-flex;

    opacity: 1;

    transform:
        translateY(0);

    pointer-events: auto;

    transition-delay: 0.18s;
}


/* =========================================
   IMAGE
========================================= */

.service-image {
  display: block;

    position: absolute;

    right: 0;
    bottom: 0;

    width: 55%;
    height: 100%;

    background-size: cover;
    background-position: center;

    opacity: 0;

    transform:
        translateX(35px)
        scale(1.04);

    transition:
        opacity 0.55s ease,
        transform 0.7s cubic-bezier(0.22, 1, 0.36, 1);

    pointer-events: none;

    -webkit-mask-image:
        linear-gradient(
            to right,
            transparent 0%,
            black 30%
        );

    mask-image:
        linear-gradient(
            to right,
            transparent 0%,
            black 30%
        );
}

.service-card.active .service-image {

    display: block;

    position: absolute;

    right: 0;
    bottom: 0;

    width: 55%;
    height: 100%;

    background-size: cover;
    background-position: center;

    opacity: 0.95;

    -webkit-mask-image:
        linear-gradient(
            to right,
            transparent 0%,
            black 30%
        );

    mask-image:
        linear-gradient(
            to right,
            transparent 0%,
            black 30%
        );
            opacity: 1;

    transform:
        translateX(0)
        scale(1);

    transition-delay: 0.05s;
}


/* =========================================
   IMAGE OVERLAY
========================================= */

.service-card.active::after {

    content: "";

    position: absolute;

    inset: 0;

    pointer-events: none;

    background:
        linear-gradient(
            90deg,
            rgba(0,75,72,0.9) 0%,
            rgba(0,75,72,0.45) 50%,
            rgba(0,75,72,0.05) 100%
        );
}

.service-card.active > * {
    position: relative;
    z-index: 2;
}
@keyframes overlayFade {

    from {
        opacity: 0;
    }

    to {
        opacity: 1;
    }
}



/* =========================================
   ICON
========================================= */

.service-icon {

    width: 75px;
    height: 75px;

    border-radius: 50%;

    display: flex;

    align-items: center;
    justify-content: center;

    font-size: 32px;

    margin-bottom: 22px;

    background: rgba(255,255,255,0.25);

   transition:
        transform 0.55s cubic-bezier(0.22, 1, 0.36, 1),
        width 0.5s ease,
        height 0.5s ease,
        margin 0.5s ease;

    transform-origin: left center;
}


/* Smaller icon when expanded */

.service-card.active .service-icon {

    width: 55px;
    height: 55px;

    font-size: 25px;

    margin-bottom: 12px;

    background: rgba(255,255,255,0.15);
       transform: scale(0.75);

    transform-origin: left center;
}


/* =========================================
   ARROW
========================================= */

.card-arrow {

    position: absolute;

    right: 22px;
    bottom: 22px;

    width: 40px;
    height: 40px;

    border-radius: 50%;

    background: #006b69;

    color: white;

    display: flex;

    align-items: center;
    justify-content: center;

    font-size: 20px;
     transition:
        opacity 0.25s ease,
        transform 0.4s ease;
}

.service-card.active .card-arrow {
    display: none;
}

@media (max-width: 600px) {

    .services-grid,
    .services-grid.active-1,
    .services-grid.active-2,
    .services-grid.active-3,
    .services-grid.active-4 {

        grid-template-columns: 1fr;
    }

    .service-card.active {
        grid-column: span 1;
    }
}

/*
   Mobile and tablet-small layout.
   These overrides intentionally leave the desktop card and hover styles unchanged.
*/
.service-title-mobile {
    display: none;
}

@media (max-width: 767px) {
    body > br {
        display: none;
    }

    .services-section {
        width: 100%;
        padding: 104px 20px 50px;
    }

    .section-title {
        margin-bottom: 32px;
        color: #f7f5eb;
        font-size: clamp(32px, 8vw, 44px);
        line-height: 1.05;
    }

    .services-grid,
    .services-grid.active-1,
    .services-grid.active-2,
    .services-grid.active-3,
    .services-grid.active-4 {
        display: flex;
        flex-direction: column;
        gap: 20px;
        width: 100%;
    }

    .service-card,
    .service-card.active {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: center;
        width: 100%;
        min-width: 0;
        min-height: 280px;
        height: 280px;
        max-height: none;
        margin: 0;
        padding: 30px 48% 30px 28px;
        border: 0;
        border-radius: 22px;
        color: #073a39;
        cursor: default;
        isolation: isolate;
        background: linear-gradient(135deg, #dffcf9, #bff1ec);
        box-shadow: none;
        transform: none;
    }

    .service-card:nth-child(even),
    .service-card.active:nth-child(even) {
        background: linear-gradient(135deg, #fffdf3, #edf5e9);
    }

    .service-card:nth-child(3),
    .service-card.active:nth-child(3) {
        background: linear-gradient(135deg, #c9fbf5, #a9eee9);
    }

    .service-card::before,
    .service-card.active::before {
        position: absolute;
        inset: 0;
        z-index: 1;
        pointer-events: none;
        content: "";
        background: linear-gradient(
            90deg,
            rgba(223, 252, 249, .98) 0%,
            rgba(223, 252, 249, .98) 38%,
            rgba(223, 252, 249, .96) 59%,
            /* rgba(223, 252, 249, .84) 68%,
            rgba(223, 252, 249, .58) 80%, */
            rgba(223, 252, 249, .25) 92%,
            rgba(223, 252, 249, 0) 100%
        );
    }

    .service-card:nth-child(even)::before,
    .service-card.active:nth-child(even)::before {
        background: linear-gradient(
            90deg,
            rgba(255, 253, 243, .98) 0%,
            rgba(255, 253, 243, .98) 38%,
            rgba(255, 253, 243, .96) 59%,
            rgba(255, 253, 243, .25) 72%,
            rgba(255, 253, 243, 0) 100%
        );
    }

    .service-card:nth-child(3)::before,
    .service-card.active:nth-child(3)::before {
        background: linear-gradient(
            90deg,
            rgba(201, 251, 245, .98) 0%,
            rgba(201, 251, 245, .98) 38%,
            rgba(201, 251, 245, .96) 59%,
            /* rgba(201, 251, 245, .84) 68%,
            rgba(201, 251, 245, .58) 80%, */
            rgba(201, 251, 245, .25) 72%,
            rgba(201, 251, 245, 0) 100%
        );
    }

    /* .service-card:nth-child(3)::before,
    .service-card.active:nth-child(3)::before {
        background: linear-gradient(
            90deg,
            rgba(201, 251, 245, .98) 0%,
            rgba(201, 251, 245, .98) 38%,
            rgba(201, 251, 245, .96) 52%,
            rgba(201, 251, 245, .84) 68%,
            rgba(201, 251, 245, .58) 80%,
            rgba(201, 251, 245, .25) 92%,
            rgba(201, 251, 245, 0) 100%
        );
    } */

    .service-card:hover,
    .service-card.active:hover {
        box-shadow: none;
        transform: none;
    }

    .service-card::after,
    .service-card.active::after {
        display: none;
    }

    .service-title,
    .service-card.active .service-title {
        max-width: 100%;
        margin: 0 0 14px;
        font-size: clamp(25px, 6.5vw, 36px);
        line-height: 1.08;
        letter-spacing: -.5px;
        transform: none;
    }

    .service-title-desktop {
        display: none;
    }

    .service-title-mobile {
        display: inline;
    }

    .service-description,
    .service-card.active .service-description {
        max-width: 100%;
        margin: 0;
        color: #153b3b;
        font-size: clamp(14px, 3.8vw, 17px);
        line-height: 1.45;
        transform: none;
    }

    .service-icon,
    .service-card.active .service-icon,
    .service-number,
    .service-card.active .service-number,
    .learn-more,
    .service-card.active .learn-more,
    .card-arrow,
    .service-image {
        display: none;
    }

    .service-card .service-image,
    .service-card.active .service-image {
        display: block;
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        z-index: 0;
        width: 58%;
        height: 100%;
        opacity: 1;
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        -webkit-mask-image: none;
        mask-image: none;
        transform: none;
    }

    .service-card .service-title,
    .service-card .service-description {
        position: relative;
        z-index: 2;
    }
}

@media (min-width: 768px) {
    .service-card .service-number,
    .service-card .service-icon,
    .service-card .card-arrow,
    .service-card .learn-more {
        display: none !important;
    }
}


</style>
</head>


<body>


<section class="services-section">

    <!-- SECTION HEADING -->

    <!-- <div class="section-label">
        OUR SERVICES
    </div> -->

    <h2 class="section-title">
        Our Services</span>
    </h2>


    <!-- SERVICES -->

    <div class="services-grid" id="servicesGrid">
        <?php foreach ($services as $index => $service): ?>
            <div class="service-card">

                <div class="service-number">
                    <?= sprintf('%02d', $index + 1) ?>
                </div>

                <div class="service-icon">
                    <?= serviceText($service['icon']) ?>
                </div>

                <h3 class="service-title">
                    <span class="service-title-desktop"><?= str_replace(' ', '<br>', serviceText($service['title'])) ?></span>
                    <span class="service-title-mobile"><?= serviceText($service['title']) ?></span>
                </h3>

                <p class="service-description">
                    <?= serviceText($service['description']) ?>
                </p>

                <button class="learn-more">
                    Learn More
                    <span>→</span>
                </button>

                <div class="card-arrow">
                    →
                </div>

                <div class="service-image" style="background-image: url('<?= serviceImagePath($service['image_path']) ?>');"></div>

            </div>
        <?php endforeach; ?>
    </div>

</section>



<script>
const servicesGrid = document.getElementById("servicesGrid");
const cards = document.querySelectorAll(".service-card");

cards.forEach((card, index) => {

    card.addEventListener("mouseenter", () => {

        servicesGrid.classList.remove(
            "active-1",
            "active-2",
            "active-3",
            "active-4"
        );

        cards.forEach(item => {
            item.classList.remove("active");
        });

        card.classList.add("active");

        servicesGrid.classList.add(
            `active-${index + 1}`
        );
    });

});

servicesGrid.addEventListener("mouseleave", () => {

    servicesGrid.classList.remove(
        "active-1",
        "active-2",
        "active-3",
        "active-4"
    );

    cards.forEach(card => {
        card.classList.remove("active");
    });

});
</script>


</body>
</html>
    <br><br>
    <?php include('footer.php')?>
</body>

</html>