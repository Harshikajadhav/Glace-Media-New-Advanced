<?php
include 'header.php';

$didYouKnowItems = [1 => [], 2 => []];
$didYouKnowResult = $conn->query('SELECT scroller, text_content FROM did_you_know ORDER BY scroller ASC, id ASC');
if ($didYouKnowResult instanceof mysqli_result) {
    while ($didYouKnowRow = $didYouKnowResult->fetch_assoc()) {
        $scroller = (int) $didYouKnowRow['scroller'];
        if (isset($didYouKnowItems[$scroller]) && trim($didYouKnowRow['text_content']) !== '') {
            $didYouKnowItems[$scroller][] = $didYouKnowRow['text_content'];
        }
    }
}

$homepageReels = [];
$homepageReelsResult = $conn->query('SELECT id, name, video_path FROM reels ORDER BY id ASC');
if ($homepageReelsResult instanceof mysqli_result) {
    while ($homepageReel = $homepageReelsResult->fetch_assoc()) {
        if (!empty($homepageReel['video_path'])) {
            $homepageReels[] = $homepageReel;
        }
    }
}

$renderHomepageReels = static function (array $reels): void {
    foreach ($reels as $index => $reel) {
        $number = sprintf('%02d', $index + 1);
        $name = htmlspecialchars($reel['name'], ENT_QUOTES, 'UTF-8');
        $videoPath = htmlspecialchars($reel['video_path'], ENT_QUOTES, 'UTF-8');
        echo '<article class="reel"><div class="reel-placeholder"></div><video loop playsinline preload="metadata" src="' . $videoPath . '"></video><div class="reel-overlay"></div></article>';
    }
};

$conn->query(
    'CREATE TABLE IF NOT EXISTS client_icons (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
);

$clientIcons = [];
$clientIconsResult = $conn->query('SELECT name, image_path FROM client_icons ORDER BY id ASC');
if ($clientIconsResult instanceof mysqli_result) {
    while ($clientIcon = $clientIconsResult->fetch_assoc()) {
        if (!empty($clientIcon['image_path'])) {
            $clientIcons[] = $clientIcon;
        }
    }
}

$renderClientIcons = static function (array $icons, bool $decorative = false): void {
    foreach ($icons as $icon) {
        $name = htmlspecialchars($icon['name'], ENT_QUOTES, 'UTF-8');
        $imagePath = htmlspecialchars($icon['image_path'], ENT_QUOTES, 'UTF-8');
        $alt = $decorative ? '' : $name;
        echo '<div class="brand"><img src="admin/uploads/client-icons/' . $imagePath . '" alt="' . $alt . '"></div><div class="brand-separator"></div>';
    }
};
?>

<link rel="stylesheet" href="style.css?v=7">

<style>
    html,
    body {
        height: auto;
        min-height: 100%;
        overflow-x: hidden;
        overflow-y: auto;
        overscroll-behavior-y: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    html::-webkit-scrollbar,
    body::-webkit-scrollbar {
        display: none;
    }

    .landing-preloader {
        position: fixed;
        inset: 0;
        width: 100%;
        height: 100vh;
        height: 100svh;
        overflow: hidden;
        background: #000;
        z-index: 1000;
        transform: translateY(0);
        transition: transform 850ms cubic-bezier(.76, 0, .24, 1);
        will-change: transform;
    }

    .landing-preloader img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }

    .landing-preloader::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0, 0, 0, .65), transparent 35%);
        pointer-events: none;
        opacity: 0;
        animation: preloaderGradient 700ms ease-out 2s forwards;
    }

    .preloader-scroll-prompt {
        position: absolute;
        z-index: 1;
        left: 50%;
        bottom: 32px;
        display: flex;
        align-items: center;
        gap: 12px;
        color: #ebe1d3;
        font-family: Helvetica, Arial, sans-serif;
        font-size: 10px;
        letter-spacing: 3px;
        text-transform: uppercase;
        transform: translateX(-50%);
        opacity: 0;
        animation: preloaderPrompt 1.8s ease-in-out 1s infinite both;
    }

    .preloader-scroll-prompt::after {
        content: "↓";
        color: #14a49a;
        font-size: 18px;
    }

    .landing-preloader.is-exiting {
        transform: translateY(-100%);
        pointer-events: none;
    }

    html.preloader-active,
    body.preloader-active {
        overflow: hidden !important;
        height: 100%;
    }

    @keyframes preloaderPrompt {
        0% { opacity: 0; }
        20% { opacity: .55; }
        50% { opacity: 1; }
        100% { opacity: .55; }
    }

    @keyframes preloaderGradient {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @media (prefers-reduced-motion: reduce) {
        .preloader-scroll-prompt {
            animation: none;
            opacity: 1;
        }

        .landing-preloader::after {
            animation: none;
            opacity: 1;
        }
    }
</style>
<div class="landing-preloader" aria-label="Scroll to enter Glace Media">
    <img src="image/frontpageimg.png" alt="Glace Media">
    <!-- <div class="preloader-scroll-prompt">Welcome to Glace</div> -->
</div>

<script>
    (() => {
        const preloader = document.querySelector('.landing-preloader');
        const root = document.documentElement;
        const body = document.body;

        if (!preloader) {
            return;
        }

        let dismissed = false;
        root.classList.add('preloader-active');
        body.classList.add('preloader-active');

        const dismissPreloader = () => {
            if (dismissed) {
                return;
            }

            dismissed = true;
            preloader.classList.add('is-exiting');
        };

        const preventPreloaderScroll = (event) => {
            if (!dismissed) {
                event.preventDefault();
            }
        };

        window.addEventListener('wheel', preventPreloaderScroll, { passive: false });
        window.addEventListener('touchmove', preventPreloaderScroll, { passive: false });
        window.setTimeout(dismissPreloader, 2000);

        preloader.addEventListener('transitionend', (event) => {
            if (event.propertyName === 'transform' && dismissed) {
                preloader.remove();
                root.classList.remove('preloader-active');
                body.classList.remove('preloader-active');
                window.removeEventListener('wheel', preventPreloaderScroll);
                window.removeEventListener('touchmove', preventPreloaderScroll);
            }
        });
    })();
</script>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --teal: #14a49a;
    --cream: #ebe1d3;
    --black: #030908;
    --dark: #071211;
    --muted: #94a09e;
}

html {
    scroll-behavior: smooth;
}

body {
    background: var(--black);
    color: var(--cream);
    font-family: Helvetica, Arial, sans-serif;
    overflow-x: hidden;
}


/* =====================================================
   HERO
===================================================== */

.hero {
    min-height: 100vh;
    min-height: 100svh;

    position: relative;
    overflow: hidden;

    display: flex;
    align-items: center;

    background:
        radial-gradient(
            circle at 75% 50%,
            rgba(20,164,154,.12),
            transparent 32%
        ),
        linear-gradient(
            90deg,
            #030908 0%,
            #040b0a 42%,
            #06100f 100%
        );
}


/* =====================================================
   BACKGROUND GRID
===================================================== */

.grid {
    position: absolute;
    inset: 0;

    background-image:
        linear-gradient(
            rgba(235,225,211,.045) 1px,
            transparent 1px
        ),
        linear-gradient(
            90deg,
            rgba(235,225,211,.045) 1px,
            transparent 1px
        );

    background-size: 70px 70px;

    mask-image:
        linear-gradient(
            to right,
            black 0%,
            black 55%,
            transparent 100%
        );

    pointer-events: none;
}


/* =====================================================
   AMBIENT LIGHT
===================================================== */

.glow {
    position: absolute;

    width: 550px;
    height: 550px;

    right: 15%;
    top: 50%;

    transform: translateY(-50%);

    border-radius: 50%;

    background: var(--teal);

    opacity: .08;

    filter: blur(130px);

    pointer-events: none;
}


/* =====================================================
   HERO CONTAINER
===================================================== */

.hero-container {
    width: 100%;
    max-width: 1500px;

    margin: 0 auto;

        padding: 95px 6vw 116px;

    position: relative;

    display: flex;
    align-items: center;
}


/* =====================================================
   LEFT CONTENT
===================================================== */

.hero-content {
    width: 53%;

    position: relative;

    z-index: 5;
}


/* =====================================================
   EYEBROW
===================================================== */

.eyebrow {
    display: flex;
    align-items: center;

    gap: 13px;

    margin-bottom: 30px;

    color: var(--teal);

    font-size: 12px;
    font-weight: 600;

    letter-spacing: 2.7px;
    text-transform: uppercase;
}

.eyebrow-line {
    width: 45px;
    height: 2px;

    background: var(--teal);

    box-shadow:
        0 0 15px rgba(20,164,154,.5);
}


/* =====================================================
   HEADING
===================================================== */

.hero-title {
    font-family: Helvetica, Arial, sans-serif;

    font-size:
        clamp(
            4rem,
            7.2vw,
            8.5rem
        );

    line-height: .87;

    letter-spacing: -.065em;

    font-weight: 600;

    max-width: 850px;
}


/* Normal */

.hero-title .solid {
    color: var(--cream);
}


/* Outline */

.hero-title .outline {
    color: transparent;

    -webkit-text-stroke:
        1.5px
        rgba(235,225,211,.85);

    text-stroke:
        1.5px
        rgba(235,225,211,.85);
}


/* Teal */

.hero-title .accent {
    color: var(--teal);

    position: relative;

    display: inline-block;
}


/* =====================================================
   DESCRIPTION
===================================================== */

.hero-description {
    margin-top: 55px;

    max-width: 600px;

    padding-left: 18px;

    border-left:
        1px solid
        rgba(20,164,154,.6);

    color: var(--muted);

    font-size: 22px;

    line-height: 1.7;
}

.hero-description strong {
    display: block;

    margin-bottom: 5px;

    color: var(--teal);

    font-size: 13px;

    letter-spacing: 2px;

    text-transform: uppercase;

    font-weight: 600;
}


/* =====================================================
   CTA
===================================================== */

.hero-actions {
    display: flex;

    align-items: center;

    gap: 25px;

    margin-top: 38px;
}


/* Circle */

.cta-circle {
    width: 82px;
    height: 82px;

    border-radius: 50%;

    background: var(--teal);

    color: #03100e;

    display: flex;

    align-items: center;
    justify-content: center;

    text-decoration: none;

    font-size: 27px;

    transition:
        transform .35s ease,
        box-shadow .35s ease;
}

.cta-circle:hover {
    transform: scale(1.1);

    box-shadow:
        0 0 0 12px
        rgba(20,164,154,.08),

        0 0 45px
        rgba(20,164,154,.4);
}


/* CTA text */

.cta-label {
    color: var(--teal);

    font-size: 12px;

    letter-spacing: 2.2px;

    text-transform: uppercase;

    font-weight: 600;
}


/* =====================================================
   RIGHT IMAGE
===================================================== */

.hero-art {
    position: absolute;

    width: 67%;
    height: 100%;

    right: -3%;
    top: 0;

    z-index: 2;

    overflow: hidden;

    pointer-events: none;
}


/*
    The generated image is used as a visual source.
    object-position keeps the cinematic artwork on
    the right while hiding the text baked into the
    left side of the generated image.
*/

.hero-art img {
    position: absolute;

    width: 155%;
    height: 100%;

    max-width: none;

    object-fit: cover;

    object-position: right center;

    right: -1%;

    top: 0;

    opacity: .92;

    mix-blend-mode: screen;

    filter:
        contrast(1.05)
        saturate(.9);
}


/* Fade image into website */

.hero-art::before {
    content: "";

    position: absolute;

    inset: 0;

    z-index: 3;

    background:
        linear-gradient(
            90deg,
            var(--black) 0%,
            rgba(3,9,8,.95) 7%,
            rgba(3,9,8,.35) 27%,
            transparent 55%
        );

    pointer-events: none;
}


/* Bottom fade */

.hero-art::after {
    content: "";

    position: absolute;

    inset: 0;

    z-index: 4;

    background:
        linear-gradient(
            to bottom,
            transparent 60%,
            var(--black) 100%
        );

    pointer-events: none;
}


/* =====================================================
   DECORATIVE ORBIT
===================================================== */

.orbit {
    position: absolute;

    width: 520px;
    height: 520px;

    right: 12%;
    top: 50%;

    transform:
        translateY(-50%);

    border:
        1px solid
        rgba(20,164,154,.15);

    border-radius: 50%;

    z-index: 1;

    animation:
        slowRotate 20s linear infinite;
}

.orbit::before {
    content: "";

    position: absolute;

    width: 10px;
    height: 10px;

    border-radius: 50%;

    background: var(--teal);

    top: 35px;
    left: 90px;

    box-shadow:
        0 0 25px
        var(--teal);
}

.orbit-2 {
    position: absolute;

    width: 340px;
    height: 340px;

    right: 18%;
    top: 50%;

    transform:
        translateY(-50%);

    border:
        1px solid
        rgba(235,225,211,.08);

    border-radius: 50%;

    z-index: 1;
}


/* =====================================================
   STATS / MICRO LABEL
===================================================== */

.micro-label {
    position: absolute;

    right: 7vw;
    bottom: 45px;

    z-index: 10;

    display: flex;

    align-items: center;

    gap: 12px;

    color: rgba(235,225,211,.45);

    font-size: 10px;

    text-transform: uppercase;

    letter-spacing: 2px;
}

.micro-label span {
    width: 38px;
    height: 1px;

    background:
        rgba(235,225,211,.35);
}


/* =====================================================
   SCROLL
===================================================== */

.scroll {
    position: absolute;

    left: 7vw;
    bottom: 45px;

    z-index: 10;

    display: flex;

    align-items: center;

    gap: 12px;

    color:
        rgba(235,225,211,.4);

    font-size: 10px;

    text-transform: uppercase;

    letter-spacing: 2px;
}

.scroll-line {
    width: 35px;
    height: 1px;

    background:
        rgba(235,225,211,.4);
}


/* =====================================================
   ANIMATIONS
===================================================== */

.reveal {
    opacity: 0;

    transform:
        translateY(35px);

    animation:
        revealUp .9s
        cubic-bezier(.2,.8,.2,1)
        forwards;
}

.delay-1 {
    animation-delay: .1s;
}

.delay-2 {
    animation-delay: .25s;
}

.delay-3 {
    animation-delay: .4s;
}

.delay-4 {
    animation-delay: .55s;
}

@keyframes revealUp {

    to {
        opacity: 1;

        transform:
            translateY(0);
    }

}

@keyframes slowRotate {

    from {
        transform:
            translateY(-50%)
            rotate(0deg);
    }

    to {
        transform:
            translateY(-50%)
            rotate(360deg);
    }

}


/* =====================================================
   TABLET
===================================================== */

@media (max-width: 1100px) {

    .hero-container {
        padding-left: 6vw;
        padding-right: 6vw;
    }

    .hero-content {
        width: 65%;
    }

    .hero-art {
        width: 70%;
        right: -12%;
    }

    .hero-title {
        font-size:
            clamp(
                4rem,
                8vw,
                7rem
            );
    }

}


/* =====================================================
   MOBILE
===================================================== */

@media (max-width: 700px) {

    .hero {
        min-height: 37svh;
    }

    .hero-container {

        min-height: 100svh;

         padding: 95px 6vw 116px;

        align-items:
            flex-start;
    }


    /* Artwork becomes background */

    .hero-art {

        width: 100%;
        height: 65%;

        right: 0;
        top: 35%;

        opacity: .6;
    }

    .hero-art img {

        width: 180%;

        right: -38%;

        object-position:
            right center;
    }

    .hero-art::before {

        background:
            linear-gradient(
                to bottom,
                var(--black) 0%,
                rgba(3,9,8,.7) 25%,
                transparent 65%
            );
    }


    .hero-content {

        width: 100%;

        z-index: 8;
    }


    .hero-title {

        font-size:
            clamp(
                3.5rem,
                15vw,
                6rem
            );

        line-height: .9;
    }


    .eyebrow {

        margin-bottom: 24px;

        font-size: 10px;

        letter-spacing: 2px;
    }


    .hero-description {

        margin-top: 40px;

        max-width: 340px;

        font-size: 20px;
    }


    .hero-actions {

        margin-top: 30px;
    }


    .cta-circle {

        width: 70px;
        height: 70px;

        font-size: 22px;
    }


    .orbit,
    .orbit-2,
    .micro-label,
    .scroll {

        display: none;
    }

}


/* =====================================================
   REDUCED MOTION
===================================================== */

@media (prefers-reduced-motion: reduce) {

    *,
    *::before,
    *::after {

        animation-duration: .01ms !important;

        animation-iteration-count: 1 !important;

        scroll-behavior: auto !important;
    }
}

/* =====================================================
   HOMEPAGE CREATOR STUDIO SCENE
===================================================== */

.hero-scene {
    position: absolute;
    top: 50%;
    right: 2%;
    width: 58%;
    height: min(72vw, 720px);
    transform: translateY(-50%);
    z-index: 3;
    pointer-events: none;
}

.hero-scene-table {
    position: absolute;
    top: 32%;
    left: 91%;
    z-index: 8;
    width: 179%;
    height: 48%;
    object-fit: contain;
    pointer-events: none;
}

.hero-scene-item {
    position: absolute;
    display: block;
    margin: 0;
    padding: 0;
    border: 0;
    background: transparent;
    color: inherit;
    font: inherit;
    pointer-events: auto;
    cursor: pointer;
    transform-origin: center center;
    transition:
        filter .35s ease-in-out;
}

.hero-scene-item img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.hero-scene-item:hover,
.hero-scene-item:focus-visible {
    z-index: 20;
    filter:
        drop-shadow(0 0 0 #14A59B)
        drop-shadow(0 0 20px rgba(20, 165, 155, .35))
        drop-shadow(0 0 45px rgba(20, 165, 155, .18));
}

.hero-scene-creator:hover,
.hero-scene-creator:focus-visible {
    z-index: 6;
}

.hero-scene-camera {
    top: 5%;
    left: 100%;
    width: 47%;
    height: 89%;
    z-index: 8;
}

.hero-scene-phone {
top: 18%;
    left: 136%;
    width: 39%;
    height: 68%;
    z-index: 10;
}

.hero-scene-creator {
top: 0;
    left: 159%;
    width: 64%;
    height: 83%;
    z-index: 6;
}

.hero-scene-target {
    top: 3%;
    right: -156%;
    width: 42%;
    height: 98%;
    z-index: 9;
}

.hero-scene-target img {
    transform: scaleX(-1);
}

@media screen and (max-width: 1024px) {
    .hero-scene-item,
    .hero-scene-table {
        display: none !important;
    }
}

@media (max-width: 1100px) {
    .hero-scene {
        right: -4%;
        width: 62%;
    }
}

@media (max-width: 700px) {
    .hero-container {
        min-height: 35svh;
    }

    .hero-scene {
        top: 61%;
        right: 0;
        width: 100%;
        height: 58svh;
        transform: translateY(-50%);
    }

    .hero-scene-table {
        top: 53%;
        left: -14%;
        width: 128%;
        height: 47%;
    }

    .hero-scene-camera {
        top: 8%;
        left: -8%;
        width: 34%;
        height: 76%;
    }

    .hero-scene-phone {
        top: 22%;
        left: 23%;
        width: 20%;
        height: 62%;
    }

    .hero-scene-creator {
        top: 4%;
        left: 34%;
        width: 46%;
        height: 82%;
    }

    .hero-scene-target {
        top: 8%;
        right: -8%;
        width: 36%;
        height: 76%;
    }

    .hero-scene-item:hover,
    .hero-scene-item:focus-visible {
        filter: none;
    }

}

 </style>

<br><br><br><br><br>
<section class="hero">


    <!-- Background grid -->

    <div class="grid"></div>


    <!-- Ambient teal glow -->

    <div class="glow"></div>


    <!-- =================================================
         HERO CONTENT
    ================================================== -->

    <div class="hero-container">


        <div class="hero-content">


            <!-- Eyebrow -->

            <div class="eyebrow reveal">

                <!-- <span class="eyebrow-line"></span>

                Creative Media Agency -->

            </div>


            <!-- Main heading -->

            <h1 class="hero-title reveal delay-1">

                <span class="solid">
                    We build
                </span>

                <br>

                <!-- <span class="outline">
                    brands
                </span> -->

                <!-- <br> -->

                <span class="accent">
                     brands.
                </span>

                <br>

                <!-- <span class="solid">
                    to ignore.
                </span> -->

            </h1>


            <!-- Description -->

            <div class="hero-description reveal delay-2">

                <!-- <strong>
                    Strategy. Influence. Content.
                </strong> -->

                Brands that capture attention, create meaningful connections and leave a lasting impression by bringing together strategy, creativity and culture to turn the attention into long-term growth.

            </div>

            <div class="hero-scene" aria-label="Content creator studio equipment">
                <img class="hero-scene-table" src="image/homepage/table-top.png" alt="">
                <button class="hero-scene-item hero-scene-camera" type="button" data-service="Video Production" aria-label="Cinema camera">
                    <img src="image/homepage/videography.png" alt="Professional cinema camera on tripod">
                </button>
                <button class="hero-scene-item hero-scene-phone" type="button" data-service="Social Media Marketing" aria-label="Smartphone on vertical stand">
                    <img src="image/homepage/Influencer.png" alt="Smartphone on a vertical stand">
                </button>
                <button class="hero-scene-item hero-scene-creator" type="button" data-service="Influencer Marketing" aria-label="Female content creator with ring light">
                    <img src="image/homepage/Influencer_Marketing.png" alt="Female content creator with ring light">
                </button>
                <button class="hero-scene-item hero-scene-target" type="button" data-service="Campaign Management" aria-label="Target board with arrow">
                    <img src="image/homepage/Strategy.png" alt="Target board with arrow on wooden tripod">
                </button>
            </div>

        </div>


    </div>


</section>

<div class="videoo-containerr">
    <video preload="none" loop playsinline class="backgroundd-videoo backgroundd-videoo--desktop">
        <source data-src="videos/Glace-Media-Website-Intro-Video.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    <video preload="none" loop playsinline class="backgroundd-videoo backgroundd-videoo--mobile">
        <source data-src="videos/16X9 size slate_V2_Vertical-withAudio.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
</div>

<style>
    html,
    body {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    .videoo-containerr {
        --video-padding: 10%;
        width: calc(100% - (2 * var(--video-padding)));
        max-width: 100%;
        min-width: 0;
        height: auto;
        margin-right: auto;
        margin-bottom: 80px;
        margin-left: auto;
        overflow: visible;
    }

    .videoo-containerr .backgroundd-videoo {
        display: none;
        width: 100%;
        max-width: 100%;
        height: auto;
        object-fit: cover;
        border-radius: 40px;
        transition: border-radius .16s ease-out;
    }

    .videoo-containerr .backgroundd-videoo--desktop {
        display: block;
    }

    @media (max-width: 767px) {
        .videoo-containerr .backgroundd-videoo--desktop {
            display: none;
        }

        .videoo-containerr .backgroundd-videoo--mobile {
            display: block;
        }
    }

    .brand-section {
        width: 100%;
        padding: 150px 0 100px;
        background: radial-gradient(ellipse at center, rgba(20, 164, 154, .035), transparent 65%);
        border-top: 1px solid rgba(235, 225, 211, .08);
        border-bottom: 1px solid rgba(235, 225, 211, .08);
        overflow: hidden;
    }

    .brand-heading {
        margin-bottom: 28px;
        color: rgba(235, 225, 211, .88);
        font-size: 20px;
        font-weight: 500;
        letter-spacing: 4px;
        text-align: center;
        text-transform: uppercase;
    }

    .brand-heading span {
        color: var(--teal);
    }

    .brand-marquee {
        position: relative;
        width: 100%;
        overflow: hidden;
    }

    .brand-marquee::before,
    .brand-marquee::after {
        content: "";
        position: absolute;
        top: 0;
        bottom: 0;
        z-index: 5;
        width: 130px;
        pointer-events: none;
    }

    .brand-marquee::before {
        left: 0;
        background: linear-gradient(to right, var(--black), transparent);
    }

    .brand-marquee::after {
        right: 0;
        background: linear-gradient(to left, var(--black), transparent);
    }

    .brand-marquee-track {
        display: flex;
        width: max-content;
        animation: brandScroll 28s linear infinite;
    }

    .brand-marquee:hover .brand-marquee-track {
        animation-play-state: paused;
    }

    .brand-group {
        display: flex;
        align-items: center;
    }

    .brand {
        position: relative;
        min-width: 165px;
        height: 82px;
        padding: 0 18px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .brand::after {
        content: "";
        position: absolute;
        left: 18%;
        right: 18%;
        bottom: 2px;
        height: 2px;
        background: #14a49a;
        box-shadow: 0 0 5px #14a49a, 0 0 18px rgba(20, 164, 154, .9);
        opacity: 0;
        transform: scaleX(.2);
        transition: opacity .35s ease, transform .35s ease;
    }

    .brand img {
        display: block;
        width: 150px;
        height: 66px;
        object-fit: contain;
        opacity: .82;
        transition: filter .3s ease, opacity .3s ease, transform .3s ease;
    }

    .brand:hover img {
        filter: drop-shadow(0 0 5px rgba(20, 164, 154, .9));
        opacity: 1;
        transform: scale(1.05);
    }

    .brand:hover::after {
        opacity: 1;
        transform: scaleX(1);
    }

    .brand-separator {
        width: 1px;
        height: 42px;
        flex-shrink: 0;
        background: linear-gradient(to bottom, transparent, rgba(20, 164, 154, .22), transparent);
    }

    @keyframes brandScroll {
        from { transform: translateX(0); }
        to { transform: translateX(-50%); }
    }

    @media (max-width: 700px) {
        .brand-section {
            padding: 26px 0 60px;
        }

        .brand-heading {
            margin-bottom: 20px;
            font-size: 9px;
            letter-spacing: 3px;
        }

        .brand {
            min-width: 130px;
            height: 68px;
            padding: 0 12px;
        }

        .brand img {
            width: 120px;
            height: 54px;
        }

        .brand-separator {
            height: 32px;
        }

        .brand-marquee::before,
        .brand-marquee::after {
            width: 60px;
        }
    }
</style>

<section class="brand-section">
    <div class="brand-heading">
        TRUSTED BY <span>BRANDS</span> ACROSS INDUSTRIES
    </div>

    <div class="brand-marquee">
        <div class="brand-marquee-track">
            <div class="brand-group">
                <?php $renderClientIcons($clientIcons); ?>
            </div>

            <div class="brand-group" aria-hidden="true">
                <?php $renderClientIcons($clientIcons, true); ?>
            </div>
        </div>
    </div>
</section>

<style>
    .reel-section {
        position: relative;
        width: 100%;
        min-height: 100vh;
        padding: 170px 0 100px;
        overflow: hidden;
        isolation: isolate;
        background: radial-gradient(circle at 50% 45%, rgba(20, 164, 154, .055), transparent 40%), #050807;
        color: #ebe1d3;
    }

    .reel-section::before {
        content: "";
        position: absolute;
        width: 700px;
        height: 700px;
        left: 50%;
        top: 42%;
        transform: translate(-50%, -50%);
        border-radius: 50%;
        background: rgba(20, 164, 154, .025);
        filter: blur(100px);
        pointer-events: none;
        z-index: -1;
    }

    .reel-header {
        width: min(90%, 1400px);
        margin: 0 auto 65px;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 30px;
    }

    .section-label {
        margin-bottom: 18px;
        color: #14a49a;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 3px;
        text-transform: uppercase;
    }

    .section-title {
        font-family: Helvetica, Arial, sans-serif;
        font-size: clamp(3.5rem, 7vw, 7rem);
        line-height: .88;
        letter-spacing: -.07em;
        font-weight: 600;
    }

    .section-title .accent {
        color: #14a49a;
    }

    .feed-instruction {
        padding-bottom: 8px;
        color: #75817e;
        font-size: 11px;
        line-height: 1.6;
        letter-spacing: 1px;
        text-align: right;
        text-transform: uppercase;
    }

    .reel-viewport {
        position: relative;
        width: 100%;
        overflow: hidden;
    }

    .reel-viewport::before,
    .reel-viewport::after {
        content: "";
        position: absolute;
        top: 0;
        bottom: 0;
        z-index: 30;
        width: 150px;
        pointer-events: none;
    }

    .reel-viewport::before {
        left: 0;
        background: linear-gradient(to right, #050807, transparent);
    }

    .reel-viewport::after {
        right: 0;
        background: linear-gradient(to left, #050807, transparent);
    }

    .reel-track {
        display: flex;
        align-items: stretch;
        gap: 20px;
        width: max-content;
        padding: 10px 0 35px;
        animation: reelScroll 38s linear infinite;
        will-change: transform;
    }

    .reel-group {
        display: flex;
        align-items: stretch;
        gap: 20px;
    }

    .reel-viewport:hover .reel-track {
        animation-play-state: paused;
    }

    .reel {
        position: relative;
        width: 260px;
        height: 500px;
        flex: 0 0 260px;
        overflow: hidden;
        border: 1px solid rgba(235, 225, 211, .12);
        border-radius: 20px;
        background: linear-gradient(145deg, #10201d, #171b19);
        transition: transform .5s cubic-bezier(.2, .8, .2, 1), border-color .35s ease, box-shadow .35s ease, opacity .35s ease;
    }

    .reel-placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: radial-gradient(circle at 50% 40%, rgba(20, 164, 154, .18), transparent 35%), linear-gradient(145deg, #10201d, #171b19);
    }

    .reel-placeholder::before {
        content: "";
        position: absolute;
        width: 180px;
        height: 260px;
        border: 1px solid rgba(20, 164, 154, .28);
        border-radius: 50% 50% 45% 45%;
        transform: rotate(25deg);
        box-shadow: 0 0 45px rgba(20, 164, 154, .12);
    }

    .reel video {
        position: absolute;
        inset: 0;
        z-index: 1;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: .82;
    }

    .reel-number {
        position: relative;
        z-index: 1;
        color: rgba(235, 225, 211, .8);
        font-family: Helvetica, Arial, sans-serif;
        font-size: 48px;
        font-weight: 600;
        letter-spacing: -.08em;
    }

    .reel-overlay {
        position: absolute;
        inset: 0;
        z-index: 2;
        background: linear-gradient(to bottom, transparent 45%, rgba(3, 9, 8, .9) 100%);
    }

    .play-icon {
        position: absolute;
        z-index: 4;
        top: 50%;
        left: 50%;
        width: 48px;
        height: 48px;
        border: 1px solid rgba(235, 225, 211, .55);
        border-radius: 50%;
        transform: translate(-50%, -50%);
    }

    .play-icon::after {
        content: "";
        position: absolute;
        top: 50%;
        left: 52%;
        border-top: 7px solid transparent;
        border-bottom: 7px solid transparent;
        border-left: 10px solid #ebe1d3;
        transform: translate(-50%, -50%);
    }

    .reel:hover {
        border-color: rgba(20, 164, 154, .7);
        box-shadow: 0 20px 50px rgba(0, 0, 0, .35);
        transform: translateY(-12px);
    }

    @keyframes reelScroll {
        from { transform: translate3d(0, 0, 0); }
        to { transform: translate3d(-50%, 0, 0); }
    }

    @media (max-width: 700px) {
        .reel-section {
            min-height: auto;
            padding: 120px 0 80px;
        }

        .reel-header {
            width: 88%;
            margin-bottom: 45px;
            align-items: flex-start;
            flex-direction: column;
        }

        .feed-instruction {
            text-align: left;
        }

        .reel {
            width: 220px;
            height: 420px;
            flex-basis: 220px;
        }

        .reel-viewport::before,
        .reel-viewport::after {
            width: 60px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .reel-track {
            animation-play-state: paused;
        }
    }
</style>

<section class="reel-section">
    <div class="reel-header">
        <div class="reel-heading">
            <!-- <div class="section-label">The Feed</div> -->
            <h2 class="section-title">Ideas brought to <span class="accent">life.</span></h2>
        </div>
        <div class="feed-instruction">
            <!-- Hover to play.<br>
            Move away to keep the feed moving. -->
        </div>
    </div>

    <div class="reel-viewport">
        <div class="reel-track">
            <div class="reel-group">
                <?php $renderHomepageReels($homepageReels); ?>
            </div>
            <div class="reel-group" aria-hidden="true">
                <?php $renderHomepageReels($homepageReels); ?>
            </div>
        </div>
    </div>
</section>

<script>
    document.querySelectorAll(".reel").forEach((reel) => {
        reel.addEventListener("mouseenter", () => {
            const video = reel.querySelector("video");
            if (!video) return;
            video.currentTime = 0;
            video.play().catch(() => {});
        });

        reel.addEventListener("mouseleave", () => {
            const video = reel.querySelector("video");
            if (!video) return;
            video.pause();
            video.currentTime = 0;
        });

        reel.addEventListener("touchstart", () => {
            const video = reel.querySelector("video");
            if (!video) return;
            video.currentTime = 0;
            video.play().catch(() => {});
        }, { passive: true });
    });
</script>

<?php
include_once 'db_connection.php';
$workResult = mysqli_query($conn, "SELECT * FROM homepage_work");
if (!$workResult) {
    die("Error fetching data: " . mysqli_error($conn));
}
?>

<section class="index-content-sections">
    <div class="thoughts">
        <h2>At Glacé, we turn brands into<br><span>experiences.</span></h2>
    </div>

    <div class="text-container">
        <div class="subline">
            <p>We are a creative agency helping brands build a <span>distinctive presence</span> through branding, social media, influencer marketing, campaigns and video production.</p>
            <p>We bring together ideation, creative direction, strategies and seamless execution to create work that is not only visually appealing, but <span>purposeful</span> and built around your business goals.</p>
            <p>From shaping your brand identity and defining your voice to creating content, leading campaigns and bringing ideas to life on digital, we work across every touchpoint to create a <span>cohesive brand experience.</span></p>
        </div>
    </div>

    <div class="carousel-main-ka-bhi-main">
        <div class="carousel-main-main">
            <div class="carousel">
                <div class="list">
                    <?php while ($row = $workResult->fetch_assoc()) { ?>
                        <div class="item">
                            <img src="admin/uploads/<?php echo htmlspecialchars($row['main_img']); ?>" alt="Main Image">
                        </div>
                    <?php } ?>
                </div>
                <div class="thumbnail-controls">
                    <div class="arrows">
                        <button id="prev" type="button" aria-label="Previous work image" title="Previous">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M14.5 5 7.5 12l7 7" />
                            </svg>
                        </button>
                        <button id="next" type="button" aria-label="Next work image" title="Next">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="m9.5 5 7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                    <div class="thumbnail">
                    <?php
                    $workResult->data_seek(0);
                    while ($row = $workResult->fetch_assoc()) {
                    ?>
                        <div class="item">
                            <img src="admin/uploads/<?php echo htmlspecialchars($row['thumbnail_img']); ?>" alt="Thumbnail Image">
                            <div class="work-content"></div>
                        </div>
                    <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-spacer"></div>

    <div class="services-wrapper">
        <div class="list-of-services">
            <div class="service service1">
                <div class="headings">Influencer Marketing</div>
                <div class="services-main-content">
                    <div class="service1-desc">Connecting brands with voices that inspire and influence</div><br>
                    <div class="service1-desc"><br><br><br><br><br>
                        <button class="btnn two" type="button" onclick="window.location.href='ourservices.php'">View More <i class="fa-solid fa-arrow-right" style="color: black"></i></button>
                    </div>
                </div>
            </div>
            <div class="service service2">
                <div class="headings">Campaign <br>Management</div>
                <div class="services-main-content">
                    <div class="service1-desc">Formulating Campaigns that stand apart</div>
                    <div class="service1-desc"><br><br><br><br><br>
                        <button class="btnn two" type="button" onclick="window.location.href='ourservices.php'">View More <i class="fa-solid fa-arrow-right" style="color: black"></i></button>
                    </div>
                </div>
            </div>
            <div class="service service3">
                <div class="headings">Videography</div>
                <div class="services-main-content">
                    <div class="service1-desc">From script to screen, we do it all!</div>
                    <div class="service1-desc"><br><br><br><br><br>
                        <button class="btnn two" type="button" onclick="window.location.href='ourservices.php'">View More <i class="fa-solid fa-arrow-right" style="color: black"></i></button>
                    </div>
                </div>
            </div>
            <div class="service service4">
                <div class="headings">Social Media <br>Marketing</div>
                <div class="services-main-content">
                    <div class="service1-desc">Our creative strategies turn feeds into brand powerhouses.</div>
                    <div class="service1-desc"><br><br><br><br><br>
                        <button class="btnn two" type="button" onclick="window.location.href='ourservices.php'">View More <i class="fa-solid fa-arrow-right" style="color: black"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- 
    <div class="mobile-services-main">
        <div class="mobile-services"><p>Influencer Marketing</p><span>Lorem ipsum dolor sit amet consectetur adipisicing elit. Vel mollitia sint incidunt exercitationem assumenda, vero suscipit itaque earum molestiae facilis est nulla, explicabo fugiat.</span></div>
        <div class="mobile-services"><p>Campaign Management</p><span>Lorem ipsum dolor sit amet consectetur adipisicing elit. Vel mollitia sint incidunt exercitationem assumenda, vero suscipit itaque earum molestiae facilis est nulla, explicabo fugiat.</span></div>
        <div class="mobile-services"><p>Videography</p><span>Lorem ipsum dolor sit amet consectetur adipisicing elit. Vel mollitia sint incidunt exercitationem assumenda, vero suscipit itaque earum molestiae facilis est nulla, explicabo fugiat.</span></div>
        <div class="mobile-services"><p>Social Media Marketing</p><span>Lorem ipsum dolor sit amet consectetur adipisicing elit. Vel mollitia sint incidunt exercitationem assumenda, vero suscipit itaque earum molestiae facilis est nulla, explicabo fugiat.</span></div>
    </div> -->

    <div class="max-spacer"></div>
    <style>
        .did-you-know-section {
            margin: 0;
        }

        .did-you-know {
            --dyk-teal: #14a49a;
            --dyk-cream: #ebe1d3;
            position: relative;
            width: 100%;
            min-height: 100vh;
            overflow: hidden;
            background: #000;
            color: var(--dyk-cream);
            padding: clamp(80px, 10vw, 145px) 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .did-you-know::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: radial-gradient(circle at 50% 48%, rgba(20, 164, 154, .075), transparent 35%);
        }

        .did-you-know .dyk-header,
        .did-you-know .dyk-footer {
            position: relative;
            z-index: 1;
            width: min(90%, 1500px);
            margin-left: auto;
            margin-right: auto;
        }

        .did-you-know .dyk-header {
            margin-bottom: clamp(55px, 7vw, 85px);
            text-align: center;
            background: transparent;
        }

        .dyk-kicker {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-bottom: 24px;
            color: var(--dyk-teal);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .22em;
            text-transform: uppercase;
        }

        .dyk-kicker::before,
        .dyk-kicker::after {
            content: "";
            width: 34px;
            height: 1px;
            background: var(--dyk-teal);
            opacity: .65;
        }

        .dyk-title {
            color: var(--dyk-cream);
            font-size: clamp(62px, 9vw, 155px);
            line-height: .78;
            letter-spacing: -.085em;
            font-weight: 600;
        }

        .dyk-title span,
        .did-you-know .info em {
            color: var(--dyk-teal);
        }

        .dyk-subtitle {
            margin: 27px auto 0;
            color: rgba(235, 225, 211, .48);
            font-size: 12px;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .did-you-know .marquee {
            position: relative;
            z-index: 1;
            width: 100%;
            overflow: hidden;
            padding: 13px 0;
        }

        .did-you-know .marquee + .marquee {
            margin-top: 4px;
        }

        .did-you-know .marquee::before,
        .did-you-know .marquee::after {
            content: "";
            position: absolute;
            z-index: 5;
            top: 0;
            bottom: 0;
            width: 14vw;
            pointer-events: none;
        }

        .did-you-know .marquee::before {
            left: 0;
            background: linear-gradient(90deg, #000 0%, rgba(0, 0, 0, .85) 30%, transparent 100%);
        }

        .did-you-know .marquee::after {
            right: 0;
            background: linear-gradient(270deg, #000 0%, rgba(0, 0, 0, .85) 30%, transparent 100%);
        }

        .did-you-know .track {
            display: flex;
            width: max-content;
            will-change: transform;
        }

        .did-you-know .track.left {
            animation: dykScrollLeft 44s linear infinite;
        }

        .did-you-know .track.right {
            animation: dykScrollRight 50s linear infinite;
        }

        .did-you-know .marquee:hover .track {
            animation-play-state: paused;
        }

        .did-you-know .set {
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }

        .did-you-know .info {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px 38px;
            color: var(--dyk-cream);
            font-size: clamp(27px, 3.8vw, 67px);
            line-height: 1;
            letter-spacing: -.055em;
            font-weight: 500;
            white-space: nowrap;
        }

        .did-you-know .info-number {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 39px;
            height: 39px;
            flex: 0 0 39px;
            border: 1px solid var(--dyk-teal);
            border-radius: 50%;
            color: var(--dyk-teal);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0;
        }

        .did-you-know .separator {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            color: var(--dyk-teal);
            font-size: 18px;
            flex-shrink: 0;
        }

        .did-you-know .secondary {
            border-top: 1px solid rgba(235, 225, 211, .13);
            border-bottom: 1px solid rgba(235, 225, 211, .13);
            margin-top: 12px;
            padding: 7px 0;
        }

        .did-you-know .secondary .info {
            color: rgba(235, 225, 211, .73);
            font-size: clamp(20px, 2.7vw, 46px);
            padding: 18px 32px;
        }

        .did-you-know .secondary .info-number {
            width: 31px;
            height: 31px;
            flex-basis: 31px;
            font-size: 8px;
        }

        .did-you-know .secondary .separator {
            font-size: 13px;
        }

        .did-you-know .dyk-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: clamp(55px, 7vw, 85px);
            color: rgba(235, 225, 211, .32);
            font-size: 10px;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .did-you-know .footer-mark {
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .did-you-know .footer-mark span {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--dyk-teal);
            box-shadow: 0 0 14px var(--dyk-teal);
        }

        @keyframes dykScrollLeft {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }

        @keyframes dykScrollRight {
            from { transform: translateX(-50%); }
            to { transform: translateX(0); }
        }

        @media (max-width: 700px) {
            .did-you-know {
                min-height: auto;
                padding: 80px 0;
            }

            .did-you-know .dyk-header {
                width: 88%;
                margin-bottom: 48px;
            }

            .dyk-title {
                font-size: clamp(62px, 18vw, 105px);
            }

            .dyk-subtitle {
                font-size: 9px;
                line-height: 1.5;
            }

            .did-you-know .info {
                gap: 13px;
                padding: 17px 23px;
                font-size: clamp(27px, 8vw, 48px);
            }

            .did-you-know .info-number {
                width: 31px;
                height: 31px;
                flex-basis: 31px;
                font-size: 8px;
            }

            .did-you-know .separator {
                width: 23px;
                font-size: 13px;
            }

            .did-you-know .secondary .info {
                padding: 16px 22px;
                font-size: clamp(21px, 6vw, 37px);
            }

            .did-you-know .track.left {
                animation-duration: 31s;
            }

            .did-you-know .track.right {
                animation-duration: 36s;
            }

            .did-you-know .dyk-footer {
                width: 88%;
                font-size: 8px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .did-you-know .track {
                animation-play-state: paused !important;
                transform: none !important;
            }
        }
    </style>
    <div class="did-you-know-section">
        <section class="did-you-know">
            <header class="dyk-header">
                <div class="dyk-kicker">Curiosity never stops</div>
                <h1 class="dyk-title">DID YOU <span>KNOW ?</span></h1>
                <p class="dyk-subtitle">A few things worth knowing</p>
            </header>
            <div class="marquee">
                <div class="track left">
                    <div class="set">
                        <?php foreach ($didYouKnowItems[1] as $index => $text): ?>
                            <div class="info"><span class="info-number"><?= sprintf('%02d', $index + 1) ?></span><?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?></div>
                            <span class="separator">✦</span>
                        <?php endforeach; ?>
                    </div>
                    <div class="set" aria-hidden="true">
                        <?php foreach ($didYouKnowItems[1] as $index => $text): ?>
                            <div class="info"><span class="info-number"><?= sprintf('%02d', $index + 1) ?></span><?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?></div>
                            <span class="separator">✦</span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="marquee secondary">
                <div class="track right">
                    <div class="set">
                        <?php foreach ($didYouKnowItems[2] as $index => $text): ?>
                            <div class="info"><span class="info-number"><?= sprintf('%02d', $index + 1) ?></span><?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?></div><span class="separator">✦</span>
                        <?php endforeach; ?>
                    </div>
                    <div class="set" aria-hidden="true">
                        <?php foreach ($didYouKnowItems[2] as $index => $text): ?>
                            <div class="info"><span class="info-number"><?= sprintf('%02d', $index + 1) ?></span><?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?></div><span class="separator">✦</span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <!-- <footer class="dyk-footer"><span>Keep discovering</span><span class="footer-mark"><span></span>Infinite information</span></footer> -->
        </section>
    </div>
</section>

<script src="script.js?v=3"></script>

<style>
    .hero,
    .reel-section {
        background: transparent;
    }

    .reel-section::before {
        background: transparent;
    }
</style>

<?php
include 'footer.php';
?>
