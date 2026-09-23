<x-coreweb::modal id="deleteConfirmationModal" title="{{ __('profile_page.Enter OTP') }}"
    subtitle="{{ __('profile_page.Verify your mobile number by entering the OTP sent to your mobile number') }}"
    modalPosition="modal-dialog-centered" titlePosition="text-center" subtitlePosition="text-center"
    modalHeaderPadding="" closeButtonHide="" subTitleTextColor="text-secondary">
    <div class="">

        @include('coreweb::components/error', ['heading' => 'Error'])

        <!-- OTP Input Fields -->
        <div class="mb-3">
            <input type="hidden" class="form-control" name="otpModalType" id="otpModalType" value="" readonly />

            <div class="col-12 d-flex gap-2">
                @for($i = 1; $i <= 6; $i++)
                    <input type="text" class="form-control deleteotpbox text-center" maxlength="1" id="deleteotp-{{ $i }}"
                        data-index="{{ $i }}" />
                @endfor
            </div>
        </div>

        <div class="mb-3 d-flex justify-content-end align-items-center resend-section">

            <button type="button" class="resend-btn btn btn-link text-secondary p-0 m-0 px-2" disabled
                id="{{ isset($loc) && $loc == 'profile' ? 'profile-resend-otp-btn' : 'resend-otp-btn' }}">
                {{ __('common.Resend OTP') }}
            </button>
            <span class="text-secondary fw-bold me-2 resendText">{{ __('common.In') }}</span>
            <span class="text-secondary fw-bold resendText" id="otpTimer1">01:00</span>
        </div>

    </div>
</x-coreweb::modal>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const otpModal = document.getElementById('deleteConfirmationModal');

        otpModal.addEventListener('shown.bs.modal', function () {
            const firstOtpBox = document.getElementById('deleteotp-1');
            if (firstOtpBox) {
                firstOtpBox.focus(); // Auto-focus the first OTP input box
            }
        });
    });
</script>