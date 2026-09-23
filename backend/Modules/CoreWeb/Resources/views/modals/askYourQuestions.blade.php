<x-coreweb::modal id="askYourQuestionModal" title="Ask Your Questions" titlePosition="text-start"
    subtitle="Fields marked with a star (*) are mandatory" modalPosition="modal-dialog-centered"
    titlePosition="text-start" subtitlePosition="text-start" subTitleTextColor="text-danger" modalHeaderPadding=""
    closeButtonHide="">

    <div class="row">
        <form id="send-query">
            <div class="col-12 mb-3">
                <label for="title" class="form-label fw-bold">{{ __('common.Title') }}<span class="text-danger">*</span></label>
                <input type="title" class="form-control" id="title" name="title" placeholder="{{ __('common.Title') }}" autofocus />
                <span class="text-danger title-error d-none"></span>
            </div>
            <div class="col-12 mb-3">
                <label for="description" class="form-label fw-bold">{{ __('common.Description') }}<span class="text-danger">*</span></label>
                <textarea name="description" class="form-control" id="description" placeholder="{{ __('common.Title') }}"
                    style="height: 100px"></textarea>
                <span class="text-danger description-error d-none"></span>
            </div>
            <div class="col-12">
                <div class="row mb-3">
                    <label for="attachment" class="col-form-label fw-bold">{{ __('common.Attachment') }}</label>
                    <div class="col-12">
                        <div class="">
                            <!-- Image -->
                            <img src="" class="img-fluid mb-2 text-center d-none" id="attachmentFile" alt="attachment">

                            <!-- Delete Button -->
                            <button type="button" class="btn btn-link position-absolute d-none"
                                id="attachment-clear-button"
                                style="top: 0px; right: -60px; z-index: 1; font-size: 12px; color: red; background: rgba(255, 255, 255, 0.7); border-radius: 4px;">
                                {{ __('common.Delete') }}
                            </button>
                        </div>

                        <!-- Error Message -->
                        <span class="text-danger logo-error d-none attachementMsg"
                            style="padding-bottom:2px !important;">
                            {{ __('common.No attachment has been uploaded') }}
                        </span>

                        <!-- File Input -->
                        <input name="attachment" type="file" class="form-control" id="attachment" value="">
                        <span class="text-danger attachment-error d-none"></span>
                    </div>
                </div>
            </div>
            <div class="col-12 text-left">
                <button type="submit" class="btn btn-primary askYourQuestionsSubmitButton">{{ __('common.Send') }}</button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('common.Close') }}</button>
            </div>
        </form>
    </div>

</x-coreweb::modal>