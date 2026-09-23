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

                        <div class="col-12 table-responsive">
                            <!-- Table with stripped rows -->
                            <table class="table table-responsive-md table-responsive-lg table-responsive-xl"
                                id="shopDataList">
                                <thead>
                                    <tr>
                                        <th>Entity ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Contact Number</th>
                                        <!-- <th>Business Name</th>
                                        <th>Address</th>
                                        <th>Shop Type</th>
                                        <th>GST Number</th> -->
                                        <th>Logo</th>
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

        $('#shopDataList').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.shop.list.data") }}', // Change to your route
                type: 'POST',
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
                    data: 'email'
                },
                {
                    data: 'mobile'
                },
                // {
                //     data: 'business_name'
                // },
                // {
                //     data: 'address'
                // },
                // {
                //     data: 'shop_type'
                // },
                // {
                //     data: 'gstin'
                // },
                // {
                //     data: 'logo'
                // },

                {
                    data: 'logo',
                    render: function (data, type, row) {
                        if (data && data != 'NA') {
                            return `<img src="${media_url}logo/${data}" alt="Photo" class="img-thumbnail" style="max-width: 50px;">`;
                        } else {
                            // return 'No Photo';
                            return `<img src="assets/img/user.jpg" alt="Photo" class="img-thumbnail" style="max-width: 50px;">`;
                        }
                    }
                },

                {
                    data: null,
                    orderable: false,
                    searchable: false,

                    render: function (data, type, row) {
                        let buttons = '<div class="flex items-center justify-center">';

                        buttons += `<a class="btn btn-primary mb-2" href="/admin/shop/${data.user_id}" role="button">View Details</a>`;

                        buttons += '</div>';


                        return buttons;
                    }
                }
            ],
            order: [[1, 'desc']],
            search: {
                smart: true // Enable smart search
            }
        });

    });
</script>

@endsection