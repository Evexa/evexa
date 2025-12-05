SA_ReviewsList = {
    init: function (params) {
        this.signedParameters = params.signedParameters;

        if (params.arFile) {
            this.arFile = JSON.parse(params.arFile);
        }

        this.blockParent = document.querySelector('.reviews__content_wrapper');

        if (this.blockParent) {
            this.btnComplaints = this.blockParent.querySelectorAll('[data-type="complaints"]');
            this.btnLike = this.blockParent.querySelectorAll('[data-type="like"]');
            this.btnCopy = this.blockParent.querySelectorAll('[data-type="copy"]');
            this.btnEdit = this.blockParent.querySelectorAll('[data-type="edit"]');
            this.btnMore = document.querySelector('.more-reviews button');
            this.initSlider();

            if (this.btnComplaints) {
                this.initEventComplaints();
            }
            if (this.btnLike) {
                this.initEventLike();
            }
            if (this.btnCopy) {
                this.initEventCopy();
            }
            if (this.btnMore) {
                this.initMore();
            }
            if (this.btnEdit) {
                this.initEventEdit();
            }
        }
    },
    initEventComplaints: function () {
        this.btnComplaints.forEach(itemComplaints => {
            itemComplaints.addEventListener('click', () => {
                this.currentComplaintItem = itemComplaints;
                SA_ReviewsAdd.showModalResult(BX.message('TITLE_MODAL_COMPLAINTS_SELECTION'), 'selection');
            });
        })
    },

    initEventLike: function () {
        this.btnLike.forEach(itemLike => {
            itemLike.addEventListener('click', (e) => {


                this.isNeedActiveLike = true;

                itemLike.closest('.content__reviews__item__control').querySelectorAll('.content__reviews__item__control__item').forEach(item => {
                    if (item.classList.contains('btn-reviews-small--active')) {
                        if (item != itemLike) {
                            this.isNeedActiveLike = false;
                            this.elementRecalculationLike(item, false);
                            this.elementRecalculationLike(itemLike);
                        }
                    }
                });

                if (this.isNeedActiveLike) {
                    this.elementRecalculationLike(itemLike);
                }
            });
        })
    },
    initEventCopy: function () {
        this.btnCopy.forEach(itemCopy => {
            itemCopy.addEventListener('click', () => {
                const url = new URL(itemCopy.dataset.value, location.href);
                this.copy(url.href);
            });
        })
    },

    initEventEdit: function () {
        this.btnEdit.forEach(itemEdit => {

            itemEdit.addEventListener('click', () => {
                const modal = document.getElementById('review_add__modal');
                const anonymousCheckbox = modal.querySelector('[name="ANONYMOUS"]');

                if (anonymousCheckbox) {
                    BX.hide(anonymousCheckbox.closest('.control__item-filter--checkbox'));
                }

                SA_ReviewsAdd.showRating(itemEdit.dataset.rating);
                SA_ReviewsAdd.setRatingValue(itemEdit.dataset.rating);
                if (this.arFile) {
                    const files = this.arFile[itemEdit.dataset.id];

                    if (files) {
                        Object.keys(files).forEach(key => {

                            SA_ReviewsAdd.dropzone.displayExistingFile({
                                id: key,
                                src: files[key].src,
                                type: files[key].type,
                                edit: files[key].edit,
                            }, files[key].src);

                            SA_ReviewsAdd.form.appendChild(
                                SA_ReviewsAdd.hiddenInputs[key] = BX.create('input', {
                                    attrs: {
                                        'type': 'hidden',
                                        'name': 'MEDIA[]',
                                        'value': key
                                    }
                                })
                            );
                        });
                    }
                }

                SA_ReviewsAdd.setRatingValue(itemEdit.dataset.rating);
                SA_ReviewsAdd.form.setAttribute('data-active', 'edit');

                SA_ReviewsAdd.form.appendChild(
                    SA_ReviewsAdd.hiddenInputs[itemEdit.dataset.id] = BX.create('input', {
                        attrs: {
                            'type': 'hidden',
                            'name': 'ID',
              'value': itemEdit.dataset.id,
            },
          }),
        );

        BX.Sotbit.Reviews.fixBodyScroll();

        if (!SA_ReviewsAdd.overlay) {
          SA_ReviewsAdd.overlay = BX.Sotbit.Reviews.showOverlay();
                        }

        SA_ReviewsAdd.overlay.element.addEventListener('click', () => {
          BX.Sotbit.Reviews.hideElement(modal, {
            callback: () => {
                BX.Sotbit.Reviews.unfixBodyScroll();
            }
          });
          SA_ReviewsAdd.overlay?.hide();
          SA_ReviewsAdd.overlay = null;
        }, { once: true });

        BX.Sotbit.Reviews.showElement(modal);
                modal.querySelector('#review-comment').value = itemEdit.closest('.reviews__content').querySelector('.content__reviews__item__body p').innerHTML.trim();

                if (SA_ReviewsAdd.form.dataset.active == 'edit') {
                    //document.querySelector('#review_add__modal form').addEventListener('submit', this.editReviewsController.bind(this));
                }
            });
        });
    },

    initMore: function () {
        this.btnMore.addEventListener('click', () => {
            let value = this.btnMore.value;

            if (value <= 0) {
                this.btnMore.setAttribute('value', '2');
                value = 2;
            } else {
                this.btnMore.setAttribute('value', (value = Number(value) + 1));
            }

            this.moreReviewsController(value);
        })
    },

    editReviewsController: async function (form) {
        BX.ajax.runComponentAction('sotbit:rvw.reviews.list', 'edit', {
            mode: 'class',
            data: new FormData(form),
        }).then(
            () => {
                BX.closeWait();

        BX.Sotbit.Reviews.hideElement(SA_ReviewsAdd.mainModal, {
                    callback: () => {
            SA_ReviewsAdd.overlay?.hide();
          },
                });

        BX.Sotbit.Reviews.unfixBodyScroll();
        SA_ReviewsAdd.overlay?.hide();
        SA_ReviewsAdd.overlay = null;
                SA_ReviewsAdd.showModalResult(BX.message('TITLE_MODAL_EDIT'), 'success');
        SA_ReviewsAdd.deleteFormField();
                SA_Reviews.reloadList({});
            },
            (error) => {
                BX.closeWait();
        SA_ReviewsAdd.showModalResult(error.errors.map(item => item.message).join('\n'), 'error');
      },
        );


    },

    moreReviewsController: async function (value) {
        try {
            BX.showWait();
            const {data} = await BX.ajax.runComponentAction('sotbit:rvw.reviews', 'contentLoader', {
                mode: 'class',
                signedParameters: this.signedParameters,
                data: {
                    more: value
                },
            });

            const processed = BX.processHTML(data.html, false),
                html = new DOMParser().parseFromString(processed.HTML, "text/html"),
                content = html.querySelector('.reviews__content_wrapper');

            if (content) {
                this.blockParent.querySelector('.pagination-reviews-wrapper')?.remove();
                this.blockParent.insertAdjacentHTML('beforeend', content.innerHTML);
                BX.ajax.processScripts(processed.SCRIPT);
            }

        } catch (e) {
            console.error(e);
        } finally {
            BX.closeWait();
        }
    },

    copy: async function (text) {
        if (navigator.clipboard) {
            await navigator.clipboard.writeText(text);
            SA_ReviewsAdd.showModalResult(BX.message('TITLE_MODAL_COPY'), 'success');
            return;
        }

        try {
            this.fallbackCopyTextToClipboard(text);
        } catch (e) {
            return console.error('Unable to copy text');
        }

        SA_ReviewsAdd.showModalResult(BX.message('TITLE_MODAL_COPY'), 'success');
    },

    fallbackCopyTextToClipboard: function (text) {
        const hideNode = BX.create('input', {
            style: {
                position: 'absolute',
                height: 0,
            },
            attrs: {
                value: text
            }
        });
        document.body.appendChild(hideNode);
        hideNode.select();

        const copyResult = document.execCommand('copy');
        document.body.removeChild(hideNode);

        if (!copyResult) {
            throw new Error('Unable to copy text');
        }
    },


    elementRecalculationLike: function (elem, isController = true) {

        let valueNode = elem.querySelector('p');
        let valueLike = valueNode.innerHTML;

        itemNodeParent = valueNode.closest('.content__reviews__item__control__item');

        if (!elem.classList.contains('btn-reviews-small--active')) {
            let field = {};
            field[elem.dataset.value.toUpperCase()] = 'Y';
            field['REVIEW_ID'] = elem.dataset.id;


            if (isController) {
                this.elementLikeController(field, elem);
            } else {

                if (valueLike != '0') {
                    valueNode.textContent = Number(valueLike) + 1;
                } else {
                    valueNode.textContent = '1';
                }

                elem.classList.add('btn-reviews-small--active');
            }
        } else {
            let field = {};
            field[elem.dataset.value.toUpperCase()] = 'N';
            field['REVIEW_ID'] = elem.dataset.id;

            if (isController) {
                this.elementLikeController(field, elem);
            } else {
                if (valueLike != '0') {
                    valueNode.textContent = (Number(valueLike) - 1) == 0 ? '0' : Number(valueLike) - 1;
                } else {
                    valueNode.textContent = '0';
                }

                elem.classList.remove('btn-reviews-small--active');
            }
        }
    },

    elementLikeController: async function (field, elem) {

        BX.ajax.runComponentAction('sotbit:rvw.reviews.list', 'like', {
            mode: 'class',
            data: {data: field},
        }).then(
            (response) => {
                if (response.data != true) {
                    this.elementRecalculationLike(elem, false);
                }
            },
            (error) => {
                this.elementRecalculationLike(elem, false);
            }
        );
    },

    initSlider: function () {
        const swiperReviews = new Swiper('.swiper-reviews', {
            slidesPerView: 7,
            spaceBetween: 10,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });
    },

    elementComplaintController: async function () {
        const reviewId = this.currentComplaintItem.dataset.id;
        if (!reviewId) {
            return;
        }

        try {
            const {data} = await BX.ajax.runComponentAction('sotbit:rvw.reviews.list', 'complaints', {
                mode: 'class',
                data: {
                    id: reviewId,
                },
            });

            if (data.status === 'success') {
                SA_ReviewsAdd.closeModalResult();
                SA_ReviewsAdd.showModalResult(data.message, data.status);
            }

        } catch (e) {
            SA_ReviewsAdd.closeModalResult();
            SA_ReviewsAdd.showModalResult(e.errors.map(item => item.message).join('\n'), 'error');
        }
    },
};
