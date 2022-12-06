<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Users</h1>
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
                            <a role="button" class="btn btn-primary btn-sm text-white" href="<?= base_url('users/add') ?>">
                                <b class="ti-plus"></b> Add New User
                            </a>
                            <button type="button" class="btn btn-primary btn-sm" onclick="reload_table()"><b class="ti-reload"></b> Refresh</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="table" class="table table-bordered table-small small">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Status</th>
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