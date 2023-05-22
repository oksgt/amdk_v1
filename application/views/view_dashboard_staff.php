<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header ">
            <div class="page-title text-center">
                <h1>Dashboard</h1>
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

                <div id="col-md-12 alert-container">
                    <?=
                    $this->session->flashdata('item');
                    ?>
                </div>

                <div class="card" onclick="window.open('<?= base_url('dashboard/pending') ?>', '_self');">
                    <div class="card-body">
                        <div class="stat-widget-one">
                            <div class="stat-icon dib"><i class="ti-truck text-success border-success"></i></div>
                            <div class="stat-content dib">
                                <div class="stat-text">Pending Delivery</div>
                                <div class="stat-digit"><?= $pending_pengiriman['total'] ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- <div class="card" onclick="window.open('<?= base_url() ?>', '_self');">
                    <div class="card-body">
                        <div class="stat-widget-one">
                            <div class="stat-icon dib"><i class="ti-files text-primary border-primary"></i></div>
                            <div class="stat-content dib">
                                <div class="stat-text">History Delivery</div>
                                <div class="stat-digit"><?= $history_pengiriman['total'] ?></div>
                            </div>
                        </div>
                    </div>
                </div> -->

            </div>

        </div>
    </div><!-- .animated -->
</div>