<?php
include 'header.php';
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
    <div class="preloader-scroll-prompt">Welcome to Glace</div>
</div>

<script>
    (() => {
        const preloader = document.querySelector('.landing-preloader');
        let dismissed = false;
        const root = document.documentElement;
        const body = document.body;

        if (!preloader) {
            return;
        }

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

    padding:
        90px
        7vw
        70px;

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


/* Underline */

.hero-title .accent::after {
    content: "";

    position: absolute;

    width: 55%;

    height: 6px;

    left: 0;
    bottom: -10px;

    background: var(--teal);

    border-radius: 50px;

    opacity: .7;

    box-shadow:
        0 0 20px
        rgba(20,164,154,.45);
}


/* =====================================================
   DESCRIPTION
===================================================== */

.hero-description {
    margin-top: 55px;

    max-width: 470px;

    padding-left: 18px;

    border-left:
        1px solid
        rgba(20,164,154,.6);

    color: var(--muted);

    font-size: 16px;

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
        min-height: 100svh;
    }

    .hero-container {

        min-height: 100svh;

        padding:
            110px
            6vw
            80px;

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

        font-size: 14px;
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

 </style>

<br><br><br><br><br>
<section class="hero">


    <!-- Background grid -->

    <div class="grid"></div>


    <!-- Ambient teal glow -->

    <div class="glow"></div>


    <!-- Decorative circles -->

    <div class="orbit"></div>

    <div class="orbit-2"></div>


    <!-- =================================================
         HERO CONTENT
    ================================================== -->

    <div class="hero-container">


        <div class="hero-content">


            <!-- Eyebrow -->

            <div class="eyebrow reveal">

                <span class="eyebrow-line"></span>

                Creative Media Agency

            </div>


            <!-- Main heading -->

            <h1 class="hero-title reveal delay-1">

                <span class="solid">
                    We make
                </span>

                <br>

                <span class="outline">
                    brands
                </span>

                <br>

                <span class="accent">
                    impossible
                </span>

                <br>

                <span class="solid">
                    to ignore.
                </span>

            </h1>


            <!-- Description -->

            <div class="hero-description reveal delay-2">

                <strong>
                    Strategy. Influence. Content.
                </strong>

                We turn bold ideas into campaigns
                that capture attention, build
                communities and move brands forward.

            </div>


        </div>


    </div>


</section>

<div class="videoo-containerr">
    <video preload="auto" loop playsinline class="backgroundd-videoo">
        <source src="videos/Glace-Media-Website-Intro-Video.mp4" type="video/mp4">
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
        display: block;
        width: 100%;
        max-width: 100%;
        height: auto;
        object-fit: cover;
        border-radius: 40px;
        transition: border-radius .16s ease-out;
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
        font-size: 12px;
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
        A FEW <span>STORIES</span> WE'VE HELPED TELL
    </div>

    <div class="brand-marquee">
        <div class="brand-marquee-track">
            <div class="brand-group">
                <div class="brand"><img src="brands/189d09859fa8a76ac669fbba4d99b192610df42ae51ad0db782284e27d3e09a9.jpg" alt="Brand logo"></div>
                <div class="brand-separator"></div>
                <div class="brand"><img src="brands/330e4a1bf9724d2e55fd39f9b024137e98f93f419393d2669608386a88d0a518.jpg" alt="Brand logo"></div>
                <div class="brand-separator"></div>
                <div class="brand"><img src="brands/6_big3.png" alt="Brand logo"></div>
                <div class="brand-separator"></div>
                <div class="brand"><img src="brands/9ab85d5d37cf0b3665e90df34923117f536a2636d536d5e92dedbb5e4412ed31.jpg" alt="Brand logo"></div>
                <div class="brand-separator"></div>
                <div class="brand"><img src="brands/attachment_72908652-e1567626808989.jpg" alt="Brand logo"></div>
                <div class="brand-separator"></div>
                <div class="brand"><img src="brands/chanel-white.jpg" alt="Chanel"></div>
                <div class="brand-separator"></div>
                <div class="brand"><img src="brands/e9e9c73b24fcd453dd9f8d3c8a8ec92f.webp" alt="Brand logo"></div>
                <div class="brand-separator"></div>
                <div class="brand"><img src="brands/Monogram-Logo-Design-in-Black-White.jpg" alt="Monogram logo"></div>
                <div class="brand-separator"></div>
                <div class="brand"><img src="brands/Number-Logo-Example-in-Black-White.jpg" alt="Number logo"></div>
                <div class="brand-separator"></div>
                <div class="brand"><img src="brands/reasons-why-you-should-have-a-black-and-white-logo-design-kreafolk_d5129459-37b7-4840-abb8-1a24d2de1b0f.jpg" alt="Brand logo"></div>
                <div class="brand-separator"></div>
                <div class="brand"><img src="brands/Reasons_Why_You_Should_Have_A_Black_White_Logo_Design_7_1024x1024.webp" alt="Brand logo"></div>
                <div class="brand-separator"></div>
                <div class="brand"><img src="brands/toyota-brand-logo-car-symbol-white-design-japan-automobile-illustration-with-black-background-free-vector.jpg" alt="Toyota"></div>
                <div class="brand-separator"></div>
            </div>

            <div class="brand-group" aria-hidden="true">
                <div class="brand"><img src="brands/189d09859fa8a76ac669fbba4d99b192610df42ae51ad0db782284e27d3e09a9.jpg" alt=""></div>
                <div class="brand-separator"></div>
                <div class="brand"><img src="brands/330e4a1bf9724d2e55fd39f9b024137e98f93f419393d2669608386a88d0a518.jpg" alt=""></div>
                <div class="brand-separator"></div>
                <div class="brand"><img src="brands/6_big3.png" alt=""></div>
                <div class="brand-separator"></div>
                <div class="brand"><img src="brands/9ab85d5d37cf0b3665e90df34923117f536a2636d536d5e92dedbb5e4412ed31.jpg" alt=""></div>
                <div class="brand-separator"></div>
                <div class="brand"><img src="brands/attachment_72908652-e1567626808989.jpg" alt=""></div>
                <div class="brand-separator"></div>
                <div class="brand"><img src="brands/chanel-white.jpg" alt=""></div>
                <div class="brand-separator"></div>
                <div class="brand"><img src="brands/e9e9c73b24fcd453dd9f8d3c8a8ec92f.webp" alt=""></div>
                <div class="brand-separator"></div>
                <div class="brand"><img src="brands/Monogram-Logo-Design-in-Black-White.jpg" alt=""></div>
                <div class="brand-separator"></div>
                <div class="brand"><img src="brands/Number-Logo-Example-in-Black-White.jpg" alt=""></div>
                <div class="brand-separator"></div>
                <div class="brand"><img src="brands/reasons-why-you-should-have-a-black-and-white-logo-design-kreafolk_d5129459-37b7-4840-abb8-1a24d2de1b0f.jpg" alt=""></div>
                <div class="brand-separator"></div>
                <div class="brand"><img src="brands/Reasons_Why_You_Should_Have_A_Black_White_Logo_Design_7_1024x1024.webp" alt=""></div>
                <div class="brand-separator"></div>
                <div class="brand"><img src="brands/toyota-brand-logo-car-symbol-white-design-japan-automobile-illustration-with-black-background-free-vector.jpg" alt=""></div>
                <div class="brand-separator"></div>
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

    .playing {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 4;
        color: #14a49a;
        font-size: 9px;
        letter-spacing: 2px;
        opacity: 0;
        text-transform: uppercase;
    }

    .reel-info {
        position: absolute;
        right: 22px;
        bottom: 20px;
        left: 22px;
        z-index: 4;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .reel-name {
        color: #ebe1d3;
        font-size: 11px;
        letter-spacing: 2px;
        text-transform: uppercase;
    }

    .reel-arrow {
        color: #14a49a;
        font-size: 20px;
    }

    .reel:hover {
        border-color: rgba(20, 164, 154, .7);
        box-shadow: 0 20px 50px rgba(0, 0, 0, .35);
        transform: translateY(-12px);
    }

    .reel:hover .playing {
        opacity: 1;
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
            <div class="section-label">The Feed</div>
            <h2 class="section-title">Made to be <span class="accent">felt.</span></h2>
        </div>
        <div class="feed-instruction">
            Hover to play.<br>
            Move away to keep the feed moving.
        </div>
    </div>

    <div class="reel-viewport">
        <div class="reel-track">
            <div class="reel-group">
                <article class="reel"><div class="reel-placeholder"><span class="reel-number">01</span></div><video loop playsinline preload="metadata" src="videos/reel1.mp4"></video><div class="reel-overlay"></div><div class="play-icon"></div><div class="playing">Playing</div><div class="reel-info"><span class="reel-name">Reel / 01</span><span class="reel-arrow">↗</span></div></article>
                <article class="reel"><div class="reel-placeholder"><span class="reel-number">02</span></div><video loop playsinline preload="metadata" src="videos/reel2.mp4"></video><div class="reel-overlay"></div><div class="play-icon"></div><div class="playing">Playing</div><div class="reel-info"><span class="reel-name">Reel / 02</span><span class="reel-arrow">↗</span></div></article>
                <article class="reel"><div class="reel-placeholder"><span class="reel-number">03</span></div><video loop playsinline preload="metadata" src="videos/reel3.mp4"></video><div class="reel-overlay"></div><div class="play-icon"></div><div class="playing">Playing</div><div class="reel-info"><span class="reel-name">Reel / 03</span><span class="reel-arrow">↗</span></div></article>
                <article class="reel"><div class="reel-placeholder"><span class="reel-number">04</span></div><video loop playsinline preload="metadata" src="videos/reel4.mp4"></video><div class="reel-overlay"></div><div class="play-icon"></div><div class="playing">Playing</div><div class="reel-info"><span class="reel-name">Reel / 04</span><span class="reel-arrow">↗</span></div></article>
                <article class="reel"><div class="reel-placeholder"><span class="reel-number">05</span></div><video loop playsinline preload="metadata" src="videos/reel5.mp4"></video><div class="reel-overlay"></div><div class="play-icon"></div><div class="playing">Playing</div><div class="reel-info"><span class="reel-name">Reel / 05</span><span class="reel-arrow">↗</span></div></article>
                <article class="reel"><div class="reel-placeholder"><span class="reel-number">06</span></div><video loop playsinline preload="metadata" src="videos/reel6.mp4"></video><div class="reel-overlay"></div><div class="play-icon"></div><div class="playing">Playing</div><div class="reel-info"><span class="reel-name">Reel / 06</span><span class="reel-arrow">↗</span></div></article>
            </div>
            <div class="reel-group" aria-hidden="true">
                <article class="reel"><div class="reel-placeholder"><span class="reel-number">01</span></div><video loop playsinline preload="metadata" src="videos/reel1.mp4"></video><div class="reel-overlay"></div><div class="play-icon"></div><div class="playing">Playing</div><div class="reel-info"><span class="reel-name">Reel / 01</span><span class="reel-arrow">↗</span></div></article>
                <article class="reel"><div class="reel-placeholder"><span class="reel-number">02</span></div><video loop playsinline preload="metadata" src="videos/reel2.mp4"></video><div class="reel-overlay"></div><div class="play-icon"></div><div class="playing">Playing</div><div class="reel-info"><span class="reel-name">Reel / 02</span><span class="reel-arrow">↗</span></div></article>
                <article class="reel"><div class="reel-placeholder"><span class="reel-number">03</span></div><video loop playsinline preload="metadata" src="videos/reel3.mp4"></video><div class="reel-overlay"></div><div class="play-icon"></div><div class="playing">Playing</div><div class="reel-info"><span class="reel-name">Reel / 03</span><span class="reel-arrow">↗</span></div></article>
                <article class="reel"><div class="reel-placeholder"><span class="reel-number">04</span></div><video loop playsinline preload="metadata" src="videos/reel4.mp4"></video><div class="reel-overlay"></div><div class="play-icon"></div><div class="playing">Playing</div><div class="reel-info"><span class="reel-name">Reel / 04</span><span class="reel-arrow">↗</span></div></article>
                <article class="reel"><div class="reel-placeholder"><span class="reel-number">05</span></div><video loop playsinline preload="metadata" src="videos/reel5.mp4"></video><div class="reel-overlay"></div><div class="play-icon"></div><div class="playing">Playing</div><div class="reel-info"><span class="reel-name">Reel / 05</span><span class="reel-arrow">↗</span></div></article>
                <article class="reel"><div class="reel-placeholder"><span class="reel-number">06</span></div><video loop playsinline preload="metadata" src="videos/reel6.mp4"></video><div class="reel-overlay"></div><div class="play-icon"></div><div class="playing">Playing</div><div class="reel-info"><span class="reel-name">Reel / 06</span><span class="reel-arrow">↗</span></div></article>
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
include 'db_connection.php';
$workConnection = new mysqli("localhost", "root", "", "glace");
$workResult = $workConnection->query("SELECT * FROM homepage_work");
if (!$workResult) {
    die("Error fetching data: " . $workConnection->error);
}
?>

<section class="index-content-sections">
    <div class="thoughts">
        <p>At Glace Media, every story is a masterpiece<br>waiting to unfold</p>
    </div>

    <div class="text-container">
        <span class="headline">
            We Craft ideas, design experiences, and <br>power growth through strategy, creativity and collaboration
        </span>
        <p class="subline">
            At our core, we connect brands to audiences through bold ideas and smart solutions!<br>
            Driven by creativity, fueled by innovation, and focused on your success.
        </p>
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
                <div class="arrows">
                    <button id="prev" style="padding-left: 20px;">&lt;</button>
                    <button id="next" style="padding-right: 20px;">&gt;</button>
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
                        <button class="btnn two">View More <i class="fa-solid fa-arrow-right" style="color: black"></i></button>
                    </div>
                </div>
            </div>
            <div class="service service2">
                <div class="headings">Campaign <br>Management</div>
                <div class="services-main-content">
                    <div class="service1-desc">Formulating Campaigns that stand apart</div>
                    <div class="service1-desc"><br><br><br><br><br>
                        <button class="btnn two">View More <i class="fa-solid fa-arrow-right" style="color: black"></i></button>
                    </div>
                </div>
            </div>
            <div class="service service3">
                <div class="headings">Videography</div>
                <div class="services-main-content">
                    <div class="service1-desc">From script to screen, we do it all!</div>
                    <div class="service1-desc"><br><br><br><br><br>
                        <button class="btnn two">View More <i class="fa-solid fa-arrow-right" style="color: black"></i></button>
                    </div>
                </div>
            </div>
            <div class="service service4">
                <div class="headings">Social Media <br>Marketing</div>
                <div class="services-main-content">
                    <div class="service1-desc">Our creative strategies turn feeds into brand powerhouses.</div>
                    <div class="service1-desc"><br><br><br><br><br>
                        <button class="btnn two">View More <i class="fa-solid fa-arrow-right" style="color: black"></i></button>
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
                        <div class="info"><span class="info-number">01</span>Instagram's original filters were named after <em>alcoholic drinks.</em></div>
                        <span class="separator">✦</span>
                        <div class="info"><span class="info-number">02</span>The first influencer was likely <em>Cindy Margolis.</em></div>
                        <span class="separator">✦</span>
                        <div class="info"><span class="info-number">03</span>Stories transformed disappearing content into a <em>daily habit.</em></div>
                        <span class="separator">✦</span>
                    </div>
                    <div class="set" aria-hidden="true">
                        <div class="info"><span class="info-number">01</span>Instagram's original filters were named after <em>alcoholic drinks.</em></div>
                        <span class="separator">✦</span>
                        <div class="info"><span class="info-number">02</span>The first influencer was likely <em>Cindy Margolis.</em></div>
                        <span class="separator">✦</span>
                        <div class="info"><span class="info-number">03</span>Stories transformed disappearing content into a <em>daily habit.</em></div>
                        <span class="separator">✦</span>
                    </div>
                </div>
            </div>
            <div class="marquee secondary">
                <div class="track right">
                    <div class="set">
                        <div class="info"><span class="info-number">04</span>The internet transformed brands into <em>communities.</em></div><span class="separator">✦</span>
                        <div class="info"><span class="info-number">05</span>Great typography can change the <em>personality</em> of a website.</div><span class="separator">✦</span>
                        <div class="info"><span class="info-number">06</span>Motion gives interfaces a feeling of <em>continuity.</em></div><span class="separator">✦</span>
                        <div class="info"><span class="info-number">07</span>Simple design often creates the strongest <em>impression.</em></div><span class="separator">✦</span>
                    </div>
                    <div class="set" aria-hidden="true">
                        <div class="info"><span class="info-number">04</span>The internet transformed brands into <em>communities.</em></div><span class="separator">✦</span>
                        <div class="info"><span class="info-number">05</span>Great typography can change the <em>personality</em> of a website.</div><span class="separator">✦</span>
                        <div class="info"><span class="info-number">06</span>Motion gives interfaces a feeling of <em>continuity.</em></div><span class="separator">✦</span>
                        <div class="info"><span class="info-number">07</span>Simple design often creates the strongest <em>impression.</em></div><span class="separator">✦</span>
                    </div>
                </div>
            </div>
            <footer class="dyk-footer"><span>Keep discovering</span><span class="footer-mark"><span></span>Infinite information</span></footer>
        </section>
    </div>
</section>

<script src="script.js?v=2"></script>

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
