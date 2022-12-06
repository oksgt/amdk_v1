<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Stocks</h1>
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
                            <a role="button" class="btn btn-primary btn-sm text-white" href="<?= base_url('stocks/add') ?>">
                                <b class="ti-plus"></b> Add Stock
                            </a>
                            <button type="button" class="btn btn-primary btn-sm" onclick="reload_table()"><b class="ti-reload"></b> Refresh</button>
                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="table" class="table table-bordered small table-small">
                            <thead>
                                <tr>
                                    <th>Input Date</th>
                                    <th>Product Name</th>
                                    <th>Input Stock</th>
                                    <th>Notes</th>
                                    <th>Input By</th>
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