<ul class="nav navbar-nav text-white">
    <h3 class="menu-title text-center text-white"><?= $this->session->userdata('role_name'); ?></h3>
    <li class="color: white !important">
        <a style="color: white !important; text-decoration: none;" href="<?= base_url('transactions') ?>" > <i class="menu-icon ti-shopping-cart-full text-white"></i>Transactions </a>
    </li>

    <li>
        <a style="color: white !important; text-decoration: none;" href="<?= base_url('deliveries') ?>"> <i class="menu-icon ti-truck text-white"></i>Deliveries </a>
    </li>

    <li>
        <a style="color: white !important; text-decoration: none;" href="<?= base_url('products') ?>"> <i class="menu-icon ti-bag text-white"></i>Products </a>
    </li>

    <li>
        <a style="color: white !important; text-decoration: none;" href="<?= base_url('stocks') ?>"> <i class="menu-icon ti-check-box text-white"></i>Stocks </a>
    </li>

    <li>
        <a style="color: white !important; text-decoration: none;" href="<?= base_url('users') ?>"> <i class="menu-icon ti-user text-white"></i>Users </a>
    </li>

    <!-- <li>
        <a href="<?= base_url('users') ?>"> <i class="menu-icon ti-user"></i>Users </a>
    </li> -->

    <li>
        <a style="color: white !important; text-decoration: none;" href="<?= base_url('users/changepassword') ?>"> <i class="menu-icon ti-key text-white"></i>Change Password </a>
    </li>

    <li>
        <a style="color: white !important; text-decoration: none;" href="<?= base_url('login/logout') ?>" class="text-danger"> <i class="menu-icon ti-arrow-circle-left text-white"></i>Logout </a>
    </li>
</ul>