@extends('admin::layouts.admin')
@section('content')
<div class="pagetitle">
    <h1>{{$business_name ?? ''}} Staff List</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="home">Home</a></li>
            <li class="breadcrumb-item active">{{$business_name ?? ''}} Staff List</li>
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
                                id="userDataList">
                                <thead>
                                    <tr>
                                        <th>User Name</th>
                                        <th>Email</th>
                                        <th>Contact Number</th>
                                        <th>Address</th>
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
        // ----------------------------------- ALL SUB USER --------------------------------------
        var userDataTable;

        // Set up the CSRF token for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function userDataList() {

            userDataTable = $('#userDataList').DataTable({
                processing: true,
                serverSide: true,
                // responsive: true,
                ajax: {
                    url: '{{ route("admin.staff.shop.list.data") }}',
                    type: 'POST',
                    data: function (d) {
                        // Include the selected option value in the request
                        d.filter_option = $('#users_filter-select').val();
                        d.shop_id = {{$shop_id}};
                    },
                    dataSrc: function (response) {
                        console.log('response.data', response.data)
                        // $(".totalUsersRecords").text("Total " + response.recordsFiltered +
                        //     " records found");
                        $(".totalUsersRecords").html("rows of total <strong>" + response.recordsFiltered +
                            " records</strong>");
                        // originalData = response.data;
                        return response.data;
                    },
                    error: function (error) {
                        // Handle error
                    }
                },
                columns: [
                    {
                        data: 'name'
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'mobile'
                    },
                    {
                        data: 'address'
                    },
                ],
                pagingType: 'full_numbers',
                order: [
                    [0, 'asc']
                ],
                // lengthMenu: [10, 25, 50, 100], // Define available page lengths
                pageLength: 100, // Set the default page length to 100
                // searching: false,

                language: {
                    "emptyTable": "Currently there are no Users"
                },
            });
        }

        userDataList();

        // Handle DataTables processing event
        userDataTable.on('preXhr.dt', function () {
            $(".overlay").show();
        });

        // Handle DataTables processed event
        userDataTable.on('xhr.dt', function () {
            $(".overlay").hide();
        });

        // ----------------------------------- ALL SUB USER --------------------------------------

    });

</script>
@endsection