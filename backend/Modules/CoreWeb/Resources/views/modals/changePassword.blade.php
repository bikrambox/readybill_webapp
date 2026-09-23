<x-coreweb::modal id="changePasswordModal" title="{{ $loc == 'change' ? __('modal.Change Password') :
    ($loc == 'forgot' ? __('modal.Forgot Password') :
        ($loc == 'profile' ? __('modal.Mobile Number Update') : '')) }}" titlePosition="text-center" subtitle="{!! $loc == 'forgot' ? __('modal.subtitle2') :
    ($loc == 'change' ? __('modal.subtitle1') :
        ($loc == 'profile' ? __('modal.subtitle3') : '')) !!}" modalPosition="modal-dialog-centered"
    subtitlePosition="text-center" subTitleTextColor="text-secondary" modalHeaderPadding="" closeButtonHide="">

    @include('coreweb::components/error', ['heading' => 'Error'])

    <div class="mb-3">

        <div class="mb-4 select2-dropdown-container {{ ($loc == 'change') || ($loc == 'profile') ? 'd-none' : '' }}">
            <label for="mobile" class="form-label fw-semibold">{{ __('common.Mobile Number') }}</label>
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
        <a class="btn btn-success {{ ($loc == 'profile') ? 'updateMobileNumberOTPVerificaion' : 'generateOtpForm' }}"
            href="#" role="button">{{ __('common.Send OTP') }}</a>
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('common.Close') }}</button>
    </div>

</x-coreweb::modal>