@extends('coreweb::layouts.groceryIndia')
@section('title', "{{  __('support_page.title') }}")
@section('content')

    <style>
        .accordion {
            border: 1px solid #e2e6ea;
            border-radius: 8px;
        }

        .accordion-item {
            margin: 0;
        }

        .accordion-trigger {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            text-align: left;
            padding: 12px 16px;
            background: #f8f9fb;
            border: 0;
            border-bottom: 1px solid #e2e6ea;
            font: 600 16px/1.4 system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            cursor: pointer;
        }

        .accordion-trigger:focus {
            outline: 2px solid #0d6efd;
            outline-offset: 2px;
        }

        .accordion-trigger[aria-expanded="true"] {
            background: #eef5ff;
        }

        .accordion-arrow {
            display: inline-block;
            transition: transform .2s ease;
            transform-origin: 50% 50%;
        }

        .accordion-trigger[aria-expanded="true"] .accordion-arrow {
            transform: rotate(90deg);
        }

        .accordion-panel {
            padding: 0 16px 16px;
            border-bottom: 1px solid #e2e6ea;
        }

        .accordion-panel[hidden] {
            display: none;
        }

        .accordion-body {
            color: #333;
        }
    </style>


    <div class="pagetitle">
        <h1>{{  __('support_page.title') }}</h1>
        <h6 style="color:#012970">{{  __('support_page.subtitle') }}</h6>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="home">{{  __('common.Home') }}</a></li>
                <li class="breadcrumb-item active">{{  __('support_page.title') }}</li>
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
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-body pt-3">

                        <div class="row mb-3">
                            <div class="col-md-4 text-start">
                                <button type="button" class="btn btn-info" data-bs-toggle="modal"
                                    data-bs-target="#askYourQuestionModal">
                                    {{  __('support_page.ask_your_questions') }}
                                </button>
                            </div>

                            <div class="col-md-8 text-end">
                                <p>{{  __('support_page.contact_n_sale_support') }}</p>
                            </div>
                        </div>

                        <section class="accordion" id="faq">

                            <h2 class="accordion-item">
                                <button class="accordion-trigger" id="acc-h1" aria-expanded="true" aria-controls="acc-p1">
                                    <span class="accordion-arrow" aria-hidden="true">▸</span>
                                    <span class="accordion-title">{!!  __('support_page.faq1.quest') !!}</span>
                                </button>
                            </h2>
                            <div id="acc-p1" class="accordion-panel" role="region" aria-labelledby="acc-h1">
                                <div class="accordion-body">
                                    <p class="text-break">{!!  __('support_page.faq1.para.1') !!}</p>
                                    <p class="text-break">{!!  __('support_page.faq1.para.2') !!}</p>
                                    <p class="text-break">{!!  __('support_page.faq1.para.3') !!}</p>
                                    <p class="text-break">{!!  __('support_page.faq1.para.4') !!}</p>
                                    <p class="text-break">{!!  __('support_page.faq1.para.5') !!}
                                    </p>
                                    <p class="text-break">{!!  __('support_page.faq1.para.6') !!}</p>

                                    <p class="text-break">{!!  __('support_page.faq1.para.7') !!}</p>
                                    <p class="text-break">{!!  __('support_page.faq1.para.8') !!}</p>
                                    <p class="text-break">{!!  __('support_page.faq1.para.9') !!}</p>
                                    <p class="text-break">{!!  __('support_page.faq1.para.10') !!}</p>
                                    <p class="text-break">
                                        {!!  __('support_page.faq1.para.11') !!}
                                    </p>
                                </div>
                            </div>


                            <h2 class="accordion-item">
                                <button class="accordion-trigger" id="acc-h2" aria-expanded="false" aria-controls="acc-p2">
                                    <span class="accordion-arrow" aria-hidden="true">▸</span>
                                    <span class="accordion-title">{!!  __('support_page.faq2.quest') !!}</span>
                                </button>
                            </h2>
                            <div id="acc-p2" class="accordion-panel" role="region" aria-labelledby="acc-h2" hidden>
                                <div class="accordion-body">
                                    <p class="text-break">{!!  __('support_page.faq2.para.1') !!}</p>
                                    <p class="text-break">{!!  __('support_page.faq2.para.2') !!}</p>
                                    <p class="text-break">{!!  __('support_page.faq2.para.3') !!}</p>
                                    <p class="text-break">{!!  __('support_page.faq2.para.4') !!}</p>
                                    <p class="text-break">{!!  __('support_page.faq2.para.5') !!}</p>
                                    <p class="text-break">{!!  __('support_page.faq2.para.6') !!}</p>

                                </div>
                            </div>

                            <h2 class="accordion-item">
                                <button class="accordion-trigger" id="acc-h3" aria-expanded="false" aria-controls="acc-p3">
                                    <span class="accordion-arrow" aria-hidden="true">▸</span>
                                    <span class="accordion-title">{!!  __('support_page.faq3.quest') !!}</span>
                                </button>
                            </h2>
                            <div id="acc-p3" class="accordion-panel" role="region" aria-labelledby="acc-h3" hidden>
                                <div class="accordion-body">
                                    <p class="text-break">{!!  __('support_page.faq3.para.1') !!}</p>
                                </div>
                            </div>



                            <h2 class="accordion-item">
                                <button class="accordion-trigger" id="acc-h4" aria-expanded="false" aria-controls="acc-p4">
                                    <span class="accordion-arrow" aria-hidden="true">▸</span>
                                    <span class="accordion-title">{!!  __('support_page.faq4.quest') !!}</span>
                                </button>
                            </h2>
                            <div id="acc-p4" class="accordion-panel" role="region" aria-labelledby="acc-h4" hidden>
                                <div class="accordion-body">
                                    <p class="text-break">{!!  __('support_page.faq4.para.1') !!}</p>
                                    <p class="text-break">{!!  __('support_page.faq4.para.2') !!}</p>

                                </div>
                            </div>



                            <h2 class="accordion-item">
                                <button class="accordion-trigger" id="acc-h5" aria-expanded="false" aria-controls="acc-p5">
                                    <span class="accordion-arrow" aria-hidden="true">▸</span>
                                    <span class="accordion-title">{!!  __('support_page.faq5.quest') !!}</span>
                                </button>
                            </h2>
                            <div id="acc-p5" class="accordion-panel" role="region" aria-labelledby="acc-h5" hidden>
                                <div class="accordion-body">
                                    <p class="text-break">{!!  __('support_page.faq5.para.1') !!}</p>

                                </div>
                            </div>



                            <h2 class="accordion-item">
                                <button class="accordion-trigger" id="acc-h6" aria-expanded="false" aria-controls="acc-p6">
                                    <span class="accordion-arrow" aria-hidden="true">▸</span>
                                    <span class="accordion-title">{!!  __('support_page.faq6.quest') !!}</span>
                                </button>
                            </h2>
                            <div id="acc-p6" class="accordion-panel" role="region" aria-labelledby="acc-h6" hidden>
                                <div class="accordion-body">
                                    <p class="text-break">{!!  __('support_page.faq6.para.1') !!}</p>

                                </div>
                            </div>


                            <h2 class="accordion-item">
                                <button class="accordion-trigger" id="acc-h7" aria-expanded="false" aria-controls="acc-p7">
                                    <span class="accordion-arrow" aria-hidden="true">▸</span>
                                    <span class="accordion-title">{!!  __('support_page.faq7.quest') !!}</span>
                                </button>
                            </h2>
                            <div id="acc-p7" class="accordion-panel" role="region" aria-labelledby="acc-h7" hidden>
                                <div class="accordion-body">
                                    <p class="text-break">{!!  __('support_page.faq7.para.1') !!}</p>
                                    <ol>
                                        <li>
                                            {!!  __('support_page.faq7.para.2') !!}
                                        </li>
                                        <li>
                                            {!!  __('support_page.faq7.para.3') !!}
                                        </li>
                                        <li>
                                            {!!  __('support_page.faq7.para.4') !!}
                                        </li>
                                        <li>
                                            {!!  __('support_page.faq7.para.5') !!}
                                        </li>
                                        <li>
                                            {!!  __('support_page.faq7.para.6') !!}
                                        </li>

                                    </ol>
                                </div>
                            </div>


                            <h2 class="accordion-item">
                                <button class="accordion-trigger" id="acc-h8" aria-expanded="false" aria-controls="acc-p8">
                                    <span class="accordion-arrow" aria-hidden="true">▸</span>
                                    <span class="accordion-title">{!!  __('support_page.faq8.quest') !!}</span>
                                </button>
                            </h2>
                            <div id="acc-p8" class="accordion-panel" role="region" aria-labelledby="acc-h8" hidden>
                                <div class="accordion-body">
                                    <p class="text-break">{!!  __('support_page.faq8.para.1') !!}</p>

                                </div>
                            </div>


                            <h2 class="accordion-item">
                                <button class="accordion-trigger" id="acc-h9" aria-expanded="false" aria-controls="acc-p9">
                                    <span class="accordion-arrow" aria-hidden="true">▸</span>
                                    <span class="accordion-title">{!!  __('support_page.faq9.quest') !!}</span>
                                </button>
                            </h2>
                            <div id="acc-p9" class="accordion-panel" role="region" aria-labelledby="acc-h9" hidden>
                                <div class="accordion-body">
                                    <p class="text-break">{!!  __('support_page.faq9.para.1') !!}</p>

                                </div>
                            </div>

                            <h2 class="accordion-item">
                                <button class="accordion-trigger" id="acc-h10" aria-expanded="false"
                                    aria-controls="acc-p10">
                                    <span class="accordion-arrow" aria-hidden="true">▸</span>
                                    <span class="accordion-title">{!!  __('support_page.faq10.quest') !!}</span>
                                </button>
                            </h2>
                            <div id="acc-p10" class="accordion-panel" role="region" aria-labelledby="acc-h10" hidden>
                                <div class="accordion-body">
                                    <p class="text-break">{!!  __('support_page.faq10.para.1') !!}</p>

                                </div>
                            </div>


                            <h2 class="accordion-item">
                                <button class="accordion-trigger" id="acc-h11" aria-expanded="false"
                                    aria-controls="acc-p11">
                                    <span class="accordion-arrow" aria-hidden="true">▸</span>
                                    <span class="accordion-title">{!!  __('support_page.faq11.quest') !!}</span>
                                </button>
                            </h2>
                            <div id="acc-p11" class="accordion-panel" role="region" aria-labelledby="acc-h11" hidden>
                                <div class="accordion-body">
                                    <p class="text-break">{!!  __('support_page.faq11.para.1') !!}</p>
                                    <p class="text-break">{!!  __('support_page.faq11.para.2') !!}</p>
                                    <p class="text-break">{!!  __('support_page.faq11.para.3') !!}</p>
                                    <p class="text-break">{!!  __('support_page.faq11.para.4') !!}</p>

                                </div>
                            </div>

                        </section>


                    </div>
                </div>
            </div>


        </div>
    </section>

    <!-- ask you questions -->
    @include('coreweb::modals.askYourQuestions')
    @include('coreweb::modals.successMessage')

@endsection


@section('scripts')

    @parent
    <script>

        $(document).ready(function () {

            (function () {
                const root = document.getElementById('faq');
                const triggers = root.querySelectorAll('.accordion-trigger');

                function getPanel(trigger) {
                    const id = trigger.getAttribute('aria-controls');
                    return document.getElementById(id);
                }

                function toggle(trigger) {
                    const panel = getPanel(trigger);
                    const open = trigger.getAttribute('aria-expanded') === 'true';
                    trigger.setAttribute('aria-expanded', String(!open));
                    if (panel) panel.hidden = open;
                }

                function onTriggerClick(e) {
                    toggle(e.currentTarget);
                }

                // Keyboard nav across triggers; Space/Enter toggles current
                function onKeyDown(e) {
                    const keys = ['ArrowUp', 'ArrowDown', 'Home', 'End', ' ', 'Enter'];
                    if (!keys.includes(e.key)) return;

                    const btns = Array.from(triggers);
                    const idx = btns.indexOf(e.currentTarget);

                    if (e.key === ' ' || e.key === 'Enter') {
                        e.preventDefault();
                        e.currentTarget.click();
                        return;
                    }
                    if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        btns[(idx - 1 + btns.length) % btns.length].focus();
                    } else if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        btns[(idx + 1) % btns.length].focus();
                    } else if (e.key === 'Home') {
                        e.preventDefault();
                        btns[0].focus();
                    } else if (e.key === 'End') {
                        e.preventDefault();
                        btns[btns.length - 1].focus();
                    }
                }

                // Initialize hidden based on aria-expanded
                triggers.forEach(btn => {
                    const panel = getPanel(btn);
                    const open = btn.getAttribute('aria-expanded') === 'true';
                    if (panel) panel.hidden = !open;

                    btn.addEventListener('click', onTriggerClick);
                    btn.addEventListener('keydown', onKeyDown);
                });
            })();


            var myModal = new bootstrap.Modal(document.getElementById('askYourQuestionModal'));
            myModal._element.addEventListener('shown.bs.modal', function () {
                $('#send-query').trigger('reset');
                document.getElementById('title').focus();
            });
            myModal._element.addEventListener('hidden.bs.modal', function () {
                resetCreateQueryForm();
                $('#send-query').trigger('reset');
            });

            var myModal1 = new bootstrap.Modal(document.getElementById('successMessageModal'));
            myModal1._element.addEventListener('hidden.bs.modal', function () {
                $(".modal-backdrop").remove();
            });

            $('#send-query').submit(function (e) {
                e.preventDefault();

                var formData = new FormData();
                formData.append('title', $('#title').val());
                formData.append('description', $('#description').val());

                if ($('#attachment')[0].files.length > 0) {
                    formData.append('attachment', $('#attachment')[0].files[0]);
                }

                $.ajax({
                    url: api_url + 'create-query',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $(".overlay").show();
                    },
                    success: function (response) {

                        if (response) {
                            console.log('item', response);
                            $.toast({
                                heading: 'Success',
                                text: response.message,
                                icon: 'success',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#9EC600'
                            });

                            $('#send-query').trigger('reset');


                            $("#askYourQuestionModal").modal('hide');

                            $("#successMessage").text(`@json(__('Thank you for your query')). @json(__('We will review it and get back to you')).`);
                            $("#successMessageModal").modal('show');

                            $(".overlay").hide();
                        }
                    },
                    error: function (xhr, status, error) {
                        // Handle the error response
                        $(".overlay").hide();
                        var error = JSON.parse(xhr.responseText);
                        console.error(error);
                        handleCreateQueriesErrors(error);
                    }
                });

            });


            function handleCreateQueriesErrors(error) {

                resetCreateQueryForm();

                $.each(error.data, function (key, value) {
                    switch (key) {
                        case 'title':
                            $("#" + key).addClass('border border-2 border-danger');
                            $("." + key + "-error").removeClass('d-none');
                            $("." + key + "-error").text(value[0]);
                            break;
                        case 'description':
                            $("#" + key).addClass('border border-2 border-danger');
                            $("." + key + "-error").removeClass('d-none');
                            $("." + key + "-error").text(value[0]);
                            break;

                        case 'attachment':
                            $("#" + key).addClass('border border-2 border-danger');
                            $("." + key + "-error").removeClass('d-none');
                            $("." + key + "-error").text(value[0]);
                            break;

                        default:
                            console.warn('Unhandled error key:', key);
                            break;
                    }
                });
            }

            function resetCreateQueryForm() {
                // Reset form fields
                $("#title, #description, #attachement")
                    .removeClass('border border-2 border-danger');
                $(".title-error, .description-error, .attachment-error")
                    .addClass('d-none');

                $("#attachmentFile").attr("src", "");
                $("#attachmentFile").addClass("d-none");
                $("#attachment-clear-button").addClass("d-none");
            }



            // // When a file is selected
            // $('#attachment').change(function () {

            //     var file = this.files[0];

            //     $("#attachmentFile").removeClass("d-none");
            //     if (file) {
            //         var reader = new FileReader();
            //         reader.onload = function (e) {
            //             // Set the source of the preview image to the data URL
            //             $('#attachmentFile').attr('src', e.target.result);

            //             // Once the image is loaded, set its size
            //             $('#attachmentFile').on('load', function () {
            //                 $(this).css({
            //                     'max-width': '200px',
            //                     'max-height': '100px'
            //                 });
            //             });
            //         };

            //         // Read the file as a data URL
            //         reader.readAsDataURL(file);

            //         $("#attachment-clear-button").removeClass("d-none");
            //     }
            // });

            $('#attachment').change(function () {
                var file = this.files[0];
                var allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/bmp', 'text/plain', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/rtf'];
                var maxFileSize = 20 * 1024 * 1024; // 20 MB in bytes

                // Clear previous modal error messages
                $('#errorMessage').text('');
                $('#attachment-clear-button').addClass("d-none");
                $('#attachmentFile').addClass("d-none");
                $(".askYourQuestionsSubmitButton").prop('disabled', false);

                if (file) {
                    // Validate file type
                    if (!allowedTypes.includes(file.type)) {
                        var errorMessage = 'Invalid file type. Allowed types are: JPEG, JPG, GIF, BMP, PNG, TXT, RTF, DOC, DOCX, PDF.';
                        showErrorModal(errorMessage);
                        return;
                    }

                    // Validate file size
                    if (file.size > maxFileSize) {
                        var errorMessage = 'File size exceeds 20 MB. Please upload a smaller file.';
                        showErrorModal(errorMessage);
                        return;
                    }

                    // // If validation passes, show the file preview
                    // var reader = new FileReader();
                    // reader.onload = function (e) {
                    //     $('#attachmentFile').attr('src', e.target.result).removeClass("d-none");

                    //     // Set its size once loaded
                    //     $('#attachmentFile').on('load', function () {
                    //         $(this).css({
                    //             'max-width': '200px',
                    //             'max-height': '100px'
                    //         });
                    //     });
                    // };

                    // If it's an image file, preview it
                    if (file.type.startsWith('image/')) {
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            $('#attachmentFile').attr('src', e.target.result).removeClass("d-none");

                            // Set its size once loaded
                            $('#attachmentFile').on('load', function () {
                                $(this).css({
                                    'max-width': '200px',
                                    'max-height': '100px'
                                });
                            });
                        };
                        reader.readAsDataURL(file);
                    } else {
                        // If the file is not an image, show a default preview image
                        $('#attachmentFile').attr('src', 'assets/img/file.png').removeClass("d-none");
                        // Set its size once loaded
                        $('#attachmentFile').on('load', function () {
                            $(this).css({
                                'max-width': '200px',
                                'max-height': '100px'
                            });
                        });
                    }

                    // Read the file as a data URL
                    // reader.readAsDataURL(file);

                    // Show the clear button
                    $('#attachment-clear-button').removeClass("d-none");
                }
            });

            // Function to show the error message in a modal
            function showErrorModal(message) {
                // $('#errorMessage').text(message); // Set the error message
                // $('#errorModal').modal('show'); // Show the modal
                $(".attachment-error").text(message);
                $(".attachment-error").removeClass("d-none");
                $(".askYourQuestionsSubmitButton").prop('disabled', true);
            }



            // Function to clear the preview image and reset file input
            function clearPreview() {
                $('#attachmentFile').attr('src', ''); // Clear the image source
                $('#attachment').val(''); // Reset the file input
                $("#attachment-clear-button").addClass("d-none");

                $("#attachmentFile").addClass("d-none");
            }

            // Clear button click event handler
            $('#attachment-clear-button').click(function () {
                clearPreview();
            });


            $('#askYourQuestionModal').on('hidden.bs.modal', function () {
                $(".askYourQuestionsSubmitButton").prop('disabled', false);
            });


        });

    </script>
@endsection