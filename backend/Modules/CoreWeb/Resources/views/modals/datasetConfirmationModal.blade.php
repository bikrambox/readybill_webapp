<x-coreweb::modal id="datasetConfirmationModal" title="{{$heading}}" titlePosition="text-center"
    subtitle="{{$subHeading}}" modalPosition="modal-dialog-centered" subtitlePosition="text-center"
    modalHeaderPadding="" closeButtonHide="" subTitleTextColor="text-danger" titleColor="text-danger">

    <!-- <div class="mb-3 text-center">
        <a class="btn btn-success confrimModalButton" href="#" data-id="" data-type="" role="button">{{$buttonText}}</a>
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
    </div> -->

    <div class="FirstConfimation">
        <div class="modal-body">
            <div class="mb-3">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="datasetAction" id="append" value="1" checked>
                    <label class="form-check-label" for="append">{{ __('common.Append') }}</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="datasetAction" id="replace" value="2">
                    <label class="form-check-label" for="replace">{{ __('common.Replace') }}</label>
                </div>
            </div>

            <div class="mb-3 text-center">
                <button type="button" class="btn btn-primary confirmationButton1">{{ __('common.Proceed') }}</button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('common.Cancel') }}</button>
            </div>

        </div>
    </div>

    <div class="SecondConfimation d-none">
        <div class="modal-body">

            <div class="mb-3 text-center">
                <button type="button" class="btn btn-primary confirmationButton2">{{ __('common.Proceed') }}</button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('common.Cancel') }}</button>
            </div>

        </div>
    </div>

</x-coreweb::modal>