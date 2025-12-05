SA_ReviewsFilter = {
    init: function (params) {
        this.eventHandlerFilter();
        this.eventHandlerSelect();
        this.eventHandlerSelectDocument();
        this.initExample();
    },

    initExample: function () {
        this.example = new Choices(document.getElementById('star'), {
            searchEnabled: false,
            itemSelectText: ''
        });

        this.example.passedElement.element.addEventListener(
            'change',
             (event) => {
                const btn = event.target.closest('.btn-filter');

                if (btn.classList.contains('select') && (btn.classList.contains('select-active') === false)) {
                    BX.show(document.querySelector('.choices'));
                    btn.classList.add('select-active');

                    const checkbox = btn.querySelector('input[type="checkbox"]');
                    if (checkbox && (event.target != checkbox)) {
                        if (checkbox.checked) {
                            checkbox.checked = false;
                            checkbox.setAttribute('checked', '')
                        } else {
                            checkbox.setAttribute('checked', true);
                            checkbox.checked = true;
                            checkbox.value = 'false';
                        }
                    }
                    if (event.target.closest('.control-items')) {
                        let form = this.prepareForm(event.target.closest('.control-items'));

                        SA_Reviews.reloadList(form);
                    }
                } else {
                    BX.hide(document.querySelector('.choices'));
                    btn.classList.remove('select-active');
                    let form = this.prepareForm(btn.closest('.control-items'));
                    SA_Reviews.reloadList(form);
                }
            },
            false,
        );
    },

    eventHandlerFilter: function () {
        const buttonItemsFilter = document.querySelectorAll('.reviews__control .btn-filter');

        for (let buttonItem of buttonItemsFilter) {
            buttonItem.addEventListener('click', (e) => {
                const btn = e.target.closest('.btn-filter');
                const itemTransfer = btn.querySelector('.need-transfer');


                if (itemTransfer) {
                    if (itemTransfer.classList.contains('transfer')) {
                        itemTransfer.classList.remove('transfer');
                        btn.querySelector('[name="DATE_CREATION"]').value = 'desc';
                    } else {
                        itemTransfer.classList.add('transfer');
                        btn.querySelector('[name="DATE_CREATION"]').value = 'asc';
                    }

                    let form = this.prepareForm(e.target.closest('.control-items'));
                    SA_Reviews.reloadList(form);
                }
            });
        }
    },

    setAttr: function (prmName, val) {
        const url = new URL(window.location);  // == window.location.href
        url.searchParams.set(prmName, val);
        history.pushState(null, null, url);
    },

    eventHandlerSelect: function () {
        const buttonItemsFilter = document.querySelectorAll('.btn-filter');

        for (let buttonItem of buttonItemsFilter) {
            buttonItem.addEventListener('click', (e) => {
                const btn = e.target.closest('.btn-filter');

                if (btn.classList.contains('select') && (btn.classList.contains('select-active') === false)) {
                    BX.show(document.querySelector('.choices'));
                    btn.classList.add('select-active');
                } else {
                    BX.hide(document.querySelector('.choices'));
                    btn.classList.remove('select-active');
                }
                if (btn.querySelector('input[type="checkbox"]')) {
                    if (btn.querySelector('input[type="checkbox"]').checked) {
                        if(btn.querySelector('input[name="FILES"]')){
                            btn.querySelector('input[name="FILES"]').removeAttribute('checked');
                        }
                    } else {
                        if(btn.querySelector('input[name="FILES"]')){
                            btn.querySelector('input[name="FILES"]').setAttribute('checked', 'true');
                        }
                    }

                    let form = this.prepareForm(e.target.closest('.control-items'));
                    SA_Reviews.reloadList(form);
                }
            });
        }
    },

    prepareForm: function (elem) {
        let form = new FormData(elem)

        if (!form.get('FILES')) {
            form.append('FILES', 'off')
        }
        if (!form.get('more') && this.getParam('more')) {
            form.append('more', this.getParam('more'))
        }

        for (var pair of form.entries()) {
            this.setAttr(pair[0], pair[1])
        }
        return form;
    },

    getParam: function (param){
        let params = (new URL(document.location)).searchParams;
        return params.get(param);
    },

    eventHandlerSelectDocument: function () {
        document.addEventListener('click', (e) => {
            const btn = document.querySelector('.btn-filter.select-active');
            if (btn) {
                if (!(e.target.closest('.btn-filter.select-active'))) {
                    btn.classList.remove('select-active');
                    BX.hide(document.querySelector('.choices'));
                }
            }
        });
    },
};
