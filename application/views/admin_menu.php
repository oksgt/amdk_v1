<ul class="nav navbar-nav">
    <h3 class="menu-title text-center"><?= $this->session->userdata('role_name'); ?></h3>
    <li>
        <a href="<?= base_url('transactions') ?>"> <i class="menu-icon ti-shopping-cart-full"></i>Transactions </a>
    </li>

    <!-- <li>
        <a href="<?= base_url('delivery') ?>"> <i class="menu-icon ti-truck"></i>Delivery </a>
    </li> -->

    <li>
        <a href="<?= base_url('products') ?>"> <i class="menu-icon ti-bag"></i>Products </a>
    </li>

    <li>
        <a href="<?= base_url('stocks') ?>"> <i class="menu-icon ti-check-box"></i>Stocks </a>
    </li>

    <li>
        <a href="<?= base_url('users') ?>"> <i class="menu-icon ti-user"></i>Users </a>
    </li>

    <!-- <li>
        <a href="<?= base_url('users') ?>"> <i class="menu-icon ti-user"></i>Users </a>
    </li> -->

    <li>
        <a href="<?= base_url('users/changepassword') ?>"> <i class="menu-icon ti-key"></i>Change Password </a>
    </li>

    <li>
        <a href="<?= base_url('login/logout') ?>" class="text-danger"> <i class="menu-icon ti-arrow-circle-left"></i>Logout </a>
    </li>
</ul>