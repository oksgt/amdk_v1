<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Report</h1>
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
                        Cetak Report
                    </div>
                    <div class="card-body">
                        <div class="col-4">
                            <form id="my-form" method="post" action="<?= base_url('/report/create_excel') ?>">
                                <div class="form-group">
                                    <label for="input_date">Report Date</label>
                                    <div class="datepicker date input-group">
                                        <input type="text" placeholder="Choose Report Date" 
                                        class="form-control" id="input_date" name="input_date" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                        </div>
                                    </div>
                                </div>
                            
                        </div>
                    </div>
                    <div class="card-footer d-flex">
                        <button type="submit" class="btn btn-primary">Download</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div><!-- .animated -->
</div>