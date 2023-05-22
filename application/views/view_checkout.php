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
                <div id="alert-container">
                    <?=
                        $this->session->flashdata('item');
                    ?>
                </div>
                <div class="card">
                    <div class="card-header">
                        Details No. Trans: <?= $trans_number ?>
                    </div>
                    <div class="card-body">
                        <form enctype="multipart/form-data" method="post" action="<?= base_url('transactions/finish') ?>">
                            <input type="hidden" name="trans_number" value="<?= $trans_number ?>">
                            <input type="hidden" name="total_price" value="<?= $total_price ?>">
                            <div class="form-group">
                                <label for="product">Total Price:</label>
                                <input type="text" class="form-control" value="Rp <?= rupiah($total_price) ?>" readonly>
                            </div>

                            <div class="row">
                                <div class="col-lg">
                                    <div class="form-group">
                                        <label for="input_name">Customer Type</label>
                                        <select class="custom-select" name="input_customer_type" id="input_customer_type">
                                            <?php

                                            foreach ($customer_type as $key => $value) {
                                                echo '<option value="' . $value->id . '">' .$value->jenis_pelanggan.'</option>';
                                            }

                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="input_name">Name</label>
                                        <input type="text" class="form-control"  name="input_name" id="input_name"
                                        data-type="currency" placeholder="Type Customer Name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="input_address">Delivery Address</label>
                                        <input type="text" class="form-control"  name="input_address" id="input_address"
                                        data-type="currency" placeholder="Type Customer Delivery Address" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="input_phone">Phone</label>
                                        <input type="text" class="form-control"  name="input_phone" id="input_phone"
                                        data-type="currency" placeholder="Type Customer Phone" required>
                                    </div>
                                </div>
                                <div class="col-lg">
                                    <div class="form-group">
                                        <label for="input_delivery">Delivery Date</label>
                                        <div class="datepicker date input-group">
                                            <input type="text" placeholder="Choose Delivery Date Plan" 
                                            class="form-control" id="input_delivery" name="input_delivery" required>
                                            <div class="input-group-append">
                                            <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="input_payment_status">Payment Status</label>
                                        <select class="custom-select" name="input_payment_status" id="input_payment_status">
                                            <option value="0">-- Select --</option>
                                            <option value="1">Paid</option>
                                            <option value="2">Pending</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="notes">Notes</label>
                                        <textarea class="form-control" placeholder="Type notes if any"  name="notes" id="notes" cols="30" rows="3"></textarea>
                                    </div>  
                                </div>
                            </div>
                    </div>
                    <div class="card-footer d-flex">
                        <a role="button" class="btn btn-outline-secondary mr-auto" >Cancel</a>
                        <button type="submit" class="btn btn-primary">Finish</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div><!-- .animated -->
</div>