<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Transaction Checkout</h1>
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
                    <div class="card-header">
                        Details
                    </div>
                    <div class="card-body">
                        <form enctype="multipart/form-data" method="post" action="<?= base_url('transactions/finish') ?>">
                            <input type="hidden" name="trans_number" value="<?= $trans_number ?>">
                            <input type="hidden" name="total_price" value="<?= $total_price ?>">
                            <div class="form-group">
                                <label for="product">No. Trans: <?= $trans_number ?></label>
                            </div>
                            <div class="form-group">
                                <label for="product">Total Price:</label>
                                <input type="text" class="form-control" value="Rp <?= rupiah($total_price) ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="input_name">Name</label>
                                <input type="text" class="form-control"  name="input_name" id="input_name"
                                data-type="currency" placeholder="" required>
                            </div>
                            <div class="form-group">
                                <label for="input_address">Address</label>
                                <input type="text" class="form-control"  name="input_address" id="input_address"
                                data-type="currency" placeholder="" required>
                            </div>
                            <div class="form-group">
                                <label for="input_phone">Phone</label>
                                <input type="text" class="form-control"  name="input_phone" id="input_phone"
                                data-type="currency" placeholder="" required>
                            </div>
                            <!-- <div class="form-group">
                                <label for="input_delivery">Delivery Date</label>
                                <input type="text" class="form-control"  name="input_delivery" id="input_delivery"
                                data-type="currency" placeholder="" required>
                            </div> -->
                            <div class="form-group">
                                <label for="notes">Notes</label>
                                <textarea class="form-control"  name="notes" id="notes" cols="30" rows="3"></textarea>
                            </div>
                    </div>
                    <div class="card-footer d-flex">
                        <a role="button" href="<?= base_url('transactions/add') ?>" class="btn btn-outline-secondary mr-auto" >Cancel</a>
                        <button type="submit" class="btn btn-primary">Finish</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div><!-- .animated -->
</div>