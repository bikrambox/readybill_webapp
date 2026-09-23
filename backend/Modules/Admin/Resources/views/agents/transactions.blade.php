@extends('admin::layouts.admin')
@section('content')

    <div class="pagetitle">
        <h1>Agent Transactions</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="home">Home</a></li>
                <li class="breadcrumb-item active">Agent Transactions</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row mt-4">

                            {{-- ── Search Bar ──────────────────────────────── --}}
                            <div class="col-12 my-3 d-flex flex-wrap gap-2 align-items-end">

                                <div>
                                    <label for="filter_option" class="form-label fw-bold">Search By</label>
                                    <select id="filter_option" name="filter_option" class="form-select"
                                        style="min-width: 160px;">
                                        <option value="agent_name" selected>Agent Name</option>
                                        <option value="agent_id">Agent ID</option>
                                        <option value="date">Date of Subscription</option>
                                        <option value="status">Payment Status</option>
                                    </select>
                                </div>

                                {{-- Text input (agent_name / agent_id) --}}
                                <div class="flex-grow-1" id="search_text_wrap">
                                    <label for="search_input" class="form-label fw-bold">Search</label>
                                    <input type="text" id="search_input" class="form-control"
                                        placeholder="Type to search..." autocomplete="off">
                                </div>

                                {{-- Date input dd/mm/yyyy --}}
                                <div class="flex-grow-1 d-none" id="search_date_wrap">
                                    <label for="search_date" class="form-label fw-bold">Date of Subscription</label>
                                    <input type="text" id="search_date" class="form-control" placeholder="dd/mm/yyyy"
                                        maxlength="10" autocomplete="off">
                                </div>

                                {{-- Status dropdown --}}
                                <div class="flex-grow-1 d-none" id="search_status_wrap">
                                    <label for="search_status" class="form-label fw-bold">Payment Status</label>
                                    <select id="search_status" class="form-select">
                                        <option value="">-- Select --</option>
                                        <option value="paid">Paid</option>
                                        <option value="pending">Pending</option>
                                    </select>
                                </div>

                            </div>

                            {{-- ── Table ───────────────────────────────────── --}}
                            <div class="col-12 table-responsive">
                                <table class="table table-striped table-hover" id="agentDataList">
                                    <thead>
                                        <tr>
                                            <th data-col="agent_name">Agent Name (Agent ID)</th>
                                            <th>Shop Name (Entity ID)</th>
                                            <th data-col="date">Date of Subscription</th>
                                            <th>Amount</th>
                                            <th data-col="status">Payment Status</th>
                                            <th>Payment Mode</th>
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

    {{-- ── Make Payment Modal ───────────────────────────────────────────────── --}}
    <div class="modal fade" id="makePaymentModal" tabindex="-1" aria-labelledby="makePaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="makePaymentModalLabel">
                        <i class="bi bi-cash-coin me-2"></i>Make Payment
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    {{-- Agent + Amount summary --}}
                    <div class="d-flex align-items-center gap-3 p-3 rounded mb-4 border">
                        <img id="pay_agent_photo" src="" alt="Agent" width="48" height="48"
                            class="rounded-circle object-fit-cover"
                            onerror="this.src='/assets/img/default_agent.png'">
                        <div>
                            <div class="fw-semibold" id="pay_agent_name">–</div>
                            <small class="text-muted" id="pay_agent_entity_id">–</small>
                        </div>
                        <div class="ms-auto text-end">
                            <div class="fw-bold text-success fs-5" id="pay_amount">–</div>
                            <small class="text-muted">Commission Amount</small>
                        </div>
                    </div>

                    <form id="makePaymentForm" novalidate>
                        <input type="hidden" id="pay_commission_id" name="subscription_commission_id">

                        {{-- Payment Mode --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Payment Mode <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_mode" id="mode_offline"
                                        value="cash" checked>
                                    <label class="form-check-label" for="mode_offline">
                                        <i class="bi bi-cash me-1"></i> Cash
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_mode" id="mode_online"
                                        value="online">
                                    <label class="form-check-label" for="mode_online">
                                        <i class="bi bi-phone me-1"></i> Online
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Payment Note --}}
                        <div class="mb-3">
                            <label for="payment_note" class="form-label fw-bold">
                                Payment Note
                                <span class="text-muted fw-normal">(Optional)</span>
                            </label>
                            <textarea id="payment_note" name="payment_note" class="form-control" rows="3"
                                placeholder="Enter any notes about this payment..."></textarea>
                        </div>

                        {{-- Error alert --}}
                        <div class="alert alert-danger d-none mb-0" id="pay_error_msg"></div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmPaymentBtn">
                        <span id="payBtnText">Confirm Payment</span>
                        <span id="payBtnSpinner" class="spinner-border spinner-border-sm d-none ms-1" role="status"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Confirm Status Modal (existing) ─────────────────────────────────── --}}
    <div class="modal fade" id="toggleStatusModal" tabindex="-1" aria-labelledby="toggleStatusModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="toggleStatusModalLabel">Confirm Status Change</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to <strong id="toggleActionText"></strong> agent
                    <strong id="toggleAgentName"></strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn" id="confirmToggleBtn">Confirm</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function () {

            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            // ── Helper: get active search value ─────────────────────────────
            function getSearchValue() {
                var filter = $('#filter_option').val();
                if (filter === 'date') return $('#search_date').val();
                if (filter === 'status') return $('#search_status').val();
                return $('#search_input').val();
            }

            // ── Init DataTable ───────────────────────────────────────────────
            var table = $('#agentDataList').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '{{ route("admin.agent.transactions.data") }}',
                    type: 'POST',
                    data: function (d) {
                        d.filter_option = $('#filter_option').val();
                        d.search = { value: getSearchValue() };
                    }
                },
                columns: [
                    // 0 — Agent Name + Entity ID
                    {
                        data: null,
                        name: 'agent_name',
                        render: function (row) {
                            var photo = row.agent_photo
                                ? `<img src="${row.agent_photo}" alt="photo"
                                            width="32" height="32"
                                            class="rounded-circle me-2 object-fit-cover"
                                            onerror="this.src='/assets/img/default_agent.png'">`
                                : `<i class="bi bi-person-circle fs-4 text-secondary me-2"></i>`;
                            return `<div class="d-flex align-items-center">
                                            ${photo}
                                            <div>
                                                <div class="fw-semibold">${row.agent_name ?? '–'}</div>
                                                <small class="text-muted">#${row.agent_entity_id ?? '–'}</small>
                                            </div>
                                        </div>`;
                        }
                    },
                    // 1 — Shop Name + Entity ID
                    {
                        data: null,
                        orderable: false,
                        render: function (row) {
                            return `<div>
                                            <div class="fw-semibold">${row.shop_name ?? '–'}</div>
                                            <small class="text-muted">${row.shop_entity_id ?? '–'}</small>
                                        </div>`;
                        }
                    },
                    // 2 — Date of Subscription
                    {
                        data: 'date_of_subscription',
                        name: 'date',
                        render: function (val) {
                            if (!val) return '–';
                            return new Date(val).toLocaleDateString('en-GB', {
                                day: '2-digit', month: 'short', year: 'numeric'
                            });
                        }
                    },
                    // 3 — Amount
                    {
                        data: 'amount',
                        orderable: false,
                        render: function (val) {
                            return val != null
                                ? `<span class="fw-semibold">₹${parseFloat(val).toFixed(2)}</span>`
                                : '–';
                        }
                    },
                    // 4 — Payment Status
                    {
                        data: 'agent_payment_date',
                        name: 'status',
                        render: function (val) {
                            if (!val) {
                                return `<span class="badge bg-warning text-dark">Pending</span>`;
                            }
                            // return `<span class="badge bg-success">Paid</span>
                            //             <div><small class="text-muted">${val}</small></div>`;

                            return new Date(val).toLocaleDateString('en-GB', {
                                day: '2-digit', month: 'short', year: 'numeric'
                            });
                        }
                    },
                    // 5 — Payment Mode
                    {
                        data: 'payment_mode',
                        orderable: false,
                        render: function (val) {
                            return val && val !== '–'
                                ? `<span class="badge bg-info text-dark">${val}</span>`
                                : '–';
                        }
                    },
                    // 6 — Action
                    {
                        data: null,
                        orderable: false,
                        render: function (row) {
                            if (row.agent_payment_date) {
                                return `<span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>Paid
                                            </span>`;
                            }
                            return `<button class="btn btn-sm btn-primary make-payment-btn"
                                            data-id="${row.subscription_commission_id}"
                                            data-name="${row.agent_name ?? '–'}"
                                            data-entity="${row.agent_entity_id ?? '–'}"
                                            data-photo="${row.agent_photo ?? ''}"
                                            data-amount="${row.amount ?? '0'}">
                                            <i class="bi bi-cash me-1"></i> Make Payment
                                        </button>`;
                        }
                    },
                ],
                order: [[2, 'desc']],
                pageLength: 10,
                language: {
                    processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading...',
                    emptyTable: 'No transactions found.',
                    zeroRecords: 'No matching transactions found.',
                }
            });

            // ── Toggle search input type ─────────────────────────────────────
            $('#filter_option').on('change', function () {
                var filter = $(this).val();

                $('#search_text_wrap').addClass('d-none');
                $('#search_date_wrap').addClass('d-none');
                $('#search_status_wrap').addClass('d-none');
                $('#search_input').val('');
                $('#search_date').val('');
                $('#search_status').val('');

                if (filter === 'date') {
                    $('#search_date_wrap').removeClass('d-none');
                } else if (filter === 'status') {
                    $('#search_status_wrap').removeClass('d-none');
                } else {
                    $('#search_text_wrap').removeClass('d-none');
                }

                table.ajax.reload();
            });

            // ── Text search — debounced 400ms ────────────────────────────────
            var searchTimer;
            $('#search_input').on('keyup', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function () {
                    table.ajax.reload();
                }, 400);
            });

            // ── Date input — auto-format dd/mm/yyyy ──────────────────────────
            $('#search_date').on('input', function () {
                var digits = $(this).val().replace(/\D/g, '').slice(0, 8);
                var val = digits;

                if (digits.length > 4) {
                    val = digits.slice(0, 2) + '/' + digits.slice(2, 4) + '/' + digits.slice(4);
                } else if (digits.length > 2) {
                    val = digits.slice(0, 2) + '/' + digits.slice(2);
                }

                $(this).val(val);

                if (val.length === 10) table.ajax.reload();
            });

            $('#search_date').on('keyup', function () {
                if ($(this).val() === '') table.ajax.reload();
            });

            // ── Status dropdown ──────────────────────────────────────────────
            $('#search_status').on('change', function () {
                table.ajax.reload();
            });

            // ── Open Make Payment Modal ──────────────────────────────────────
            $(document).on('click', '.make-payment-btn', function () {
                var btn = $(this);

                $('#pay_commission_id').val(btn.data('id'));
                $('#pay_agent_name').text(btn.data('name'));
                $('#pay_agent_entity_id').text('#' + btn.data('entity'));
                $('#pay_amount').text('₹' + parseFloat(btn.data('amount')).toFixed(2));
                $('#pay_agent_photo').attr('src', btn.data('photo') || '/assets/img/default_agent.png');

                // reset form state
                $('#makePaymentForm')[0].reset();
                $('#pay_error_msg').addClass('d-none').text('');
                $('input[name="payment_mode"][value="Offline"]').prop('checked', true);

                $('#makePaymentModal').modal('show');
            });

            // ── Confirm Payment Submit ───────────────────────────────────────
            $('#confirmPaymentBtn').on('click', function () {
                var commissionId = $('#pay_commission_id').val();
                var paymentMode = $('input[name="payment_mode"]:checked').val();
                var paymentNote = $('#payment_note').val().trim();

                $('#pay_error_msg').addClass('d-none').text('');

                if (!paymentMode) {
                    $('#pay_error_msg').removeClass('d-none').text('Please select a payment mode.');
                    return;
                }

                // Loading state
                $('#payBtnText').text('Processing...');
                $('#payBtnSpinner').removeClass('d-none');
                $('#confirmPaymentBtn').prop('disabled', true);

                $.ajax({
                    url: '{{ route("admin.agent.transactions.payment.store") }}',
                    type: 'POST',
                    data: {
                        subscription_commission_id: commissionId,
                        payment_mode: paymentMode,
                        payment_note: paymentNote,
                    },
                    success: function (res) {
                        if (res.status === 'success') {
                            $('#makePaymentModal').modal('hide');
                            table.ajax.reload(null, false);
                        } else {
                            $('#pay_error_msg').removeClass('d-none').text(res.message ?? 'Something went wrong.');
                        }
                    },
                    error: function (xhr) {
                        var msg = xhr.responseJSON?.message ?? 'Server error. Please try again.';
                        $('#pay_error_msg').removeClass('d-none').text(msg);
                    },
                    complete: function () {
                        $('#payBtnText').text('Confirm Payment');
                        $('#payBtnSpinner').addClass('d-none');
                        $('#confirmPaymentBtn').prop('disabled', false);
                    }
                });
            });

        });
    </script>
@endsection