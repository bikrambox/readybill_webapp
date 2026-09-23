@extends('coreweb::layouts.guest')
@section('title', "{{ __('common.Privacy Policy') }}")
@section('content')

    <style>
        .paragraphSection p,
        h6,
        li {
            font-size: 17px !important;
        }

        .paragraphSection p {
            max-width: 100%;
            word-wrap: break-word;
        }

        .paragraphSection ol li {
            margin-bottom: 10px !important;
        }
    </style>


    <div class="container paragraphSection">

        <div class="row">
            <div class="col-12 mt-4">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="home">{{ __('common.Home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('common.Privacy Policy') }}</li>
                    </ol>
                </nav>
            </div>

            <div class="col-12">
                <div class="row">
                    <div class="col-12">
                        <h1>{{ __('common.Privacy Policy') }}</h1>
                        <p>{{ __('privacy_policy_page.last_revision') }}</p>
                        <p class="text-break">
                            {!! __('privacy_policy_page.section1.para.1') !!}
                        </p>
                        <p class="text-break">
                            {!! __('privacy_policy_page.section1.para.2') !!}
                        </p>
                        <p class="text-break">
                            {!! __('privacy_policy_page.section1.para.3') !!}
                        </p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-4">
                        <h6 class="fw-bold">
                            1. {!! __('privacy_policy_page.section2.heading') !!}
                        </h6>
                    </div>
                    <div class="col-md-8 fw-normal">
                        <p>{!! __('privacy_policy_page.section2.para.1') !!}</p>
                        <ol>
                            <li>{!! __('privacy_policy_page.section2.para.1') !!}</li>
                            <li>{!! __('privacy_policy_page.section2.para.2') !!}</li>
                            <li>{!! __('privacy_policy_page.section2.para.3') !!}</li>
                            <li>{!! __('privacy_policy_page.section2.para.4') !!}</li>
                            <li>{!! __('privacy_policy_page.section2.para.5') !!}</li>
                            <li>{!! __('privacy_policy_page.section2.para.6') !!}</li>
                            <li>{!! __('privacy_policy_page.section2.para.7') !!}
                                <ul>
                                    <li>{!! __('privacy_policy_page.section2.para.8') !!}</li>
                                    <li>{!! __('privacy_policy_page.section2.para.9') !!}</li>
                                    <li>{!! __('privacy_policy_page.section2.para.10') !!}</li>
                                    <li>{!! __('privacy_policy_page.section2.para.11') !!}</li>
                                    <li>{!! __('privacy_policy_page.section2.para.12') !!}</li>
                                    <li>{!! __('privacy_policy_page.section2.para.13') !!}</li>
                                </ul>
                            </li>
                        </ol>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-4">
                        <h6 class="fw-bold">
                            2. {!! __('privacy_policy_page.section3.heading') !!}
                        </h6>
                    </div>
                    <div class="col-md-8 fw-normal">
                        <p>{!! __('privacy_policy_page.section3.para.1') !!}</p>
                        <p>{!! __('privacy_policy_page.section3.para.2') !!}</p>

                    </div>
                </div>

                <hr>


                <div class="row">
                    <div class="col-md-4">
                        <h6 class="fw-bold">
                            3. {!! __('privacy_policy_page.section4.heading') !!}
                        </h6>
                    </div>
                    <div class="col-md-8 fw-normal">
                        <p>{!! __('privacy_policy_page.section4.para.1') !!}</p>
                        <p>
                            {!! __('privacy_policy_page.section4.para.2') !!}
                        </p>

                        <p>
                            {!! __('privacy_policy_page.section4.para.3') !!}
                        </p>

                        <p>
                            {!! __('privacy_policy_page.section4.para.4') !!}
                        </p>

                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-4">
                        <h6 class="fw-bold">
                            4. {!! __('privacy_policy_page.section5.heading') !!}
                        </h6>
                    </div>
                    <div class="col-md-8 fw-normal">
                        <p>{!! __('privacy_policy_page.section5.para.1') !!}</p>

                        <p>
                            {!! __('privacy_policy_page.section5.para.2') !!}
                        </p>

                        <span>
                            Email: support@readybill.app
                        </span><br>
                        <span>Address: Old UCO Bank Bldg.,</span><br>
                        <span>Adabari Tiniali</span><br>
                        <span>Guwahati - 781012, India.</span><br>

                    </div>
                </div>
                <hr>

                <div class="row">
                    <div class="col-md-4">
                        <h6 class="fw-bold">
                            5. {!! __('privacy_policy_page.section6.heading') !!}
                        </h6>
                    </div>
                    <div class="col-md-8 fw-normal">
                        <p>{!! __('privacy_policy_page.section6.para.1') !!}</p>

                        <p>
                           {!! __('privacy_policy_page.section6.para.2') !!}
                        </p>

                        <p>
                            {!! __('privacy_policy_page.section6.para.3') !!}
                        </p>
                    </div>
                </div>

                <hr>
                <div class="row">
                    <div class="col-md-4">
                        <h6 class="fw-bold">
                            6. {!! __('privacy_policy_page.section7.heading') !!}
                        </h6>
                    </div>
                    <div class="col-md-8 fw-normal">
                        <p>{!! __('privacy_policy_page.section7.para.1') !!}</p>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-4">
                        <h6 class="fw-bold">
                            7. {!! __('privacy_policy_page.section8.heading') !!}
                        </h6>
                    </div>
                    <div class="col-md-8 fw-normal">
                        <p>{!! __('privacy_policy_page.section8.para.1') !!}</p>

                        <p>
                           {!! __('privacy_policy_page.section8.para.2') !!}
                        </p>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-4">
                        <h6 class="fw-bold">
                            8. {!! __('privacy_policy_page.section9.heading') !!}
                        </h6>
                    </div>
                    <div class="col-md-8 fw-normal">
                        <p>{!! __('privacy_policy_page.section9.para.1') !!}</p>

                        <p>
                            {!! __('privacy_policy_page.section9.para.2') !!}
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection