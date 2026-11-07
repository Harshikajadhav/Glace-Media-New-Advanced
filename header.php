<?php
    if (!isset($conn)) {
        include_once 'db_connection.php';
    }

    $currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');
    $newsData = [];

    if (isset($conn) && $conn instanceof mysqli) {
        mysqli_query($conn, "CREATE TABLE IF NOT EXISTS news (id INT UNSIGNED NOT NULL AUTO_INCREMENT, news_content TEXT NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $newsResult = mysqli_query($conn, "SELECT news_content FROM news ORDER BY id ASC");

        if ($newsResult instanceof mysqli_result) {
            while ($newsRow = mysqli_fetch_assoc($newsResult)) {
                if (!empty($newsRow['news_content'])) {
                    $newsData[] = $newsRow['news_content'];
                }
            }
        }
    }

    if (!$newsData) {
        $newsData[] = 'Follow us on Instagram - @glace.media';
    }
?>

<style>
    html,
    body {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    html::-webkit-scrollbar,
    body::-webkit-scrollbar {
        display: none;
    }

    .glace-header,
    .glace-header * {
        box-sizing: border-box;
    }

    .glace-header {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
        width: 100%;
        padding: 0 5.8vw;
        color: #ebe1d3;
        font-family: Helvetica, Arial, sans-serif;
        isolation: isolate;
        transform: translateY(0);
        transition: transform .65s cubic-bezier(.22, 1, .36, 1);
        will-change: transform;
    }

    .glace-header.is-hidden {
        transform: translateY(-110%);
    }

    .glace-header::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
        border-bottom: 1px solid rgba(255, 255, 255, .08);
        background: linear-gradient(180deg, rgba(12, 38, 31, .76), rgba(7, 25, 21, .68));
        backdrop-filter: blur(16px) saturate(120%);
        -webkit-backdrop-filter: blur(16px) saturate(120%);
    }

    .glace-header-inner {
        position: relative;
        width: 100%;
        max-width: 1600px;
        min-height: 94px;
        margin: 0 auto;
    }

    .glace-header-announcement {
        min-height: 30px;
        margin-top:20px;
        padding-bottom: 10px;
        border-bottom: 1px solid rgba(235, 225, 211, .12);
        color: rgba(235, 225, 211, .72);
        font-size: 11px;
        letter-spacing: 1.8px;
        text-align: center;
        text-transform: uppercase;
        transition: transform .45s ease, opacity .45s ease;
    }

    .glace-header-announcement.flip {
        opacity: 0;
        transform: translateY(-8px);
    }

    .glace-header-main {
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 82px;
    }

    .glace-brand {
        display: inline-flex;
        align-items: center;
        min-width: 155px;
        color: #14a49a;
        text-decoration: none;
    }

    .glace-brand img {
        display: block;
        width: 116px;
        height: auto;
        filter: contrast(1.08) saturate(1.1);
    }

    .glace-nav {
        display: flex;
        align-items: center;
        gap: clamp(28px, 4vw, 58px);
    }

    .glace-nav-links {
        display: flex;
        align-items: center;
        gap: clamp(25px, 3.5vw, 54px);
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .glace-nav a {
        position: relative;
        padding: 12px 0;
        color: rgba(246, 241, 232, .76);
        font-size: 16px;
        text-decoration: none;
        transition: color .25s ease;
    }

    .glace-nav-links a::after {
        content: "";
        position: absolute;
        right: 0;
        bottom: 3px;
        left: 0;
        height: 1px;
        background: #14a49a;
        transform: scaleX(0);
        transform-origin: right;
        transition: transform .3s ease;
    }

    .glace-nav-links a:hover,
    .glace-nav-links a.is-active {
        color: #fff;
    }

    .glace-nav-links a:hover::after,
    .glace-nav-links a.is-active::after {
        transform: scaleX(1);
        transform-origin: left;
    }

    .glace-contact {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 155px;
        min-height: 48px;
        padding: 0 24px;
        border: 1px solid #14a49a;
        border-radius: 999px;
        color: #14a59b !important;
        font-size: 16px;
        text-decoration: none;
        overflow: hidden;
        transition: color .3s ease, box-shadow .3s ease;
    }

    .glace-contact::before {
        position: absolute;
        top: var(--contact-y, 50%);
        left: var(--contact-x, 50%);
        width: 0;
        height: 0;
        border-radius: 50%;
        background: #14a59b;
        content: "";
        transform: translate(-50%, -50%);
        transition: width .55s cubic-bezier(.4, 0, .2, 1), height .55s cubic-bezier(.4, 0, .2, 1);
        z-index: 0;
    }

    .glace-contact span {
        position: relative;
        z-index: 1;
        transition: color .25s ease;
    }

    .glace-contact svg {
        position: relative;
        z-index: 1;
        width: 18px;
        height: 18px;
        margin-left: 8px;
        color: inherit;
        fill: currentColor;
        flex: 0 0 auto;
        transition: color .25s ease;
    }

    .glace-contact.is-hovered::before {
        width: 350%;
        height: 350%;
    }

    .glace-contact.is-hovered span {
        color: #03100e;
    }

    .glace-contact:hover {
        color: #03100e !important;
        box-shadow: 0 0 28px rgba(20, 164, 154, .26);
    }

    .glace-menu-toggle {
        display: none;
        width: 42px;
        height: 42px;
        padding: 0;
        border: 1px solid rgba(20, 164, 154, .65);
        border-radius: 50%;
        background: transparent;
        color: #ebe1d3;
        cursor: pointer;
    }

    .glace-menu-toggle span,
    .glace-menu-toggle span::before,
    .glace-menu-toggle span::after {
        display: block;
        width: 16px;
        height: 1px;
        margin: auto;
        background: currentColor;
        content: "";
    }

    .glace-menu-toggle span::before { transform: translateY(-5px); }
    .glace-menu-toggle span::after { transform: translateY(4px); }

    .glace-sidebar {
        position: fixed;
        inset: 0 0 0 auto;
        z-index: 1001;
        display: flex;
        align-items: center;
        width: min(82vw, 360px);
        padding: 32px;
        border-left: 1px solid rgba(20, 164, 154, .32);
        background: rgba(4, 12, 11, .94);
        box-shadow: -20px 0 60px rgba(0, 0, 0, .3);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        transform: translateX(105%);
        transition: transform .45s cubic-bezier(.22, 1, .36, 1);
    }

    .glace-sidebar.is-open { transform: translateX(0); }

    .glace-sidebar-nav {
        display: flex;
        flex-direction: column;
        gap: 24px;
        width: 100%;
    }

    .glace-sidebar-close {
        align-self: flex-end;
        width: 38px;
        height: 38px;
        margin-bottom: 28px;
        border: 1px solid rgba(235, 225, 211, .35);
        border-radius: 50%;
        background: transparent;
        color: #ebe1d3;
        cursor: pointer;
        font-size: 24px;
        line-height: 1;
    }

    .glace-sidebar a {
        color: #ebe1d3;
        font-size: 24px;
        text-decoration: none;
    }

    .glace-sidebar a:hover { color: #14a49a; }

    @media (max-width: 760px) {
        .glace-header { padding: 0 18px; }
        .glace-header-inner { min-height: 72px; }
        .glace-header-announcement { font-size: 9px; letter-spacing: 1.2px; }
        .glace-header-main { min-height: 66px; }
        .glace-brand { min-width: 0; }
        .glace-brand img { width: 94px; }
        .glace-nav-links,
        .glace-contact { display: none; }
        .glace-menu-toggle { display: block; }

        .glace-sidebar {
            inset: 0;
            align-items: stretch;
            justify-content: flex-end;
            width: 100%;
            padding: 0;
            border: 0;
            background: rgba(0, 0, 0, .48);
            box-shadow: none;
            opacity: 0;
            pointer-events: none;
            transform: none;
            transition: opacity .35s ease, visibility .35s ease;
            visibility: hidden;
        }

        .glace-sidebar.is-open {
            opacity: 1;
            pointer-events: auto;
            transform: none;
            visibility: visible;
        }

        .glace-sidebar-nav {
            position: relative;
            display: flex;
            align-items: stretch;
            width: 100%;
            height: 100vh;
            height: 100dvh;
            margin: 0;
            gap: 0;
            padding: 30px 24px 24px;
            overflow: hidden;
            border-left: 1px solid rgba(20, 164, 154, .22);
            border-radius: 0;
            background:
                radial-gradient(circle at 20% 5%, rgba(20, 164, 154, .12), transparent 34%),
                linear-gradient(145deg, #061c18 0%, #020b0a 58%, #010505 100%);
            box-shadow: -24px 0 70px rgba(0, 0, 0, .4);
            transform: translateX(105%);
            transition: transform .48s cubic-bezier(.22, 1, .36, 1);
        }

        .glace-sidebar.is-open .glace-sidebar-nav {
            transform: translateX(0);
        }

        .glace-sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 64px;
            padding-bottom: 22px;
            border-bottom: 1px solid rgba(235, 225, 211, .12);
        }

        .glace-sidebar-brand img {
            display: block;
            width: 150px;
            height: auto;
        }

        .glace-sidebar-close {
            display: grid;
            place-items: center;
            width: 44px;
            height: 44px;
            margin: 0;
            border: 1px solid rgba(20, 164, 154, .65);
            color: #ebe1d3;
            font-size: 30px;
            font-weight: 300;
            line-height: 1;
            transition: background .25s ease, color .25s ease, transform .25s ease;
        }

        .glace-sidebar-close:hover,
        .glace-sidebar-close:focus-visible {
            background: #14a49a;
            color: #03100e;
            transform: rotate(90deg);
        }

        .glace-sidebar-links {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding-top: 44px;
        }

        .glace-sidebar-links a {
            display: flex;
            align-items: center;
            gap: 22px;
            min-height: 68px;
            padding: 0 18px;
            border-left: 3px solid transparent;
            border-radius: 14px;
            color: rgba(235, 225, 211, .9);
            font-size: 22px;
            text-decoration: none;
            transition: background .25s ease, border-color .25s ease, color .25s ease, transform .25s ease;
        }

        .glace-sidebar-links a:hover,
        .glace-sidebar-links a:focus-visible,
        .glace-sidebar-links a.is-active {
            border-left-color: #14a49a;
            background: rgba(20, 164, 154, .18);
            color: #fff;
            transform: translateX(3px);
        }

        .glace-sidebar-links svg {
            width: 28px;
            height: 28px;
            flex: 0 0 28px;
            fill: none;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 1.7;
        }

        .glace-sidebar-footer {
            margin-top: auto;
            padding-top: 22px;
            border-top: 1px solid rgba(235, 225, 211, .12);
            color: rgba(235, 225, 211, .48);
            font-size: 11px;
            letter-spacing: .2px;
            text-align: center;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .glace-header,
        .glace-sidebar,
        .glace-header-announcement { transition-duration: .01ms; }
    }
</style>

<header class="glace-header" aria-label="Main navigation">
    <div class="glace-header-inner">
        <div class="glace-header-announcement" id="glace-announcement">
            <?= htmlspecialchars($newsData[0], ENT_QUOTES, 'UTF-8') ?>
        </div>

        <div class="glace-header-main">
            <a class="glace-brand" href="index.php" aria-label="Glace Media home">
                <img src="image/logo.png" alt="Glace Media">
            </a>

            <nav class="glace-nav" aria-label="Primary navigation">
                <ul class="glace-nav-links">
                    <li><a class="<?= $currentPage === 'index.php' ? 'is-active' : '' ?>" href="index.php">Home</a></li>
                    <li><a class="<?= $currentPage === 'aboutus.php' ? 'is-active' : '' ?>" href="aboutus.php">About</a></li>
                    <li><a class="<?= $currentPage === 'ourservices.php' ? 'is-active' : '' ?>" href="ourservices.php">Services</a></li>
                </ul>
                <a class="glace-contact" href="contact-us.php">
                    <span>Contact Us</span>
                    <svg fill="#000000" width="800px" height="800px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M3.293,20.707a1,1,0,0,1,0-1.414L17.586,5H12a1,1,0,0,1,0-2h8a1,1,0,0,1,1,1v8a1,1,0,0,1-2,0V6.414L4.707,20.707a1,1,0,0,1-1.414,0Z"/></svg>
                </a>
                <button class="glace-menu-toggle" type="button" aria-label="Open navigation menu" aria-controls="glace-sidebar" aria-expanded="false">
                    <span></span>
                </button>
            </nav>
        </div>
    </div>
</header>

<aside class="glace-sidebar" id="glace-sidebar" aria-label="Mobile navigation">
    <nav class="glace-sidebar-nav">
        <div class="glace-sidebar-header">
            <a class="glace-sidebar-brand" href="index.php" aria-label="Glace Media home">
                <img src="image/logo.png" alt="Glace Media">
            </a>
            <button class="glace-sidebar-close" type="button" aria-label="Close navigation menu">&times;</button>
        </div>
        <div class="glace-sidebar-links">
            <a class="<?= $currentPage === 'index.php' ? 'is-active' : '' ?>" href="index.php">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10Z"/></svg>
                <span>Home</span>
            </a>
            <a class="<?= $currentPage === 'aboutus.php' ? 'is-active' : '' ?>" href="aboutus.php">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 10v6m0-9h.01"/></svg>
                <span>About</span>
            </a>
            <a class="<?= $currentPage === 'ourservices.php' ? 'is-active' : '' ?>" href="ourservices.php">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 14 5.2l3-.2.8 2.9 2.6 1.5-1.3 2.7 1.3 2.7-2.6 1.5-.8 2.9-3-.2L12 21l-2-2.2-3 .2-.8-2.9-2.6-1.5L4.9 12l-1.3-2.7L6.2 7.8 7 4.9l3 .2L12 3Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                <span>Services</span>
            </a>
            <a class="<?= $currentPage === 'contact-us.php' ? 'is-active' : '' ?>" href="contact-us.php">
                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="1"/><path d="m4 7 8 6 8-6"/></svg>
                <span>Contact Us</span>
            </a>
        </div>
        <div class="glace-sidebar-footer">© 2024 Glacé Media. All rights reserved.</div>
    </nav>
</aside>

<script>
    (() => {
        const header = document.querySelector('.glace-header');
        const announcement = document.querySelector('#glace-announcement');
        const menuToggle = document.querySelector('.glace-menu-toggle');
        const sidebar = document.querySelector('#glace-sidebar');
        const closeButton = document.querySelector('.glace-sidebar-close');
        const contactButton = document.querySelector('.glace-contact');
        const messages = <?= json_encode($newsData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        let messageIndex = 0;
        let lastScrollTop = window.pageYOffset || document.documentElement.scrollTop;
        let scrollTicking = false;

        if (messages.length > 1) {
            window.setInterval(() => {
                announcement.classList.add('flip');
                window.setTimeout(() => {
                    messageIndex = (messageIndex + 1) % messages.length;
                    announcement.textContent = messages[messageIndex];
                    announcement.classList.remove('flip');
                }, 450);
            }, 4000);
        }

        const setMenuState = (isOpen) => {
            sidebar.classList.toggle('is-open', isOpen);
            menuToggle.setAttribute('aria-expanded', String(isOpen));
            document.body.style.overflow = isOpen ? 'hidden' : '';
        };

        menuToggle.addEventListener('click', () => setMenuState(true));
        closeButton.addEventListener('click', () => setMenuState(false));
        sidebar.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setMenuState(false)));
        sidebar.addEventListener('click', (event) => {
            if (event.target === sidebar) {
                setMenuState(false);
            }
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setMenuState(false);
            }
        });

        const setContactOrigin = (event) => {
            const rect = contactButton.getBoundingClientRect();
            contactButton.style.setProperty('--contact-x', `${event.clientX - rect.left}px`);
            contactButton.style.setProperty('--contact-y', `${event.clientY - rect.top}px`);
        };

        contactButton.addEventListener('mouseenter', (event) => {
            setContactOrigin(event);
            contactButton.classList.add('is-hovered');
        });

        contactButton.addEventListener('mouseleave', (event) => {
            setContactOrigin(event);
            contactButton.classList.add('is-hovered');
            window.requestAnimationFrame(() => contactButton.classList.remove('is-hovered'));
        });

        const updateHeader = () => {
            const currentScrollTop = Math.max(window.pageYOffset || document.documentElement.scrollTop, 0);
            const scrollDelta = currentScrollTop - lastScrollTop;

            if (currentScrollTop <= 4 || scrollDelta < 0) {
                header.classList.remove('is-hidden');
            } else if (scrollDelta > 0) {
                header.classList.add('is-hidden');
            }

            lastScrollTop = currentScrollTop;
            scrollTicking = false;
        };

        window.addEventListener('scroll', () => {
            if (!scrollTicking) {
                window.requestAnimationFrame(updateHeader);
                scrollTicking = true;
            }
        }, { passive: true });
    })();
</script>
