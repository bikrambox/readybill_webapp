<x-coreweb::modal id="errorModal" title="Duplicate entry" titlePosition="text-start" subtitle=""
    modalPosition="modal-dialog-centered" titlePosition="text-center" subtitlePosition="text-start"
    modalHeaderPadding="" subTitleTextColor="text-danger" closeButtonHide="">
    <div class="text-center">
        <h5 id="errorMessage" class="text-danger">{{ __('common.An item with the same name already exists') }}</h5>
    </div>
</x-coreweb::modal>