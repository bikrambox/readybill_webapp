@extends('coreweb::layouts.groceryGermany_vue_layout')
@section('title', "{{ __('transaction_page.Transactions') }}")
@section('content')
            <div class="pagetitle">
                <h1>{{ __('transaction_page.Transactions') }}</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="home">{{ __('common.Home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('transaction_page.Transactions') }}</li>
                    </ol>
                </nav>
            </div><!-- End Page Title -->

            <section class="section">
                <div class="row">
                    <div class="col-xl-8">
                        @include('coreweb::components/error', [
                            'heading' => __('common.Subscription Alert'),
                            'modalIdAttribute' => 'subscripitonErrorId',
                        ])
                    </div>
                    <div class="col-lg-12">

                        <div class="card">
                            <div class="card-body">
                                <div class="row mt-4">

                                    <!-- <div class="col-12 text-end isAdminSection">
                                        <a class="btn btn-primary" href="{{  locale_route('generate.report') }}" role="button">{{ __('common.Generate Report') }}</a>
                                    </div> -->

                                    <gemany-transactions-component :user-role="'{{ Auth::user()->role }}'"></tgemany-transactions-component>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </section>

            @include("coreweb::modals/transaction")

@endsection

