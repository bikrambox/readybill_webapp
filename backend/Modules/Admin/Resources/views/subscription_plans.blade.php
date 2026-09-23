@extends('admin::layouts.admin')
@section('content')

    <div class="pagetitle">
        <h1>Subscription Plans</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="home">Home</a></li>
                <li class="breadcrumb-item active">Subscription Plans</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row mt-4">

                            <div class="col-12 d-flex justify-content-end mb-3">
                                <button type="button" class="btn btn-success" id="openCreateModal">
                                    <i class="bi bi-plus-circle me-1"></i> Create Plan
                                </button>
                            </div>

                            <div class="col-12 table-responsive">
                                <table class="table table-responsive-md" id="shopDataList">
                                    <thead>
                                        <tr>
                                            <th>Duration (Months)</th>
                                            <th>Plan Name</th>
                                            <th>Price</th>
                                            <th>Heading</th>
                                            <th>Subheading</th>
                                            <th>Description</th>
                                            <th>Best Value</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    {{-- ==================== CREATE MODAL ==================== --}}
    <div class="modal fade" id="createModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fw-bold" id="createModalLabel">Create Subscription Plan</h3>
                </div>
                <div class="modal-body">
                    <form id="createSubscriptionForm" class="row g-3" novalidate>

                        {{-- MANDATORY --}}
                        <div class="col-12">
                            <p class="fw-semibold text-muted mb-0">Mandatory Fields</p>
                            <hr class="mt-1">
                        </div>

                        {{-- Duration --}}
                        <div class="col-12 col-md-6 text-start">
                            <label for="create_months" class="form-label fw-bold">
                                Duration (Months) <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="create_months" name="months" placeholder="1 - 12"
                                min="1" max="12" />
                            <div class="invalid-feedback" id="create_error_months"></div>
                        </div>

                        {{-- Plan Name --}}
                        <div class="col-12 col-md-6 text-start">
                            <label for="create_plan_name" class="form-label fw-bold">
                                Plan Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="create_plan_name" name="plan_name"
                                placeholder="e.g. Basic, Pro, Premium" />
                            <div class="invalid-feedback" id="create_error_plan_name"></div>
                        </div>

                        {{-- Price --}}
                        <div class="col-12 col-md-6 text-start">
                            <label for="create_price" class="form-label fw-bold">
                                Price <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="create_price" name="price" placeholder="e.g. 499" />
                            <div class="invalid-feedback" id="create_error_price"></div>
                        </div>

                        {{-- Status --}}
                        <div class="col-12 col-md-6 text-start">
                            <label class="form-label fw-bold">
                                Status <span class="text-danger">*</span>
                            </label>
                            <div class="mt-1">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="create_active" id="createActiveYes"
                                        value="1" checked>
                                    <label class="form-check-label" for="createActiveYes">Active</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="create_active" id="createActiveNo"
                                        value="0">
                                    <label class="form-check-label" for="createActiveNo">Inactive</label>
                                </div>
                            </div>
                            <div class="text-danger mt-1" id="create_error_active"
                                style="font-size: 0.875em; display: none;"></div>
                        </div>

                        {{-- Best Value --}}
                        <div class="col-12 col-md-6 text-start">
                            <label class="form-label fw-bold">Best Value</label>
                            <div class="mt-1">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="create_is_best_value"
                                        id="createBestValueYes" value="1">
                                    <label class="form-check-label" for="createBestValueYes">Yes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="create_is_best_value"
                                        id="createBestValueNo" value="0" checked>
                                    <label class="form-check-label" for="createBestValueNo">No</label>
                                </div>
                            </div>
                            <div class="text-danger mt-1" id="create_error_is_best_value"
                                style="font-size: 0.875em; display: none;"></div>
                        </div>

                        {{-- OPTIONAL --}}
                        <div class="col-12">
                            <p class="fw-semibold text-muted mb-0">
                                Optional Fields
                                <small class="fw-normal">(shown on subscription page)</small>
                            </p>
                            <hr class="mt-1">
                        </div>

                        {{-- Heading --}}
                        <div class="col-12 text-start">
                            <label for="create_heading" class="form-label fw-bold">Heading</label>
                            <input type="text" class="form-control" id="create_heading" name="heading"
                                placeholder="e.g. Upgrade to Premium for ₹499/month" />
                            <div class="invalid-feedback" id="create_error_heading"></div>
                        </div>

                        {{-- Subheading --}}
                        <div class="col-12 text-start">
                            <label for="create_subheading" class="form-label fw-bold">Subheading</label>
                            <input type="text" class="form-control" id="create_subheading" name="subheading"
                                placeholder="e.g. Unlock all features with the Premium plan" />
                            <div class="invalid-feedback" id="create_error_subheading"></div>
                        </div>

                        {{-- Description --}}
                        <div class="col-12 text-start">
                            <label for="create_description" class="form-label fw-bold">Description</label>
                            <textarea class="form-control" id="create_description" name="description" rows="4"
                                placeholder="e.g. Take your billing experience to the next level..."></textarea>
                            <div class="invalid-feedback" id="create_error_description"></div>
                        </div>

                        {{-- Buttons --}}
                        <div class="col-12 text-start mt-2">
                            <button type="submit" class="btn btn-success">Create</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>

                    </form>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
    {{-- ==================== END CREATE MODAL ==================== --}}


    {{-- ==================== EDIT MODAL ==================== --}}
    <div class="modal fade" id="editModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fw-bold" id="editModalLabel">Update Subscription Plan</h3>
                </div>
                <div class="modal-body">
                    <form id="updateSubscriptionForm" class="row g-3" novalidate>

                        <input type="hidden" id="subscription_id" name="subscription_id" />

                        {{-- MANDATORY --}}
                        <div class="col-12">
                            <p class="fw-semibold text-muted mb-0">Mandatory Fields</p>
                            <hr class="mt-1">
                        </div>

                        {{-- Duration --}}
                        <div class="col-12 col-md-6 text-start">
                            <label for="months" class="form-label fw-bold">
                                Duration (Months) <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="months" name="months" placeholder="1 - 12" min="1"
                                max="12" />
                            <div class="invalid-feedback" id="error_months"></div>
                        </div>

                        {{-- Plan Name --}}
                        <div class="col-12 col-md-6 text-start">
                            <label for="plan_name" class="form-label fw-bold">
                                Plan Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="plan_name" name="plan_name"
                                placeholder="e.g. Basic, Pro, Premium" />
                            <div class="invalid-feedback" id="error_plan_name"></div>
                        </div>

                        {{-- Price --}}
                        <div class="col-12 col-md-6 text-start">
                            <label for="price" class="form-label fw-bold">
                                Price <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="price" name="price" placeholder="e.g. 499" />
                            <div class="invalid-feedback" id="error_price"></div>
                        </div>

                        {{-- Status --}}
                        <div class="col-12 col-md-6 text-start">
                            <label class="form-label fw-bold">
                                Status <span class="text-danger">*</span>
                            </label>
                            <div class="mt-1">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="active" id="activeYes" value="1">
                                    <label class="form-check-label" for="activeYes">Active</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="active" id="activeNo" value="0">
                                    <label class="form-check-label" for="activeNo">Inactive</label>
                                </div>
                            </div>
                            <div class="text-danger mt-1" id="error_active" style="font-size: 0.875em; display: none;">
                            </div>
                        </div>

                        {{-- Best Value --}}
                        <div class="col-12 col-md-6 text-start">
                            <label class="form-label fw-bold">Best Value</label>
                            <div class="mt-1">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_best_value" id="bestValueYes"
                                        value="1">
                                    <label class="form-check-label" for="bestValueYes">Yes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_best_value" id="bestValueNo"
                                        value="0">
                                    <label class="form-check-label" for="bestValueNo">No</label>
                                </div>
                            </div>
                            <div class="text-danger mt-1" id="error_is_best_value"
                                style="font-size: 0.875em; display: none;"></div>
                        </div>

                        {{-- OPTIONAL --}}
                        <div class="col-12">
                            <p class="fw-semibold text-muted mb-0">
                                Optional Fields
                                <small class="fw-normal">(shown on subscription page)</small>
                            </p>
                            <hr class="mt-1">
                        </div>

                        {{-- Heading --}}
                        <div class="col-12 text-start">
                            <label for="heading" class="form-label fw-bold">Heading</label>
                            <input type="text" class="form-control" id="heading" name="heading"
                                placeholder="e.g. Upgrade to Premium for ₹499/month" />
                            <div class="invalid-feedback" id="error_heading"></div>
                        </div>

                        {{-- Subheading --}}
                        <div class="col-12 text-start">
                            <label for="subheading" class="form-label fw-bold">Subheading</label>
                            <input type="text" class="form-control" id="subheading" name="subheading"
                                placeholder="e.g. Unlock all features with the Premium plan" />
                            <div class="invalid-feedback" id="error_subheading"></div>
                        </div>

                        {{-- Description --}}
                        <div class="col-12 text-start">
                            <label for="description" class="form-label fw-bold">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"
                                placeholder="e.g. Take your billing experience to the next level..."></textarea>
                            <div class="invalid-feedback" id="error_description"></div>
                        </div>

                        {{-- Buttons --}}
                        <div class="col-12 text-start mt-2">
                            <button type="submit" class="btn btn-primary">Update</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>

                    </form>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
    {{-- ==================== END EDIT MODAL ==================== --}}


    {{-- ==================== DELETE MODAL ==================== --}}
    <div class="modal fade" id="deleteModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fw-bold text-danger" id="deleteModalLabel">Delete Subscription Plan</h3>
                </div>
                <div class="modal-body text-center">
                    <p class="fs-5">Are you sure you want to delete
                        <strong id="delete_plan_name"></strong>?
                    </p>
                    <p class="text-muted">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>
    {{-- ==================== END DELETE MODAL ==================== --}}


@endsection


@section('scripts')
    @parent
    <script>
        $(document).ready(function () {

            var dataTable;
            var deleteSubscriptionId = null;

            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });


            // ════════════════════════════════════════════════════════
            // DATATABLE
            // ════════════════════════════════════════════════════════
            function dataList() {
                if ($.fn.DataTable.isDataTable('#shopDataList')) {
                    $('#shopDataList').DataTable().destroy();
                }

                dataTable = $('#shopDataList').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route("admin.subscription.plan.data") }}',
                        type: 'POST',
                        dataSrc: function (response) { return response.data; },
                        error: function (error) { console.error('DataTable error', error); }
                    },
                    columns: [
                        { data: 'months' },
                        { data: 'plan_name' },
                        { data: 'price' },
                        {
                            data: 'heading',
                            render: function (data) {
                                return data
                                    ? `<span title="${data}">${data.length > 40 ? data.substring(0, 40) + '...' : data}</span>`
                                    : '<span class="text-muted fst-italic">—</span>';
                            }
                        },
                        {
                            data: 'subheading',
                            render: function (data) {
                                return data
                                    ? `<span title="${data}">${data.length > 40 ? data.substring(0, 40) + '...' : data}</span>`
                                    : '<span class="text-muted fst-italic">—</span>';
                            }
                        },
                        {
                            data: 'description',
                            render: function (data) {
                                if (!data) return '<span class="text-muted fst-italic">—</span>';
                                return data.length > 60
                                    ? `<span title="${data}">${data.substring(0, 60)}...</span>`
                                    : data;
                            }
                        },
                        {
                            data: 'is_best_value',
                            orderable: false,
                            searchable: false,
                            render: function (data) {
                                return parseInt(data) === 1
                                    ? '<span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>Yes</span>'
                                    : '<span class="badge bg-light text-muted">No</span>';
                            }
                        },
                        {
                            data: 'active',
                            orderable: false,
                            searchable: false,
                            render: function (data) {
                                return parseInt(data) === 1
                                    ? '<span class="badge bg-success">Active</span>'
                                    : '<span class="badge bg-danger">Inactive</span>';
                            }
                        },
                        {
                            data: null,
                            orderable: false,
                            searchable: false,
                            render: function (data) {
                                return `
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-primary btn-sm updateSubscription"
                                                data-subscription_id="${data.subscription_id}">
                                                Edit
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm deleteSubscription"
                                                data-subscription_id="${data.subscription_id}"
                                                data-plan_name="${data.plan_name}">
                                                Delete
                                            </button>
                                        </div>`;
                            }
                        }
                    ],
                    order: [[0, 'asc']],
                    search: { smart: true }
                });
            }

            dataList();


            // ════════════════════════════════════════════════════════
            // CREATE
            // ════════════════════════════════════════════════════════
            $('#openCreateModal').on('click', function () {
                clearCreateFormErrors();
                $('#createSubscriptionForm')[0].reset();
                $('#createActiveYes').prop('checked', true);
                $('#createBestValueNo').prop('checked', true);
                $('#createModal').modal('show');
            });

            $('#createSubscriptionForm').submit(function (e) {
                e.preventDefault();
                clearCreateFormErrors();

                var formData = new FormData();
                formData.append('months', $('#create_months').val());
                formData.append('plan_name', $('#create_plan_name').val());
                formData.append('price', $('#create_price').val());
                formData.append('active', $('input[name="create_active"]:checked').val());
                formData.append('is_best_value', $('input[name="create_is_best_value"]:checked').val());
                formData.append('heading', $('#create_heading').val());
                formData.append('subheading', $('#create_subheading').val());
                formData.append('description', $('#create_description').val());

                $.ajax({
                    url: "{{ route('admin.store.subscription.plan') }}",
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function () { $('.overlay').show(); },
                    success: function (response) {
                        $.toast({
                            heading: 'Success',
                            text: response.message,
                            icon: 'success',
                            loader: true,
                            position: 'top-right',
                            loaderBg: '#9EC600'
                        });
                        dataTable.destroy();
                        dataList();
                        $('#createModal').modal('hide');
                        $('.overlay').hide();
                    },
                    error: function (xhr) {
                        $('.overlay').hide();
                        handleAjaxError(xhr, 'create');
                    }
                });
            });


            // ════════════════════════════════════════════════════════
            // EDIT
            // ════════════════════════════════════════════════════════
            $(document).on('click', '.updateSubscription', function () {
                clearEditFormErrors();
                getById($(this).data('subscription_id'));
            });

            function getById(subscription_id) {
                $.ajax({
                    type: 'GET',
                    url: `/${admin_url}/subscription-plan/${subscription_id}`,
                    beforeSend: function () { $('.overlay').show(); },
                    success: function (response) {
                        var d = response.data;

                        $('#subscription_id').val(d.subscription_id);
                        $('#months').val(d.months);
                        $('#plan_name').val(d.plan_name);
                        $('#price').val(d.price);
                        $('#heading').val(d.heading);
                        $('#subheading').val(d.subheading);
                        $('#description').val(d.description);

                        // Status radio
                        parseInt(d.active) === 1
                            ? $('#activeYes').prop('checked', true)
                            : $('#activeNo').prop('checked', true);

                        // Best Value radio
                        parseInt(d.is_best_value) === 1
                            ? $('#bestValueYes').prop('checked', true)
                            : $('#bestValueNo').prop('checked', true);

                        $('#editModal').modal('show');
                        $('.overlay').hide();
                    },
                    error: function (error) {
                        console.error('getById error', error);
                        $('.overlay').hide();
                    }
                });
            }

            $('#updateSubscriptionForm').submit(function (e) {
                e.preventDefault();
                clearEditFormErrors();

                var formData = new FormData();
                formData.append('subscription_id', $('#subscription_id').val());
                formData.append('months', $('#months').val());
                formData.append('plan_name', $('#plan_name').val());
                formData.append('price', $('#price').val());
                formData.append('active', $('input[name="active"]:checked').val());
                formData.append('is_best_value', $('input[name="is_best_value"]:checked').val());
                formData.append('heading', $('#heading').val());
                formData.append('subheading', $('#subheading').val());
                formData.append('description', $('#description').val());

                $.ajax({
                    url: "{{ route('admin.update.subscription.plan') }}",
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function () { $('.overlay').show(); },
                    success: function (response) {
                        $.toast({
                            heading: 'Success',
                            text: response.message,
                            icon: 'success',
                            loader: true,
                            position: 'top-right',
                            loaderBg: '#9EC600'
                        });
                        dataTable.destroy();
                        dataList();
                        $('#editModal').modal('hide');
                        $('.overlay').hide();
                    },
                    error: function (xhr) {
                        $('.overlay').hide();
                        handleAjaxError(xhr, 'edit');
                    }
                });
            });


            // ════════════════════════════════════════════════════════
            // DELETE
            // ════════════════════════════════════════════════════════
            $(document).on('click', '.deleteSubscription', function () {
                deleteSubscriptionId = $(this).data('subscription_id');
                $('#delete_plan_name').text($(this).data('plan_name'));
                $('#deleteModal').modal('show');
            });

            $('#confirmDeleteBtn').on('click', function () {
                if (!deleteSubscriptionId) return;

                $.ajax({
                    url: `/${admin_url}/subscription-plan/delete/${deleteSubscriptionId}`,
                    type: 'POST',
                    beforeSend: function () { $('.overlay').show(); },
                    success: function (response) {
                        $.toast({
                            heading: 'Deleted',
                            text: response.message,
                            icon: 'success',
                            loader: true,
                            position: 'top-right',
                            loaderBg: '#9EC600'
                        });
                        dataTable.destroy();
                        dataList();
                        $('#deleteModal').modal('hide');
                        $('.overlay').hide();
                        deleteSubscriptionId = null;
                    },
                    error: function (xhr) {
                        $('.overlay').hide();
                        try {
                            var err = JSON.parse(xhr.responseText);
                            $.toast({
                                heading: 'Error',
                                text: err.message || 'Something went wrong.',
                                icon: 'error',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#FF0000'
                            });
                        } catch (e) {
                            $.toast({
                                heading: 'Error',
                                text: 'An unexpected error occurred.',
                                icon: 'error',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#FF0000'
                            });
                        }
                        $('#deleteModal').modal('hide');
                        deleteSubscriptionId = null;
                    }
                });
            });


            // ════════════════════════════════════════════════════════
            // HELPERS
            // ════════════════════════════════════════════════════════
            function clearCreateFormErrors() {
                $('#createSubscriptionForm .form-control').removeClass('is-invalid');
                $('#createSubscriptionForm .invalid-feedback').text('');
                $('#create_error_active').text('').hide();
                $('#create_error_is_best_value').text('').hide();
            }

            function clearEditFormErrors() {
                $('#updateSubscriptionForm .form-control').removeClass('is-invalid');
                $('#updateSubscriptionForm .invalid-feedback').text('');
                $('#error_active').text('').hide();
                $('#error_is_best_value').text('').hide();
            }

            // Shared AJAX error handler
            // mode = 'create' | 'edit'
            function handleAjaxError(xhr, mode) {
                var prefix = mode === 'create' ? 'create_' : '';
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.data) {
                        $.each(response.data, function (field, messages) {
                            var msg = Array.isArray(messages) ? messages[0] : messages;
                            if (field === 'active') {
                                $('#' + prefix + 'error_active').text(msg).show();
                            } else if (field === 'is_best_value') {
                                $('#' + prefix + 'error_is_best_value').text(msg).show();
                            } else {
                                $('#' + prefix + field).addClass('is-invalid');
                                $('#' + prefix + 'error_' + field).text(msg);
                            }
                        });
                    } else {
                        $.toast({
                            heading: 'Error',
                            text: response.message || 'Something went wrong.',
                            icon: 'error',
                            loader: true,
                            position: 'top-right',
                            loaderBg: '#FF0000'
                        });
                    }
                } catch (e) {
                    $.toast({
                        heading: 'Error',
                        text: 'An unexpected error occurred.',
                        icon: 'error',
                        loader: true,
                        position: 'top-right',
                        loaderBg: '#FF0000'
                    });
                }
            }

        });
    </script>
@endsection