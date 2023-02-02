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
            <div class="col-md-12 align-self-center">

                <div id="col-md-12 alert-container">
                    <?=
                    $this->session->flashdata('item');
                    ?>
                </div>

                <div class="card">
                    <div class="card-header">
                        Add Order Item Delivery to <b>"<?= $delivery_code; ?>"</b>
                    </div>
                    <div class="card-body">
                        <table id="table_trans" class="table table-striped table-bordered table-sm small">
                            <thead>
                                <tr>
                                    <th>No. Trans</th>
                                    <th>Trans. Date</th>
                                    <th>Customer Info</th>
                                    <th>Delivery Date Plan</th>
                                    <th>Payment Status</th>
                                    <th>Total</th>
                                    <th>Notes</th>
                                    <th>#</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($undeliver_transactions) > 0){ ?>
                                    <?php 
                                        foreach ($undeliver_transactions as $key => $value) { ?>
                                        <tr>
                                            <td><?= $value->trans_number ?></td>
                                            <td><?= formatTglIndo($value->trans_date) ?></td>
                                            <td><?= "Name: " . $value->name . "<br>Address: " . $value->address . "<br>Phone: " . $value->phone ?></td>
                                            <td><?= formatTglIndo($value->delivery_date_plan) ?></td>
                                            <td><?= ($value->payment_type_id == 1) ? "Paid" : "Pending" ?></td>
                                            <td><?= rupiah($value->total_price); ?></td>
                                            <td><?= $value->notes ?></td>
                                            <td>
                                                <div class="btn-group-sm d-flex" role="group" aria-label="Action Button">
                                                    <a role="button" class="btn btn-success btn-sm w-100 text-white" onclick="pick('<?= $value->trans_number  ?>')">
                                                        <b class="ti-check"></b> Select
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr>
                                        <td class="text-center" colspan="8">No Data Available</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer d-flex">
                        <a role="button" href="<?= base_url('deliveries/add') ?>" class="btn btn-outline-secondary mr-auto">Back</a>
                        <a role="button" href="<?= base_url('deliveries/add') ?>" class="btn btn-primary text-white">Continue</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- .animated -->
</div>

<script>
    function pick(trans_number) {
        Swal.fire({
            title: "Add this order to delivery?",
            showCancelButton: true,
            confirmButtonText: "Yes",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    data: {
                        delivery_code: "<?= $delivery_code; ?>",
                        trans_number: trans_number
                    },
                    url: base_url + "/deliveries/save_delivery_order_list",
                    success: function(data) {
                        var id = <?= $id ?>;
                        if(id == 0){
                            window.open(base_url + "deliveries/add", "_self");
                        } else {
                            window.open(base_url + "deliveries/edit/"+id, "_self");
                        }
                    },
                });
            }
        });
    }
</script>