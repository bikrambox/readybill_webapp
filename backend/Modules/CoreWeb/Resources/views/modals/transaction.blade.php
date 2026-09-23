<x-coreweb::modal id="transactionalDetails" title="{{ __('common.Item List') }}" subtitle="" modalPosition=""
    titlePosition="text-start" subtitlePosition="text-start" modalHeaderPadding="" closeButtonHide=""
    subTitleTextColor="text-danger" modalLg="modal-lg">

    <form id="update-transaction" class="row g-3">
        <div class="col-12 col-lg-6 mb-3">
            <label for="formFile" class="form-label fw-bold">{{ __('common.Invoice Number') }}</label>
            <input type="hidden" class="form-control" id="billing_id" name="billing_id" placeholder="Item Name"
                readonly />
            <input type="text" class="form-control" id="u_invoic_number" name="u_invoic_number" placeholder="Item Name"
                readonly style="background-color:#EEEEEE" />
        </div>

        <div class="col-12 mb-3">
            <table class="table" id="e_sale_itemList">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">{{ __('common.Item Name') }}</th>
                        <th scope="col">{{ __('common.Quantity') }}</th>
                        <th scope="col">{{ __('common.Rate') }}</th>
                        <th scope="col">{{ __('common.Amount') }}</th>
                        <th scope="col">{{ __('common.Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>

        <div class="col-6 text-start">
            <button type="button" class="btn btn-primary generateInvoice" data-id="">{{ __('common.Print') }}</button>
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('common.Cancel') }}</button>
        </div>
    </form>

</x-coreweb::modal>