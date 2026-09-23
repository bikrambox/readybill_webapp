<x-coreweb::modal id="signup" title="Sign Up" subtitle="Fields marked with a star (*) are mandatory" modalPosition=""
    titlePosition="text-start" subtitlePosition="text-start" modalHeaderPadding="" closeButtonHide=""
    subTitleTextColor="text-danger">

    <form id="signupForm" class="row g-3" method="POST" action="{{ locale_route('register.verify.otp') }}"
        enctype="multipart/form-data">
        @csrf
        <div class="col-md-6">
            <input type="text" class="form-control" id="name" name="name" placeholder="Full Name*" />
            <span class="text-danger name-error d-none"></span>
        </div>
        <div class="col-md-6">
            <input type="text" class="form-control" id="business_name" name="business_name"
                placeholder="Business Name*" />
            <span class="text-danger business_name-error d-none"></span>
        </div>

        <div class="col-md-6">
            <input type="email" class="form-control" id="email" name="email" placeholder="Email*" />
            <span class="text-danger email-error d-none"></span>
        </div>

        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text">+91</span>
                <input type="text" class="form-control" id="mobile" name="mobile" placeholder="Mobile Number*" />
            </div>
            <span class="text-danger mobile-error d-none"></span>
        </div>


        <div class="col-md-6">
            <input type="password" class="form-control" id="password" name="password" placeholder="Password*" />
            <span class="text-danger password-error d-none"></span>
        </div>
        <div class="col-md-6">
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                placeholder="Confirm Password*" />
            <span class="text-danger password_confirmation-error d-none"></span>
        </div>
        <div class="col-12">
            <textarea class="form-control" id="address" name="address" rows="3" placeholder="Address*"></textarea>
            <span class="text-danger address-error d-none"></span>
        </div>

        <div class="col-md-12">
            <input type="text" class="form-control" id="gstin" name="gstin" placeholder="GISTIN Number" />
            <span class="text-danger gstin-error d-none"></span>
        </div>

        <div class="col-md-12">
            <input type="file" class="form-control" id="logo" name="logo" />
            <span class="text-danger logo-error d-none"></span>
        </div>
        <div class="col-md-12 text-end d-none clear-button-div">
            <button type="button" class="btn btn-link" id="clear-button">Clear</button>
        </div>
        <div class="col-md-12 text-center d-none preview-image-div">
            <img id="preview-image" src="#" alt="Preview Image">
        </div>

        <div class="col-md-12">
            <input type="checkbox" class="form-check-input" id="terms_n_conditions" name="terms_n_conditions">
            <label class="form-check-label" for="terms_n_conditions">I accept the <a
                    href="{{locale_route('terms.and.conditions')}}" target="_BLANK">Terms and
                    Conditions</a></label>
            <br>
            <span class="text-danger terms_n_conditions-error d-none"></span>
        </div>

        <div class="col-12 text-end">
            <button type="submit" class="btn btn-primary">Sign Up</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
    </form>

</x-coreweb::modal>

<script>

</script>