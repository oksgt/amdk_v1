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
                        Edit Stock
                    </div>
                    <div class="card-body">
                        <form enctype="multipart/form-data" method="post" action="<?= base_url('stocks/update') ?>">
                            <input type="hidden" name="id" value="<?= $detail['id'] ?>">
                            <input type="hidden" name="last_stock" value="<?= $detail['last_stock'] ?>">
                            <input type="hidden" name="updated_stock" value="<?= $detail['updated_stock'] ?>">
                            <div class="form-group">
                                <label for="product">Choose Product</label>
                                <select class="custom-select" name="product">
                                    <?php 
                                    $id_product = $detail['id_product']; 
                                    echo '<option >-- Select --</option>';
                                    $selected = '';
                                    foreach ($product as $key => $value) {
                                        
                                        if($value->id == $id_product){
                                            $selected = 'selected';
                                        } else {
                                            $selected = '';
                                        }
                                            echo '<option '.$selected.' value="'.$value->id.'">'.$value->name.' (Stock '.$value->stock.' '.$value->unit.')</option>';
                                        
                                    }
                                    
                                    ?>
                                    
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="input_stock">Input Stock</label>
                                <input type="text" class="form-control"  name="input_stock" id="input_stock" value="<?= $detail['input_stock'] ?>"
                                data-type="currency" placeholder="Product price" required>
                            </div>
                            <div class="form-group">
                                <label for="notes">Notes</label>
                                <textarea class="form-control"  name="notes" id="notes" cols="30" rows="3"><?= $detail['notes'] ?></textarea>
                            </div>
                    </div>
                    <div class="card-footer d-flex">
                        <a role="button" href="<?= base_url('products') ?>" class="btn btn-outline-secondary mr-auto" >Back</a>
                        <button type="submit" class="btn btn-primary">Save</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div><!-- .animated -->
</div>