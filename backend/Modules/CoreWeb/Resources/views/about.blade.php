@extends('coreweb::layouts.guest')
@section('title', "{{ __('about_page.title') }}")
@section('content')

    <style>
        .rounded-circle1-container {
            /* padding: 20px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    width: 170px;
                    height: 170px; */
        }

        .rounded-circle1 {
            border-radius: 50%;
            width: 100px;
            height: 100px;
            object-fit: cover;
        }

        @media (max-width: 576px) {
            /* .rounded-circle1-container {
                        width: 130px;
                        height: 130px;
                    } */

            .rounded-circle1 {
                width: 100px;
                height: 100px;
            }
        }
    </style>


    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8 mt-4">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="home">{{ __('common.Home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('about_page.title') }}</li>
                    </ol>
                </nav>
            </div>
            <div class="col-12 col-md-10 col-lg-8">
                <h1>{{ __('about_page.title') }}</h1>
                <p>{{ __('about_page.subtitle') }}</p>
            </div>
            <div class="col-12 col-md-10 col-lg-8 d-flex align-items-center my-md-3">
                <div class="rounded-circle1-container">
                    <img src="{{ asset('assets/img/ceo.jpg')}}" alt="Rounded Image" class="rounded-circle1">
                </div>
                <div class="mx-4">
                    <h4 class="fw-bold my-0 py-0">Jay J. Das</h4>
                    <p class="fw-bold fs-5 my-0 py-0">{{ __('about_page.founder') }}</p>
                </div>
            </div>
            <div class="col-12 col-md-10 col-lg-8 py-4 py-md-0 py-lg-0 py-xl-0">
                <p class="text-break">
                    {{ __('about_page.para1') }}
                </p>
                <p class="text-break">
                    {{ __('about_page.para2') }}
                </p>
                <p class="text-break">
                    {{ __('about_page.para3') }}
                </p>
            </div>
        </div>
    </div>

@endsection