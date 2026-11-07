(function () {
    const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)');

    if (!finePointer.matches) {
        return;
    }

    const style = document.createElement('style');
    style.textContent = `
        @media (hover: hover) and (pointer: fine) {
            html.custom-cursor-enabled,
            html.custom-cursor-enabled body,
            html.custom-cursor-enabled body * {
                cursor: none !important;
            }

            .custom-cursor {
                position: fixed;
                top: 0;
                left: 0;
                width: 12px;
                height: 12px;
                border-radius: 50%;
                background: #FFFFFF;
                mix-blend-mode: difference;
                pointer-events: none;
                transform: translate3d(-50%, -50%, 0) scale(1);
                transform-origin: center;
                transition:
                    width 300ms cubic-bezier(.22, 1, .36, 1),
                    height 300ms cubic-bezier(.22, 1, .36, 1),
                    border-radius 300ms cubic-bezier(.22, 1, .36, 1),
                    background-color 300ms ease,
                    border-color 300ms ease,
                    box-shadow 300ms ease;
                z-index: 999999;
            }

            .custom-cursor.is-interactive {
                width: 30px;
                height: 30px;
            }

            .custom-cursor.is-service {
                width: max-content;
                height: auto;
                min-height: 12px;
                padding: 11px 18px;
                border: 1px solid #14A59B;
                border-radius: 50px;
                background: #060706;
                color: #FFFFFF;
                mix-blend-mode: normal;
                box-shadow: 0 0 15px rgba(20, 165, 155, .2);
                font-size: 14px;
                font-weight: 500;
                line-height: 1;
                letter-spacing: .2px;
                white-space: nowrap;
            }

            .custom-cursor-label {
                opacity: 0;
                transition: opacity 300ms cubic-bezier(.22, 1, .36, 1);
            }

            .custom-cursor.is-service .custom-cursor-label {
                opacity: 1;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .custom-cursor {
                transition: none;
            }
        }
    `;
    document.head.appendChild(style);
    document.documentElement.classList.add('custom-cursor-enabled');

    const cursor = document.createElement('div');
    cursor.className = 'custom-cursor';
    cursor.setAttribute('aria-hidden', 'true');
    const cursorLabel = document.createElement('span');
    cursorLabel.className = 'custom-cursor-label';
    cursor.appendChild(cursorLabel);
    document.body.appendChild(cursor);

    const interactiveSelector = [
        'a',
        'button',
        'input',
        'textarea',
        'select',
        'summary',
        '[role="button"]',
        '[tabindex]:not([tabindex="-1"])',
        'label',
        'img[onclick]',
        '[onclick]'
    ].join(',');

    let pointerX = -100;
    let pointerY = -100;
    let frameRequested = false;

    const renderCursor = () => {
        cursor.style.left = `${pointerX}px`;
        cursor.style.top = `${pointerY}px`;
        frameRequested = false;
    };

    document.addEventListener('pointermove', event => {
        pointerX = event.clientX;
        pointerY = event.clientY;

        if (!frameRequested) {
            frameRequested = true;
            requestAnimationFrame(renderCursor);
        }

        const hoveredElement = document.elementFromPoint(event.clientX, event.clientY);
        const serviceImage = hoveredElement?.closest('.hero-scene-item');
        const interactiveElement = hoveredElement?.closest(interactiveSelector);
        const serviceName = serviceImage?.dataset.service || '';

        cursor.classList.toggle('is-service', Boolean(serviceName));
        cursor.classList.toggle('is-interactive', Boolean(interactiveElement) && !serviceImage);
        if (cursorLabel.textContent !== serviceName) {
            cursorLabel.textContent = serviceName;
        }
    }, { passive: true });

    document.addEventListener('pointerleave', () => {
        cursor.classList.remove('is-interactive');
        cursor.classList.remove('is-service');
        cursorLabel.textContent = '';
    });
})();
