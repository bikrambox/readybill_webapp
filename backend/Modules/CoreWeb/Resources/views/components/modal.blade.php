<!-- Sign Up Modal -->
<div class="modal fade" id="{{$id}}" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog {{$modalPosition}} {{$modalLg ?? 'custom-modal-width'}}">
        <div class="modal-content">
            <div class="modal-header {{$modalHeaderPadding}}">
                <div class="row w-100 mx-0 px-0">
                    <!-- Close button column: aligned to the right -->
                    <div class="col-12 text-end mx-0 px-0 {{$closeButtonHide}}">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <!-- Title column: centered using mx-auto -->
                    <div class="col-12 text-left mx-0 px-0">
                        <h3 class="modal-title fw-bold {{$titlePosition}} {{ $titleColor ?? "" }}">
                            <!-- <img src="{{asset('assets/img/warning.png')}}" alt="" class="modal-icon me-2"
                                style="height: 28px;"> -->
                            {{$title}}
                        </h3>
                        <h6 class="fw-normal {{$subTitleTextColor}} {{$subtitlePosition}}">
                            {!! $subtitle !!}
                        </h6>
                    </div>
                </div>

            </div>
            <div class="modal-body pt-0">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>