<x-coreweb::modal id="shareInvoiceModal" title="{{ __('modal.Send Invoice') }}" titlePosition="text-center" subtitle="" modalPosition="modal-dialog-centered"
    subtitlePosition="text-center" subTitleTextColor="text-secondary" modalHeaderPadding="" closeButtonHide="">

    @include('coreweb::components/error', ['heading' => 'Error'])

    <div class="mb-3">

        <div class="mb-4 select2-dropdown-container">
            <label for="mobile" class="form-label">{{ __('common.Mobile Number') }}</label>
            <div class="input-group custom-input-group">

                <div class="custom-select-wrapper">
                    <select class="form-select country-code-select countryCode" id="">
                    </select>
                </div>

                <input type="text" class="form-control form-control-lg phone_number_check" id="phone_number" name="phone_number"
                    placeholder="{{ __('common.Enter your mobile number') }}" autofocus>
            </div>
        </div>

    </div>

    <div class="text-center">
        <a class="btn btn-success" id="sendSMS"
            href="#" role="button">{{ __('common.Send') }}</a>
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('common.Close') }}</button>
    </div>

</x-coreweb::modal>