<?php
require_once __DIR__ . '/db_connection.php';

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

    $seedInsert = $conn->prepare('INSERT INTO founders (name, tagline, image_path, sort_order) VALUES (?, ?, ?, ?)');
    foreach ($defaultFounders as $founder) {
        [$name, $tagline, $imagePath, $sortOrder] = $founder;
        $seedInsert->bind_param('sssi', $name, $tagline, $imagePath, $sortOrder);
        $seedInsert->execute();
    }
    $seedInsert->close();
}

$founders = [];
$result = $conn->query('SELECT name, tagline, image_path FROM founders ORDER BY sort_order ASC, id ASC');
while ($row = $result->fetch_assoc()) {
    $founders[] = $row;
}

function founderText(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function founderImagePath(?string $value): string
{
    $path = trim($value ?? '');
    if ($path === '') {
        return '';
    }

    if (strpos($path, 'uploads/founders/') === 0) {
        $path = 'admin/' . $path;
    } elseif (strpos($path, '/') === false) {
        $path = 'admin/uploads/founders/' . basename($path);
    }

    return founderText($path);
}
include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Glacé — About Us</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>

:root {
    --black: #050505;
    --black2: #0b0b0b;
    --black3: #111111;

    --teal: #14a49a;
    --cream: #ebe1d3;

    --muted: rgba(235,225,211,.55);
    --border: rgba(235,225,211,.13);

    --ease: cubic-bezier(.22,1,.36,1);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    background: var(--black);
    color: var(--cream);
    font-family: "DM Sans", sans-serif;
    overflow-x: hidden;
}

body::before {
    content: "";
    position: fixed;
    inset: 0;

    pointer-events: none;

    opacity: .035;

    z-index: 9999;

    background-image:
        url("data:image/svg+xml,%3Csvg viewBox='0 0 180 180' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.8' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.8'/%3E%3C/svg%3E");
}

a {
    color: inherit;
    text-decoration: none;
}

img {
    width: 100%;
    display: block;
}

button {
    font-family: inherit;
}


/* =========================================================
   HEADER
========================================================= */

.header {
    position: fixed;
    top: 0;
    left: 0;

    width: 100%;
    height: 74px;

    padding: 0 5vw;

    display: flex;
    align-items: center;
    justify-content: space-between;

    background: rgba(5,5,5,.72);

    backdrop-filter: blur(20px);

    border-bottom: 1px solid rgba(235,225,211,.08);

    z-index: 1000;
}

.logo {
    font-family: "Manrope";
    font-size: 23px;
    font-weight: 800;

    letter-spacing: -1.5px;

    color: var(--teal);
}

.logo span {
    color: var(--cream);
    font-size: 10px;
}

.header-center {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);

    font-size: 9px;

    letter-spacing: 3px;
    text-transform: uppercase;

    color: rgba(235,225,211,.4);
}

.menu-button {
    width: 45px;
    height: 45px;

    border-radius: 50%;

    background: transparent;

    border: 1px solid var(--border);

    color: var(--cream);

    cursor: pointer;

    transition: .4s var(--ease);
}

.menu-button:hover {
    background: var(--teal);
    border-color: var(--teal);
    color: var(--black);
    transform: rotate(90deg);
}

.menu-lines {
    display: flex;
    flex-direction: column;
    gap: 5px;
    align-items: center;
}

.menu-lines span {
    width: 15px;
    height: 1px;
    background: currentColor;
}


/* =========================================================
   SIDE MENU
========================================================= */

.side-menu {
    position: fixed;

    top: 0;
    right: -420px;

    width: min(420px, 90vw);
    height: 100vh;

    padding: 120px 55px;

    background: rgba(6,6,6,.98);

    border-left: 1px solid var(--border);

    z-index: 3000;

    transition: right .7s var(--ease);
}

.side-menu.active {
    right: 0;
}

.close-menu {
    position: absolute;
    top: 25px;
    right: 35px;

    background: none;
    border: none;

    color: var(--cream);

    font-size: 28px;

    cursor: pointer;
}

.menu-label {
    color: var(--teal);

    font-size: 9px;
    text-transform: uppercase;
    letter-spacing: 3px;

    margin-bottom: 40px;
}

.side-menu nav {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.side-menu nav a {
    font-family: "Manrope";

    font-size: 40px;
    font-weight: 600;

    letter-spacing: -2px;

    transition: .3s ease;
}

.side-menu nav a:hover {
    color: var(--teal);
    transform: translateX(8px);
}


/* =========================================================
   HERO
========================================================= */

.hero {
    position: relative;
    min-height: 100vh;
    padding: 150px 5vw 80px;
    display: flex;
    align-items: center;
    isolation: isolate;
    background:
        linear-gradient(
            90deg,
            #060706 0%,
            rgba(6, 7, 6, .98) 25%,
            rgba(6, 7, 6, .90) 38%,
            rgba(6, 7, 6, .72) 48%,
            rgba(6, 7, 6, .40) 60%,
            rgba(6, 7, 6, .12) 75%,
            rgba(6, 7, 6, 0) 90%
        ),
        url("image/work1/2.JPG") 19% center / 115% auto no-repeat
        /* url("https://images.unsplash.com/photo-1758613653231-bae4e1131dde?auto=format&fit=crop&q=85&w=1600") center right / cover no-repeat; */
}

.hero-text {
    position: relative;
    z-index: 1;
    width: min(52%, 720px);
    padding-bottom: 0;
}

.eyebrow {
    display: flex;
    align-items: center;
    gap: 12px;

    color: var(--teal);

    font-size: 9px;
    text-transform: uppercase;
    letter-spacing: 3px;

    margin-bottom: 30px;
}

.eyebrow::before {
    content: "";

    width: 35px;
    height: 1px;

    background: transparent;
}

.hero h1 {
    font-family: "Manrope";
    font-size: clamp(58px, 7vw, 112px);
    line-height: .92;
    letter-spacing: -5px;
    font-weight: 700;
}

.hero h1 span {
    color: var(--teal);
}

.hero-description {
    max-width: 650px;
    margin-top: 34px;
    color: #EAE1D5;
    font-size: 16px;
    line-height: 1.75;
}

/* HERO IMAGE */

.hero-visual {
    display: none;
}

.hero-visual img {
    height: 100%;

    object-fit: cover;

    filter: saturate(.7) contrast(1.05);

    transition: transform 1.2s var(--ease);
}

.hero-visual:hover img {
    transform: scale(1.05);
}

.hero-visual::after {
    content: "";

    position: absolute;
    inset: 0;

    background:
        linear-gradient(
            90deg,
            rgba(20,164,154,.08),
            transparent 50%,
            rgba(0,0,0,.25)
        );

    pointer-events: none;
}

.hero-caption {
    position: absolute;

    bottom: 20px;
    left: 20px;

    z-index: 2;

    padding: 10px 14px;

    background: rgba(5,5,5,.6);

    backdrop-filter: blur(10px);

    border: 1px solid rgba(235,225,211,.2);

    font-size: 8px;

    letter-spacing: 2px;

    text-transform: uppercase;
}


/* =========================================================
   INTRO
========================================================= */

.intro {
    padding: 160px 5vw 130px;

    display: grid;

    grid-template-columns: .7fr 1.3fr;

    gap: 10vw;
}

.section-number {
    font-size: 9px;

    text-transform: uppercase;

    letter-spacing: 3px;

    color: var(--teal);
}

.intro h2 {
    font-family: "Manrope";

    font-size: clamp(45px, 5.5vw, 80px);

    line-height: .95;

    letter-spacing: -4px;
}

.intro h2 span {
    color: var(--teal);
}

.intro-text p {
    max-width: 650px;

    margin-top: 30px;

    color: var(--muted);

    font-size: 15px;

    line-height: 1.9;
}

.intro-text strong {
    color: var(--cream);
}

.intro-line {
    height: 1px;

    width: 100%;

    background: var(--border);

    margin-top: 55px;
}


/* =========================================================
   MARQUEE
========================================================= */

.marquee {
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);

    overflow: hidden;

    padding: 25px 0;
}

.marquee-track {
    display: flex;

    width: max-content;

    animation: marquee 25s linear infinite;
}

.marquee-item {
    display: flex;
    align-items: center;
    gap: 30px;

    padding-right: 30px;

    font-family: "Manrope";

    text-transform: uppercase;

    font-size: 12px;

    letter-spacing: 3px;

    white-space: nowrap;
}

.marquee-item span {
    color: var(--teal);

    font-size: 18px;
}

@keyframes marquee {

    from {
        transform: translateX(0);
    }

    to {
        transform: translateX(-50%);
    }

}


/* =========================================================
   STORY
========================================================= */

.story {
    padding: 10px 0;
}

.story-header {
    padding: 0 5vw;

    display: flex;
    justify-content: space-between;
    align-items: end;

    margin-bottom: 70px;
}

.story-header h2 {
    font-family: "Manrope";

    font-size: clamp(55px, 7vw, 100px);

    line-height: .85;

    letter-spacing: -6px;
}

.story-header h2 span {
    color: var(--teal);
}

.story-header p {
    max-width: 280px;

    color: rgba(235,225,211,.4);

    font-size: 11px;

    line-height: 1.7;
}


/* STORY CARD */

.story-card {
    position: sticky;

    width: 90%;

    min-height: 610px;

    margin: 0 auto 40px;

    border-radius: 30px;

    overflow: hidden;

    border: 1px solid var(--border);

    background: var(--black2);

    display: grid;

    grid-template-columns: 1fr 1fr;

    box-shadow: 0 -20px 70px rgba(0,0,0,.4);
}

.story-card:nth-child(1) {
    top: 95px;
}

.story-card:nth-child(2) {
    top: 115px;
}

.story-card:nth-child(3) {
    top: 135px;
}

.story-card:nth-child(4) {
    top: 155px;
}

.story-content {
    padding: 65px;

    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.card-number {
    color: var(--teal);

    font-size: 9px;

    letter-spacing: 3px;
}

.story-content h3 {
    font-family: "Manrope";

    font-size: clamp(40px, 4vw, 70px);

    line-height: .9;

    letter-spacing: -4px;

    margin: 35px 0;
}

.story-content p {
    max-width: 430px;

    color: var(--muted);

    font-size: 16px;

    line-height: 1.8;
}

.story-link {
    display: inline-flex;

    width: fit-content;

    margin-top: 30px;

    padding-bottom: 9px;

    border-bottom: 1px solid var(--teal);

    font-size: 9px;

    text-transform: uppercase;

    letter-spacing: 2px;

    transition: .3s ease;
}

.story-link:hover {
    color: var(--teal);
    transform: translateX(8px);
}

.story-image {
    position: relative;

    overflow: hidden;

    min-height: 610px;
}

.story-image img {
    height: 100%;

    object-fit: cover;

    filter: saturate(.65);

    transition: transform 1s var(--ease);
}

.story-card:hover .story-image img {
    transform: scale(1.06);
}

.story-image::after {
    content: "";

    position: absolute;
    inset: 0;

    background:
        linear-gradient(
            135deg,
            rgba(20,164,154,.13),
            transparent 60%
        );

    pointer-events: none;
}

.story-tag {
    position: absolute;

    bottom: 20px;
    left: 20px;

    z-index: 2;

    padding: 9px 12px;

    background: rgba(5,5,5,.6);

    backdrop-filter: blur(8px);

    /* border: 1px solid rgba(235,225,211,.15); */

    font-size: 8px;

    text-transform: uppercase;

    letter-spacing: 2px;
}

.story-card:nth-child(even) .story-content {
    order: 2;
}

.story-card:nth-child(even) .story-image {
    order: 1;
}


/* =========================================================
   IMAGE COLLAGE
========================================================= */

.visual-section {
    padding: 130px 5vw;

    border-top: 1px solid var(--border);
}

.visual-heading {
    display: grid;

    grid-template-columns: .7fr 1.3fr;

    gap: 8vw;

    margin-bottom: 70px;
}

.visual-heading h2 {
    font-family: "Manrope";

    font-size: clamp(45px, 6vw, 85px);

    line-height: .9;

    letter-spacing: -5px;
}

.visual-heading h2 span {
    color: var(--teal);
}

.visual-heading p {
    max-width: 500px;

    color: var(--muted);

    line-height: 1.8;

    font-size: 14px;
}

.visual-grid {
    display: grid;

    grid-template-columns: 1.2fr .8fr;

    gap: 18px;

    height: 720px;
}

.visual-main {
    position: relative;

    overflow: hidden;
}

.visual-side {
    display: grid;

    grid-template-rows: 1fr 1fr;

    gap: 18px;
}

.visual-item {
    position: relative;

    overflow: hidden;

    border-radius: 2px;
}

.visual-item img {
    height: 100%;

    object-fit: cover;

    filter: saturate(.65);

    transition: transform 1s var(--ease);
}

.visual-item:hover img {
    transform: scale(1.07);
}

.visual-item::after {
    content: "";

    position: absolute;
    inset: 0;

    background: linear-gradient(
        180deg,
        transparent 50%,
        rgba(5,5,5,.5)
    );
}

.visual-label {
    position: absolute;

    bottom: 20px;
    left: 20px;

    z-index: 2;

    font-size: 9px;

    letter-spacing: 2px;

    text-transform: uppercase;

    color: var(--cream);
}


/* =========================================================
   VALUES
========================================================= */

.values {
    padding: 140px 5vw;

    border-top: 1px solid var(--border);
}

.values-top {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 18px;
    margin-bottom: 75px;
    text-align: left;
}

.values h2 {
    font-family: "Manrope";

    font-size: clamp(50px, 6vw, 90px);

    line-height: .88;

    letter-spacing: -5px;
    margin: 0;
}

.values h2 span {
    color: var(--teal);
}

.values-description {
    max-width: 500px;

    margin-top: 0;

    color: var(--muted);

    line-height: 1.8;
}

.values-grid {
    display: grid;

    grid-template-columns: repeat(3,1fr);

    border-left: 1px solid var(--border);

    border-top: 1px solid var(--border);
}

.value {
    min-height: 190px;

    padding: 35px;

    border-right: 1px solid var(--border);

    border-bottom: 1px solid var(--border);

    transition: .5s var(--ease);
}

.value:hover {
    background: var(--teal);

    color: var(--black);
}

.value.active {
    background: var(--teal);

    color: var(--black);
}

.value-number {
    font-size: 30px;

    color: var(--teal);

    letter-spacing: 2px;
}

.value:hover .value-number {
    color: var(--black);
}

.value.active .value-number {
    color: var(--black);
}

.value h3 {
    margin-top: 28px;

    font-family: "Manrope";

    font-size: 27px;
}

.value p {
    margin-top: 15px;

    color: rgba(235,225,211,.45);

    font-size: 16px;

    line-height: 1.7;
}

.value:hover p {
    color: rgba(5,5,5,.65);
}

.value.active p {
    color: rgba(5,5,5,.65);
}


/* =========================================================
   FOUNDERS
========================================================= */

.founders {
    padding: 150px 5vw;
}

.founders-heading {
    display: flex;

    justify-content: space-between;

    align-items: end;

    margin-bottom: 70px;
}

.founders-heading h2 {
    font-family: "Manrope";

    font-size: clamp(50px, 6vw, 90px);

    line-height: .88;

    letter-spacing: -5px;
}

.founders-heading h2 span {
    color: var(--teal);
}

.founders-heading p {
    max-width: 330px;

    color: #EAE1D2;

    font-size: 16px;

    line-height: 1.7;
}

.founder-slider {
    max-width: 1200px;

    margin: auto;

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 18px;

    position: relative;

    height: 620px;
}

.founder {
    position: absolute;

    top: 0;

    width: 330px;

    opacity: .45;

    transform: translateX(-50%) scale(.82);

    transition:
        transform .7s cubic-bezier(.22,1,.36,1),
        opacity .7s ease,
        left .7s cubic-bezier(.22,1,.36,1),
        z-index .7s ease,
        filter .7s ease;

    z-index: 1;
}

.founder {
    cursor: default;
}

.founder:not(.active) {
    cursor: pointer;
}

.founder:not(.active):hover {
    opacity: .75;

    filter: brightness(1.08);
}

.founder:not(.active):focus-visible {
    outline: 1px solid rgba(235,225,211,.6);

    outline-offset: 4px;
}

.founder.left {
    left: 22%;

    transform: translateX(-50%) scale(.82);

    opacity: .75;

    z-index: 2;
}

.founder.center,
.founder.active {
    left: 50%;

    transform: translateX(-50%) scale(1);

    opacity: 1;

    z-index: 3;
}

.founder.right {
    left: 78%;

    transform: translateX(-50%) scale(.82);

    opacity: .75;

    z-index: 2;
}

.founder-photo {
    height: 460px;

    overflow: hidden;

    border: 1px solid var(--border);

    border-radius: 28px;
}

.founder.center .founder-photo,
.founder.active .founder-photo {
    height: 490px;
}

.founder.left .founder-photo,
.founder.right .founder-photo {
    height: 410px;
}

.founder-photo img {
    height: 100%;

    object-fit: cover;

    filter: grayscale(100%);

    transition: .6s ease;
}

.founder.center img,
.founder.active img {
    filter: grayscale(0) saturate(.8);
}

.founder-name {
    padding-top: 18px;

    text-align: center;

    opacity: .6;

    transition: .6s ease;
}

.founder.center .founder-name,
.founder.active .founder-name {
    opacity: 1;
}

.founder-name h3 {
    font-family: "Manrope";
    color: #16A39A;
    font-size: 28px;
}

.founder-name p {
    margin-top: 5px;

    color: #EAE1D2;

    font-size: 16px;
}

.slider-btn {
    position: absolute;

    top: 180px;

    width: 50px;
    height: 50px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    border: 1px solid var(--border);

    background:#14A49A;;

    color: var(--cream);

    cursor: pointer;

    transition: .3s ease;

    z-index: 10;
}

#prev {
    left: -2%;
}

#next {
    right: -2%;
}

.slider-btn span {
    display: inline-block;
    transition: transform .3s ease;
}

.slider-btn:hover {
    border-color: rgba(235,225,211,.6);
}

#prev:hover span {
    transform: translateX(-2px);
}

#next:hover span {
    transform: translateX(2px);
}


/* =========================================================
   CTA
========================================================= */

.cta {
    position: relative;

    margin: 30px 5vw 150px;

    padding: 100px 7vw;

    overflow: hidden;

    border-radius: 30px;

    background: var(--teal);

    color: var(--black);
}

.cta::after {
    content: "GLACÉ";

    position: absolute;

    right: -30px;
    bottom: -100px;

    font-family: "Manrope";

    font-size: 190px;

    font-weight: 800;

    color: rgba(5,5,5,.08);
}

.cta-small {
    font-size: 20px;
    font-weight: 600;
    text-transform: uppercase;

    letter-spacing: 3px;
}

.cta h2 {
    position: relative;

    z-index: 2;

    max-width: 1850px;

    margin-top: 30px;

    font-family: "Manrope";

    font-size: clamp(50px,7vw,105px);

    line-height: .85;

    letter-spacing: -6px;
}

.cta-button {
    position: relative;

    z-index: 3;

    display: inline-flex;

    gap: 20px;

    margin-top: 50px;

    padding: 16px 23px;

    border-radius: 50px;

    border: 1px solid rgba(5,5,5,.45);

    font-size: 9px;

    text-transform: uppercase;

    letter-spacing: 2px;

    transition: .3s ease;
}

.cta-button:hover {
    background: var(--black);

    color: var(--cream);
}


/* =========================================================
   FOOTER
========================================================= */

footer {
    padding: 70px 5vw 30px;

    border-top: 1px solid var(--border);
}

.footer-top {
    display: flex;

    justify-content: space-between;

    margin-bottom: 100px;
}

.footer-logo {
    font-family: "Manrope";

    font-size: 42px;

    font-weight: 800;

    letter-spacing: -3px;

    color: var(--teal);
}

.footer-links {
    display: flex;

    gap: 70px;
}

.footer-column {
    display: flex;

    flex-direction: column;

    gap: 12px;
}

.footer-column span {
    margin-bottom: 8px;

    color: rgba(235,225,211,.3);

    font-size: 8px;

    text-transform: uppercase;

    letter-spacing: 2px;
}

.footer-column a {
    color: rgba(235,225,211,.6);

    font-size: 11px;

    transition: .3s ease;
}

.footer-column a:hover {
    color: var(--teal);
}

.footer-bottom {
    padding-top: 20px;

    border-top: 1px solid var(--border);

    display: flex;

    justify-content: space-between;

    color: rgba(235,225,211,.25);

    font-size: 8px;
}


/* =========================================================
   REVEAL
========================================================= */

.reveal {
    opacity: 0;

    transform: translateY(35px);

    transition:
        opacity 1s var(--ease),
        transform 1s var(--ease);
}

.reveal.visible {
    opacity: 1;

    transform: translateY(0);
}


/* =========================================================
   MOBILE
========================================================= */

@media(max-width:900px) {

    .header-center {
        display: none;
    }

    .hero { padding: 130px 6vw 70px; }

    .intro,
    .visual-heading,
    .values-top {
        grid-template-columns: 1fr;

        gap: 40px;
    }

    .story-card {
        width: 94%;

        grid-template-columns: 1fr;
    }

    .story-card:nth-child(even) .story-content {
        order: 1;
    }

    .story-card:nth-child(even) .story-image {
        order: 2;
    }

    .story-image {
        min-height: 430px;
    }

    .visual-grid {
        height: 650px;
    }

    .values-grid {
        grid-template-columns: 1fr 1fr;
    }

    .founder:not(.active) {
        display: none;
    }

    .footer-top {
        flex-direction: column;

        gap: 50px;
    }
}

@media(max-width:767px) {

    .values-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        border: 0;
    }

    .value {
        min-height: 0;
        padding: 22px 16px;
        border: 1px solid var(--border);
    }

    .value-number {
        font-size: 20px;
    }

    .value h3 {
        margin-top: 18px;
        font-size: 20px;
    }

    .value p {
        margin-top: 10px;
        font-size: 13px;
        line-height: 1.5;
    }
}


@media(max-width:600px) {

    .header {
        height: 65px;

        padding: 0 20px;
    }

    .hero {
        min-height: 100svh;
        padding: 110px 20px 55px;
        align-items: flex-end;
        background:
            linear-gradient(
                180deg,
                rgba(6, 7, 6, .92) 0%,
                rgba(6, 7, 6, .82) 35%,
                rgba(6, 7, 6, .42) 68%,
                rgba(6, 7, 6, .96) 100%
            );
    }

    .hero-text {
        width: 100%;
    }

    .hero h1 {
        font-size: clamp(46px, 13vw, 68px);
        letter-spacing: -3px;
    }

    .hero-description {
        margin-top: 26px;
        font-size: 14px;
        line-height: 1.7;
    }

    .intro {
        padding: 100px 20px;
    }

    .story {
        padding: 10px 0;
    }

    .story-header {
        padding: 0 20px;

        display: block;
    }

    .story-header p {
        margin-top: 25px;
    }

    .story-card {
        width: calc(100% - 30px);

        border-radius: 22px;
    }

    .story-content {
        padding: 38px 28px;
    }

    .story-content h3 {
        font-size: 42px;
    }

    .story-image {
        min-height: 350px;
    }

    .visual-section {
        padding: 100px 20px;
    }

    .visual-grid {
        height: 650px;

        grid-template-columns: 1fr;

        grid-template-rows: 1.2fr 1fr;
    }

    .visual-side {
        grid-template-columns: 1fr 1fr;
        grid-template-rows: 1fr;
    }

    .values {
        padding: 100px 20px;
    }

    .values-grid {
        grid-template-columns: 1fr;
    }

    .founders {
        padding: 100px 20px;
    }

    .founders-heading {
        display: block;
    }

    .founders-heading p {
        margin-top: 25px;
    }

    .founder-photo {
        height: 400px;
    }

    .founder {
        width: 100%;
    }

    .cta {
        margin: 20px 15px 100px;

        padding: 65px 30px;
    }

    .cta h2 {
        font-size: 55px;

        letter-spacing: -3px;
    }

    .footer-links {
        gap: 35px;
    }

    .footer-bottom {
        flex-direction: column;

        gap: 15px;
    }
}

</style>

</head>


<body>


<!-- =========================================================
     HERO
========================================================= -->

<section class="hero">

    <div class="hero-text reveal">

        <div class="eyebrow">
            
        </div>

        <h1>
            Your Growth,<br>
            <span>Our Goal.</span>
        </h1>

        <p class="hero-description">
            We help brands build high-impact campaigns, eye-catchy content and meaningful digital experiences that connect with the right audiences.
        </p>
        <p class="hero-description">
            Our team of creative experts understands that every brand is unique, which is why we work closely with our clients to develop customised strategies aligned with their specific needs, objectives and vision.
        </p>
        <p class="hero-description">
            From Social media and Influencer marketing to Campaign planning and Video production, we bring together the right creative expertise to build communication that feels distinct, relevant and purposeful. We don’t believe in one-size-fits-all solutions; we create strategies and ideas built around your brand, your audience and your goals. Our approach combines strategic ideation with creative execution, ensuring every campaign has a clear purpose, every piece of content has a role to play and every idea contributes to the larger brand story.
        </p>

    </div>


    <div class="hero-visual reveal">

        <img
            src="https://images.unsplash.com/photo-1758613653231-bae4e1131dde?auto=format&fit=crop&q=85&w=1600"
            alt="Creative photography production"
        >

        <div class="hero-caption">
            Behind the scenes / Glacé
        </div>

    </div>

</section>



<!-- =========================================================
     INTRO
========================================================= -->

<!-- <section class="intro" id="about">

    <div class="section-number reveal">
        01 / Who we are
    </div>

    <div class="intro-text reveal">

        <h2>
            Not just content.
            <span>A point of view.</span>
        </h2>

        <div class="intro-line"></div>

        <p>
            At Glacé Media, we help brands create high-impact
            campaigns that actually mean something.
        </p>

        <p>
            Every brand has a personality.
            We understand it, shape it and turn it into
            content people want to watch, remember and share.
        </p>

        <p>
            Strategy, social media, production and storytelling —
            everything works together to create one clear
            visual identity.
        </p>

    </div>

</section> -->



<!-- =========================================================
     MARQUEE
========================================================= -->

<!-- <div class="marquee">

    <div class="marquee-track">

        <div class="marquee-item">
            Strategy
            <span>✦</span>
            Storytelling
            <span>✦</span>
            Social
            <span>✦</span>
            Production
            <span>✦</span>
            Culture
            <span>✦</span>
        </div>

        <div class="marquee-item">
            Strategy
            <span>✦</span>
            Storytelling
            <span>✦</span>
            Social
            <span>✦</span>
            Production
            <span>✦</span>
            Culture
            <span>✦</span>
        </div>

    </div>

</div> -->



<!-- =========================================================
     STORY CARDS
========================================================= -->

<section class="story" id="story">

    <div class="story-header reveal">

        <h2>
            Why Glacé?<br>
            <span></span>
        </h2>

        <p>
            <!-- Scroll through the ideas, people and
            culture that shape the way we create. -->
        </p>

    </div>


    <!-- CARD 1 -->

    <article class="story-card">

        <div class="story-content">

            <div>

                <div class="card-number">
                    <!-- 01 -->
                </div>

                <h3>
                    What Drives Us<br>
                    <!-- with an idea. -->
                </h3>

                <p>
                    We’re driven by the opportunity to make brands better, not simply busier. Every idea, campaign and piece of content should contribute to something bigger: stronger positioning, deeper audience connection and meaningful growth. Your vision sets the direction; our job is to turn it into content that moves your brand forward.
                </p>

            </div>

            <a href="#" class="story-link">
                Discover our story →
            </a>

        </div>


        <div class="story-image">

            <img
                src="https://images.unsplash.com/photo-1771072426459-1ab467cd80f0?auto=format&fit=crop&q=85&w=1600"
                alt="Editorial fashion campaign"
            >

            <div class="story-tag">
                <!-- Editorial / 01 -->
            </div>

        </div>

    </article>



    <!-- CARD 2 -->

    <article class="story-card">

        <div class="story-content">

            <div>

                <div class="card-number">
                    <!-- 02 -->
                </div>

                <h3>
                    Where We Focus<br>
                    <!-- Create better. -->
                </h3>

                <p>
                    Your growth is at the centre of everything we do. We analyse your brand, audience, category and existing presence to understand what is working, what can be improved and where the opportunities lies. From positioning and communication to content and campaigns, we find the right ways to make your brand stand out and move forward.

                </p>

            </div>

            <a href="#" class="story-link">
                <!-- How we work → -->
            </a>

        </div>


        <div class="story-image">

            <img
                src="https://images.unsplash.com/photo-1781606424661-de1dc2b4c6e1?auto=format&fit=crop&q=85&w=1600"
                alt="Social media content creation"
            >

            <div class="story-tag">
                <!-- Social / 02 -->
            </div>

        </div>

    </article>



    <!-- CARD 3 -->

    <article class="story-card">

        <div class="story-content">

            <div>

                <div class="card-number">
                    <!-- 03 -->
                </div>

                <h3>
                    Our Process<br>
                    <!-- language. -->
                </h3>

                <p>
                   Every project begins with a detailed briefing to understand your vision, objectives and requirements. Moving into research, strategy and ideation, developing concepts and moodboards that establish the creative direction before execution begins. Our highly skilled strategists, creatives, designers, creators and production teams then bring the idea to life, refining every detail to deliver a final product that is cohesive and ready to perform.
                </p>

            </div>

            <a href="#" class="story-link">
                <!-- Explore our thinking → -->
            </a>

        </div>


        <div class="story-image">

            <img
                src="https://images.unsplash.com/photo-1786325492193-bb05874e01b9?auto=format&fit=crop&q=85&w=1600"
                alt="Creative studio"
            >

            <div class="story-tag">
                <!-- Studio / 03 -->
            </div>

        </div>

    </article>



    <!-- CARD 4 -->

    <article class="story-card">

        <div class="story-content">

            <div>

                <div class="card-number">
                    <!-- 04 -->
                </div>

                <h3>
                    Our Expertise<br>
                    <!-- remember. -->
                </h3>

                <p>
                  We expertise in the FMCG, F&B and Hospitality categories, with a significant part of our work coming from these sectors. We have a strong understanding of their audiences, trends and competitive landscapes, what captures attention, what creates connection and what simply doesn't work. We use this experience to build smarter and more relevant strategies.
                </p>

            </div>

            <a href="#" class="story-link">
                <!-- Work with us → -->
            </a>

        </div>


        <div class="story-image">

            <img
                src="https://images.unsplash.com/photo-1753094881961-1e56e8e17a68?auto=format&fit=crop&q=85&w=1600"
                alt="Fashion photoshoot"
            >

            <div class="story-tag">
                <!-- Campaign / 04 -->
            </div>

        </div>

    </article>

</section>



<!-- =========================================================
     VISUAL COLLAGE
========================================================= -->

<!-- <section class="visual-section" id="work">

    <div class="visual-heading reveal">

        <div class="section-number">
            02 / In the studio
        </div>

        <div>

            <h2>
                Ideas become<br>
                <span>visual.</span>
            </h2>

            <p>
                From the first moodboard to the final frame,
                we're obsessed with making every visual
                feel intentional.
            </p>

        </div>

    </div>


    <div class="visual-grid reveal">

        <div class="visual-item visual-main">

            <img
                src="https://images.unsplash.com/photo-1758613653231-bae4e1131dde?auto=format&fit=crop&q=85&w=1800"
                alt="Photographer working with model"
            >

            <div class="visual-label">
                Art direction / 001
            </div>

        </div>


        <div class="visual-side">

            <div class="visual-item">

                <img
                    src="https://images.unsplash.com/photo-1781606424661-de1dc2b4c6e1?auto=format&fit=crop&q=85&w=1000"
                    alt="Social media production"
                >

                <div class="visual-label">
                    Social / 002
                </div>

            </div>


            <div class="visual-item">

                <img
                    src="https://images.unsplash.com/photo-1786325492193-bb05874e01b9?auto=format&fit=crop&q=85&w=1000"
                    alt="Creative studio space"
                >

                <div class="visual-label">
                    Production / 003
                </div>

            </div>

        </div>

    </div>

</section> -->



<!-- =========================================================
     VALUES
========================================================= -->

<section class="values">

    <div class="values-top">

        <div class="section-number">
            <!-- 03 / What drives us -->
        </div>

        <div>

            <h2>
                What we bring to
                the <span>table.</span> 
            </h2>

            <p class="values-description">
                <br>
                <!-- The work changes every day.
                The principles behind it don't. -->
            </p>

        </div>

    </div>


    <div class="values-grid reveal">

        <div class="value">

            <div class="value-number">01</div>

            <h3>Creativity</h3>

            <p>
               We make the familiar feel fresh.
            </p>

        </div>


        <div class="value">

            <div class="value-number">02</div>

            <h3>Craft</h3>

            <p>
                We believe great work lives in the details.
            </p>

        </div>


        <div class="value">

            <div class="value-number">03</div>

            <h3>Perspective</h3>

            <p>
                We bring a wider lens to every brief.
            </p>

        </div>


        <div class="value">

            <div class="value-number">04</div>

            <h3>Impact</h3>

            <p>
                Every campaign should leave something behind.
            </p>

        </div>


        <div class="value">

            <div class="value-number">05</div>

            <h3>Purpose</h3>

            <p>
                Every idea needs a reason to exist.
            </p>

        </div>


        <div class="value">

            <div class="value-number">06</div>

            <h3>Growth</h3>

            <p>
                We create with the bigger picture in mind.
            </p>

        </div>

    </div>

</section>



<!-- =========================================================
     FOUNDERS
========================================================= -->

<section class="founders">

    <div class="founders-heading">

        <div>

            <div class="section-number">
                <!-- 04 / The people -->
            </div>

            <h2>
                Meet the<br>
                <span>founders.</span>
            </h2>

        </div>

        <p>
            The people behind the ideas,
            cameras, strategy and questionable
            coffee decisions.
        </p>

    </div>


    <div class="founder-slider">

        <button class="slider-btn" id="prev" aria-label="Previous founder">
            <span>←</span>
        </button>

        <?php foreach ($founders as $index => $founder): ?>
            <div class="founder" data-founder="<?= founderText($founder['name']) ?>" tabindex="<?= $index === 1 ? '-1' : '0' ?>" role="button" aria-label="Activate <?= founderText($founder['name']) ?> founder">

                <div class="founder-photo">
                    <img src="<?= founderImagePath($founder['image_path']) ?>" alt="<?= founderText($founder['name']) ?> founder">
                </div>

                <div class="founder-name">
                    <h3><?= founderText($founder['name']) ?></h3>
                    <p><?= founderText($founder['tagline']) ?></p>
                </div>

            </div>
        <?php endforeach; ?>

        <button class="slider-btn" id="next" aria-label="Next founder">
            <span>→</span>
        </button>

    </div>

</section>



<!-- =========================================================
     CTA
========================================================= -->

<section class="cta" id="contact">

    <div class="cta-small">
        GOT A VISION?
    </div>

    <h2>
        Let's bring it to Life.
    </h2>

    <a href="contact-us.php" class="cta-button">
        Get in Touch
        <span>↗</span>
    </a>

</section>



<script>

/* =========================================================
   SCROLL REVEAL
========================================================= */

const observer =
    new IntersectionObserver(
        entries => {

            entries.forEach(entry => {

                if (entry.isIntersecting) {

                    entry.target
                        .classList
                        .add("visible");

                    observer
                        .unobserve(entry.target);
                }

            });

        },
        {
            threshold: .12
        }
    );

document
    .querySelectorAll(".reveal")
    .forEach(el => observer.observe(el));



/* =========================================================
   FOUNDER SLIDER
========================================================= */

const founders =
    Array.from(document.querySelectorAll(".founder"));

let founderNames = Array.from(document.querySelectorAll('.founder')).map((card) => card.dataset.founder);
let founderState = {
    left: founderNames[0] || '',
    center: founderNames[1] || founderNames[0] || '',
    right: founderNames[2] || founderNames[0] || ''
};

let isAnimating = false;

function renderFounderState() {

    founders.forEach(founder => {

        const founderName = founder.dataset.founder;
        let slot = "center";

        if (founderState.left === founderName) {
            slot = "left";
        } else if (founderState.right === founderName) {
            slot = "right";
        }

        founder.classList.remove("left", "center", "right", "active");
        founder.classList.add(slot);

        if (founderState.center === founderName) {
            founder.classList.add("active");
            founder.tabIndex = -1;
            founder.setAttribute("aria-hidden", "true");
            founder.setAttribute("role", "presentation");
        } else {
            founder.tabIndex = 0;
            founder.setAttribute("aria-hidden", "false");
            founder.setAttribute("role", "button");
        }

    });

}

function moveCarousel(direction) {

    if (isAnimating) {
        return;
    }

    isAnimating = true;

    const nextState =
        direction === 1
            ? {
                left: founderState.center,
                center: founderState.right,
                right: founderState.left
            }
            : {
                left: founderState.right,
                center: founderState.left,
                right: founderState.center
            };

    founderState = nextState;
    renderFounderState();

    setTimeout(() => {
        isAnimating = false;
    }, 700);

}

document
    .getElementById("next")
    .onclick = () => moveCarousel(1);

document
    .getElementById("prev")
    .onclick = () => moveCarousel(-1);

founders.forEach(founder => {

    if (founderState.center === founder.dataset.founder) {
        founder.tabIndex = -1;
        founder.setAttribute("aria-hidden", "true");
        return;
    }

    founder.tabIndex = 0;
    founder.setAttribute("aria-hidden", "false");

    founder.onclick = () => {
        const name = founder.dataset.founder;

        if (founderState.center === name) {
            return;
        }

        if (founderState.left === name) {
            moveCarousel(-1);
        } else if (founderState.right === name) {
            moveCarousel(1);
        }
    };

    founder.onkeydown = event => {
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            founder.click();
        }
    };

});

renderFounderState();



/* =========================================================
   MOBILE VALUES FOCUS
========================================================= */

const mobileValuesQuery = window.matchMedia("(max-width: 767px)");
const valuesSection = document.querySelector(".values");
const valueCards = Array.from(document.querySelectorAll(".values-grid .value"));
let valuesFocusFrame = 0;

function updateMobileValuesFocus() {
    valuesFocusFrame = 0;

    if (!mobileValuesQuery.matches || !valuesSection || valueCards.length === 0) {
        valueCards.forEach(card => card.classList.remove("active"));
        return;
    }

    const sectionRect = valuesSection.getBoundingClientRect();
    const viewportCenter = window.innerHeight / 2;

    if (sectionRect.bottom <= 0 || sectionRect.top >= window.innerHeight) {
        valueCards.forEach(card => card.classList.remove("active"));
        return;
    }

    let closestCard = null;
    let closestDistance = Infinity;

    valueCards.forEach(card => {
        const rect = card.getBoundingClientRect();

        if (rect.bottom <= 0 || rect.top >= window.innerHeight) {
            return;
        }

        const distance = Math.abs((rect.top + rect.bottom) / 2 - viewportCenter);

        if (distance < closestDistance) {
            closestDistance = distance;
            closestCard = card;
        }
    });

    valueCards.forEach(card => {
        card.classList.toggle("active", card === closestCard);
    });
}

function scheduleMobileValuesFocus() {
    if (!valuesFocusFrame) {
        valuesFocusFrame = window.requestAnimationFrame(updateMobileValuesFocus);
    }
}

window.addEventListener("scroll", scheduleMobileValuesFocus, { passive: true });
window.addEventListener("resize", scheduleMobileValuesFocus);
mobileValuesQuery.addEventListener("change", scheduleMobileValuesFocus);
scheduleMobileValuesFocus();



/* =========================================================
   IMAGE PARALLAX
========================================================= */

window.addEventListener("scroll", () => {

    document
        .querySelectorAll(".story-image img")
        .forEach(img => {

            const parent =
                img.closest(".story-image");

            const rect =
                parent.getBoundingClientRect();

            const offset =
                (rect.top -
                window.innerHeight / 2) * -.025;

            img.style.transform =
                `scale(1.04) translateY(${offset}px)`;

        });

});

</script>

    <?php include 'footer.php'; ?>
</body>
</html>