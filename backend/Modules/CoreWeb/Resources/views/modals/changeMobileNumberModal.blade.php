<x-coreweb::modal id="changeMobileNumberModal" title="{{ __('profile_page.Change Mobile Number') }}"
    titlePosition="text-center" subtitle="{{ __('profile_page.To change your mobile number, Please enter your new mobile number') }}"
    modalPosition="modal-dialog-centered" subtitlePosition="text-center" subTitleTextColor="text-secondary"
    modalHeaderPadding="" closeButtonHide="">

    @include('coreweb::components/error', ['heading' => 'Error'])

    <div class="mb-3">

        <div class="mb-4 select2-dropdown-container">
            <label for="newMobileNumber" class="form-label fw-semibold">{{ __('common.Mobile Number') }}</label>
            <div class="input-group custom-input-group">

                <div class="custom-select-wrapper">
                    <select class="form-select country-code-select countryCode">
                    </select>
                </div>

                <input type="text" class="form-control form-control-lg phone_number_check" id="newMobileNumber" name="newMobileNumber"
                    placeholder="Enter your mobile number" autofocus>
            </div>
        </div>

    </div>

    <div class="text-center">
        <a class="btn btn-success changeMobileNumberSendOTP" href="#" role="button">{{ __('common.Send OTP') }}</a>
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('common.Close') }}</button>
    </div>
</x-coreweb::modal>