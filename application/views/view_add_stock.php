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

            <div class="col align-self-center">
                <div class="card">
                    <div class="card-header">
                        Add Stock
                    </div>
                    <div class="card-body">
                        <form enctype="multipart/form-data" method="post" action="<?= base_url('stocks/save') ?>">
                            <div class="form-group">
                                <label for="product">Choose Product</label>
                                <select class="custom-select" name="product">
                                    <option value=0 selected>-- Select --</option>
                                    <?php 
                                    
                                    foreach ($product as $key => $value) {
                                        echo '<option value="'.$value->id.'">'.$value->name.' (Stock '.$value->stock.' '.$value->unit.')</option>';
                                    }
                                    
                                    ?>
                                    
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="input_stock">Input Stock</label>
                                <input type="text" class="form-control"  name="input_stock" id="input_stock"
                                data-type="currency" placeholder="Product price" required>
                            </div>
                            <div class="form-group">
                                <label for="notes">Notes</label>
                                <textarea class="form-control"  name="notes" id="notes" cols="30" rows="3"></textarea>
                            </div>
                    </div>
                    <div class="card-footer d-flex">
                        <a role="button" href="<?= base_url('stocks') ?>" class="btn btn-outline-secondary mr-auto" >Back</a>
                        <button type="submit" class="btn btn-primary">Save</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div><!-- .animated -->
</div>