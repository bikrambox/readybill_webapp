@extends('coreweb::layouts.groceryGermany')
@section('title', "{{ __('subscription_page.Subscription') }}")
@section('content')


    <style>
        .plan-card {
            border: 2px solid transparent;
            padding: 20px;
            border-radius: 8px;
            transition: 0.3s;
        }

        .basic {
            border-color: #007bff;
            background-color: #f8fbff;
        }

        .advanced {
            border-color: #6c63ff;
            background-color: #6c63ff;
            color: white;
        }

        .professional {
            border-color: #28a745;
            background-color: #e8f5e9;
        }

        .plan-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .plan-card .price {
            font-weight: bold;
        }



        .plan-basic {
            background-color: #F7FCFF;
            border: 3px solid #4A9CF1;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .plan-basic:hover {
            background-color: #E1F3FF;
            /* Lighter blue */
            border-color: #3088E0;
        }

        .plan-advanced {
            background-color: #6A7BE9;
            border: 3px solid #293FCC;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .plan-advanced:hover {
            background-color: #AAB6F4;
            /* Lighter purple */
            border-color: #1E2D99;
        }

        .plan-professional {
            background-color: #DDF2D1;
            border: 3px solid #99CE7A;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .plan-professional:hover {
            background-color: #EAF9E1;
            /* Lighter green */
            border-color: #78B95C;
        }

        .btn-upgrade {
            background-color: inherit;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .plan-basic .btn-upgrade {
            background-color: #4A9CF1;
            color: white;
        }

        .plan-basic .btn-upgrade:hover {
            background-color: #3088E0;
        }

        .plan-advanced .btn-upgrade {
            background-color: #C0C8FA;
            color: black;
        }

        .plan-advanced .btn-upgrade:hover {
            background-color: #AAB6F4;
        }

        .plan-professional .btn-upgrade {
            background-color: #99CE7A;
            color: black;
        }

        .plan-professional .btn-upgrade:hover {
            background-color: #78B95C;
        }
    </style>

    <div class="pagetitle">
        <h1 class="fw-bold">{{ __('subscription_page.Subscription') }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="home">{{ __('common.Home') }}</a></li>
                <li class="breadcrumb-item active">{{ __('subscription_page.Subscription') }}</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section profile">
        <div class="row">
            <div class="col-xl-8">
               @include('coreweb::components/error', [
    'heading' => __('common.Subscription Alert'),
    'modalIdAttribute' => 'subscripitonErrorId',
])
            </div>
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body pt-3">
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="fw-bold">{{ __('subscription_page.Current Plan') }}</h5>
                            </div>
                            <hr class="my-3">
                            <div class="col-6 text-start">
                                <p id="currentPlan"></p>
                            </div>
                            <div class="col-6 text-end">
                                <p>{{ __('subscription_page.Expiring on') }}: <span id="expiryDate"></span></p>
                            </div>
                        </div>

                        <div class="row text-center mb-4 subscriptionPlanList g-3">

                            <!-- <div class="col-md-4 mb-3 mb-md-0">
                                        <div class="border p-3 rounded">
                                            <p class="fw-bold fs-5">3 Month Plan</p>
                                            <hr>
                                            <h4 class="fw-bold">Rs. 100/-</h4>
                                            <button class="btn"
                                                style="background-color: #4da051; color: white; font-size: 1rem; padding: 0.5rem 1rem;">Upgrade
                                                now</button>
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <div class="border p-3 rounded">
                                            <p class="fw-bold fs-5">6 Month Plan</p>
                                            <hr>
                                            <h4 class="fw-bold">Rs. 200/-</h4>
                                            <button class="btn"
                                                style="background-color: #4da051; color: white; font-size: 1rem; padding: 0.5rem 1rem;">Upgrade
                                                now</button>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="border p-3 rounded">
                                            <p class="fw-bold fs-5">1 Year Plan</p>
                                            <hr>
                                            <h4 class="fw-bold">Rs. 300/-</h4>
                                            <button class="btn"
                                                style="background-color: #4da051; color: white; font-size: 1rem; padding: 0.5rem 1rem;">Upgrade
                                                now</button>
                                        </div>
                                    </div> -->



                            <!-- <div class="col-md-4">
                                        <div class="rounded p-3" style="background-color:#F7FCFF;border:3px solid #4A9CF1">
                                            <div class="d-flex justify-content-between">
                                                <div class="text-start">
                                                    <h5 class="fw-bold mb-0">Basic</h5>
                                                    <p class="text-black mb-0">3 month plan</p>
                                                </div>
                                                <p class="fw-bold mb-0">Rs. 100/-</p>
                                            </div>
                                            <div class="d-flex justify-content-start mt-3">
                                                <button class="btn text-white" style="background-color:#4A9CF1">Upgrade now</button>
                                            </div>
                                        </div>
                                    </div>



                                    <div class="col-md-4">
                                        <div class="text-white rounded p-3"
                                            style="background-color:#6A7BE9;border:3px solid #293FCC">
                                            <div class="d-flex justify-content-between">
                                                <div class="text-start">
                                                    <h5 class="fw-bold mb-0">Advanced</h5>
                                                    <p class="text-white mb-0">6 month plan</p>
                                                </div>
                                                <p class="fw-bold mb-0">Rs. 600/-</p>
                                            </div>
                                            <div class="d-flex justify-content-start mt-3">
                                                <button class="btn fw-bold" style="background-color:#C0C8FA">Upgrade now</button>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="col-md-4">
                                        <div class="rounded p-3"
                                        style="background-color:#DDF2D1;border:3px solid #99CE7A">
                                            <div class="d-flex justify-content-between">
                                                <div class="text-start">
                                                    <h5 class="fw-bold mb-0">Professional</h5>
                                                    <p class="text-black mb-0">1 year plan</p>
                                                </div>
                                                <p class="fw-bold mb-0">Rs. 1200/-</p>
                                            </div>
                                            <div class="d-flex justify-content-start mt-3">
                                                <button class="btn fw-bold" style="background-color:#99CE7A">Upgrade now</button>
                                            </div>
                                        </div>
                                    </div> -->

                        </div>

                        <div class="row">
                            <div class="col-12">
                                <p class="fw-bold text-decoration-underline">{{ __('subscription_page.Sales and Technical Support') }}</p>
                                <p><i class="bi bi-telephone-fill me-2"></i>+91 88227 74191 / +91 98640 81806</p>
                                <p><i class="bi bi-envelope-fill me-2"></i>info@alegralabs.com</p>
                            </div>
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

            function subscriptionPlans() {
                $.ajax({
                    type: 'GET',
                    url: grocery_germany_api_url + 'subscription-plans',
                    headers: {
                        'Authorization': 'Bearer ' + access_token
                    },
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $(".overlay").show();
                    },
                    success: function (response) {
                        if (response) {
                            console.log('subscriptionPlans', response);

                            // Clear existing plans
                            $("#subscription-plans-container").empty();

                            // Loop through the response and dynamically add plans

                            var planHtml = '';

                            var buttonText = '';

                            if(response.isSubscriptionExpired == 0){
                                buttonText = @json(__('subscription_page.Upgrade Now'));
                            }
                            else if(response.isSubscriptionExpired == 1){
                                buttonText = @json(__('subscription_page.Subscribe Now'));
                            }

                            response.data.forEach(plan => {

                                if (plan.subscription_id == 2) {
                                    planHtml = `
                                    <div class="col-md-4 planCard">
                                        <div class="rounded p-3 plan-basic">
                                            <div class="d-flex justify-content-between">
                                                <div class="text-start">
                                                    <h5 class="fw-bold mb-0">Basic</h5>
                                                    <p class="text-black mb-0">${plan.plan_name}</p>
                                                </div>
                                                <p class="fw-bold mb-0">Rs. ${plan.price}/-</p>
                                            </div>
                                            <div class="d-flex justify-content-start mt-3">
                                                <button class="btn text-white btn-upgrade" data-plan-id="${plan.subscription_id}">${buttonText}</button>
                                            </div>
                                        </div>
                                    </div>
                                    `;
                                } else if (plan.subscription_id == 3) {
                                    planHtml = `
                                    <div class="col-md-4 planCard">
                                        <div class="text-white rounded p-3 plan-advanced">
                                            <div class="d-flex justify-content-between">
                                                <div class="text-start">
                                                    <h5 class="fw-bold mb-0">Advanced</h5>
                                                    <p class="text-white mb-0">${plan.plan_name}</p>
                                                </div>
                                                <p class="fw-bold mb-0">Rs. ${plan.price}/-</p>
                                            </div>
                                            <div class="d-flex justify-content-start mt-3">
                                                <button class="btn fw-bold btn-upgrade" data-plan-id="${plan.subscription_id}">${buttonText}</button>
                                            </div>
                                        </div>
                                    </div>
                                    `;
                                } else if (plan.subscription_id == 4) {
                                    planHtml = `
                                    <div class="col-md-4 planCard">
                                        <div class="rounded p-3 plan-professional">
                                            <div class="d-flex justify-content-between">
                                                <div class="text-start">
                                                    <h5 class="fw-bold mb-0">Professional</h5>
                                                    <p class="text-black mb-0">${plan.plan_name}</p>
                                                </div>
                                                <p class="fw-bold mb-0">Rs. ${plan.price}/-</p>
                                            </div>
                                            <div class="d-flex justify-content-start mt-3">
                                                <button class="btn fw-bold btn-upgrade" data-plan-id="${plan.subscription_id}">${buttonText}</button>
                                            </div>
                                        </div>
                                    </div>
                                    `;
                                }
                                $(".subscriptionPlanList").append(planHtml);
                            });




                            $(".overlay").hide();
                        }
                    },
                    error: function (xhr) {
                        $(".overlay").hide();
                        var error = JSON.parse(xhr.responseText);
                        console.error('Error:', error);
                    }
                });
            }

            subscriptionPlans();
            
        });
    </script>
@endsection