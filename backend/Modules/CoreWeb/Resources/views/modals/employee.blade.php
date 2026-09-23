<x-coreweb::modal id="editUserModal" title="{{ __('employee_page.Employee Detail') }}"
    subtitle="{{ __('employee_page.Fields marked with a star (*) are mandatory') }}" modalPosition=""
    titlePosition="text-start" subtitlePosition="text-start" modalHeaderPadding="" closeButtonHide=""
    subTitleTextColor="text-danger" modalLg="">

    <form id="sub-user-profile-update">

        <input name="staff_id" type="hidden" class="form-control" id="staff_id" value="" readonly />
        <div class="row mb-3">
            <label for="fullName" class="col-md-4 col-lg-3 col-form-label">{{ __('common.Name') }}</label>
            <div class="col-md-8 col-lg-9">
                <input name="sub_user_name" type="text" class="form-control" id="sub_user_name" value="" autofocus />
                <span class="text-danger sub_user_name-error d-none"></span>
            </div>
        </div>

        <div class="row mb-3">
            <label for="company" class="col-md-4 col-lg-3 col-form-label">{{ __('common.Email') }}</label>
            <div class="col-md-8 col-lg-9">
                <input name="sub_user_email" type="text" class="form-control" id="sub_user_email" value="" />
                <span class="text-danger sub_user_email-error d-none"></span>
            </div>
        </div>

        <div class="row mb-3 select2-dropdown-container">
            <label for="company" class="col-md-4 col-lg-3 col-form-label">{{ __('common.Contact Number') }}<span
                    class="text-danger">*</span></label>
            <div class="col-md-8 col-lg-9">
                <div class="mb-4">
                    <div class="input-group custom-input-group">

                        <div class="custom-select-wrapper">
                            <select class="form-select country-code-select countryCode" id="">
                            </select>
                        </div>

                        <input type="text" class="form-control form-control-lg phone_number_check" id="sub_user_mobile"
                            name="sub_user_mobile" placeholder="{{ __('common.Enter your mobile number') }}" autofocus>
                    </div>
                    <span class="text-danger sub_user_mobile-error d-none"></span>
                </div>
            </div>
        </div>

        <div class="row mb-1">
            <label for="about" class="col-md-4 col-lg-3 col-form-label">{{ __('common.Address') }}</label>
            <div class="col-md-8 col-lg-9">
                <textarea name="sub_user_address" class="form-control" id="sub_user_address"
                    style="height: 100px"></textarea>
                <span class="text-danger sub_user_address-error d-none"></span>
            </div>
        </div>

        <div class="row mb-3 changePasswordButton">
            <label for="" class="col-md-4 col-lg-3 col-form-label"></label>
            <div class="col-md-8 col-lg-9">
                <button type="button"
                    class="btn btn-link text-danger pt-4 upadateEmpPasswordChangeButton">{{ __('common.Change Password') }}</button>
            </div>
        </div>

        <div class="row mb-3 d-none passwordChangeSection">
            <label for="Job" class="col-md-4 col-lg-3 col-form-label">{{ __('common.New Password') }}</label>
            <div class="col-md-8 col-lg-9">
                <!-- <input name="sub_user_password" type="password" class="form-control" id="sub_user_password" value=""> -->
                <div class="input-group">
                    <input type="password" class="form-control password" id="sub_user_password" name="sub_user_password"
                        placeholder="{{ __('common.Password') }}" />
                    <span class="input-group-text position-relative togglePasswordWrapper"
                        data-target="sub_user_password">
                        <img class="togglePassword" src="{{ asset('assets/img/icons/eye.svg') }}" alt="Toggle Password"
                            style="width: 18px; height: 18px;">
                    </span>
                </div>
                <span class="text-danger sub_user_password-error d-none"></span>
            </div>
        </div>

        <div class="row mb-3 d-none passwordChangeSection">
            <label for="Job" class="col-md-4 col-lg-3 col-form-label">{{ __('common.Confirm New Password') }}<span
                    class="text-danger">*</span></label>
            <div class="col-md-8 col-lg-9">
                <!-- <input name="sub_user_password_confirmation" type="password" class="form-control"
                    id="sub_user_password_confirmation" value=""> -->

                <div class="input-group">
                    <input type="password" class="form-control password" id="sub_user_password_confirmation"
                        name="sub_user_password_confirmation" placeholder="{{ __('common.Password') }}" />
                    <span class="input-group-text position-relative togglePasswordWrapper"
                        data-target="sub_user_password_confirmation">
                        <img class="togglePassword" src="{{ asset('assets/img/icons/eye.svg') }}" alt="Toggle Password"
                            style="width: 18px; height: 18px;">
                    </span>
                </div>
                <span class="text-danger sub_user_password_confirmation-error d-none"></span>
            </div>
        </div>

        <div class="row mb-3">
            <label for="Job" class="col-md-4 col-lg-3 col-form-label">{{ __('common.Photo') }}</label>
            <div class="col-md-8 col-lg-9">
                <div class="position-relative d-inline-block">
                    <!-- Image -->
                    <img src="" class="img-fluid mb-2 text-center d-none" id="userPhoto" alt="Logo">

                    <!-- Delete Button -->
                    <button type="button" class="btn btn-link position-absolute d-none" id="photo-clear-button"
                        style="top: 0px; right: -60px; z-index: 1; font-size: 12px; color: red; background: rgba(255, 255, 255, 0.7); border-radius: 4px;">
                        Delete
                    </button>
                </div>

                <!-- File Input -->
                <input name="photo" type="file" class="form-control" id="photo" value="" accept="image/*" />
                <input type="hidden" name="isPhotoDelete" id="isPhotoDelete" value="0" readonly />
                <span class="text-danger sub_user_photo-error d-none"></span>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6 text-start">
                <button type="submit" class="btn btn-primary sub_user_updateBtn">{{ __('common.Update') }}</button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('common.Cancel') }}</button>
            </div>
        </div>

    </form>

</x-coreweb::modal>