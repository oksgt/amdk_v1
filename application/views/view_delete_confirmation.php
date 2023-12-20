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

            <div class="col align-self-center">

                <div id="alert-container">
                    <?=
                        $this->session->flashdata('item');
                    ?>
                </div>

                <div class="card">
                    <div class="card-header">
                        Add User
                    </div>
                    <div class="card-body">
                        <form enctype="multipart/form-data" method="post" action="<?= base_url('users/save') ?>">
                            <div class="form-group">
                                <label for="role">Choose User Role</label>
                                <select class="custom-select" name="role">
                                    <option value=0 selected>-- Select --</option>
                                    <?php 
                                    
                                    foreach ($role as $key => $value) {
                                        echo '<option value="'.$value->id.'">'.$value->role_name.' </option>';
                                    }
                                    
                                    ?>
                                    
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="input_name">Name</label>
                                <input type="text" class="form-control"  name="input_name" id="input_name" placeholder="Name" required>
                            </div>
                            <div class="form-group">
                                <label for="input_username">Username</label>
                                <input type="text" class="form-control"  name="input_username" id="input_username" placeholder="Username" required>
                            </div>
                            <div class="form-group">
                                <label for="input_password">Password</label>
                                <input type="text" class="form-control"  name="input_password" id="input_password" 
                                value="default: amdk_123"
                                placeholder="Username" readonly>
                            </div>
                    </div>
                </div>
            </div>

        </div>
    </div><!-- .animated -->
</div>
