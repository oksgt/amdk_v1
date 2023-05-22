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
                <div class="card">
                    <div class="card-header bg-info text-white">
                        Detail
                    </div>
                    <div class="card-body">

                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <td>No. Trans</td>
                                    <td>:</td>
                                    <td><?= $transaksi['trans_number'] ?></td>
                                </tr>
                                <tr>
                                    <td>Name</td>
                                    <td>:</td>
                                    <td><?= $transaksi['name'] ?></td>
                                </tr>
                                <tr>
                                    <td>Address</td>
                                    <td>:</td>
                                    <td><?= $transaksi['address'] ?></td>
                                </tr>
                                <tr>
                                    <td>Phone</td>
                                    <td>:</td>
                                    <td><?= $transaksi['phone'] ?></td>
                                </tr>
                                <tr>
                                    <td>Notes</td>
                                    <td>:</td>
                                    <td><?= $transaksi['notes'] ?></td>
                                </tr>
                            </tbody>
                        </table>
                        <br>
                        <h5 class="card-title">Item List</h5>
                        <table class="table table-sm ">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Qty</th>
                                    <th>#</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($transaksi_detail as $key => $value) {
                                    echo "<tr>";
                                    echo "<td>" . $value->name . "</td>";
                                    echo "<td>" . $value->qty . "</td>";
                                    echo "<td>-</td>";
                                    // echo '<td>
                                    //         <div class="btn-group" role="group" aria-label="Basic example">
                                    //             <button type="button" class="btn btn-primary"><i class="ti-pencil-alt"></i></button>
                                    //             <button type="button" class="btn btn-primary"><i class="ti-check-box"></i></button>
                                    //         </div>
                                    //     </td>';
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                        <br>
                        <?php echo form_open_multipart('dashboard/submit_delivery'); ?>
                                <input type="hidden" name="delivery_code" value="<?= $transaksi['delivery_code']  ?>">
                                <input type="hidden" name="trans_number" value="<?= $transaksi['trans_number']  ?>">
                            <div class="form-group">
                                <label for="exampleFormControlTextarea1">Delivery Notes</label>
                                <textarea class="form-control" id="exampleFormControlTextarea1" rows="3" name="notes"><?= $transaksi['deliv_notes'] ?></textarea>
                            </div>
                            <!-- <div class="form-group">
                                <label for="exampleFormControlInput1">Choose photo or take from camera</label>
                                <div class="custom-file">
                                    <input class="form-control-file" type="file" accept="image/*" id="file-input"  name="file-input">
                                </div>
                            </div> -->
                            <div class="btn-group special" role="group" aria-label="..." style="display: flex;">
                                <a role="button" class="btn btn-outline-dark" style="flex: 1" href="<?= base_url('dashboard/pending') ?>">Back</a>
                                <?php 
                                if($transaksi['received_at'] == null){
                                    echo '<button type="submit" class="btn btn-success" style="flex: 1">Submit</button>';
                                }
                                ?>
                                
                            </div>
                        </form>
                    </div>
                </div>

            </div>

        </div>
    </div><!-- .animated -->
</div>