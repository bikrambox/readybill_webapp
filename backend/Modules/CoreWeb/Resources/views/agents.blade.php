@extends('coreweb::layouts.guest')
@section('title', "{{ __('common.Authorized Agents') }}")
@section('content')


    <style>
        .square-box {
            aspect-ratio: 1 / 1;
            /* Ensures a square shape */
            display: flex;
            align-items: center;
            justify-content: center;
            /* background-color: #f8f9fa; */
            /* Optional: Adds a background */
        }

        img {
            object-fit: contain;
            /* Ensures the image maintains its aspect ratio */
        }
    </style>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8 mt-4">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="home">{{ __('common.Home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('common.Authorized Agents') }}</li>
                    </ol>
                </nav>
            </div>
            <div class="col-12 col-md-10 col-lg-8">
                <h1>{{ __('common.Authorized Agents') }}</h1>
            </div>


            <div class="col-12 col-md-10 col-lg-8 py-4 py-md-0 py-lg-0 py-xl-0">
                <div class="card">
                    <div class="card-body pt-3">
                        <div class="row">

                            <div class="col-12">
                                <div class="card mb-3">
                                    <div class="row">

                                        <div class="col-md-4 d-flex justify-content-center" style="padding: 30px;">
                                            <div class="square-box w-100" style="max-width: 200px;">
                                                <img src="{{ asset('assets/img/agents/agent-1.png') }}"
                                                    class="img-fluid w-100 h-100 object-fit-contain" alt="Agent">
                                            </div>
                                        </div>


                                        <div class="col-md-8" style="padding:30px;">
                                            <div class="card-body">
                                                <div class="row" style="font-size:20px">
                                                    <div class="col-7 text-end fw-bold">
                                                        {{ __('common.ID') }}:
                                                    </div>
                                                    <div class="col-5 text-start">
                                                        202529011
                                                    </div>

                                                    <div class="col-7 text-end fw-bold">
                                                        {{ __('common.Name') }}:
                                                    </div>
                                                    <div class="col-5 text-start">
                                                        Prasanta Sanyal
                                                    </div>

                                                    <div class="col-7 text-end fw-bold">
                                                        {{ __('common.Address') }}:
                                                    </div>
                                                    <div class="col-5 text-start">
                                                        #12, Shanti Path,
                                                        P.O: Fatasil Ambari,
                                                        Guwahati - 781025, Assam
                                                    </div>

                                                    <div class="col-7 text-end fw-bold">
                                                        {{ __('common.Mobile') }}:
                                                    </div>
                                                    <div class="col-5 text-start">
                                                        +91 9531227130
                                                    </div>

                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>


                            </div>

                            <div class="col-12">
                                <div class="card mb-3">
                                    <div class="row">

                                        <div class="col-md-4 d-flex justify-content-center" style="padding: 30px;">
                                            <div class="square-box w-100" style="max-width: 200px;">
                                                <img src="{{ asset('assets/img/agents/agent-2.jpg') }}"
                                                    class="img-fluid w-100 h-100 object-fit-contain" alt="Agent">
                                            </div>
                                        </div>

                                        <div class="col-md-8" style="padding:30px;">
                                            <div class="card-body">
                                                <div class="row" style="font-size:20px">
                                                    <div class="col-7 text-end fw-bold">
                                                        {{ __('common.ID') }}:
                                                    </div>
                                                    <div class="col-5 text-start">
                                                        202529012
                                                    </div>

                                                    <div class="col-7 text-end fw-bold">
                                                        {{ __('common.Name') }}:
                                                    </div>
                                                    <div class="col-5 text-start">
                                                        Jay J. Das
                                                    </div>

                                                    <div class="col-7 text-end fw-bold">
                                                        {{ __('common.Address') }}:
                                                    </div>
                                                    <div class="col-5 text-start">
                                                        Old UCO Bank Bldg.,
                                                        Adabari Tiniali,
                                                        Guwahati - 781012, Assam
                                                    </div>

                                                    <div class="col-7 text-end fw-bold">
                                                        {{ __('common.Mobile') }}:
                                                    </div>
                                                    <div class="col-5 text-start">
                                                        +91 9864081806
                                                    </div>

                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>


                            </div>

                            <div class="col-12">
                                <div class="card mb-3">
                                    <div class="row">

                                        <div class="col-md-4 d-flex justify-content-center" style="padding: 30px;">
                                            <div class="square-box w-100" style="max-width: 200px;">
                                                <img src="{{ asset('assets/img/agents/agent-3.png') }}"
                                                    class="img-fluid w-100 h-100 object-fit-contain" alt="Agent">
                                            </div>
                                        </div>

                                        <div class="col-md-8" style="padding:30px;">
                                            <div class="card-body">
                                                <div class="row" style="font-size:20px">
                                                    <div class="col-7 text-end fw-bold">
                                                        {{ __('common.ID') }}:
                                                    </div>
                                                    <div class="col-5 text-start">
                                                        202529013
                                                    </div>

                                                    <div class="col-7 text-end fw-bold">
                                                        {{ __('common.Name') }}:
                                                    </div>
                                                    <div class="col-5 text-start">
                                                        Michael Baskear
                                                    </div>

                                                    <div class="col-7 text-end fw-bold">
                                                        {{ __('common.Address') }}:
                                                    </div>
                                                    <div class="col-5 text-start">
                                                        Shyam Nagar, C/o Lachit Das,
                                                        Last Building, 1st Floor,
                                                        Near Airport Guest House,
                                                        P.O.: Azara
                                                        Guwahati - 781015, Assam
                                                    </div>

                                                    <div class="col-7 text-end fw-bold">
                                                        {{ __('common.Mobile') }}:
                                                    </div>
                                                    <div class="col-5 text-start">
                                                        +91 7002871610
                                                    </div>

                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>


                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection

@section('scripts')
    @parent

    <script>
        $(document).ready(function () {

        });
    </script>
@endsection