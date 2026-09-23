@extends('coreweb::layouts.guest')
@section('title', 'Home')
@section('content')

    <div class="container">
        <div class="row">
            <!-- Main Content Section -->
            <div class="col-md-6">
                <div class="row">
                    <div class="col-12">
                        <p class="fw-bold lh-sm" style="color:#456582; font-size:70px !important;">
                            {{  __('index_page.Quick sell with your mobile and a voice command') }}</p>
                        <h2 class="fw-bold" style="color:#d6536d; margin-top:-15px !important; padding-bottom:30px;">
                            {{ __('index_page.Super fast') }}. {{  __('index_page.Super easy') }}.</h2>
                        <p class="lh-sm pb-1" style="color:#456582;font-size:1.20rem !important">
                            {{ __('index_page.No customer waiting. No billing desk. Even your staff can make sales and bills while serving the customers.') }}
                        </p>
                        <div class="alert lh-sm"
                            style="background-color:#d6536d; color:white; border-radius:20px;font-size:1.20rem !important">
                            {{ __('index_page.Manage your shop inventory, sales, refunds, transactions, employees - all from your mobile or computer.') }}
                        </div>

                    </div>

                    <div class="col-12 text-center py-3">
                        <a href="#"><img src="{{ asset('assets/img/google-play.png') }}" alt="Google Play" width="150" height="46"
                                class="me-3" /></a>
                        <a href="#"><img src="{{  asset('assets/img/apple-store.png')}}" alt="Apple Store" width="150" height="46" /></a>
                    </div>
                </div>
            </div>

            <!-- Right Image -->
            <div class="col-md-5">
                <img src="{{ asset('assets/img/homepage-photo.jpg')}}" alt="Homepage" class="img-fluid rounded">
            </div>
        </div>
    </div>

@endsection