BX.namespace("Sotbit.Reviews");

/**
 * @namespace
 * @property {Object} Sotbit
 * @property {Object} Sotbit.Reviews
 */

BX.Sotbit.Reviews = {
    isBodyScrollFixed: false,

    fixBodyScroll: function () {
        if (this.isBodyScrollFixed) {
            return;
        }

        document.documentElement.style.setProperty('--body-scrollbar-width', `${window.innerWidth - document.body.offsetWidth}px`);
        document.body.classList.add('overflow-hidden');
        this.isBodyScrollFixed = true;
    },

    unfixBodyScroll: function () {
        if (!this.isBodyScrollFixed) {
            return;
        }

        document.documentElement.style.setProperty('--body-scrollbar-width', `0px`);
        document.body.classList.remove('overflow-hidden');
        this.isBodyScrollFixed = false;
    },

    /**
     * @param {'top' | 'header' | 'filter'| number} mode
     * @param {HTMLElement} relativeElement
     * @returns {Object} overlay
     */
    showOverlay: function(
      mode = 'top',
      relativeElement = document.body,
    ) {
        const overlayElement = document.createElement('div');

        overlayElement.classList.add('overlay');
        overlayElement.style.zIndex = typeof mode === 'string' ? `var(--z-index-overlay-${mode})` : mode;
        relativeElement.appendChild(overlayElement);
        overlayElement.offsetWidth; // force reflow
        overlayElement.style.opacity = 1;

        return {
            element: overlayElement,
            hide: () => this.hideOverlay(overlayElement),
        };
    },

    /**
     * @param {HTMLElement} overlay
     */
    hideOverlay: function(overlay) {
        if (!overlay) {
            return;
        }

        overlay.style.opacity = 0;

        setTimeout(() => {
            overlay.remove();
        }, parseFloat(getComputedStyle(document.body).getPropertyValue('--transition')) * 1000);
    },

    currentlyAnimatedElements: new Map(),

    /**
     * @param {HTMLElement} element
     * @param {Object} [options]
     * @param {number} [options.duration]
     * @param {Function} [options.callback]
     */
    showElement: function (element, options) {
        if (!element.style.opacity == 0
            && !element.classList.contains('d-none')
            && !this.currentlyAnimatedElements.has(element)) {
            return console.warn(`Element is already shown!`, element);
        }

        options = {
            duration: 300,
            complete: () => this.hideElement(element),
            ...options
        };

        const easing = new BX.easing({
            duration: options.duration,
            start: {
                opacity: 0,
            },
            finish: {
                opacity: 100,
            },
            transition: BX.easing.transitions.linear,
            step: function (state) {
                if (state.opacity === 0 && element.classList.contains('d-none')) {
                    element.classList.remove('d-none');
                }

                element.style.opacity = state.opacity / 100;
            },
            complete: () => {
                options.callback && options.callback(element);
            },
        });

        this.currentlyAnimatedElements.set(element, easing);

        easing.animate();
    },

    /**
     * @param {HTMLElement} element
     * @param {Object} [options]
     * @param {number} [options.duration]
     * @param {Function} [options.callback]
     */
    hideElement: function (element, options) {
        if (element.style.opacity == 0
            && element.classList.contains('d-none')
            && !this.currentlyAnimatedElements.has(element)
        ) {
            return console.warn(`Element is already hidden!`, element);
        }

        const currentlyAnimatedElementEasing = this.currentlyAnimatedElements.get(element);

        if (currentlyAnimatedElementEasing) {
            this.currentlyAnimatedElements.delete(element);
            currentlyAnimatedElementEasing.stop(true);
        }

        options = {
            duration: 300,
            ...options
        };

        const easing = new BX.easing({
            duration: options.duration,
            start: {
                opacity: 100,
            },
            finish: {
                opacity: 0,
            },
            transition: BX.easing.transitions.linear,
            step: function (state) {
                element.style.opacity = state.opacity / 100;
            },
            complete: () => {
                element.classList.add('d-none');
                options.callback && options.callback(element);
            },
        });

        easing.animate();
    },

    /**
     * @param {string} message
     * @param {Object} [options]
     * @param {"success" | "error"} [options.icon]
     * @param {number} [options.durationShow]
     * @param {number} [options.durationHide]
     * @param {number} [options.durationVisible]
     * @param {Function} [options.onShow]
     * @param {Function} [options.onHide]
     */
    showMessage: function (message, options) {
        options = {
            icon: 'success',
            durationShow: 500,
            durationHide: 300,
            durationVisible: 2500,
            ...options
        };

        const icons = {
            success: `<svg class="top-message-icon" width="32" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                    <path d="M29.726 5.3125C30.1058 5.71343 30.0887 6.34637 29.6877 6.7262L10.6877 24.7262C10.4943 24.9095 10.2356 25.008 9.96926 24.9998C9.70288 24.9916 9.45079 24.8774 9.26895 24.6826L2.26895 17.1826C1.89211 16.7788 1.91393 16.146 2.31769 15.7692C2.72144 15.3924 3.35423 15.4142 3.73106 15.8179L10.0436 22.5814L28.3123 5.27429C28.7132 4.89446 29.3461 4.91157 29.726 5.3125Z" fill="currentColor"/>
                </svg>
                `,
            error: `<svg class="top-message-icon" width="32" height="32" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4.39705 4.55379L4.46967 4.46967C4.73594 4.2034 5.1526 4.1792 5.44621 4.39705L5.53033 4.46967L12 10.939L18.4697 4.46967C18.7626 4.17678 19.2374 4.17678 19.5303 4.46967C19.8232 4.76256 19.8232 5.23744 19.5303 5.53033L13.061 12L19.5303 18.4697C19.7966 18.7359 19.8208 19.1526 19.6029 19.4462L19.5303 19.5303C19.2641 19.7966 18.8474 19.8208 18.5538 19.6029L18.4697 19.5303L12 13.061L5.53033 19.5303C5.23744 19.8232 4.76256 19.8232 4.46967 19.5303C4.17678 19.2374 4.17678 18.7626 4.46967 18.4697L10.939 12L4.46967 5.53033C4.2034 5.26406 4.1792 4.8474 4.39705 4.55379L4.46967 4.46967L4.39705 4.55379Z" fill="currentColor"/>
                </svg>
                `,
        };
        const element = BX.create('div', {
            props: {
                className: 'top-message'
            },
            events: {
                click: (event) => {
                    if (event.target.tagName.toUpperCase() === 'A') {
                        event.stopPropagation();
                    }
                }
            },
            html: `
            ${icons[options.icon]}
            <div class="top-message-content">
                <b>${message}</b>
            </div>
        `
        });

        document.body.appendChild(element);

        let topOffset = 163;

        if (window.matchMedia('(max-width: 768px)').matches) {
            topOffset = 70;
        }

        new BX.easing({
            duration: options.durationShow,
            start: {
                Y: 0,
                opacity: 0
            },
            finish: {
                Y: topOffset,
                opacity: 100
            },
            transition: BX.easing.makeEaseOut(BX.easing.transitions.cubic),
            step: function (state) {
                element.style.transform = `translateX(-50%) translateY(${state.Y}px)`;
                element.style.opacity = state.opacity / 100;
            },
            complete: () => {
                options.onShow && options.onShow(element);

                element.addEventListener('click', () => {
                    this.hideMessage({
                        element,
                        duration: options.durationHide,
                        callback: options.onHide
                    });
                });

                setTimeout(() => {
                    this.hideMessage({
                        element,
                        duration: options.durationHide,
                        callback: options.onHide
                    });
                }, options.durationVisible);
            },
        }).animate();
    },

    /**
     * @param {Object} [options]
     * @param {HTMLElement} [options.element]
     * @param {number} [options.duration]
     * @param {Function} [options.callback]
     */
    hideMessage: function (options) {
        if (!options.element) {
            const element = document.querySelector('.top-message');

            if (!element) {
                return false;
            }

            options.element = element;
        }

        new BX.easing({
            duration: options.duration,
            start: {
                opacity: 100
            },
            finish: {
                opacity: 0
            },
            transition: BX.easing.makeEaseOut(BX.easing.transitions.cubic),
            step: function (state) {
                options.element.style.opacity = state.opacity / 100;
            },
            complete: () => {
                options.callback && options.callback(element);
                options.element.remove();
            },
        }).animate();
    }
};
