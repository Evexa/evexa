SA_Reviews = {
    init: function (param){
        this.signedParameters = param.signedParameters;
    },

    reloadList: function (param) {
        BX.Sotbit.Reviews.isBodyScrollFixed = true;
        BX.showWait();

        let request = BX.ajax.runComponentAction('sotbit:rvw.reviews', 'contentLoader', {
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
                    if (document.querySelector('.reviews__content_wrapper')) {
                        document.querySelector('.reviews__content_wrapper').innerHTML = processed.HTML;
                    }

                    if (document.querySelector('.pagination-reviews-wrapper')) {
                        document.querySelector('.pagination-reviews-wrapper').classList.add('display-none-important')
                    }
                }

                if (element.querySelector('#reviews-statistics')) {
                    document.querySelector('#reviews-statistics').innerHTML = element.querySelector('#reviews-statistics').innerHTML;
                }

                SA_ReviewsAdd.initModals();
                if (this.canRepeat === false && this.isTimeRepeat) {
                    this.showModalResult(BX.message('repeat_time_message'), 'error');
                    return;
                }
                if (this.canRepeat === false) {
                    this.showModalResult(BX.message('repeat_message'), 'error');
                    return;
                }

                BX.ajax.processScripts(processed.SCRIPT);

                ratingValue = document.querySelector('#reviews-statistics .statistic__info-estimation__title').innerHTML;
                ratingCountValue = parseInt(document.querySelector('#reviews-statistics .statistic__info-estimation span.gray-text').innerHTML.match(/\d+/));
                itemsRating = document.querySelectorAll('[data-marker="product_rating"]');
                itemsReviewsCount = document.querySelectorAll('[data-marker="product_rvw_count"]');
                if (itemsRating) {
                    itemsRating.forEach(item => {
                        item.innerHTML = ratingValue;
                    })
                }
                if (itemsReviewsCount) {
                    itemsReviewsCount.forEach(item => {
                        item.innerHTML = ratingCountValue;
                    })
                }

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