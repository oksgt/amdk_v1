<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Deliveries</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">

    </div>
</div>

<div class="content mt-3">
    <div class="animated fadeIn">

        <div class="row">

            <div class="col align-self-center">

                <div id="col-md-12 alert-container">
                    <?=
                    $this->session->flashdata('item');
                    ?>
                </div>

                <div class="card">
                    <div class="card-header">
                        Add Delivery
                    </div>
                    <div class="card-body">
                        <!-- <form method="post"> -->
                        <div class="col-6">
                            <div class="form-group row">
                                <label for="input_delivery_date" class="col-sm-4 col-form-label">Delivery Date</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="input_delivery_date" name="input_delivery_date" value="<?= formatTglIndo(Date('Y-m-d')) ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group row">
                                <label for="input_delivery_date" class="col-sm-4 col-form-label">Delivery Code</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" name="input_code" id="input_code" readonly value="<?= "dlv" . Date('Ymd') . "_" . $batch ?>">
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <hr>
                        </div>

                        <div class="col-md-6 table-responsive">
                        <a role="button" style="color: white;" class="btn btn-primary btn-sm" onclick="openOrderItemDelivery()">Add Order</a>
                            <table id="table_list_trans" class="table table-bordered table-sm small">
                                <thead>
                                    <tr>
                                        <th>Trans Number</th>
                                        <th>Detail</th>
                                        <th>#</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($ready_to_deliver) > 0) { ?>
                                        <?php
                                        foreach ($ready_to_deliver as $key => $value) { ?>
                                            <tr>
                                                <td><?= $value->trans_number ?></td>
                                                <td><?= "Name: " . $value->name . "<br>Address: " . $value->address . "<br>Phone: " . $value->phone ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-block btn-danger" onclick="cancel_list(<?= $value->id ?>)">
                                                        Cancel
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                        ?>
                                    <?php } else { ?>
                                        <tr class="text-center">
                                            <td colspan="3">No Data Available</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="col-md-6 table-responsive">
                            <button type="button" onclick="openStaffDelivery()" 
                            class="btn btn-primary btn-sm text-white">Add Staff Delivery</button>
                            <table id="table_list_carier" class="table table-bordered table-sm small">
                                <thead>
                                    <tr>
                                        <th width="70%">Name</th>
                                        <th>#</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($selected_staff) > 0) { ?>
                                        <?php
                                        foreach ($selected_staff as $key => $value) { ?>
                                            <tr>
                                                <td><?= $value->name ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-block btn-danger" onclick="cancel_staff_list(<?= $value->id ?>)">
                                                        Cancel
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                        ?>
                                    <?php } else { ?>
                                        <tr class="text-center">
                                            <td colspan="3">No Data Available</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer d-flex">
                        <a role="button" href="<?= base_url('deliveries') ?>" class="btn btn-outline-secondary mr-auto">Back</a>
                        <?php
                        if (count($ready_to_deliver) > 0 && count($selected_staff) > 0) {
                            echo '<button type="button" class="btn btn-primary" onclick="save_deliv()">Save</button>';
                        } else {
                            echo '<button type="button" class="btn btn-primary " disabled="true" >Please add order and staff delivery to continue</button>';
                        }

                        ?>
                        <!-- </form> -->
                    </div>
                </div>
            </div>

        </div>
    </div><!-- .animated -->
</div>

<script>
    function cancel_list(id) {
        Swal.fire({
            title: "Cancel this item?",
            showCancelButton: true,
            confirmButtonText: "Yes",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    data: {
                        id: id
                    },
                    url: base_url + "/deliveries/cancel_delivery_item_list",
                    success: function(data) {
                        window.open(base_url + "deliveries/add", "_self");
                    },
                });
            }
        });
    }

    function cancel_staff_list(id) {
        Swal.fire({
            title: "Cancel this item?",
            showCancelButton: true,
            confirmButtonText: "Yes",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    data: {
                        id: id
                    },
                    url: base_url + "/deliveries/cancel_delivery_staff_list",
                    success: function(data) {
                        window.open(base_url + "deliveries/add", "_self");
                    },
                });
            }
        });
    }

    function save_deliv() {
        Swal.fire({
            title: "Save?",
            showCancelButton: true,
            confirmButtonText: "Yes",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    data: {
                        delivery_code: $('#input_code').val(),
                        batch: <?= $batch ?>
                    },
                    url: base_url + "/deliveries/save",
                    success: function(data) {
                        window.open(base_url + "deliveries", "_self");
                    },
                });
            }
        });
    }

    function openStaffDelivery() {
        var url = base_url + "deliveries/available_staff_list";
        $.redirect(url, {'source': 'add'});
    }

    function openOrderItemDelivery() {
        var url = base_url + "deliveries/order_item";
        $.redirect(url, {
            'batch': "",
            'id': 0
        });
    }
</script>