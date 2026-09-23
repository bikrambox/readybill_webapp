@extends('coreweb::layouts.guest')
@section('title', "{{ __('terms_n_conditions_page.title') }}")
@section('content')


    <style>
        .paragraphSection p,
        h6 li {
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
                        <li class="breadcrumb-item active">{{ __('terms_n_conditions_page.title') }}</li>
                    </ol>
                </nav>
            </div>

            <div class="col-12">
                <div class="row mb-4">
                    <div class="col-12">
                        <h3>{{ __('terms_n_conditions_page.title') }}</h3>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h2>{!! __('terms_n_conditions_page.section1.heading') !!}</h2>
                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section1.para.1') !!}
                        </p>
                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section1.para.2') !!}
                        </p>
                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section1.para.3') !!}
                        </p>
                    </div>
                </div>
                <hr>

                <div class="row">
                    <div class="col-12">
                        <h2>{!! __('terms_n_conditions_page.section2.heading') !!}</h2>
                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section2.para.1') !!}
                        </p>
                    </div>
                </div>
                <hr>


                <div class="row">
                    <div class="col-12">
                        <h2>{!! __('terms_n_conditions_page.section3.heading') !!}</h2>
                        <p class="text-break">
                            {!!   __('terms_n_conditions_page.section3.para.1', ['subscription_url' => locale_route('subscription')]) !!}
                        </p>
                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section3.para.2') !!}
                        <ol>
                            <li>{!! __('terms_n_conditions_page.section3.para.3') !!}.</li>
                            <li>{!!   __('terms_n_conditions_page.section3.para.4', ['contact_url' => locale_route('contact')]) !!}
                            </li>
                            <li>{!! __('terms_n_conditions_page.section3.para.5') !!}</li>
                            <li>{!! __('terms_n_conditions_page.section3.para.6') !!}</li>
                        </ol>
                        </p>
                    </div>
                </div>
                <hr>

                <div class="row">
                    <div class="col-12">
                        <h2>{!! __('terms_n_conditions_page.section4.heading') !!}</h2>
                        <p class="text-break">
                        <ol>
                            <li>
                                {!! __('terms_n_conditions_page.section4.para.1') !!}

                                <br><br>

                                {!! __('terms_n_conditions_page.section4.para.2') !!}

                                <ul style="list-style-type: lower-alpha; padding-left: 20px;">
                                    <li>{!! __('terms_n_conditions_page.section4.para.3') !!}</li>
                                    <li>{!! __('terms_n_conditions_page.section4.para.4', ['contact_url' => locale_route('contact')]) !!}
                                    </li>
                                    <li>{!! __('terms_n_conditions_page.section4.para.5') !!}</li>

                                    <li>{!! __('terms_n_conditions_page.section4.para.6') !!}</li>
                                    <li>{!! __('terms_n_conditions_page.section4.para.7') !!}</li>
                                    <li>{!! __('terms_n_conditions_page.section4.para.8') !!}</li>
                                    <li>{!! __('terms_n_conditions_page.section4.para.9') !!}
                                        <br><br>
                                        {!! __('terms_n_conditions_page.section4.para.10') !!}

                                        <br><br>

                                        {!! __('terms_n_conditions_page.section4.para.11') !!}

                                    </li>

                                    <li>
                                        {!! __('terms_n_conditions_page.section4.para.12') !!}
                                    </li>
                                    <li>{!! __('terms_n_conditions_page.section4.para.13', ['agent_url' => locale_route('agents')]) !!}
                                    </li>
                                    <li>
                                        {!! __('terms_n_conditions_page.section4.para.14') !!}
                                    </li>

                                </ul>

                            </li>

                            <li>
                                {!! __('terms_n_conditions_page.section4.para.15') !!}

                                <br><br>
                                {!! __('terms_n_conditions_page.section4.para.16') !!}
                            </li>
                        </ol>
                        </p>
                    </div>
                </div>
                <hr>

                <div class="row">
                    <div class="col-12">
                        <h2>{!! __('terms_n_conditions_page.section5.heading') !!}</h2>
                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section5.para.1') !!}
                        </p>
                    </div>
                </div>
                <hr>


                <div class="row">
                    <div class="col-12">
                        <h2>{!! __('terms_n_conditions_page.section6.heading') !!}</h2>
                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section6.para.1') !!}
                        </p>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-12">
                        <h2>{!! __('terms_n_conditions_page.section7.heading') !!}</h2>
                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section7.para.1') !!}
                        </p>
                    </div>
                </div>

                <hr>


                <div class="row">
                    <div class="col-12">
                        <h2>
                            {!! __('terms_n_conditions_page.section8.heading') !!}
                        </h2>
                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section8.para.1') !!}
                        </p>
                    </div>
                </div>

                <hr>
                <div class="row">
                    <div class="col-12">
                        <h2>
                            {!! __('terms_n_conditions_page.section9.heading') !!}
                        </h2>
                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section9.para.1') !!}
                        </p>

                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section9.para.2') !!}
                        </p>

                        <p>
                            {!! __('terms_n_conditions_page.section9.para.3') !!}
                        </p>
                        <p>
                            {!! __('terms_n_conditions_page.section9.para.4') !!}
                        </p>
                        <p>
                            {!! __('terms_n_conditions_page.section9.para.5') !!}
                        </p>

                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-12">
                        <h2>
                            {!! __('terms_n_conditions_page.section10.heading') !!}
                        </h2>
                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section10.para.1') !!}
                        </p>

                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section10.para.2') !!}
                        </p>

                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section10.para.3') !!}
                        </p>

                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section10.para.4') !!}
                        </p>

                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section10.para.5') !!}
                        </p>

                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section10.para.6') !!}
                        </p>

                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section10.para.7') !!}
                        </p>

                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section10.para.8') !!}
                        </p>

                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section10.para.9') !!}
                        </p>

                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section10.para.10') !!}
                        </p>

                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section10.para.11') !!}
                        </p>

                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section10.para.12') !!}
                        </p>

                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section10.para.13') !!}
                        </p>

                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section10.para.14') !!}
                        </p>

                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section10.para.15') !!}
                        </p>

                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section10.para.16') !!}
                        </p>

                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section10.para.17') !!}
                        </p>


                    </div>
                </div>

                <hr>
                <div class="row">
                    <div class="col-12">
                        <h2>
                            {!! __('terms_n_conditions_page.section11.heading') !!}
                        </h2>
                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section11.para.1') !!}
                        </p>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-12">
                        <h2>
                            {!! __('terms_n_conditions_page.section12.heading') !!}
                        </h2>
                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section12.para.1') !!}
                        </p>
                    </div>
                </div>


                <hr>

                <div class="row">
                    <div class="col-12">
                        <h2>
                            {!! __('terms_n_conditions_page.section13.heading') !!}
                        </h2>
                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section13.para.1') !!}
                        </p>
                        <ol>
                            <li>{!! __('terms_n_conditions_page.section13.para.2') !!}</li>
                            <li>{!! __('terms_n_conditions_page.section13.para.3') !!}</li>
                            <li>{!! __('terms_n_conditions_page.section13.para.4') !!}</li>
                        </ol>
                        <p>
                            {!! __('terms_n_conditions_page.section13.para.5') !!}
                        </p>

                        <p>
                            {!! __('terms_n_conditions_page.section13.para.6') !!}
                        </p>
                        <p>
                            {!! __('terms_n_conditions_page.section13.para.7') !!}
                        </p>
                        <p>
                            {!! __('terms_n_conditions_page.section13.para.8') !!}
                        </p>
                        <p>
                            {!! __('terms_n_conditions_page.section13.para.9') !!}
                        </p>
                        <p>
                            {!! __('terms_n_conditions_page.section13.para.10') !!}
                        </p>
                        <p>
                            {!! __('terms_n_conditions_page.section13.para.11') !!}
                        </p>
                        <ol>
                            <li>{!! __('terms_n_conditions_page.section13.para.12') !!}</li>
                            <li>
                                {!! __('terms_n_conditions_page.section13.para.13') !!}
                            </li>
                            <li>{!! __('terms_n_conditions_page.section13.para.14') !!}</li>
                        </ol>
                        <p>{!! __('terms_n_conditions_page.section13.para.15') !!}</p>
                        <ol>
                            <li>{!! __('terms_n_conditions_page.section13.para.16') !!}</li>
                            <li>{!! __('terms_n_conditions_page.section13.para.17') !!}</li>
                        </ol>
                        <p>
                            {!! __('terms_n_conditions_page.section13.para.18') !!}
                        </p>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-12">
                        <h2>
                            {!! __('terms_n_conditions_page.section14.heading') !!}
                        </h2>
                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section14.para.1') !!}
                        </p>

                        <p>
                            {!! __('terms_n_conditions_page.section14.para.2') !!}
                        </p>

                        <p>
                            {!! __('terms_n_conditions_page.section14.para.3') !!}
                        </p>

                        <p>
                            {!! __('terms_n_conditions_page.section14.para.4') !!}
                        </p>

                        <p>
                            {!! __('terms_n_conditions_page.section14.para.5') !!}
                        </p>

                        <p>
                            {!! __('terms_n_conditions_page.section14.para.6') !!}
                        </p>

                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-12">
                        <h2>
                            {!! __('terms_n_conditions_page.section15.heading') !!}
                        </h2>
                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section15.para.1') !!}
                        </p>
                        <p>
                            {!! __('terms_n_conditions_page.section15.para.2') !!}
                        </p>

                        <p>
                            {!! __('terms_n_conditions_page.section15.para.3') !!}
                        </p>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-12">
                        <h2>
                            {!! __('terms_n_conditions_page.section16.heading') !!}
                        </h2>
                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section16.para.1') !!}
                        </p>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-12">
                        <h2>
                            {!! __('terms_n_conditions_page.section17.heading') !!}
                        </h2>
                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section17.para.1') !!}
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <h2>
                            {!! __('terms_n_conditions_page.section18.heading') !!}
                        </h2>
                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section18.para.1') !!}
                        </p>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-12">
                        <h2>
                            {!! __('terms_n_conditions_page.section19.heading') !!}
                        </h2>
                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section19.para.1') !!}
                        </p>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-12">
                        <h2>
                            {!! __('terms_n_conditions_page.section20.heading') !!}
                        </h2>
                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section20.para.1') !!}
                        </p>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-12">
                        <h2>
                            {!! __('terms_n_conditions_page.section21.heading') !!}
                        </h2>
                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section21.para.1') !!}
                        </p>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-12">
                        <h2>
                            {!! __('terms_n_conditions_page.section22.heading') !!}
                        </h2>
                        <p class="text-break">
                            {!! __('terms_n_conditions_page.section22.para.1') !!}
                        </p>
                    </div>
                </div>



            </div>
        </div>
    </div>

@endsection