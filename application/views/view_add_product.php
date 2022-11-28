<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Products</h1>
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
                        Add Product
                    </div>
                    <div class="card-body">
                        <form enctype="multipart/form-data" method="post" action="<?= base_url('products/save') ?>">
                            <div class="form-group">
                                <label for="name">Product Name</label>
                                <input type="text" class="form-control" id="name" name="name"
                                aria-describedby="productName" placeholder="Product Name" required>
                            </div>
                            <div class="form-group">
                                <label for="price">Price</label>
                                <input type="text" class="form-control"  name="price" id="price"
                                data-type="currency" placeholder="Product price" required>
                            </div>
                            <div class="form-group">
                                <label for="notes">Notes</label>
                                <textarea class="form-control"  name="notes" id="notes" cols="30" rows="3"></textarea>
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