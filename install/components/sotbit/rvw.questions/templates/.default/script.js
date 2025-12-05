SA_Questions = {
    init: function (param){
        this.signedParameters = param.signedParameters;
    },

    reloadList: function (param) {
    BX.showWait();

    let request = BX.ajax.runComponentAction('sotbit:rvw.questions', 'contentLoader', {
        mode: 'class',
        signedParameters: this.signedParameters,
        data: param,
    }).then(
        (response) => {
            BX.closeWait();
            let processed = BX.processHTML(response.data.html, false);
            element = new DOMParser().parseFromString(processed.HTML, "text/html");

            if (element.querySelector('.reviews__control') && document.querySelector('.reviews__control')) {
                document.querySelector('.reviews__control').innerHTML = element.querySelector('.reviews__control').innerHTML;
            } else {
                document.querySelector('.control__item-filter--checkbox').classList.add('display-none-important');
            }

            if (element.querySelector('.reviews__content_wrapper') && document.querySelector('.reviews__content_wrapper')) {
                document.querySelector('.reviews__content_wrapper').innerHTML = element.querySelector('.reviews__content_wrapper').innerHTML;
            } else {
                if (document.querySelector('.reviews__content_wrapper')){
                    document.querySelector('.reviews__content_wrapper').innerHTML = processed.HTML;
                }

                if(document.querySelector('.pagination-reviews-wrapper')){
                    document.querySelector('.pagination-reviews-wrapper').classList.add('display-none-important')
                }
            }

            if (element.querySelector('#reviews-statistics')) {
                document.querySelector('#reviews-statistics').innerHTML = element.querySelector('#reviews-statistics').innerHTML;
            }
            removeEventListener('submit',SA_QuestionsAdd.sendQuestion);
            BX.ajax.processScripts(processed.SCRIPT);

            refreshFsLightbox();

        },
        (error) => {
            console.log(error)
            BX.closeWait();
            //this.showModalResult(error.errors.map(item => item.message).join('\n'), 'error')
        }
    );
}
}