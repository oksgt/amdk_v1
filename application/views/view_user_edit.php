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
                        Edit User
                    </div>
                    <div class="card-body">
                        <form enctype="multipart/form-data" method="post" action="<?= base_url('users/update') ?>">
                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                            <div class="form-group">
                                <label for="role">Choose User Role</label>
                                <select class="custom-select" name="role">
                                    <option value=0 selected>-- Select --</option>
                                    <?php 
                                    $role_id = $user['role_id'];
                                    $selected = '';
                                    foreach ($role as $key => $value) {

                                        if($value->id == $role_id){
                                            $selected = 'selected';
                                        } else {
                                            $selected = '';
                                        }

                                        echo '<option '.$selected.' value="'.$value->id.'">'.$value->role_name.' </option>';
                                    }
                                    
                                    ?>
                                    
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="input_name">Name</label>
                                <input type="text" class="form-control"  name="input_name" id="input_name" placeholder="Name" required
                                value="<?= $user['name'] ?>">
                            </div>
                            <div class="form-group">
                                <label for="input_username">Username</label>
                                <input type="text" class="form-control"  name="input_username" id="input_username" placeholder="Username" required
                                value="<?= $user['username'] ?>">
                            </div>
                    </div>
                    <div class="card-footer d-flex">
                        <a role="button" href="<?= base_url('users') ?>" class="btn btn-outline-secondary mr-auto" >Back</a>
                        <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div><!-- .animated -->
</div>