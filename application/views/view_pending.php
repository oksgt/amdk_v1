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
                <div class="accordion" id="accordionExample">
                    <div class="card">
                        <?php foreach ($data_delivery_detail_list as $key => $value) { ?>
                            <div class="card-header bg-info text-center" id="headingOne">
                                <h2 class="mb-0 text-center">
                                    <button style="text-decoration: none; color: white;" class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        Code: <?= $key ?>
                                    </button>
                                </h2>
                            </div>

                            <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                                <div class="card-body pl-0">
                                    <ul class="list-unstyled">
                                        <?php foreach ($data_delivery_detail_list[$key] as $k => $v) { ?>
                                            <?php 
                                            $label = "";
                                            if($v['received_at'] !== null){
                                                $label = '<span class="badge badge-success">Sent</span>';
                                            }    
                                                
                                            ?>

                                            <li><p class="h6"><?= $k + 1 ?>. <?= $v['name'] ?> <?= $label ?></p></li>
                                            <li><p class="h6"><?= $v['address'].', '.$v['phone']  ?></p></li>
                                            <li class="ml-4">
                                                <ul>
                                                    <?php foreach ($v['detail'] as $kk => $vv) { ?>
                                                        <li><?= $vv['name'].' ( '.$vv['qty'].' '.$vv['unit'].' )'; ?></li>
                                                    <?php } ?>
                                                </ul>
                                            </li>
                                            <li class="mt-2">
                                                <div class="btn-group special" role="group" aria-label="..." style="display: flex;">
                                                        <a role="button" class="btn btn-success" style="flex: 1"
                                                        href="<?= base_url('dashboard/detail_order/'.$v['trans_number']) ?>"
                                                        >Detail</a>
                                                        <!-- <button type="button" class="btn btn-danger" style="flex: 1">Failed</button> -->
                                                </div>
                                            </li>
                                            <hr>
                                        <?php } ?>
                                    </ul>
                                    
                                    
                                </div>
                            </div>
                        <?php } ?>



                    </div>
                </div>

            </div>

        </div>
    </div><!-- .animated -->
</div>