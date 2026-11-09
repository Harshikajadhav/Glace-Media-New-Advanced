document.addEventListener("DOMContentLoaded", () => {
    const videoContainer = document.querySelector('.videoo-containerr');
    const videos = Array.from(document.querySelectorAll('.backgroundd-videoo'));
    const desktopVideo = document.querySelector('.backgroundd-videoo--desktop');
    const mobileVideo = document.querySelector('.backgroundd-videoo--mobile');
    let backgroundVideo = null;
    let targetVideoInset = 10;
    let currentVideoInset = 10;
    let videoAnimationFrame = null;
    let shouldVideoPlay = false;

    const setVideoSource = (video, isActive) => {
        const source = video.querySelector('source');

        if (isActive) {
            source.src = source.dataset.src;
            video.preload = 'auto';
            video.load();
        } else {
            video.pause();
            video.removeAttribute('src');
            source.removeAttribute('src');
            video.preload = 'none';
            video.load();
        }
    };

    const switchActiveVideo = () => {
        const mobileViewport = window.matchMedia('(max-width: 767px)').matches;
        const nextVideo = mobileViewport ? mobileVideo : desktopVideo;

        if (!nextVideo || backgroundVideo === nextVideo) {
            return;
        }

        if (backgroundVideo) {
            setVideoSource(backgroundVideo, false);
        }

        backgroundVideo = nextVideo;
        setVideoSource(backgroundVideo, true);
        updateVideoPlayback();
    };

    const attemptVideoPlayback = (fromUserGesture = false) => {
        if (!shouldVideoPlay || !backgroundVideo || !backgroundVideo.paused) {
            return;
        }

        backgroundVideo.volume = 1;

        if (fromUserGesture) {
            backgroundVideo.muted = false;
        }

        backgroundVideo.play().then(() => {
            if (fromUserGesture) {
                backgroundVideo.muted = false;
            }
        }).catch(() => {
            if (!fromUserGesture) {
                backgroundVideo.muted = true;
                backgroundVideo.play().catch(() => {});
            }
        });
    };

    const updateVideoPlayback = () => {
        shouldVideoPlay = targetVideoInset <= 5;

        if (shouldVideoPlay && backgroundVideo.paused) {
            attemptVideoPlayback();
        } else if (!shouldVideoPlay && !backgroundVideo.paused) {
            backgroundVideo.pause();
            backgroundVideo.muted = false;
        }
    };

    const animateVideoInset = () => {
        currentVideoInset += (targetVideoInset - currentVideoInset) * 0.16;
        videoContainer.style.setProperty('--video-padding', `${currentVideoInset}%`);
        updateVideoPlayback();

        if (Math.abs(targetVideoInset - currentVideoInset) > 0.05) {
            videoAnimationFrame = requestAnimationFrame(animateVideoInset);
        } else {
            currentVideoInset = targetVideoInset;
            videoContainer.style.setProperty('--video-padding', `${currentVideoInset}%`);
            videoAnimationFrame = null;
        }
    };

    const updateVideoWidth = () => {
        switchActiveVideo();

        if (!backgroundVideo) {
            return;
        }

        const videoRect = videoContainer.getBoundingClientRect();
        const viewportCenter = window.innerHeight * 0.5;
        const videoCenter = videoRect.top + (videoRect.height * 0.5);
        const centerDistance = Math.abs(videoCenter - viewportCenter);
        const zoomRange = Math.max(window.innerHeight * 0.75, videoRect.height * 0.5);
        const centerProgress = centerDistance <= 24
            ? 1
            : Math.max(0, 1 - ((centerDistance - 24) / zoomRange));

        targetVideoInset = 10 * (1 - centerProgress);
        updateVideoPlayback();

        if (!videoAnimationFrame) {
            videoAnimationFrame = requestAnimationFrame(animateVideoInset);
        }
    };

    switchActiveVideo();
    updateVideoWidth();

    window.addEventListener('scroll', updateVideoWidth, { passive: true });
    window.addEventListener('resize', updateVideoWidth, { passive: true });
    videos.forEach(video => {
        video.addEventListener('canplay', () => {
            if (video === backgroundVideo) {
                attemptVideoPlayback();
            }
        });
        video.addEventListener('loadeddata', () => {
            if (video === backgroundVideo) {
                attemptVideoPlayback();
            }
        });
    });

    const startVideoFromGesture = () => {
        if (shouldVideoPlay) {
            attemptVideoPlayback(true);
        }
    };

    document.addEventListener('wheel', startVideoFromGesture, { passive: true });
    document.addEventListener('pointerdown', startVideoFromGesture, { passive: true });
    document.addEventListener('touchstart', startVideoFromGesture, { passive: true });
    document.addEventListener('keydown', startVideoFromGesture, { passive: true });

    // Smooth Mousemove Scrolling for Services Section
    const wrapper = document.querySelector('.services-wrapper');
    const services = document.querySelector('.list-of-services');
    let targetScroll = 0;
    let currentScroll = 0;

    function smoothScroll() {
        currentScroll += (targetScroll - currentScroll) * 0.1;
        services.style.transform = `translateX(-${currentScroll}px)`;

        if (Math.abs(targetScroll - currentScroll) > 0.5) {
            requestAnimationFrame(smoothScroll);
        }
    }

    wrapper.addEventListener('mouseenter', () => {
        wrapper.addEventListener('mousemove', (e) => {
            const wrapperWidth = wrapper.offsetWidth;
            const mouseX = e.clientX;

            targetScroll = (services.scrollWidth - wrapperWidth) * (mouseX / wrapperWidth);
            requestAnimationFrame(smoothScroll);
        });
    });

    wrapper.addEventListener('mouseleave', () => {
        targetScroll = 0;
        requestAnimationFrame(smoothScroll);
    });


    // Brands Hover Effect
    function initializeBrandHover(brandBoxes, imagePlaceholder, hoveredImage, brandLists) {
        brandBoxes.forEach((box, index) => {
            const rowIndex = Math.floor(index / 4);

            box.addEventListener('mouseenter', () => {
                const imageSrc = box.getAttribute('data-image');
                hoveredImage.src = imageSrc;
                imagePlaceholder.classList.add('active');
                hoveredImage.classList.add('active');

                if (brandLists[rowIndex + 1]) {
                    brandLists[rowIndex + 1].classList.add('moved-down');
                }
            });

            box.addEventListener('mouseleave', () => {
                imagePlaceholder.classList.remove('active');
                hoveredImage.classList.remove('active');

                if (brandLists[rowIndex + 1]) {
                    brandLists[rowIndex + 1].classList.remove('moved-down');
                }
            });
        });
    }

    const brandBoxes = document.querySelectorAll('.brand-box');
    const imagePlaceholder = document.querySelector('.image-placeholder');
    const hoveredImage = document.getElementById('hovered-image');
    const brandLists = document.querySelectorAll('.brand-list');

    const brandBoxes2 = document.querySelectorAll('.brand-box2');
    const imagePlaceholder2 = document.querySelector('.image-placeholder2');
    const hoveredImage2 = document.getElementById('hovered-image2');
    const brandLists2 = document.querySelectorAll('.brand-list2');

    initializeBrandHover(brandBoxes, imagePlaceholder, hoveredImage, brandLists);
    initializeBrandHover(brandBoxes2, imagePlaceholder2, hoveredImage2, brandLists2);
});

let listItems = document.querySelectorAll('.list .item');
let thumbnails = document.querySelectorAll('.thumbnail .item');
let prevButton = document.getElementById('prev');
let nextButton = document.getElementById('next');
let currentIndex = 0;

function showImage(index) {
    const visibleThumbnailCount = 4;
    const firstVisibleIndex = Math.min(
        Math.max(index - visibleThumbnailCount + 1, 0),
        Math.max(thumbnails.length - visibleThumbnailCount, 0)
    );

    listItems.forEach((item, i) => {
        item.classList.toggle('active', i === index);
    });
    thumbnails.forEach((thumbnail, i) => {
        thumbnail.classList.toggle('active', i === index);
        thumbnail.classList.toggle(
            'is-hidden',
            i < firstVisibleIndex || i >= firstVisibleIndex + visibleThumbnailCount
        );
    });
    currentIndex = index;
}

thumbnails.forEach((thumbnail, index) => {
    thumbnail.addEventListener('click', () => {
        showImage(index);
    });
});

prevButton.addEventListener('click', () => {
    let newIndex = (currentIndex - 1 + listItems.length) % listItems.length;
    showImage(newIndex);
});

nextButton.addEventListener('click', () => {
    let newIndex = (currentIndex + 1) % listItems.length;
    showImage(newIndex);
});

// Set the default active image
showImage(0);



// Contact us button
(function() {
    const buttons = document.querySelectorAll(".btn-posnawr");
  
    buttons.forEach(button => {
      ["mouseenter", "mouseout"].forEach(evt => {
        button.addEventListener(evt, e => {
          let parentOffset = button.getBoundingClientRect(),
              relX = e.pageX - parentOffset.left,
              relY = e.pageY - parentOffset.top;
  
          const span = button.getElementsByTagName("span");
  
          span[0].style.top = relY + "px";
          span[0].style.left = relX + "px";
        });
      });
    });
  })();


  // Select all service items
const serviceItems = document.querySelectorAll('.mobile-services p');

serviceItems.forEach((item) => {
    item.addEventListener('click', () => {
        const parent = item.parentElement;
        parent.classList.toggle('active');
    });
});

(function initializeMobileServicesStack() {
    const mediaQuery = window.matchMedia('(max-width: 700px)');
    const stack = document.querySelector('.mobile-services-stack__cards');
    const cards = Array.from(document.querySelectorAll('.mobile-services-stack__card'));
    let activeIndex = 0;

    if (!stack || cards.length !== 4) return;

    function clearMobileStackStyles() {
        cards.forEach((card, index) => {
            card.style.removeProperty('--mobile-service-top');
            card.style.removeProperty('--mobile-service-height');
            card.style.removeProperty('z-index');
            card.setAttribute('aria-expanded', index === 0 ? 'true' : 'false');
        });
    }

    function updateMobileStack() {
        if (!mediaQuery.matches) {
            clearMobileStackStyles();
            return;
        }

        const stackWidth = stack.getBoundingClientRect().width;
        const expandedHeight = 277;
        const summaryHeight = Math.min(Math.max(stackWidth * .17, 62), 78);

        cards.forEach((card, index) => {
            const stackOrder = (index - activeIndex + cards.length) % cards.length;
            const isExpanded = stackOrder === 0;
            const top = isExpanded
                ? 0
                : expandedHeight + ((stackOrder - 1) * summaryHeight);

            card.style.setProperty('--mobile-service-top', `${top}px`);
            card.style.setProperty(
                '--mobile-service-height',
                `${isExpanded ? expandedHeight : summaryHeight}px`
            );
            card.style.zIndex = String(cards.length - stackOrder);
            card.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
        });
    }

    function selectMobileService(index) {
        if (!mediaQuery.matches || index === activeIndex) return;
        activeIndex = index;
        updateMobileStack();
    }

    cards.forEach((card, index) => {
        card.addEventListener('click', (event) => {
            if (event.target.closest('.mobile-services-stack__link')) return;
            selectMobileService(index);
        });

        card.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') return;
            event.preventDefault();
            selectMobileService(index);
        });
    });

    mediaQuery.addEventListener('change', updateMobileStack);
    window.addEventListener('resize', updateMobileStack, { passive: true });
    updateMobileStack();
})();
