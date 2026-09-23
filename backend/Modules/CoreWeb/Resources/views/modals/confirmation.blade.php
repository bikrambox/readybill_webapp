<x-coreweb::modal id="confirmationModal" title="{{$heading}}" titlePosition="text-center" subtitle="{{$subHeading}}"
    modalPosition="modal-dialog-centered" subtitlePosition="text-center" modalHeaderPadding="" closeButtonHide=""
    subTitleTextColor="text-danger">

    <div class="mb-3 text-center">
        <a class="btn btn-success confrimModalButton" href="#" data-id="" data-type="" role="button">{{$buttonText}}</a>
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('common.Cancel') }}</button>
    </div>

</x-coreweb::modal>