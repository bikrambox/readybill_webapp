@extends('admin::layouts.admin')

@section('content')
<div class="pagetitle">
    <h1 class="inventoryHeading">View & Update Inventory</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="home">Home</a></li>
            <li class="breadcrumb-item active inventoryLi">View & Update Inventory</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-12">

            <div class="card">
                <div class="card-body">

                    <div class="row mt-4">

                        <div class="col-12 table-responsive my-3">

                            <!-- Table with stripped rows -->
                            <table class="table table-responsive-md table-responsive-lg table-responsive-xl"
                                id="dataList">
                                <thead>
                                    <tr>
                                        <!-- <th>ID</th> -->
                                        <th>Item Name</th>
                                        <th>Stock</th>
                                        <!-- <th>Minimum Stock Alert</th> -->
                                        <th>MRP</th>
                                        <th>Rate</th>
                                        <th>Unit</th>
                                        <th>HSN</th>
                                        <th>GST</th>
                                        <th>CESS</th>
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


<!-- ADD OR UPDATE TAG -->
<!-- <div class="modal fade" id="tagModal" data-bs-keyboard="true" tabindex="-1" aria-labelledby="staticBackdropLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Item Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateItemForm" class="row g-3">

                    <input type="hidden" class="form-control" id="item_id" name="item_id" readonly />

                    <div class="col-12 col-lg-12">
                        <label for="formFile" class="form-label">Item Name<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="item_name" name="item_name"
                            placeholder="Item Name" readonly />
                        <span class="text-danger u_item_name-error d-none"></span>
                    </div>

                    <div class="col-6 text-start">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div> -->

<div class="modal fade" id="tagModal" data-bs-keyboard="true" tabindex="-1" aria-labelledby="tagModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="tagModalLabel">Add Tags</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- Modal Body -->
            <div class="modal-body">
                <form id="tagForm" class="row g-3">
                    <!-- Hidden Input for Item ID -->
                    <input type="hidden" class="form-control" id="item_id" name="item_id" readonly />
                    <input type="hidden" class="form-control" id="shop_id" name="shop_id" readonly />

                    <!-- Tag Input Field -->
                    <div class="col-12">
                        <label for="tag_input" class="form-label fw-bold">Add Tag<span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="tag_input" name="tag_input"
                            placeholder="Enter a tag and press Enter" />
                        <span class="text-danger u_tag_input-error d-none"></span>
                    </div>

                    <input type="hidden" name="tag_list" id="tag_list" value="" readonly />

                    <!-- Tags List -->
                    <div class="col-12">
                        <label class="form-label fw-bold">Tags:</label>
                        <div id="tagsList" class="row g-2">
                            <!-- Tags will be dynamically appended here -->
                        </div>
                    </div>

                    <div class="col-12 error-div text-danger">
                    </div>


                    <!-- Submit and Cancel Buttons -->
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



<!-- ADD OR UPDATE TAG -->


@endsection

@section('scripts')
    @parent
    <!-- Include parent scripts -->
    <script>
        $(document).ready(function () {
            // ----------------------------------- VIEW ITEMS ON TABLE --------------------------------------
            var dataTable;
            var table;
            var originalData;


            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });


            let module_type = "{{$module_type}}";

            function dataList() {

                // Check if the DataTable has already been initialized
                if ($.fn.DataTable.isDataTable('#dataList')) {
                    // If it's already initialized, just destroy it before reinitializing
                    $('#dataList').DataTable().destroy();
                }

                dataTable = $('#dataList').DataTable({
                    processing: true,
                    serverSide: true,
                    // responsive: true,
                    ajax: {
                        url: "{{route('admin.item.list.data')}}",
                        type: 'POST',

                        data: function (d) {
                            // Include the selected option value in the request
                            d.filter_option = $('#filter-select').val();
                            d.shop_id = {{$shop_id}};
                            d.module_type = module_type;
                        },
                        dataSrc: function (response) {
                            console.log('response.data', response.data);
                            $(".totalItemsRecords").html("rows of total <strong>" + response
                                .recordsFiltered +
                                " records</strong>");
                            originalData = response.data;
                            return response.data;
                        },
                        error: function (error) {
                            // Handle error
                        }
                    },
                    columns: [{
                        data: 'item_name'
                    },
                    {
                        data: 'quantity'
                    },
                    {
                        data: null,
                        render: function (data, type, row) {
                            return '₹ ' + data.mrp;
                        }
                    },
                    {
                        data: null,
                        render: function (data, type, row) {
                            return '₹ ' + data.sale_price;
                        }
                    },
                    {
                        data: null,
                        render: function (data, type, row) {
                            return data.short_unit;
                        }
                    },
                    {
                        data: 'hsn'
                    },
                    {
                        data: null,
                        render: function (data, type, row) {

                            if (data.tax1 == 'GST') {
                                return data.rate1;
                            } else if (data.tax2 == 'GST') {
                                return data.rate2;
                            } else if (data.tax3 == 'GST') {
                                return data.rate3;
                            } else {
                                return 'NA';
                            }
                        }
                    },

                    {
                        data: null,
                        render: function (data, type, row) {

                            if (data.tax1 == 'CESS') {
                                return data.rate1;
                            } else if (data.tax2 == 'CESS') {
                                return data.rate2;
                            } else if (data.tax3 == 'CESS') {
                                return data.rate3;
                            } else {
                                return 'NA';
                            }
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,

                        render: function (data, type, row) {
                            let buttons = '<div class="flex items-center justify-center">';

                            // buttons += `<a class="btn btn-primary mb-2" href="" role="button">Tag</a>`;

                            buttons += `<button type="button" class="btn btn-primary tagModalButton" data-id="${data.id}" data-shop_id = "{{$shop_id}}" >Add Tag</button>`;
                            // buttons +=`<button type="button" class="btn btn-primary tagModalButton" data-id="${data.id}" data-shop_id="${shop_id}">Add Tag</button>`;

                            buttons += '</div>';
                            return buttons;
                        }
                    }
                    ],


                    pagingType: 'full_numbers',
                    order: [
                        [0, 'asc']
                    ],
                    // lengthMenu: [10, 25, 50, 100], // Define available page lengths
                    pageLength: 100, // Set the default page length to 100
                    // searching: false,
                    language: {
                        "emptyTable": "Currently there are no items"
                    },
                });
            }

            dataList();

            // Handle DataTables processing event
            dataTable.on('preXhr.dt', function () {
                $(".overlay").show();
            });

            // Handle DataTables processed event
            dataTable.on('xhr.dt', function () {
                $(".overlay").hide();
            });

            // ----------------------------------- VIEW ITEMS ON TABLE --------------------------------------


            // ----------------------------------- TAGS --------------------------------------

            let tags = [];

            $(document).on('click', '.tagModalButton', function (e) {
                let item_id = $(this).data('id');
                let shop_id = $(this).data('shop_id');
                getItemTags(item_id, shop_id);
            });

            function getItemTags(item_id, shop_id) {
                $.ajax({
                    type: 'GET',
                    url: `/${admin_url}/${module_type}/shop/${shop_id}/item-tags/${item_id}`,
                    contentType: false,
                    processData: false,

                    beforeSend: function () {
                        $('.overlay').show();
                    },
                    success: function (response) {
                        console.log('getItemTags', response);

                        tags = [];
                        $('#tagsList').empty();
                        $('#tag_input').val('');
                        $('#tag_list').val('');

                        $("#item_id").val(response.data.id);
                        $("#shop_id").val(shop_id);
                        // $("#item_name").val(response.data.item_name);

                        // Ensure tags is an array
                        tags = response.data.tags && response.data.tags.length > 0 ? JSON.parse(response.data.tags) : [];

                        // if (typeof tags === 'string') {
                        //     tags = tags.split(','); // Convert comma-separated string to array
                        // }

                        // tags = response.data.tags.split(',');
                        // console.log('tags',tags);

                        // Append tags to the list
                        tags.forEach(tag => {
                            console.log('tag', tag);

                            $('#tagsList').append(`
                                <div class="col-4">
                                    <div class="p-2 border rounded d-flex justify-content-between align-items-center">
                                        ${tag}
                                        <button type="button" class="btn btn-sm btn-danger remove-tag" data-tag="${tag}">&times;</button>
                                    </div>
                                </div>
                            `);
                        });


                        $("#tag_list").val(JSON.stringify(tags)); // Store tags as a JSON string

                        $('#tagModal').modal('show');
                        $('.overlay').hide();
                    },
                    error: function (error) {
                        console.log('Error', error);
                    }
                });
            }

            // Handle Enter key to add a tag
            $('#tag_input').on('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const tag = $(this).val().trim();
                    if (tag && !tags.includes(tag)) {
                        tags.push(tag);
                        $("#tag_list").val(JSON.stringify(tags));
                        $('#tagsList').append(`<div class="col-4">
                            <div class="p-2 border rounded d-flex justify-content-between align-items-center">
                                ${tag}
                                <button type="button" class="btn btn-sm btn-danger remove-tag" data-tag="${tag}">&times;</button>
                            </div>
                        </div>`);
                        $(this).val(''); // Clear the input field
                    } else if (tags.includes(tag)) {
                        alert('Tag already added.');
                    }
                }
            });

            // Remove a tag
            // $(document).on('click', '.remove-tag', function () {
            //     const tag = $(this).data('tag');
            //     tags = tags.filter(t => t !== tag);
            //     $(this).parent().remove();
            //     $("#tag_list").val(JSON.stringify(tags));
            // });

            $(document).on('click', '.remove-tag', function () {
                const tag = $(this).data('tag');

                // Remove the tag from the `tags` array, ensuring type consistency
                tags = tags.filter(t => t.toString() !== tag.toString());

                // Remove the corresponding tag box
                $(this).closest('.col-4').remove();

                // Update the hidden field with the updated tags
                $("#tag_list").val(JSON.stringify(tags));

                // Optionally: You can log the updated tags for debugging
                console.log("Updated tags:", tags);
            });



            // Handle form submission
            // $('#tagForm').on('submit', function (e) {
            //     e.preventDefault();
            //     console.log('Tags:', tags); // Process the tags (e.g., send via AJAX)
            //     $('#tagModal').modal('hide');
            // });

            // // Reset tags when modal is opened
            // $('#tagModal').on('show.bs.modal', function () {
            //     tags = [];
            //     $('#tagsList').empty();
            //     $('#tag_input').val('');
            // });
            // ----------------------------------- TAGS --------------------------------------


            // ----------------------------------- UPDATE TAG --------------------------------------
            $('#tagForm').submit(function (e) {

                e.preventDefault();

                var formData = new FormData();
                formData.append('item_id', $('#item_id').val());
                formData.append('shop_id', $('#shop_id').val());
                formData.append('tags', $("#tag_list").val());
                formData.append('module_type', module_type);

                $.ajax({
                    url: "{{route('admin.update.item.tag')}}",
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $(".overlay").show();
                    },
                    success: function (response) {

                        if (response) {
                            console.log('item', response);
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
                            $("#tagModal").modal('hide');

                            $(".error-div").hide();
                            $(".overlay").hide();
                        }
                    },
                    error: function (xhr, status, error) {
                        // Handle the error response
                        $(".overlay").hide();
                        var error = JSON.parse(xhr.responseText);
                        console.error(error);

                        $.each(error.data, function (key, value) {
                            switch (key) {
                                case 'shop_id':
                                case 'item_id':
                                case 'tags':
                                    $(".error-div").show();
                                    $(".error-div").text(value[0]);
                                    break;
                                default:
                                    console.warn('Unhandled error key:', key);
                                    break;
                            }
                        });
                    }
                });
            });
            // ----------------------------------- UPDATE TAG --------------------------------------


            $('#tagModal').on('hidden.bs.modal', function () {
                $(".error-div").hide();
            });


        });

    </script>
@endsection