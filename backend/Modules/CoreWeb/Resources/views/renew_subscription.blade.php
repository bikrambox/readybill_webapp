@extends('coreweb::layouts.guest')
@section('title', 'Welcome to Your App')
@section('content')


<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8 mt-4">
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="home">Home</a></li>
                    <li class="breadcrumb-item active">Renew Subscription</li>
                </ol>
            </nav>
        </div>

        <div class="col-12 col-md-10 col-lg-8">
            <h1>Renew Subscription</h1>
        </div>

        <div class="col-12 col-md-10 col-lg-8">
            <div class="row mb-4">
                <hr class="my-3">
            </div>

            <div class="row text-center mb-4">
                <div class="col-md-4 mb-3 mb-md-0">
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
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <p class="fw-bold text-decoration-underline">Sales and Technical Support</p>
                    <p><i class="bi bi-telephone-fill me-2"></i>+91 88227 74191 / +91 98640 81806</p>
                    <p><i class="bi bi-envelope-fill me-2"></i>info@alegralabs.com</p>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection