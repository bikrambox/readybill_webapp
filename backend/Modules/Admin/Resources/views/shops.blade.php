@extends('admin::layouts.admin')
@section('content')

    <div class="pagetitle">
        <h1>Shop List</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="home">Home</a></li>
                <li class="breadcrumb-item active">Shop List</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
            
            <div class="col-lg-12">

                <div class="card">
                    <div class="card-body">
                        <div class="row mt-4">

                            <div class="col-12 my-3">
                                <label for="module_type" class="col-md-4 col-lg-3 col-form-label fw-bold">Shop Type</label>
                                <select id="module_type" name="module_type" class="form-select">
                                    <option disabled selected value="">Choose Type</option>
                                    @foreach(config('general.module_type') as $key => $shopType)
                                        <option value="{{ $key }}" {{ strtolower($key) == $module_type ? 'selected' : '' }}>
                                            {{ ucfirst(strtolower($shopType)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 table-responsive">
                                <!-- Table with stripped rows -->
                                <table class="table table-responsive-md table-responsive-lg table-responsive-xl"
                                    id="shopDataList">
                                    <thead>
                                        <tr>
                                            <th>Entity ID</th>
                                            <th>Name</th>
                                            <th>Contact Number</th>
                                            <th>Business Name</th>
                                            <th>Active</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Table body content will be populated dynamically -->
                                    </tbody>
                                </table>
                                <!-- End Table with stripped rows -->
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>


@endsection


@section('scripts')
    @parent
    <!-- Include parent scripts -->
    <script>
        $(document).ready(function () {
            // Set up the CSRF token for AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let module_type = $("#module_type").val();

            var dataTable;

            function dataList(){
                dataTable =  $('#shopDataList').DataTable({

                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("admin.shop.list.data") }}', // Change to your route
                    type: 'POST',
                    data: function(d){
                        d.module_type = module_type;
                    },
                    dataSrc: function (response) {
                        console.log('response.data', response.data)
                        return response.data;
                    },
                    error: function (error) {
                        // Handle error
                    }
                },
                columns: [

                    {
                        data: 'entity_id'
                    },

                    {
                        data: 'name'
                    },
                    {
                        data: 'user_mobile'
                    },
                    {
                        data: 'business_name'
                    },

                     {
                        data: null,
                        orderable: false,
                        searchable: false,

                        render: function (data, type, row) {
                            if (data.user_active == 1) {
                                return `<img src="${base_url}/assets/img/right.png" alt="Photo" class="" style="max-width: 40px;">`;
                            }
                            else if (data.user_active == 0) {
                                return `<img src="${base_url}/assets/img/cross.png" alt="Photo" class="" style="max-width: 40px;">`;
                            }
                        }
                    },


                    {
                        data: null,
                        orderable: false,
                        searchable: false,

                        render: function (data, type, row) {
                            if (data.shop_subscription.payment_status == 'paid') {
                                return `<span class="badge bg-success">Premium</span>`;
                            }
                            else if (data.shop_subscription.payment_status == 'free') {
                                return `<span class="badge bg-warning text-dark">Free</span>`;
                            }
                        }
                    },

                    {
                        data: null,
                        orderable: false,
                        searchable: false,

                        render: function (data, type, row) {
                            let buttons = '<div class="flex items-center justify-center">';

                            buttons += `<a class="btn btn-primary mb-2" href="/${admin_url}/item-list/${module_type}/${data.shop_id}" role="button">Item List</a>`;
                            buttons += `<a class="btn btn-primary mb-2 mx-2" href="/${admin_url}/shop/${module_type}/${data.user_id}" role="button">View Details</a>`;
                            buttons += `<a class="btn btn-primary mb-2 mx-2" href="/${admin_url}/shop/transactions/${data.user_id}" role="button">Transaction Details</a>`;

                            buttons += '</div>';


                            return buttons;
                        }
                    }
                ],
                order: [[2, 'desc']],
                search: {
                    smart: true // Enable smart search
                }
                });
            }

            dataList();


            $("#module_type").change(function (e) {
                module_type = $("#module_type").val();
                dataTable.destroy();
                dataList();
            });

        });
    </script>

@endsection