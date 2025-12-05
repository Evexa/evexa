function UserConsentView(params) {
    this.isAgreementShown = false;
    this.text = params.text;
    this.buttonId = params.buttonId;
    this.title = params.title;

    this.addEventListeners();
}

UserConsentView.prototype.showAgreement = function () {
    if (this.isAgreementShown) {
        return;
    }

    BX.Sotbit.Reviews.fixBodyScroll();

    swal.fire({
        title: this.title,
        html: `<div data-simplebar class="user-consent-view-modal">${this.text}</div>`,
        scrollbarPadding: false,
        width: 700,
        showCancelButton: true,
        showConfirmButton: false,
        cancelButtonText: BX.message('USER_CONSENT_VIEW_CLOSE'),
        showClass: {
            'popup': ''
        },
        hideClass: {
            'popup': ''
        },
        customClass: {
            title: 'user-consent-view-title',
            cancelButton: 'user-consent-view-cancel-button'
        }
    }).then(() => {
        BX.Sotbit.Reviews.unfixBodyScroll();
        this.isAgreementShown = false;
    });
}

UserConsentView.prototype.addEventListeners = function () {
    document.getElementById(this.buttonId).addEventListener('click', () => this.showAgreement());
}
