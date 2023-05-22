<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Transactions</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">

    </div>
</div>

<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="row">

            <div class="col-md-12">

                <div id="alert-container">
                    <?=
                        $this->session->flashdata('item');
                    ?>
                </div>


                <div class="card">
                    <div class="card-header bg-white">
                        <div class="btn-group-sm" role="group">
                            <a type="button" class="btn btn-primary btn-sm" href="<?= base_url('transactions/add') ?>"><b class="ti-plus"></b> Add New Transaction</a>
                            <!-- <button type="button" class="btn btn-primary btn-sm"><b class="ti-reload"></b> Push</button> -->
                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="table_trans" class="table table-striped table-bordered table-sm small">
                            <thead>
                                <tr>
                                    <th>No. Trans</th>
                                    <th>Trans. Date</th>
                                    <th>Customer Info</th>
                                    <th>Delivery Date Plan</th>
                                    <th>Delivery Date</th>
                                    <th>Delivery Status</th>
                                    <th>Payment Status</th>
                                    <th>Total</th>
                                    <th>Notes</th>
                                    <th>#</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


        </div>
    </div><!-- .animated -->
</div>