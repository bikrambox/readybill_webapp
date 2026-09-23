@extends('coreweb::layouts.groceryIndia')
@section('title', "{{ __('modal.Change Password') }}")
@section('content')

        <div class="pagetitle">
            <h1>{{ __('modal.Change Password') }}</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="home">{{ __('common.Home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('modal.Change Password') }}</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section" id="changePasswordSection">
            <div class="row">

                <div class="col-xl-8">
                        @include('coreweb::components/error', [
                        'heading' => __('common.Subscription Alert'),
                        'modalIdAttribute' => 'subscripitonErrorId',
                    ])
                </div>

                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-body pt-3">

                            <form id="otp-verify-form">

                                <input name="user_id" type="hidden" class="form-control" id="user_id" value="" readonly />

                                <input type="hidden" readonly id="functionLoc" value="changePasswordSection" />

                                <div class="row mb-3">
                                    <label for="otp" class="col-md-4 col-lg-3 col-form-label fw-bold">{{ __('common.OTP') }}</label>
                                    <div class="col-md-8 col-lg-9 d-flex gap-2">
                                        <!-- Six OTP boxes -->
                                        @for($i = 1; $i <= 6; $i++)
                                            <input type="text" class="form-control otp-box text-center" maxlength="1"
                                                id="otp-{{ $i }}" data-index="{{ $i }}" />
                                        @endfor
                                    </div>
                                </div>

                                <!-- <div class="text-left">
                                    <button type="submit" class="btn btn-primary">Verify</button>
                                </div> -->
                            </form>
                            <!-- End Profile Edit Form -->

                            <form class="d-none" id="change-password-form">

                                <input type="hidden" name="mobile" id="mobile" value="{{$mobile_number}}" readonly />

                                <div class="row mb-3">
                                    <label for="newPassword" class="col-md-4 col-lg-3 col-form-label">{{ __('common.New Password') }}</label>
                                    <div class="col-md-8 col-lg-9">
                                        <!-- <input name="newPassword" type="password" class="form-control" id="newPassword" value=""
                                            placeholder="New Password" /> -->

                                        <div class="input-group">
                                            <input type="password" class="form-control password" id="newPassword" name="newPassword" placeholder="Password" />
                                            <span class="input-group-text position-relative togglePasswordWrapper" data-target="newPassword">
                                                <img class="togglePassword" src="{{ asset('assets/img/icons/eye.svg') }}" alt="Toggle Password" style="width: 18px; height: 18px;">
                                            </span>
                                        </div>

                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="confirmNewPassword" class="col-md-4 col-lg-3 col-form-label">{{ __('common.Confirm New Password') }}</label>
                                    <div class="col-md-8 col-lg-9">
                                        <!-- <input name="confirmNewPassword" type="password" class="form-control"
                                            id="confirmNewPassword" value="" placeholder="Confirm New Password" /> -->

                                        <div class="input-group">
                                            <input type="password" class="form-control password" id="confirmNewPassword" name="confirmNewPassword" placeholder="Password" />
                                            <span class="input-group-text position-relative togglePasswordWrapper" data-target="confirmNewPassword">
                                                <img class="togglePassword" src="{{ asset('assets/img/icons/eye.svg') }}" alt="Toggle Password" style="width: 18px; height: 18px;">
                                            </span>
                                        </div>

                                    </div>
                                </div>

                                <div class="text-left">
                                    <button type="submit" class="btn btn-primary">{{ __('common.Change Password') }}</button>
                                </div>
                            </form>


                            <div class="mb-3 d-flex justify-content-end align-items-center resend-section">
                                <button type="button" class="btn btn-link text-secondary p-0 m-0 px-2 resend-btn" disabled
                                    id="resend-otp-btn">{{ __('common.Resend OTP') }}</button>
                                <span class="text-secondary fw-bold me-2 resendText">{{ __('common.In') }}</span>
                                <span class="text-secondary fw-bold resendText" id="otpTimer">01:00</span>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </section>

@endsection

@section('scripts')
    @parent

    <script>
        $(document).ready(function () {

            const $display = $('#otpTimer');

            startTimer(duration, $display);

        });
    </script>

@endsection