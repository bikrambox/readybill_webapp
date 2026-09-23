@extends('admin::layouts.admin')
@section('content')

    <div class="pagetitle">
        <h1>Agent List</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="home">Home</a></li>
                <li class="breadcrumb-item active">Agent List</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row mt-4">

                            {{-- Search bar --}}
                            <div class="col-12 my-3 d-flex flex-wrap gap-2 align-items-end">
                                <div>
                                    <label for="filter_option" class="form-label fw-bold">Search By</label>
                                    <select id="filter_option" name="filter_option" class="form-select"
                                        style="min-width:140px;">
                                        <option value="name" selected>Name</option>
                                        <option value="email">Email</option>
                                        <option value="mobile">Mobile</option>
                                        <option value="address">Address</option>
                                    </select>
                                </div>
                                <div class="flex-grow-1">
                                    <label for="search_input" class="form-label fw-bold">Search</label>
                                    <input type="text" id="search_input" class="form-control"
                                        placeholder="Type to search...">
                                </div>
                            </div>

                            <div class="col-12 table-responsive">
                                <table class="table table-striped table-hover" id="agentDataList">
                                    <thead>
                                        <tr>
                                            <th>Photo</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Mobile</th>
                                            <th>Address</th>
                                            <th>Status</th>
                                            <th>Email Verified</th>
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


    {{-- ── Confirmation Modal ─────────────────────────────────────────── --}}
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

            let filter_option = 'name';
            let searchValue = '';

            // ── DataTable ────────────────────────────────────────────────
            const dataTable = $('#agentDataList').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("admin.agent.list.data") }}',
                    type: 'POST',
                    data: function (d) {
                        d.search = { value: searchValue, regex: false };
                        d.filter_option = filter_option;
                    }
                },
                columns: [
                    // 0 — Photo
                    {
                        data: 'photo',
                        orderable: false,
                        searchable: false,
                        render: function (data) {
                            const src = data || `${base_url}/assets/img/default_agent.png`;
                            return `<img src="${src}" alt="Photo"
                                                style="width:40px;height:40px;object-fit:cover;border-radius:50%;"
                                                onerror="this.src='${base_url}/assets/img/default_agent.png'">`;
                        }
                    },
                    // 1 — Name
                    { data: 'name', name: 'name', defaultContent: '—' },
                    // 2 — Email
                    { data: 'email', name: 'email', defaultContent: '—' },
                    // 3 — Mobile
                    { data: 'mobile', name: 'mobile', defaultContent: '—' },
                    // 4 — Address
                    { data: 'address', name: 'address', defaultContent: '—' },
                    // 5 — Status toggle button
                    {
                        data: 'active',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            if (data == 1) {
                                return `<button
                                                    class="btn btn-sm btn-success toggle-status-btn"
                                                    data-id="${row.user_id}"
                                                    data-name="${row.name ?? ''}"
                                                    data-active="1"
                                                    title="Click to Deactivate">
                                                    Active
                                                </button>`;
                            } else {
                                return `<button
                                                    class="btn btn-sm btn-danger toggle-status-btn"
                                                    data-id="${row.user_id}"
                                                    data-name="${row.name ?? ''}"
                                                    data-active="0"
                                                    title="Click to Activate">
                                                    Inactive
                                                </button>`;
                            }
                        }
                    },
                    // 6 — Verified
                    {
                        data: 'isVerified',
                        orderable: false,
                        searchable: false,
                        render: function (data) {
                            return data == 1
                                ? '<span class="badge bg-primary">Verified</span>'
                                : '<span class="badge bg-warning text-dark">Unverified</span>';
                        }
                    },
                    // 7 — Actions
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data) {
                            return `
                                        <div class="d-flex gap-1 flex-wrap">
                                            <a class="btn btn-sm btn-outline-primary"
                                               href="/${admin_url}/agent/${data.user_id}"
                                               role="button">View</a>
                                        </div>`;
                        }
                    }
                ],
                order: [[1, 'asc']],
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"i>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            });

            //   <a class="btn btn-sm btn-outline-secondary"
            //                                    href="/${admin_url}/agent/transactions/${data.user_id}"
            //                                    role="button">Transactions</a>

            $('#agentDataList_filter').hide();

            // ── Custom search ────────────────────────────────────────────
            let searchTimer;
            $('#search_input').on('input', function () {
                clearTimeout(searchTimer);
                const val = $(this).val().trim();
                searchTimer = setTimeout(function () {
                    searchValue = val;
                    dataTable.draw();
                }, 400);
            });

            $('#filter_option').on('change', function () {
                filter_option = $(this).val();
                if (searchValue !== '') dataTable.draw();
            });


            // ── Toggle status — open modal ───────────────────────────────
            let pendingUserId = null;
            let pendingActive = null;

            $(document).on('click', '.toggle-status-btn', function () {
                pendingUserId = $(this).data('id');
                pendingActive = $(this).data('active');   // current active value

                const agentName = $(this).data('name') || 'this agent';
                const actionText = pendingActive == 1 ? 'deactivate' : 'activate';

                $('#toggleAgentName').text(agentName);
                $('#toggleActionText').text(actionText);
                $('#confirmToggleBtn')
                    .removeClass('btn-success btn-danger')
                    .addClass(pendingActive == 1 ? 'btn-danger' : 'btn-success')
                    .text(actionText.charAt(0).toUpperCase() + actionText.slice(1));

                const modal = new bootstrap.Modal(document.getElementById('toggleStatusModal'));
                modal.show();
            });


            // ── Toggle status — confirm ──────────────────────────────────
            // ── Toggle status — confirm ──────────────────────────────────
            $('#confirmToggleBtn').on('click', function () {
                if (!pendingUserId) return;

                const $btn = $(this).prop('disabled', true).text('Processing...');

                $.ajax({
                    url: '{{ route("admin.agent.toggle.status", ["userId" => ":userId"]) }}'.replace(':userId', pendingUserId),
                    type: 'POST',
                    success: function (response) {
                        bootstrap.Modal.getInstance(
                            document.getElementById('toggleStatusModal')
                        ).hide();

                        dataTable.draw(false);

                        $.toast({
                            heading: 'Success',
                            text: response.message ?? 'Status updated successfully.',
                            icon: 'success',
                            loader: true,
                            position: 'top-right',
                            loaderBg: '#9EC600'
                        });
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.message ?? 'Something went wrong.';

                        $.toast({
                            heading: 'Error',
                            text: msg,
                            icon: 'error',
                            loader: true,
                            position: 'top-right',
                            loaderBg: '#FF0000'
                        });
                    },
                    complete: function () {
                        $btn.prop('disabled', false);
                        pendingUserId = null;
                        pendingActive = null;
                    }
                });
            });

        });
    </script>
@endsection