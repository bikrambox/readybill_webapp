<x-coreweb::modal id="editModal" title="{{ __('inventory_page.Item Details') }}"
    subtitle="{{ __('inventory_page.Fields marked with a star (*) are mandatory') }}" modalPosition=""
    titlePosition="text-start" subtitlePosition="text-start" modalHeaderPadding="" closeButtonHide=""
    subTitleTextColor="text-danger" modalLg="modal-lg">

    <form id="updateItemForm" class="row g-3">
        <input type="hidden" class="form-control" id="item_id" name="item_id" readonly />
        <div class="col-12 col-lg-12">
            <label for="formFile" class="form-label">{{ __('common.Item Name') }}<span
                    class="text-danger">*</span></label>
            <input type="text" class="form-control" id="u_item_name" name="u_item_name"
                placeholder="{{ __('common.Item Name') }}" />
            <span class="text-danger u_item_name-error d-none"></span>
        </div>

        <div class="col-12">
            <div class="row">
                <div class="col-12 col-lg-6 stockQuantuty-div">
                    <label for="formFile" class="form-label">{{ __('inventory_page.Stock Quantity') }}<span
                            class="text-danger stockQuantuty-required">*</span></label>
                    <input type="text" class="form-control numericField" id="u_quantity" name="u_quantity"
                        placeholder="{{ __('inventory_page.Stock Quantity') }}" aria-describedby="stockNote" />
                    <span class="text-danger u_quantity-error d-none"></span>
                </div>

                <div class="col-12 col-lg-6 minimumStockAlert-div">
                    <label for="formFile" class="form-label">{{ __('inventory_page.Minimum Stock Alert') }}</label>
                    <input type="text" class="form-control numericField" id="u_min_stock_alert" name="u_min_stock_alert"
                        placeholder="{{ __('inventory_page.Minimum Stock Alert') }}" aria-describedby="stockNote" />
                    <span class="text-danger u_min_stock_alert-error d-none"></span>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label for="unit" class="form-label">{{ __('inventory_page.Unit') }}<span
                    class="text-danger">*</span></label>
            <select id="u_unit" name="u_unit" class="form-select">
                <option disabled selected value="">{{ __('inventory_page.Select Unit') }}</option>
                @foreach(config('german_units.units') as $fullName => $shortName)
                    <option data-full="{{ $fullName }}" value="{{ $shortName }}">{{ $fullName }}
                        ({{ $shortName }})</option>
                @endforeach
            </select>
            <span class="text-danger u_unit-error d-none"></span>
        </div>

        <div class="col-12 hsn-div">
            <div class="row">
                <div class="col-lg-6">
                    <label for="hsn" class="form-label">{{ __('inventory_page.HSN/ SAC Code') }}<span
                            class="text-danger hsn-required">*</span></label>
                    <input type="text" class="form-control" name="u_hsn" id="u_hsn"
                        placeholder="{{ __('inventory_page.HSN/ SAC Code') }}" />
                    <span class="text-danger u_hsn-error d-none"></span>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="row">
                <div class="col-12 mrp-div col-lg-6">
                    <label for="sale_price" class="form-label">{{ __('inventory_page.MRP') }}<span
                            class="text-danger mrp-required">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text currency">₹</span>
                        <input type="text" class="form-control numericField" name="u_mrp" id="u_mrp"
                            placeholder="{{ __('inventory_page.Price') }}" />
                        <span class="input-group-text unitSelected"></span>
                        <span class="text-danger u_mrp-error d-none"></span>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <label for="sale_price" class="form-label">{{ __('inventory_page.Rate') }}<span
                            class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text currency">₹</span>
                        <input type="text" class="form-control numericField" name="u_sale_price" id="u_sale_price"
                            placeholder="{{ __('inventory_page.Price') }}" />
                        <span class="input-group-text unitSelected"></span>
                    </div>
                    <span class="text-danger u_sale_price-error d-none"></span>
                </div>

            </div>
        </div>

        <div class="col-12">
            <div class="row taxSection my-2" id="row-1" data-id="1">
                <div class="col-6">
                    <label for="tax" class="form-label">{{ __('inventory_page.Tax') }} <span
                            class="text-danger">*</span></label></label>
                    <select id="u_tax" name="u_tax" class="form-select">
                        <option disabled selected value="">{{ __('inventory_page.Select Tax') }}</option>
                        @foreach(config('german_tax.taxes') as $tax)
                            <option value="{{ $tax['name'] }}" data-value="{{ $tax['value'] }}">{{ $tax['name'] }} {{ $tax['value'] }}%</option>
                        @endforeach
                    </select>
                </div>
            
            
                <div class="col-12">
                    <span class="text-danger u_tax-error d-none"></span>
                    <span class="text-danger u_rate-error d-none"></span>
                </div>
            </div>
        </div>

        <div class="col-6 text-start">
            <button type="submit" class="btn btn-primary">{{ __('common.Update') }}</button>
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('common.Cancel') }}</button>
        </div>
    </form>
</x-coreweb::modal>