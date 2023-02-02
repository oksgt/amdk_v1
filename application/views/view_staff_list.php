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

        <div class="container">
            <div class="row">
                <div class="col-md-6 offset-md-3 align-self-center">

                    <div id="col-md-12 alert-container">
                        <?=
                        $this->session->flashdata('item');
                        ?>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            Select staff to deliver <?= formatTglIndo(Date('Y-m-d')) ?> Batch <?= $batch ?>
                        </div>
                        <div class="card-body">
                            <table id="table_trans" class="table table-striped table-bordered table-sm small">
                                <thead>
                                    <tr>
                                        <th width="70%">Name</th>
                                        <th>#</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($staff) > 0) { ?>
                                        <?php
                                        foreach ($staff as $key => $value) { ?>
                                            <tr>
                                                <td><?= $value->name ?></td>
                                                <td>
                                                    <div class="btn-group-sm d-flex" role="group" aria-label="Action Button">
                                                        <a role="button" class="btn btn-success btn-sm w-100 text-white" onclick="pick_staff('<?= $value->id  ?>')">
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
        </div>
    </div><!-- .animated -->
</div>

<script>
    function pick_staff(id_staff) {
        Swal.fire({
            title: "Select this staff?",
            showCancelButton: true,
            confirmButtonText: "Yes",
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    type: "POST",
                    data: {
                        delivery_code: '<?= "dlv" . Date('Ymd') . "_" . $batch ?>',
                        id_staff: id_staff
                    },
                    url: base_url + "/deliveries/save_staff_order_list",
                    success: function(data) {
                        var id = <?= $id ?>;
                        if(id == 0 || id == ""){
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