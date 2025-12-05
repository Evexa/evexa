SA_QuestionsList = {
    init: function (params) {
        this.signedParameters = params.signedParameters;

        this.blockParent = document.querySelector('.reviews__content_wrapper');

        if (this.blockParent) {
            this.btnLike = this.blockParent.querySelectorAll('[data-type="like"]');
            this.btnEdit = this.blockParent.querySelectorAll('[data-type="edit"]');
            this.btnMore = document.querySelector('.more-reviews button');

            if (this.btnLike) {
                this.initEventLike();
            }
            if (this.btnMore) {
                this.initMore();
            }
            if (this.btnEdit) {
                this.initEventEdit();
            }
        }
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

    initEventEdit: function () {
        this.btnEdit.forEach(itemEdit => {

            itemEdit.addEventListener('click', () => {
                SA_QuestionsAdd.form.querySelector('[name="QUESTION"]').value = itemEdit.closest('.reviews__content').querySelector('.content__reviews__item__body p').innerHTML.trim();
                SA_QuestionsAdd.form.appendChild(
                    SA_ReviewsAdd.hiddenInputs[itemEdit.dataset.id] = BX.create('input', {
                        attrs: {
                            'type': 'hidden',
                            'name': 'ID',
                            'value': itemEdit.dataset.id
                        }
                    })
                );
                SA_QuestionsAdd.form.querySelector('[name="QUESTION"]').focus();
            });
        })
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

            this.moreQuestionsController(value);
        })
    },

    editQuestionsController: async function (form) {
        BX.ajax.runComponentAction('sotbit:rvw.questions.list', 'edit', {
            mode: 'class',
            data: new FormData(form),
        }).then(
            () => {

                SA_QuestionsAdd.showModalResult(BX.message('TITLE_MODAL_EDIT'), 'success');
                SA_QuestionsAdd.deleteFormField()
                SA_Questions.reloadList({});
            },
            (error) => {
                BX.closeWait();
                SA_QuestionsAdd.showModalResult(error.errors.map(item => item.message).join('\n'), 'error')
            }
        );


    },

    moreQuestionsController: async function (value) {
        try {
            BX.showWait();
            const {data} = await BX.ajax.runComponentAction('sotbit:rvw.questions', 'contentLoader', {
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

    elementRecalculationLike: function (elem, isController = true) {

        let valueNode = elem.querySelector('p');
        let valueLike = valueNode.innerHTML;

        itemNodeParent = valueNode.closest('.content__reviews__item__control__item');

        if (!elem.classList.contains('btn-reviews-small--active')) {
            let field = {};
            field[elem.dataset.value.toUpperCase()] = 'Y';
            field['QUESTION_ID'] = elem.dataset.id;


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
            field['QUESTION_ID'] = elem.dataset.id;

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

        BX.ajax.runComponentAction('sotbit:rvw.questions.list', 'like', {
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

    hideOverlay: function (
        mode = "top",
        relativeElement = document.body
    ) {
        const overlayElement = relativeElement.querySelector(".overlay");

        if (!overlayElement) {
            return;
        }

        overlayElement.style.opacity = 0;
        setTimeout(() => {
            overlayElement.remove();
        }, parseFloat(getComputedStyle(document.body).getPropertyValue("--transition")) * 1000);
    },
};