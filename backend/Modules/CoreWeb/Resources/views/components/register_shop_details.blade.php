<div class="position-fixed top-0 end-0 h-100 p-4 d-none" style="width: 650px;" id="registerSection-4">
    <div class="card h-100 bg-light bg-opacity-90 shadow d-flex flex-column"
        style="border-radius: 25px; overflow: hidden;">
        <div class="card-body d-flex flex-column p-4 p-md-5 flex-grow-1">
            <div class="row flex-grow-1 overflow-auto" id="" style="overflow-y: auto; max-height: 85vh;">
                <div class="col-12">
                    <img src="{{ asset('assets/img/favicon/64.png') }}" alt="probill" class="mb-4" width="50">
                    <p class="fw-light mb-2">
                        {{  __('login_page.Welcome to') }}
                        <a href="/"
                            class="fw-normal text-primary text-decoration-none hover-underline">{{  __('common.Ready Bill') }}</a>
                    </p>
                    <h3 class="fw-medium mb-4">{{ __('register_page.Almost there. Enter the shop details') }}</h3>

                    @include('coreweb::components/error', ['heading' => 'Error'])

                    <form id="shopDetailForm" class="d-flex flex-column gap-3">
                        <input type="hidden" name="user_id" id="user_id" value="" readonly />
                        <input type="text" class="form-control" id="name" name="name"
                            placeholder="{{ __('register_page.Your Full Name') }}*" />
                        <input type="text" class="form-control" id="business_name" name="business_name"
                            placeholder="Business Name*" />
                        <input type="email" class="form-control" id="email" name="email"
                            placeholder="{{ __('register_page.Email') }}" />
                        <span class="text-danger email-error d-none"></span>

                        <textarea class="form-control" id="address" name="address" rows="3"
                            placeholder="{{ __('register_page.Address') }}*"></textarea>
                        <span class="text-danger address-error d-none"></span>

                        <input type="text" class="form-control" id="gstin" name="gstin"
                            placeholder="{{ __('register_page.GSTIN Number') }}" />
                        <span class="text-danger gstin-error d-none"></span>

                        <div class="file-upload-container">
                            <label for="logo" class="custom-file-upload">
                                {{ __('register_page.Upload Shop photo or logo') }}
                            </label>
                            <input type="file" id="logo" name="logo" accept="image/*" />
                            <label for="logo" class="file-name">{{ __('register_page.No file selected') }}</label>
                        </div>


                        <!-- <input type="file" class="form-control" id="logo" name="logo" /> -->
                        <span class="text-danger logo-error d-none"></span>

                        <div class="text-end d-none clear-button-div">
                            <button type="button" class="btn btn-link"
                                id="clear-button">{{ __('common.Clear') }}</button>
                        </div>

                        <div class="text-center d-none preview-image-div">
                            <img id="preview-image" src="#" alt="Preview Image" class="img-fluid rounded">
                        </div>

                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="terms_n_conditions"
                                name="terms_n_conditions">
                            <label class="form-check-label" for="terms_n_conditions">
                                {{ __('register_page.I accept the') }} <a href="{{  locale_route('terms.and.conditions') }}"
                                    target="_BLANK">
                                    {{ __('register_page.Terms and Conditions') }}</a>
                            </label>
                            <span class="text-danger terms_n_conditions-error d-none"></span>
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary btn-lg w-100"
                                form="shopDetailForm">{{ __('register_page.Next') }}</button>
                            <hr class="my-3">
                            <p class="text-center text-muted small mb-0">
                                {{ __('login_page.By continuing, you agree to our') }} <strong><a href="/privacy-policy"
                                        target="_blank">{{ __('login_page.Privacy Policy') }}</a></strong>
                                {{ __('login_page.and') }} <a href="{{ locale_route('terms.and.conditions') }}"
                                    target="_blank"><strong>{{ __('login_page.Terms of Use') }}</strong></a>
                            </p>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>