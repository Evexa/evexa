(function () {
    SA_ReviewsAdd = {
        init: function (params) {
            this.mainModal = document.getElementById('review_add__modal');
            this.form = this.mainModal.querySelector('form');

            if (!this.form) {
                return;
            }

            this.btnSubmit = this.mainModal.querySelector('button[type="submit"]');

            this.ratingNumber = params.ratingNumber;
            this.ratingButtons = this.form.querySelectorAll('button[data-rating]');
            this.ratingDescriptions = this.form.querySelectorAll('.rating_description span[data-rating]');
            this.ratingInput = this.form.querySelector('input[name="RATING"]');
            this.hiddenInputs = [];
            this.fileUploadUrl = params.fileUploadUrl;
            this.imagesFolder = params.imagesFolder;
            this.canAddReview = params.canAddReview;
            this.canAddReviewError = params.canAddReviewError;
            this.isAuthUser = params.isAuthUser;
            this.isAnonymous = params.isAnonymous;
            this.isBuyProduct = params.isBuyProduct;

            this.canRepeat = !!params.canRepeat;
            this.modRepeat = params.modRepeat ? params.modRepeat : -1;
            this.countRepeat = params.countRepeat ? params.countRepeat:0;

            this.isTimeRepeat = params.isTimeRepeat == 'Y';
            this.isModerate = params.isModerate == 'Y';
            this.configFile = JSON.parse(params.configFile);
            this.signedParameters = params.signedParameters;
            this.counterImage = 0;
            this.counterVideo = 0;
            this.initModals();
            this.dropzoneInit();
            this.btnSubmit.addEventListener('click', this.sendReview.bind(this));
            this.form.addEventListener('submit', this.sendReview.bind(this));
            BX.onCustomEvent('sotbit:reviews.reviewsAddInit', [this]);
        },

        getParams: function (param){
            let params = (new URL(document.location)).searchParams;
            let result = {};

            for (var pair of params.entries()) {
                result[pair[0]] =  pair[1];
            }

            return result;
        },

        initModals: function () {
            document.addEventListener('click', async (event) => {
                const button = event.target.closest('[data-action="show-modal"]');

                if (button) {
                const target = button.dataset.target;

                if (!target) {
                    return;
                }

                const modal = document.getElementById(target);

                if (!modal) {
                    return;
                }

                    modal.querySelector('.content__reviews__item__body--quote').classList.add('display-none-important');
                    modal.querySelector('input[name="ID_QUOTE"]').value = '';

                    if (this.canAddReview === false) {
                        this.showModalResult(this.canAddReviewError, 'error');
                        return;
                    }

                    if (this.canRepeat === false ) {
                        this.showModalResult(BX.message('repeat_message'), 'error');
                        return;
                    }

                    !window.SimpleBar && await BX.loadExt('sotbit.b2c.simplebar');

                    BX.Sotbit.Reviews.showElement(modal);
                    BX.Sotbit.Reviews.fixBodyScroll();

                    if (button.dataset.id) {
                        modal.querySelector('.review_add__title').innerHTML = BX.message('title_quite');
                        modal.querySelector('.content__reviews__item__body--quote').classList.remove('display-none-important');
                        modal.querySelector('input[name="ID_QUOTE"]').value = button.closest('.reviews__content').querySelector('.content__reviews__item__body p').innerHTML;
                        modal.querySelector('.reviews--quote p').innerHTML = button.closest('.reviews__content').querySelector('.content__reviews__item__body p').innerHTML;
                        modal.querySelector('.reviews--quote p').setAttribute('title', modal.querySelector('.reviews--quote p').innerHTML);
                    }else{
                        modal.querySelector('.review_add__title').innerHTML = BX.message('title_origin');
                    }

                    if (button.dataset.target !== 'policy_modal') {
                        if (!this.overlay) {
                            this.overlay = BX.Sotbit.Reviews.showOverlay();
                    }
                        BX.Sotbit.Reviews.fixBodyScroll();
                        }

                const anonymousCheckbox = modal.querySelector('#review_add__modal input[type="checkbox"]');
                const anonymousHiddenField = this.form.querySelector('input[name="ANONYMOUS"]');

                    if (this.isAnonymous === 'Y' && anonymousCheckbox) {
                    anonymousCheckbox.addEventListener('change', () => {
                        anonymousHiddenField.value = anonymousCheckbox.checked ? 'Y' : 'N';
                    });
                }

                    this.overlay.element.addEventListener('click', () => {
                        BX.Sotbit.Reviews.hideElement(modal, {
                            callback: () => {
                                if (modal === this.mainModal) {
                                    BX.Sotbit.Reviews.unfixBodyScroll();
                                }
                            }
                        });
                        this.overlay?.hide();
                        this.overlay = null;
                        this.deleteFormField(modal);
                    }, { once: true });
                }

                const closeButton = event.target.closest('[data-action="close-modal"]');

                if (closeButton) {
                    const modal = closeButton.closest('#review_add__modal');

                    if (modal) {
                        BX.Sotbit.Reviews.hideElement(modal, {
                            callback: () => {
                                this.overlay?.hide();
                                this.overlay = null;
                                BX.Sotbit.Reviews.unfixBodyScroll();

                        this.deleteFormField(modal);
                    }
                });
                    }
                }
            });

            this.setRatingValue(this.ratingNumber);
            this.showRating(this.ratingNumber);
            this.initStarRatingEvents();
        },

        initStarRatingEvents: function () {
            this.ratingButtons.forEach(button => button.addEventListener('click', () => {
                const rating = button.dataset.rating;
                this.setRatingValue(rating);

                this.showRating(rating);
            }));

            this.ratingButtons.forEach(button => button.addEventListener('mouseover', () => {
                this.showRating(button.dataset.rating);
            }));

            this.ratingButtons.forEach(button => button.addEventListener('mouseout', () => {
                this.showRating(this.ratingInput.value);
            }));
        },

        setRatingValue: function (rating) {
            this.ratingInput.value = rating;
        },

        showRating: function (rating) {
            this.ratingButtons.forEach(button => {
                const ratingStarFilled = button.querySelector('.review_add__star-icon--filled');

                if (button.dataset.rating > rating) {
                    ratingStarFilled.style.opacity = 0;
                } else {
                    ratingStarFilled.style.opacity = 1;
                }
            });
        },

        counterFile: function (type) {
           let typeFile =  this.checkMimeType(type);
            if(typeFile == 'image'){
                ++this.counterImage;
            }if(typeFile == 'video'){
                ++this.counterVideo;
            }
            return typeFile;
        },

        counterFileMinus: function (type) {
            let typeFile =  this.checkMimeType(type);
            if(typeFile == 'image'){
                --this.counterImage;
            }if(typeFile == 'video'){
                --this.counterVideo;
            }
            return typeFile;
        },

        checkMimeType: function (mime){

            if(this.isImage(mime)){
                return 'image';
            }

            if (this.isVideo(mime)){
                return 'video';
            }

            return 'error';
        },

        isImage: function (mime){
            arrayOfStrings = mime.split(';');
            strings = arrayOfStrings[0].trim();
            let result = strings.match('^image');
            return result;
        },

        isVideo: function (mime){
            arrayOfStrings = mime.split(';');
            strings = arrayOfStrings[0].trim();
            let result = strings.match('^video');
            return result;
        },

        bytesToSize: function (bytes, precision = 0) {
            if (bytes === 0) return '0';

            const index = Math.floor(Math.log(bytes) / Math.log(1024));

            return index;
        },

        dropzoneInit: function () {

            if(this.configFile.REVIEWS_UPLOAD_FILE == 'NO' || !this.configFile.REVIEWS_UPLOAD_FILE){
                return;
            }

            let countFile = 0;
            let size = 0;
            let acceptedFiles = '.jpg, .gif, .bmp, .png, .jpeg, .webp, .mp4, .webm';

            if(this.configFile.REVIEWS_UPLOAD_FILE == 'IMAGE'){
                acceptedFiles = '.jpg, .gif, .bmp, .png, .jpeg, .webp';
                countFile = Number(this.configFile.REVIEWS_MAX_COUNT_IMAGES);
                size = Number(this.configFile.REVIEWS_MAX_IMAGE_SIZE);
            }

            if(this.configFile.REVIEWS_UPLOAD_FILE == 'IMAGE_VIDEO'){
                acceptedFiles = '.jpg, .gif, .bmp, .png, .jpeg, .webp, .mp4, .webm';
                countFile = (Number(this.configFile.REVIEWS_MAX_COUNT_VIDEO) + Number(this.configFile.REVIEWS_MAX_COUNT_IMAGES));
                size = Number(this.configFile.REVIEWS_MAX_VIDEO_SIZE);
            }
            if(!this.mainModal.querySelector('#dropzone_media')){
                return;
            }
            this.dropzone = new Dropzone(this.mainModal.querySelector('#dropzone_media'), {
                previewTemplate: '<div class="dz-file-preview dz-preview"> <div class="dz-image"><img data-dz-thumbnail=""></div> <div class="dz-details"><div class="dz-filename"><span data-dz-name=""></span></div> </div> <div class="dz-progress"> <span class="dz-upload" data-dz-uploadprogress=""></span> </div> <div class="dz-error-message"><span data-dz-errormessage=""></span></div> <div class="dz-success-mark"> <svg width="54" height="54" fill="#fff"><path d="m10.207 29.793 4.086-4.086a1 1 0 0 1 1.414 0l5.586 5.586a1 1 0 0 0 1.414 0l15.586-15.586a1 1 0 0 1 1.414 0l4.086 4.086a1 1 0 0 1 0 1.414L22.707 42.293a1 1 0 0 1-1.414 0L10.207 31.207a1 1 0 0 1 0-1.414Z"/></svg> </div> <div class="dz-error-mark"> <svg width="54" height="54" fill="#fff"><path d="m26.293 20.293-7.086-7.086a1 1 0 0 0-1.414 0l-4.586 4.586a1 1 0 0 0 0 1.414l7.086 7.086a1 1 0 0 1 0 1.414l-7.086 7.086a1 1 0 0 0 0 1.414l4.586 4.586a1 1 0 0 0 1.414 0l7.086-7.086a1 1 0 0 1 1.414 0l7.086 7.086a1 1 0 0 0 1.414 0l4.586-4.586a1 1 0 0 0 0-1.414l-7.086-7.086a1 1 0 0 1 0-1.414l7.086-7.086a1 1 0 0 0 0-1.414l-4.586-4.586a1 1 0 0 0-1.414 0l-7.086 7.086a1 1 0 0 1-1.414 0Z"/></svg> </div> </div>',
                url: this.fileUploadUrl,
                parallelUploads: 100,
                maxFiles: 100,
                maxFilesize: size,
                paramName: 'FILES_MEDIA',
                acceptedFiles: acceptedFiles,
                dictDefaultMessage: BX.message('dropzone_message'),
                dictRemoveFile: BX.message('dropzone_delete'),
                addRemoveLinks: true,
                dictMaxFilesExceeded: BX.message('dropzone_error_max_files'),
                dictCancelUpload: BX.message('dropzone_delete'),
                dictUploadCanceled: BX.message('dropzone_cancel'),
                dictFileTooBig: BX.message('dropzone_file_too_big'),
                dictInvalidFileType: BX.message('error_file_type'),
                thumbnailWidth: 90,
                thumbnailHeight: 90
            });

            this.dropzone.on("maxfilesexceeded", () => {
                return BX.Sotbit.Reviews.showMessage(BX.message('dropzone_error_max_files'), {icon: 'error'});
            });

            this.dropzone.previewsContainer.prepend(BX.create('button', {
                props: {
                    className: 'dz-placeholder',
                    type: 'button'
                },
                html: `
                        <svg viewBox="0 0 24 24" width="24">
                            <use xlink:href="${this.imagesFolder}/camera.svg#icon"></use>
                        </svg>
                    `
            }));

            this.dropzone.on("addedfile", (file)  => {
                let type =  this.checkMimeType(file.type);

                this.hiddenElement(this.dropzone.previewsContainer.querySelector('.dz-default.dz-message'));
                this.viewedElement(this.dropzone.previewsContainer.querySelector('button.dz-placeholder'));

                if (type === 'video') {
                    this.createVideoThumbnail(file);
                }

               if( type == 'image' && this.configFile.REVIEWS_MAX_IMAGE_SIZE < this.bytesToSize(file.size)){
                   if(!file.edit){
                       file.previewElement.querySelector("[data-dz-errormessage]").innerHTML = BX.message('error_image_size');
                       file.previewElement.classList.add('dz-error');
                       throw new Error(BX.message('error_image_size'));
                   }
               }else{
                   if(file.previewElement.classList.contains('dz-error')){
                       file.previewElement.classList.remove('dz-error');
                   }
               }

                if( type == 'video' && this.configFile.REVIEWS_MAX_COUNT_VIDEO < this.bytesToSize(file.size)){
                    if(!file.edit) {
                        file.previewElement.querySelector("[data-dz-errormessage]").innerHTML = BX.message('error_video_size');
                        file.previewElement.classList.add('dz-error');
                        throw new Error(BX.message('error_video_size'));
                    }
                }else{
                    if(file.previewElement.classList.contains('dz-error')){
                        file.previewElement.classList.remove('dz-error');
                    }
                }

                this.counterFile(file.type);

                if(this.configFile.REVIEWS_MAX_COUNT_VIDEO < this.counterVideo && type=='video'){
                     file.previewElement.querySelector("[data-dz-errormessage]").innerHTML = BX.message('error_video');
                     file.previewElement.classList.add('dz-error');
                     throw new Error(BX.message('error_video'));
                }
                if(this.configFile.REVIEWS_MAX_COUNT_IMAGES < this.counterImage && type=='image'){
                    file.previewElement.querySelector("[data-dz-errormessage]").innerHTML = BX.message('error_image');
                    file.previewElement.classList.add('dz-error');
                    throw new Error(BX.message('error_image'));
                }

                this.dropzone.previewsContainer.querySelectorAll('.dz-placeholder').forEach(element => {
                    if (element.localName != 'button') {
                        element.remove()
                    }
                });

                for (let i = 0; i < this.dropzone.options.maxFiles - this.dropzone.files.length; i++) {
                    this.dropzone.previewsContainer.appendChild(BX.create('div', {
                        props: {
                            className: 'dz-placeholder'
                        }
                    }));
                }
            });

            this.dropzone.on("success", (file, response) => {
                const result = JSON.parse(response);

                if(result.error){
                    file.previewElement.querySelector("[data-dz-errormessage]").innerHTML = result.error.message;
                    file.previewElement.classList.add('dz-error');
                }

                if(result.file.id){
                    file.id = result.file.id;
                    this.form.appendChild(
                        this.hiddenInputs[result.file.id] = BX.create('input', {
                            attrs: {
                                'type': 'hidden',
                                'name': 'MEDIA[]',
                                'value': result.file.id
                            }
                        })
                    );
                }
            });

            this.dropzone.on("removedfile", file => {

                this.counterFileMinus(file.type);
                BX.remove(this.hiddenInputs[file.id]);

                BX.ajax.post(
                    this.fileUploadUrl,
                    {
                        action: 'removedfile',
                        fileId: file.id
                    }
                );

                if (!this.dropzone.files.length && !this.dropzone.previewsContainer.querySelector('.dz-image-preview')) {
                    this.dropzone.previewsContainer.querySelectorAll('.dz-placeholder').forEach((e) => {
                        if(e.classList.contains('d-block')){
                            this.hiddenElement(e)
                        }else{
                            e.remove();
                        }
                    });

                    this.viewedElement(this.dropzone.previewsContainer.querySelector('.dz-default.dz-message'))

                }
            });
        },

        createVideoThumbnail: function (file) {
            const imageContainer = file.previewElement.querySelector('.dz-image');
            if (!imageContainer) {
                console.warn('imageContainer not found!');
                return;
            }

            const img = file.previewElement.querySelector('img[data-dz-thumbnail]');
            if (!img) {
                console.warn('img not found!');
                return;
            }

            const video = document.createElement('video');
            video.setAttribute('data-dz-thumbnail', '');

            const source = document.createElement('source');
            source.src = URL.createObjectURL(file)
            source.type = file.type;

            video.append(source);

            imageContainer.replaceChild(video, img);
        },

        hiddenElement: function (elem) {
            if(elem){
                if(elem.classList.contains('d-block')){
                    elem.classList.remove('d-block')
                }
                elem.classList.add('display-none-important');
            }
        },

        viewedElement: function (elem) {
            if(elem){
                if(elem.classList.contains('display-none-important')){
                    elem.classList.remove('display-none-important')
                }
                elem.classList.add('d-block');
            }
        },

        sendReview: function (event) {
            event.preventDefault();

            if (!this.form.reportValidity()) {
                return;
            }

            let form = new FormData(this.form);

            if (this.form.dataset.active == 'edit') {
                SA_ReviewsList.editReviewsController(this.form)
            }

            if (this.form.dataset.active == 'add') {
                BX.ajax.runComponentAction('sotbit:rvw.reviews.add', 'addReviews', {
                    mode: 'class',
                    data: new FormData(this.form),
                    signedParameters: this.signedParameters,
                }).then (
                     (e) => {

                        BX.Sotbit.Reviews.hideElement(this.mainModal, {
                            callback: () => {
                                BX.Sotbit.Reviews.hideOverlay();
                            }
                        });
                        BX.Sotbit.Reviews.unfixBodyScroll();

                         if (this.isModerate) {
                             if(form.get('ID_QUOTE')){
                                 this.showModalResult(BX.message('success_title_quote_moderate'), 'success', BX.message('success_quote_text'));
                             }else{
                                 this.showModalResult(BX.message('success_title_moderate'), 'success', BX.message('success_moderate_text'));
                             }
                         } else {
                             if(form.get('ID_QUOTE')){
                                 this.showModalResult(BX.message('success_title_quote'), 'success');
                             }else{
                                 this.showModalResult(BX.message('success_title'), 'success');
                             }
                         }

                         if(this.modRepeat >= 0){
                             this.canRepeat = false;
                         }
                         this.deleteFormField();
                    },
                    (error) => {
                        console.log(error)
                        this.showModalResult(error.errors.map(item => item.message).join('\n'), 'error')
                    }
                );

                setTimeout(() => {
                    SA_Reviews.reloadList(this.getParams());
                }, "300");
            }
        },

        deleteFormField: function (modal) {


            this.form.querySelector('input[name="ID_QUOTE"]').value = '';

            if(!modal){
                modal = document.querySelector('#review_add__modal');
            }

            modal.querySelector('.review_add__title').innerHTML = BX.message('title_origin');
            modal.querySelector('.content__reviews__item__body--quote').classList.add('display-none-important');

            this.form.reset();
            this.form.dataset.active = 'add';
            this.mainModal.querySelectorAll('input[name="MEDIA[]"]').forEach((e) => e.remove());
            this.counterImage = 0;
            this.counterVideo = 0;

            if(this.dropzone){
                this.dropzone.previewsContainer.querySelectorAll('.dz-preview').forEach((e) => e.remove());
                this.dropzone.countFile = this.counterImage;
                this.hiddenElement(this.dropzone?.previewsContainer.querySelector('button.dz-placeholder'));
                this.viewedElement(this.dropzone?.previewsContainer.querySelector('.dz-default.dz-message'));
            }

            if (modal.querySelectorAll('.dz-preview.dz-complete.dz-image-preview'))
                modal.querySelectorAll('.dz-preview.dz-complete.dz-image-preview').forEach((e) => e.remove());

            if (modal.querySelectorAll('.dz-file-preview.dz-preview.dz-error'))
                modal.querySelectorAll('.dz-file-preview.dz-preview.dz-error').forEach((e) => e.remove());

            this.counterImage = 0;
            this.counterVideo = 0;

            idNode = modal.querySelector('input[name="ID"]');

            if(idNode){
                idNode.remove();
            }

        },

        showModalResult: function (title, status = '', text = '') {
            if (status == 'success') {
                icon = `<svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M32.0007 5.33325C46.7282 5.33325 58.6673 17.2723 58.6673 31.9999C58.6673 46.7275 46.7282 58.6666 32.0007 58.6666C17.2731 58.6666 5.33398 46.7275 5.33398 31.9999C5.33398 17.2723 17.2731 5.33325 32.0007 5.33325ZM32.0007 9.33325C19.4822 9.33325 9.33398 19.4815 9.33398 31.9999C9.33398 44.5184 19.4822 54.6666 32.0007 54.6666C44.5191 54.6666 54.6673 44.5184 54.6673 31.9999C54.6673 19.4815 44.5191 9.33325 32.0007 9.33325ZM28.6673 35.8382L40.5864 23.919C41.3675 23.138 42.6338 23.138 43.4149 23.919C44.1249 24.6291 44.1895 25.7402 43.6085 26.5232L43.4149 26.7475L30.0815 40.0808C29.3715 40.7908 28.2604 40.8554 27.4774 40.2744L27.2531 40.0808L20.5864 33.4141C19.8054 32.6331 19.8054 31.3668 20.5864 30.5857C21.2965 29.8757 22.4076 29.8111 23.1905 30.3921L23.4149 30.5857L28.6673 35.8382L40.5864 23.919L28.6673 35.8382Z" fill="#18B131"/>
                    </svg>`;
                btns = ``;
            }
            if (status == 'error') {
                icon = `<svg width="73" height="72" viewBox="0 0 73 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g clip-path="url(#clip0_6093_11246)">
                                        <path d="M36.5 72C56.3823 72 72.5 55.8823 72.5 36C72.5 16.1177 56.3823 0 36.5 0C16.6177 0 0.5 16.1177 0.5 36C0.5 55.8823 16.6177 72 36.5 72Z" fill="#E31C1C"/>
                                        <g clip-path="url(#clip1_6093_11246)">
                                        <path d="M56 20L52 16L36 32L20 16L16 20L32 36L16 52L20 56L36 40L52 56L56 52L40 36L56 20Z" fill="white"/>
                                        </g>
                                        </g>
                                        <defs>
                                        <clipPath id="clip0_6093_11246">
                                        <rect width="72" height="72" fill="white" transform="translate(0.5)"/>
                                        </clipPath>
                                        <clipPath id="clip1_6093_11246">
                                        <rect width="40" height="40" fill="white" transform="translate(16 16)"/>
                                        </clipPath>
                                        </defs>
                                    </svg>
                `;

                btns = ``;
            }

            if (status == 'selection') {
                icon = `<svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M31.9997 17.3333C33.1042 17.3333 33.9997 18.2287 33.9997 19.3333V35.9999C33.9997 37.1045 33.1042 37.9999 31.9997 37.9999C30.8951 37.9999 29.9997 37.1045 29.9997 35.9999V19.3333C29.9997 18.2287 30.8951 17.3333 31.9997 17.3333ZM31.9997 46.6607C33.4724 46.6607 34.6663 45.4668 34.6663 43.9941C34.6663 42.5213 33.4724 41.3274 31.9997 41.3274C30.5269 41.3274 29.333 42.5213 29.333 43.9941C29.333 45.4668 30.5269 46.6607 31.9997 46.6607ZM31.9997 5.33325C46.7273 5.33325 58.6663 17.2723 58.6663 31.9999C58.6663 46.7275 46.7273 58.6666 31.9997 58.6666C27.6842 58.6666 23.5139 57.6388 19.7673 55.7013L9.56512 58.5475C7.792 59.0426 5.95322 58.0066 5.45809 56.2335C5.29425 55.6468 5.29433 55.0263 5.4582 54.4401L8.30531 44.2459C6.36332 40.4959 5.33301 36.3206 5.33301 31.9999C5.33301 17.2723 17.2721 5.33325 31.9997 5.33325ZM31.9997 9.33325C19.4812 9.33325 9.33301 19.4815 9.33301 31.9999C9.33301 35.9189 10.3271 39.6889 12.1944 43.0331L12.5961 43.7526L9.6289 54.377L20.2598 51.4112L20.9786 51.8119C24.3198 53.6749 28.0855 54.6666 31.9997 54.6666C44.5181 54.6666 54.6663 44.5184 54.6663 31.9999C54.6663 19.4815 44.5181 9.33325 31.9997 9.33325Z" fill="#FF9935"/>
                </svg>
                `;

                btns = ` 
                    <button class="btn-reviews btn-lite-reviews w-40 " value="Y">${BX.message('complains_yes')}</button>
                    <button class="btn-reviews btn-reviews--main w-40 " value="N">${BX.message('complains_no')}</button>
                `;
            }

            const modalResult = BX.create('DIV', {
                props: {
                    className: 'review_modal modal_result show',
                    id: 'modal_result'
                },
                html: `<div class="modal-content">
                        <div class="review_add__modal_header">
                           ${icon}
                        </div>
                        <div class="modal_result__content">
                            <h5>${title}</h5>
                            <div>${text}</div>
                        </div>
                            <div class="review_add__modal_footer d-flex">
                                ${btns}
                            </div>
                        </div>`});

            document.body.appendChild(modalResult);

            window.addEventListener('click', (event) => {

                if (event.target === modalResult) {
                    this.closeModalResult();
                }

                if (event.target === modalResult.querySelector('button')) {
                    if(modalResult.querySelector('button').value == ''){
                        this.closeModalResult();
                    }else{
                        if(status == 'selection'){
                            modalResult.querySelectorAll('button').forEach(item => {
                                if(item == event.target){
                                    if(event.target.value == 'Y'){
                                        console.log(event)
                                        SA_ReviewsList.elementComplaintController();
                                    }
                                }
                            });
                        }

                    }
                }

                if(event.target.value == 'N'){
                    this.closeModalResult();
                }

            })
        },

        closeModalResult: function () {
            document.body.classList.remove('overflow-hidden');
            document.getElementById('modal_result')?.remove();
            BX.Sotbit.Reviews.hideOverlay();
        }
    }
})();
