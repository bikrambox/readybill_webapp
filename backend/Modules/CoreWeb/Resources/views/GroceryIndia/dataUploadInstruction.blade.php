@extends('coreweb::layouts.groceryIndia')
@section('title', "{!! __('upload_instruction_page.title') !!}")
@section('content')


    <style>
        #modalImage {
            max-width: 100%;
            /* Ensure the image doesn't overflow */
            max-height: 100vh;
            /* Make the image fit vertically within the viewport */
            object-fit: contain;
            /* Ensure the image scales without distortion */
        }

        /* Make the image a little smaller */
        .hover-image {
            width: 80%;
            /* Adjust width to make the image smaller */
            transition: transform 0.3s ease;
            /* Smooth transition on hover */
            cursor: pointer;
            /* Show pointer cursor when hovering */
        }

        /* Enlarge the image slightly on hover */
        .hover-image:hover {
            transform: scale(1.05);
            /* Slightly enlarge the image */
        }
    </style>

    <div class="pagetitle">
        <h1>{!! __('upload_instruction_page.title') !!}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="home">{{ __('common.Home') }}</a></li>
                <li class="breadcrumb-item active">{!! __('upload_instruction_page.title') !!}</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">

                <div class="card">
                    <div class="card-body mt-4">
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="m-0">{!! __('upload_instruction_page.subtitle') !!}</h4>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <h3>{!! __('upload_instruction_page.section1.heading') !!}</h3>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <h5>{!! __('upload_instruction_page.section1.sub_heading') !!}</h5>
                            </div>
                            <div class="col-12 mb-4">
                                <p class="text-break">
                                    {!! __('upload_instruction_page.section1.para.1') !!}
                                </p>

                                <p class="text-break">
                                    {!! __('upload_instruction_page.section1.para.2') !!}
                                </p>

                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th scope="col">{{ strtoupper(__('common.Item Name')) }}</th>
                                            <th scope="col">{{ strtoupper(__('common.Quantity')) }}</th>
                                            <th scope="col">{{ strtoupper(__('common.Minimum Stock Alert')) }}</th>
                                            <th scope="col">{{ strtoupper(__('common.MRP')) }}</th>
                                            <th scope="col">{{ strtoupper(__('common.Sale Price')) }}</th>
                                            <th scope="col">{{ strtoupper(__('common.Unit')) }}</th>
                                            <th scope="col">{{ strtoupper(__('common.HSN')) }}</th>
                                            <th scope="col">{{ strtoupper(__('common.GST')) }}</th>
                                            <th scope="col">{{ strtoupper(__('common.CESS')) }}</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>

                            <div class="col-12 mb-4">
                                <ul>
                                    <li>{!! __('upload_instruction_page.section1.para.3') !!}</li>
                                    <li>{!! __('upload_instruction_page.section1.para.4') !!}</li>
                                    <li>{!! __('upload_instruction_page.section1.para.5') !!}</li>

                                    <li>{!! __('upload_instruction_page.section1.para.6') !!}.</li>
                                    <li>{!! __('upload_instruction_page.section1.para.7') !!}</li>
                                    <li>{!! __('upload_instruction_page.section1.para.8') !!}</li>
                                </ul>
                            </div>

                            <div class="col-4 mb-4">

                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th scope="col">{{ __('common.Units') }}</th>
                                            <th scope="col">{{ __('common.Short Form') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Bag</td>
                                            <td>BAG</td>
                                        </tr>

                                        <tr>
                                            <td>Bottle</td>
                                            <td>BTL</td>
                                        </tr>

                                        <tr>
                                            <td>Box</td>
                                            <td>BOX</td>
                                        </tr>

                                        <tr>
                                            <td>Bundle</td>
                                            <td>BDL</td>
                                        </tr>

                                        <tr>
                                            <td>Can</td>
                                            <td>CAN</td>
                                        </tr>

                                        <tr>
                                            <td>Cartoon</td>
                                            <td>CTN</td>
                                        </tr>

                                        <tr>
                                            <td>Gram</td>
                                            <td>GM</td>
                                        </tr>

                                        <tr>
                                            <td>Kilogram</td>
                                            <td>KG</td>
                                        </tr>

                                        <tr>
                                            <td>Litre</td>
                                            <td>LTR</td>
                                        </tr>

                                        <tr>
                                            <td>Meter</td>
                                            <td>MTR</td>
                                        </tr>

                                        <tr>
                                            <td>Millimeter</td>
                                            <td>ML</td>
                                        </tr>

                                        <tr>
                                            <td>Number</td>
                                            <td>NUM</td>
                                        </tr>
                                        <tr>
                                            <td>Pack</td>
                                            <td>PCK</td>
                                        </tr>
                                        <tr>
                                            <td>Pair</td>
                                            <td>PRS</td>
                                        </tr>
                                        <tr>
                                            <td>Piece</td>
                                            <td>PCS</td>
                                        </tr>
                                        <tr>
                                            <td>Roll</td>
                                            <td>ROL</td>
                                        </tr>
                                        <tr>
                                            <td>Square Feet</td>
                                            <td>SQF</td>
                                        </tr>
                                        <tr>
                                            <td>Square Meter</td>
                                            <td>SQM</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>


                            <div class="col-12">
                                <ul>
                                    <li>{!! __('upload_instruction_page.section1.para.9') !!}</li>
                                    <li>{!! __('upload_instruction_page.section1.para.10') !!}</li>
                                    <li>{!! __('upload_instruction_page.section1.para.11') !!}</li>
                                </ul>
                            </div>

                            <p class="text-break">{!! __('upload_instruction_page.section1.para.12') !!}</p>
                            <div class="col-12 text-center mb-3">
                                <!-- <img src="{{asset('assets/img/upload-format.png')}}" alt=""> -->
                                <img src="{{asset('assets/img/upload-format.png')}}" class="img-thumbnail hover-image"
                                    alt="Thumbnail" data-bs-toggle="modal" data-bs-target="#imageModal">
                            </div>

                            <div class="col-12">
                                <p class="text-break">{!! __('upload_instruction_page.section1.para.13') !!}</p>
                            </div>

                            <div class="col-12">
                                <p class="text-break">{!! __('upload_instruction_page.section1.para.14') !!}</p>


                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th scope="col">{{ strtoupper(__('common.Item Name')) }} <span
                                                    class="text-danger">*</span></th>
                                            <th scope="col">{{ strtoupper(__('common.Quantity')) }}</th>
                                            <th scope="col">{{ strtoupper(__('common.Minimum Stock Alert')) }}</th>
                                            <th scope="col">{{ strtoupper(__('common.MRP')) }}</th>
                                            <th scope="col">{{ strtoupper(__('common.Sale Price')) }} <span
                                                    class="text-danger">*</span></th>
                                            <th scope="col">{{ strtoupper(__('common.Unit')) }} <span
                                                    class="text-danger">*</span></th>
                                            <th scope="col">{{ strtoupper(__('common.HSN')) }}</th>
                                            <th scope="col">{{ strtoupper(__('common.GST')) }} <span
                                                    class="text-danger">*</span></th>
                                            <th scope="col">{{ strtoupper(__('common.CESS')) }} <span
                                                    class="text-danger">*</span></th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>

                            <div class="col-12 mb-3">
                                <p class="text-break">
                                    {!! __('upload_instruction_page.section1.para.15') !!}
                                </p>

                                <p class="text-break">
                                    {!! __('upload_instruction_page.section1.para.16') !!}
                                </p>
                            </div>

                            <div class="col-12">
                                <h5>{!! __('upload_instruction_page.section2.heading') !!}</h5>

                                <p class="text-break">
                                    {!! __('upload_instruction_page.section2.para.1') !!}
                                </p>

                                <ol>
                                    <li>{!! __('upload_instruction_page.section2.para.2') !!}</li>
                                    <li>{!! __('upload_instruction_page.section2.para.3') !!}</li>
                                    <li>{!! __('upload_instruction_page.section2.para.4') !!}</li>
                                    <li>{!! __('upload_instruction_page.section2.para.5') !!}</li>
                                    <li>{!! __('upload_instruction_page.section2.para.6') !!}</li>
                                    <li>{!! __('upload_instruction_page.section2.para.7') !!}</li>
                                </ol>

                                <p class="text-break">{!! __('upload_instruction_page.section2.para.8') !!}</p>
                            </div>

                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>



    <!-- Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <!-- Use 'modal-fullscreen' class for fullscreen modal -->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <!-- Close button at the top-right -->
                </div>
                <div class="modal-body">
                    <img src="" id="modalImage" class="img-fluid" alt="Full Image">
                    <!-- img-fluid class makes the image responsive -->
                </div>
            </div>
        </div>
    </div>

@endsection



@section('scripts')
    @parent

    <script>
        $(document).ready(function () {
            // When any image with class 'img-thumbnail' is clicked
            $('.img-thumbnail').on('click', function () {
                // Get the source of the clicked image
                var imageSrc = $(this).attr('src');

                // Set the modal image source to the clicked image source
                $('#modalImage').attr('src', imageSrc);
            });


            // ----------------------------------- DOWNLOAD DATASET --------------------------------------
            $(".downloadDataset").on('click', function (e) {
                $.ajax({
                    type: 'GET',
                    url: grocery_india_api_url + 'download/sample-dataset',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    contentType: false,
                    processData: false,

                    beforeSend: function () {
                        $('.overlay').show();
                    },
                    success: function (response) {

                        console.log('response', response);

                        if (response.status == 1) {

                            $.toast({
                                heading: 'Success',
                                text: response.message,
                                icon: 'success',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#9EC600'
                            });
                            $('.overlay').hide();

                            location.href = base_url + response.file;
                        } else if (response.status == 0) {
                            $.toast({
                                heading: 'Error',
                                text: response.message,
                                icon: 'error',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#9EC600'
                            });
                            $('.overlay').hide();
                        }
                    },
                    error: function (error) {
                        console.log('Error', error);

                    }
                });
            });
            // ----------------------------------- DOWNLOAD DATASET --------------------------------------



        });
    </script>
@endsection