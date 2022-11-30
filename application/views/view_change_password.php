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

            <div class="col-12 mb-1">
                <div id="alert-container" >
                    <?=
                        $this->session->flashdata('item');
                    ?>
                </div>
            </div>

            <div class="col-12 align-self-center">

                <div class="card">
                    <div class="card-header">
                        Change Password
                    </div>
                    <div class="card-body">
                        <form enctype="multipart/form-data" method="post" action="<?= base_url('users/update_password') ?>">
                            <div class="form-group">
                                <label for="old_password">Old Password</label>
                                <input type="password" class="form-control"  name="old_password" id="old_password"
                                data-type="currency" placeholder="Type Old Password" required autocomplete="off">
                            </div>
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" class="form-control"  name="new_password" id="new_password"
                                data-type="currency" placeholder="Type New Password" required autocomplete="off">
                            </div>
                    </div>
                    <div class="card-footer d-flex">
                        <a role="button" href="<?= base_url() ?>" class="btn btn-outline-secondary mr-auto" >Back</a>
                        <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div><!-- .animated -->
</div>