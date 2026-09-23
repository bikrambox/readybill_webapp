<x-coreweb::modal id="resetPasswordModal" title="New Password" subtitle="" modalPosition="modal-dialog-centered"
    titlePosition="text-center" subtitlePosition="text-center" modalHeaderPadding="" closeButtonHide=""
    subTitleTextColor="text-danger">
    <div class="">

        @include('coreweb::components/error', ['heading' => 'Error'])

        <form class="" id="change-password-form">

            <input type="hidden" name="phone_number" id="phone_number" value="" readonly />

            <div class="row mb-3">
                <div class="col-12 mb-3">

                    <!-- <input name="newPassword" type="password" class="form-control" id="newPassword" value="" autofocus
                        placeholder="{{ __('modal.New Password') }}" /> -->

                    <div class="input-group">
                        <input type="password" class="form-control password" id="newPassword" name="newPassword"
                            placeholder="Password" autofocus />
                        <span class="input-group-text position-relative togglePasswordWrapper"
                            data-target="newPassword">
                            <img class="togglePassword" src="{{ asset('assets/img/icons/eye.svg') }}"
                                alt="Toggle Password" style="width: 18px; height: 18px;">
                        </span>
                    </div>

                    <input type="hidden" readonly id="functionLoc" value="resetPasswordModal" />
                </div>

                <div class="col-12">

                    <div class="input-group">
                        <input type="password" class="form-control password" id="confirmNewPassword" name="confirmNewPassword"
                            placeholder="Password" />
                        <span class="input-group-text position-relative togglePasswordWrapper"
                            data-target="confirmNewPassword">
                            <img class="togglePassword" src="{{ asset('assets/img/icons/eye.svg') }}"
                                alt="Toggle Password" style="width: 18px; height: 18px;">
                        </span>
                    </div>

                    <!-- <input name="confirmNewPassword" type="password" class="form-control" id="confirmNewPassword"
                        value="" placeholder="{{ __('common.Confirm New Password') }}" /> -->
                </div>

            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">{{ __('modal.Change Password') }}</button>
            </div>
        </form>

    </div>
</x-coreweb::modal>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const otpModal = document.getElementById('otpModal');

        otpModal.addEventListener('shown.bs.modal', function () {
            const firstOtpBox = document.getElementById('otp-1');
            if (firstOtpBox) {
                firstOtpBox.focus(); // Auto-focus the first OTP input box
            }
        });
    });
</script>