<div class="breadcrumbs">
    <div class="col-sm-12">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Transaction Details No. <?= $trans_number ?></h1>
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
                <div class="card">
                    <div class="card-header bg-white">
                        <div class="btn-group-sm" role="group">
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" 
                            data-target="#addProduct"><b class="ti-plus"></b> Add Item</button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="reload_table()"><b class="ti-reload"></b> Refresh</button>
                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="table" class="table table-striped table-bordered table-sm small">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th width="10%">Qty</th>
                                    <th>Price</th>
                                    <th>Total Price</th>
                                    <th>#</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                        <div class="col-md-12 text-right">
                            <h2 class="grand_total"></h2>
                        </div>
                    </div>
                    <div class="card-footer d-flex">
                        <a role="button" href="<?= base_url('transactions') ?>" class="btn btn-outline-secondary mr-auto">Cancel</a>
                        <a type="submit" class="btn btn-primary" href="<?= base_url('transactions/checkout/'.$trans_number) ?>">Check out</a>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div><!-- .animated -->
</div>

<!-- Modal -->
<div class="modal fade" id="addProduct" tabindex="-1" role="dialog" aria-labelledby="addProductLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductLabel">Add Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="trans-form" method="post" onSubmit="return false;">
                    <input type="hidden" name="trans_number" id="trans_number" value="<?= $trans_number ?>">
                    <div class="form-group">
                        <label for="product">Choose Product</label>
                        <select class="custom-select" name="product" id="product">
                            <option value='x' selected>-- Select --</option>
                            <?php

                            foreach ($product as $key => $value) {
                                echo '<option value="' . $value->id . '">' . $value->name . ' (Sisa Stock ' . $value->stock . ' ' . $value->unit . ' - @ '.rupiah($value->price).')</option>';
                            }

                            ?>

                        </select>
                        <small id="product_error_detail" class="form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label for="input_qty">Quantity</label>
                        <input type="text" class="form-control" name="input_qty" id="input_qty" 
                        placeholder="Item Quantity" >
                        <small id="input_qty_error_detail" class="form-text text-danger"></small>
                    </div>

                    <div class="form-group">
                        <label for="input_qty">Price</label>
                        <input type="text" class="form-control" name="input_harga" id="input_harga" 
                        placeholder="Harga Jual" required>
                        <small id="input_harga_error_detail" class="form-text text-danger"></small>
                    </div>
                    <!-- <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" name="notes" id="notes" cols="30" rows="3"></textarea>
                    </div> -->
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit"  class="btn btn-primary" id="btn-save">Add</button>
                </form>   
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="editProduct" tabindex="-1" role="dialog" aria-labelledby="editProductLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductLabel">Edit Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="trans-edit-form" method="post" onSubmit="return false;">
                    <input type="hidden" name="trans_number" id="trans_number" value="<?= $trans_number ?>">
                    <div class="form-group">
                        <label for="product">Choose Product</label>
                        <select class="custom-select" name="product" id="product">
                            <option value='x' selected>-- Select --</option>
                            <?php

                            foreach ($product as $key => $value) {
                                echo '<option value="' . $value->id . '">' . $value->name . ' (Stock ' . $value->stock . ' ' . $value->unit . ')</option>';
                            }

                            ?>

                        </select>
                        <small id="product_error_detail" class="form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label for="input_qty">Quantity</label>
                        <input type="text" class="form-control" name="input_qty" id="input_qty" 
                        placeholder="Item Quantity" >
                        <small id="input_qty_error_detail" class="form-text text-danger"></small>
                    </div>
                    <!-- <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" name="notes" id="notes" cols="30" rows="3"></textarea>
                    </div> -->
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit"  class="btn btn-primary" id="btn-edit">Edit</button>
                </form>   
            </div>
        </div>
    </div>
</div>