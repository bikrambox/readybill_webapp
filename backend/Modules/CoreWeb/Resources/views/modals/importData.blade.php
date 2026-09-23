<x-coreweb::modal id="importModal" title="{{ __('common.Import Data') }}" subtitle="{{ __('common.Allowed file format: CSV, XLS, XLSX') }}"
    modalPosition="modal-dialog-centered" titlePosition="text-center" subtitlePosition="text-center"
    modalHeaderPadding="" closeButtonHide="" subTitleTextColor="text-danger">

    <div class="">
        @include('coreweb::components/error', ['heading' => 'Error'])

        <form id="uploadBulkData" class="row g-3">
            <div class="col-12">
                <div id="drop-area" class="border border-primary rounded p-3 text-center">
                    <p class="mb-2">{{ __('common.Drag & Drop your file here or') }} <label for="file" class="text-primary"
                            style="cursor: pointer;">{{ __('common.browse') }}</label></p>
                    <input type="file" id="file" name="file" class="form-control d-none" />
                    <p id="file-name" class="text-muted"></p>
                    <button type="button" id="clearFile" class="btn btn-sm btn-danger mt-2 d-none">{{ __('common.Clear') }}</button>
                </div>
            </div>
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary">{{ __('common.Upload') }}</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('common.Close') }}</button>
            </div>
        </form>
    </div>

</x-coreweb::modal>