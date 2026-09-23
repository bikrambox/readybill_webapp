<x-coreweb::modal id="signin" title="{{ __('modal.Sign In') }}" subtitle="{{ __('modal.subtitle4') }}" modalPosition=""
    titlePosition="text-start" subtitlePosition="text-start" modalHeaderPadding="" closeButtonHide=""
    subTitleTextColor="text-dark">

    <form id="signinForm" method="POST" action="{{ locale_route('login') }}">
        @csrf
        
        <input name="loginFrom" type="hidden" value="2" readonly />

        <div class="mb-4 select2-dropdown-container">
            <label for="mobile" class="form-label fw-semibold">{{ __('common.Mobile Number') }}</label>
            <div class="input-group custom-input-group">

                <!-- Country Code Dropdown -->
                <div class="custom-select-wrapper">
                    <select class="form-select country-code-select countryCode" id="" name="country_code">
                    </select>
                </div>

                <!-- Mobile Number Input -->
                <input type="text" class="form-control form-control-lg phone_number_check" id="signin_mobile" name="mobile"
                    placeholder="{{ __('common.Enter your mobile number') }}" autofocus>

            </div>
            <span class="text-danger signin_mobile-error d-none"></span>
        </div>

        <div class="mb-3">

            <label for="password" class="form-label fw-semibold">{{ __('common.Password') }}</label>
            <div class="input-group">
                <input type="password" class="form-control password" id="signin_password" name="password"
                    placeholder="{{ __('common.Password') }}" />
                <span class="input-group-text position-relative togglePasswordWrapper" data-target="signin_password">
                    <img class="togglePassword" src="{{ asset('assets/img/icons/eye.svg') }}" alt="Toggle Password"
                        style="width: 18px; height: 18px;">
                </span>
            </div>
            <span class="text-danger signin_password-error d-none"></span>
        </div>
        <div class="d-flex justify-content-between">
            <div><input type="checkbox" class="form-check-input" id="remember_me" name="remember_me" /><label
                    for="remember_me" class="form-check-label px-1">{{ __('common.Remember me') }}</label></div>
            <button type="button" class="btn btn-link"
                id="forgotPasswordButton">{{ __('common.Forgot Password') }}</button>

        </div>
        <div class="mt-3 text-end">
            <button type="submit" class="btn btn-primary">{{ __('common.Sign in') }}</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('common.Close') }}</button>
        </div>
    </form>

</x-coreweb::modal>