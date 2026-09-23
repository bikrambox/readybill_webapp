@extends('layouts.guest')
@section('title', 'Change Password')
@section('content')

<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body pt-3">

                    <form id="otp-verify-form">

                        <input name="user_id" type="hidden" class="form-control" id="user_id" value="{{$user_id}}" readonly />

                        <div class="row mb-3">
                            <label for="otp" class="col-md-4 col-lg-3 col-form-label fw-bold">OTP</label>
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

                        <input type="hidden" name="phone_number" id="phone_number" value="{{$mobile_number}}" readonly />

                        <div class="row mb-3">
                            <label for="newPassword" class="col-md-4 col-lg-3 col-form-label">New Password</label>
                            <div class="col-md-8 col-lg-9">
                                <input name="newPassword" type="password" class="form-control" id="newPassword" value=""
                                    autofocus />
                            </div>
                        </div>

                        <div class="text-left">
                            <button type="submit" class="btn btn-primary">Change Password</button>
                        </div>
                    </form>

                    <div class="text-center p-3 border rounded bg-white shadow-sm mt-3" style="">
                        <p class="text-muted mb-2" style="font-size: 0.9rem;">Please wait before requesting a new OTP:
                        </p>
                        <div class="d-flex justify-content-center align-items-center gap-2">
                            <div class="spinner-border text-primary" role="status" style="width: 1rem; height: 1rem;">
                            </div>
                            <span id="timer" class="text-dark fw-semibold" style="font-size: 1.4rem;">00:00:00</span>
                        </div>
                        <button id="resend-otp-btn" class="btn btn-outline-secondary btn-sm mt-3 d-none" disabled>Resend
                            OTP</button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
@parent

@endsection