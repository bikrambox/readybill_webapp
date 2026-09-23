@extends('admin::layouts.admin')
@section('content')

    <div class="pagetitle">
        <h1>Agent Details</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="home">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.agent.index') }}">Agent List</a></li>
                <li class="breadcrumb-item active">Agent Details</li>
            </ol>
        </nav>
    </div>

    <section class="section profile">
        <div class="row">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-body pt-3">
                        <div class="row">
                            <div class="col-12">
                                <form id="update-agent">

                                    {{-- Hidden fields --}}
                                    <input name="user_id"  type="hidden" id="user_id"  value="" readonly />
                                    <input name="isActive" type="hidden" id="isActive" value="" readonly />

                                    {{-- User ID --}}
                                    <div class="row mb-3">
                                        <label for="user_id_display" class="col-md-4 col-lg-3 col-form-label">User ID</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input type="text" class="form-control" id="user_id_display"
                                                style="background-color:#EEEEEE" readonly />
                                        </div>
                                    </div>

                                    {{-- Name --}}
                                    <div class="row mb-3">
                                        <label for="name" class="col-md-4 col-lg-3 col-form-label">Name</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="name" type="text" class="form-control" id="name"
                                                style="background-color:#EEEEEE" readonly />
                                        </div>
                                    </div>

                                    {{-- Email --}}
                                    <div class="row mb-3">
                                        <label for="email" class="col-md-4 col-lg-3 col-form-label">Email</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="email" type="email" class="form-control" id="email"
                                                style="background-color:#EEEEEE" readonly />
                                        </div>
                                    </div>

                                    {{-- Mobile --}}
                                    <div class="row mb-3">
                                        <label for="mobile" class="col-md-4 col-lg-3 col-form-label">Mobile Number</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="mobile" type="text" class="form-control" id="mobile"
                                                style="background-color:#EEEEEE" readonly />
                                        </div>
                                    </div>

                                    {{-- Address --}}
                                    <div class="row mb-3">
                                        <label for="address" class="col-md-4 col-lg-3 col-form-label">Address</label>
                                        <div class="col-md-8 col-lg-9">
                                            <textarea name="address" class="form-control" id="address"
                                                style="height:100px; background-color:#EEEEEE" readonly></textarea>
                                        </div>
                                    </div>

                                    {{-- Email Verified --}}
                                    <div class="row mb-3">
                                        <label class="col-md-4 col-lg-3 col-form-label">Email Verified</label>
                                        <div class="col-md-8 col-lg-9 d-flex align-items-center">
                                            <span id="isVerifiedBadge" class="badge fs-6"></span>
                                        </div>
                                    </div>

                                    {{-- Account Status --}}
                                    <div class="row mb-3">
                                        <label class="col-md-4 col-lg-3 col-form-label">Account Status</label>
                                        <div class="col-md-8 col-lg-9 d-flex align-items-center">
                                            <span id="accountStatusBadge" class="badge fs-6"></span>
                                        </div>
                                    </div>

                                    {{-- ─── Images Section ─────────────────────────────── --}}
                                    <div class="row mb-3">
                                        <label class="col-md-4 col-lg-3 col-form-label">Photo</label>
                                        <div class="col-md-8 col-lg-9">
                                            <div class="agent-img-thumb" data-label="Profile Photo" id="photoThumbWrapper" style="display:none;">
                                                <img id="agentPhoto" src="" alt="Agent Photo"
                                                    onerror="this.closest('.agent-img-thumb').style.display='none';" />
                                                <div class="agent-img-overlay">
                                                    <i class="bi bi-zoom-in"></i>
                                                </div>
                                            </div>
                                            <span id="noPhoto" class="text-muted fst-italic" style="display:none;">No photo uploaded</span>
                                        </div>
                                    </div>

                                    {{-- QR Code --}}
                                    <div class="row mb-3">
                                        <label class="col-md-4 col-lg-3 col-form-label">QR Code</label>
                                        <div class="col-md-8 col-lg-9">
                                            <div class="agent-img-thumb agent-img-thumb--square" data-label="QR Code" id="qrThumbWrapper" style="display:none;">
                                                <img id="agentQrCode" src="" alt="QR Code"
                                                    onerror="this.closest('.agent-img-thumb').style.display='none';" />
                                                <div class="agent-img-overlay">
                                                    <i class="bi bi-zoom-in"></i>
                                                </div>
                                            </div>
                                            <span id="noQrCode" class="text-muted fst-italic" style="display:none;">No QR code available</span>
                                        </div>
                                    </div>

                                    {{-- Aadhaar Card --}}
                                    <div class="row mb-3">
                                        <label class="col-md-4 col-lg-3 col-form-label">Aadhaar Card</label>
                                        <div class="col-md-8 col-lg-9">
                                            <div class="agent-img-thumb agent-img-thumb--wide" data-label="Aadhaar Card" id="aadharThumbWrapper" style="display:none;">
                                                <img id="agentAadhar" src="" alt="Aadhaar Card"
                                                    onerror="this.closest('.agent-img-thumb').style.display='none';" />
                                                <div class="agent-img-overlay">
                                                    <i class="bi bi-zoom-in"></i>
                                                </div>
                                            </div>
                                            <span id="noAadhar" class="text-muted fst-italic" style="display:none;">No Aadhaar card uploaded</span>
                                        </div>
                                    </div>

                                    {{-- Active / Deactive button --}}
                                    <div class="row mb-3">
                                        <div class="col-md-12 text-end">
                                            <button type="button" class="btn activeOrDeactiveButton">—</button>
                                        </div>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ─── Lightbox Modal ───────────────────────────────────────────── --}}
    <div id="imageLightbox" class="agent-lightbox" style="display:none;" role="dialog" aria-modal="true" aria-label="Image preview">
        <div class="agent-lightbox__backdrop"></div>
        <div class="agent-lightbox__inner">
            <button class="agent-lightbox__close" aria-label="Close preview">
                <i class="bi bi-x-lg"></i>
            </button>
            <p class="agent-lightbox__label" id="lightboxLabel"></p>
            <img src="" id="lightboxImg" alt="Full size preview" />
        </div>
    </div>

    @include('coreweb::modals.confirmation', [
        'heading'    => 'Are you sure you want to do the action?',
        'subHeading' => '',
        'buttonText' => 'Confirm'
    ])

@endsection


@section('scripts')
    @parent

    <style>
        /* ── Thumbnail Card ──────────────────────────────────────────── */
        .agent-img-thumb {
            position: relative;
            display: inline-block;
            cursor: pointer;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
            transition: box-shadow .2s ease, transform .2s ease;
            background: #f5f5f5;

            /* Default: portrait / profile photo */
            width: 110px;
            height: 110px;
        }

        .agent-img-thumb--square {
            width: 130px;
            height: 130px;
        }

        .agent-img-thumb--wide {
            width: 260px;
            height: 160px;
        }

        .agent-img-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform .25s ease;
        }

        .agent-img-thumb--square img {
            object-fit: contain;
            padding: 6px;
        }

        .agent-img-thumb:hover {
            box-shadow: 0 6px 20px rgba(0,0,0,.15);
            transform: translateY(-2px);
        }

        .agent-img-thumb:hover img {
            transform: scale(1.04);
        }

        /* zoom overlay on hover */
        .agent-img-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,.35);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity .2s ease;
        }

        .agent-img-overlay i {
            color: #fff;
            font-size: 1.6rem;
        }

        .agent-img-thumb:hover .agent-img-overlay {
            opacity: 1;
        }

        /* ── Lightbox ────────────────────────────────────────────────── */
        .agent-lightbox {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .agent-lightbox__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,.78);
            backdrop-filter: blur(4px);
        }

        .agent-lightbox__inner {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            padding: 20px;
            animation: lbFadeIn .2s ease;
        }

        @keyframes lbFadeIn {
            from { opacity: 0; transform: scale(.94); }
            to   { opacity: 1; transform: scale(1); }
        }

        .agent-lightbox__label {
            color: rgba(255,255,255,.75);
            font-size: .85rem;
            letter-spacing: .04em;
            text-transform: uppercase;
            margin: 0;
        }

        .agent-lightbox__close {
            position: absolute;
            top: -8px;
            right: 4px;
            background: rgba(255,255,255,.15);
            border: none;
            color: #fff;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            cursor: pointer;
            transition: background .2s;
        }

        .agent-lightbox__close:hover {
            background: rgba(255,255,255,.3);
        }

        #lightboxImg {
            max-width: min(90vw, 820px);
            max-height: 80vh;
            border-radius: 10px;
            object-fit: contain;
            box-shadow: 0 16px 48px rgba(0,0,0,.5);
            display: block;
        }
    </style>

    <script>
        $(document).ready(function () {

            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            // ── Load on page ready ───────────────────────────────────────
            getAgentDetails({{ $userId }});

            // ── Fetch agent details ──────────────────────────────────────
            function getAgentDetails(userId) {
                $.ajax({
                    type: 'GET',
                    url: '{{ route("admin.agent.show", ["userId" => ":userId"]) }}'.replace(':userId', userId),
                    contentType: false,
                    processData: false,
                    beforeSend: function () { $('.overlay').show(); },
                    success: function (response) {

                        const data = response.data;

                        // Hidden + text fields
                        $('#user_id').val(data.user_id);
                        $('#isActive').val(data.active);
                        $('#user_id_display').val(data.user_id);
                        $('#name').val(data.name);
                        $('#email').val(data.email);
                        $('#mobile').val(data.mobile);
                        $('#address').val(data.address);

                        // ── Email Verified badge ──────────────────────────
                        if (data.isVerified == 1) {
                            $('#isVerifiedBadge')
                                .text('Verified')
                                .removeClass('bg-warning text-dark')
                                .addClass('bg-success');
                        } else {
                            $('#isVerifiedBadge')
                                .text('Unverified')
                                .removeClass('bg-success')
                                .addClass('bg-warning text-dark');
                        }

                        // ── Account Status badge ──────────────────────────
                        setStatusBadge(data.active);

                        // ── Active / Deactivate button ────────────────────
                        setActiveButton(data.active);

                        // ── Photo ─────────────────────────────────────────
                        loadThumb('#agentPhoto', '#photoThumbWrapper', '#noPhoto', data.photo);

                        // ── QR Code ───────────────────────────────────────
                        loadThumb('#agentQrCode', '#qrThumbWrapper', '#noQrCode', data.qr_code);

                        // ── Aadhaar Card ──────────────────────────────────
                        loadThumb('#agentAadhar', '#aadharThumbWrapper', '#noAadhar', data.aadhar_card);

                        $('.overlay').hide();
                    },
                    error: function (xhr) {
                        $('.overlay').hide();
                        console.error('Error fetching agent details', xhr);
                    }
                });
            }

            // ── Helper: load image thumbnail ─────────────────────────────
            function loadThumb(imgSel, wrapperSel, noImgSel, src) {
                if (src) {
                    $(imgSel).attr('src', src);
                    $(wrapperSel).show();
                    $(noImgSel).hide();
                } else {
                    $(wrapperSel).hide();
                    $(noImgSel).show();
                }
            }

            // ── Account status badge ──────────────────────────────────────
            function setStatusBadge(active) {
                if (active == 1) {
                    $('#accountStatusBadge')
                        .text('Active')
                        .removeClass('bg-danger')
                        .addClass('bg-success');
                } else {
                    $('#accountStatusBadge')
                        .text('Inactive')
                        .removeClass('bg-success')
                        .addClass('bg-danger');
                }
            }

            // ── Active / Deactivate button label ─────────────────────────
            function setActiveButton(active) {
                if (active == 1) {
                    $('.activeOrDeactiveButton')
                        .text('Deactivate')
                        .removeClass('btn-success')
                        .addClass('btn-danger');
                } else {
                    $('.activeOrDeactiveButton')
                        .text('Activate')
                        .removeClass('btn-danger')
                        .addClass('btn-success');
                }
            }

            // ── Lightbox open on thumbnail click ─────────────────────────
            $(document).on('click', '.agent-img-thumb', function () {
                const src   = $(this).find('img').attr('src');
                const label = $(this).data('label') || 'Preview';
                $('#lightboxImg').attr('src', src);
                $('#lightboxLabel').text(label);
                $('#imageLightbox').fadeIn(180);
                $('body').css('overflow', 'hidden');
            });

            // ── Lightbox close ────────────────────────────────────────────
            function closeLightbox() {
                $('#imageLightbox').fadeOut(160);
                $('body').css('overflow', '');
            }

            $('.agent-lightbox__close').on('click', closeLightbox);
            $('.agent-lightbox__backdrop').on('click', closeLightbox);

            $(document).on('keydown', function (e) {
                if (e.key === 'Escape') closeLightbox();
            });

            // ── Open confirmation modal ───────────────────────────────────
            $('.activeOrDeactiveButton').on('click', function () {
                $('#confirmationModal').modal('show');
            });

            // ── Confirm modal → toggle status ─────────────────────────────
            $('.confrimModalButton').on('click', function () {
                const userId = $('#user_id').val();
                const active = $('#isActive').val();
                toggleAgentStatus(userId, active);
            });

            // ── AJAX toggle ───────────────────────────────────────────────
            function toggleAgentStatus(userId, active) {
                $.ajax({
                    url:  '{{ route("admin.agent.toggle.status", ["userId" => ":userId"]) }}'.replace(':userId', userId),
                    type: 'POST',
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $('.overlay').show();
                        $('.confrimModalButton').prop('disabled', true);
                    },
                    success: function (response) {
                        const newActive = response.data.active;

                        $('#isActive').val(newActive);
                        setActiveButton(newActive);
                        setStatusBadge(newActive);        // ← keep badge in sync

                        $('#confirmationModal').modal('hide');
                        $('.overlay').hide();

                        $.toast({
                            heading:  'Success',
                            text:     response.message,
                            icon:     'success',
                            loader:   true,
                            position: 'top-right',
                            loaderBg: '#9EC600'
                        });
                    },
                    error: function (xhr) {
                        $('.overlay').hide();
                        const msg = xhr.responseJSON?.message ?? 'Something went wrong.';
                        $.toast({
                            heading:  'Error',
                            text:     msg,
                            icon:     'error',
                            loader:   true,
                            position: 'top-right',
                            loaderBg: '#FF0000'
                        });
                    },
                    complete: function () {
                        $('.confrimModalButton').prop('disabled', false);
                    }
                });
            }

        });
    </script>
@endsection