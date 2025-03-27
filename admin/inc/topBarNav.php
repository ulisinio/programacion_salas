<style>
  .user-img{
        position: absolute;
        height: 27px;
        width: 27px;
        object-fit: cover;
        left: -7%;
        top: -12%;
  }
  .btn-rounded{
        border-radius: 50px;
  }
</style>

<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-navy border border-light border-top-0  border-left-0 border-right-0 navbar-dark text-sm">
  <!-- Left navbar links -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="<?php echo base_url ?>" class="nav-link">
        <?php echo (!isMobileDevice()) ? $_settings->info('name') : $_settings->info('short_name'); ?> 
        <?php if ($_settings->userdata('role') == 1) { echo " - ADMIN"; } ?> <!-- Solo agrega " - ADMIN" si es administrador -->
      </a>
    </li>
  </ul>

  <!-- Right navbar links -->
  <ul class="navbar-nav ml-auto">
    <li class="nav-item">
      <div class="btn-group nav-link">
        <button type="button" class="btn btn-rounded badge badge-light dropdown-toggle dropdown-icon" data-toggle="dropdown">
          <span class="ml-3"><?php echo ucwords($_settings->userdata('firstname').' '.$_settings->userdata('lastname')) ?></span>
          <span class="sr-only">Toggle Dropdown</span>
        </button>
        <div class="dropdown-menu" role="menu" id="userDropdownMenu">
          <!-- Aquí se insertarán las opciones dependiendo del rol -->
          <a class="dropdown-item" href="<?php echo base_url.'/classes/Login.php?f=logout' ?>"><span class="fas fa-sign-out-alt"></span> Cerrar Sesión</a>
        </div>
      </div>
    </li>
  </ul>
</nav>
<!-- /.navbar -->

<script>
  // Asumimos que el rol se pasa desde PHP o se almacena en una variable de JavaScript.
  var userRole = <?php echo $_settings->userdata('role'); ?>; // 1 para administrador, 0 para usuario normal
  
  // Si el usuario es administrador, agregamos la opción "Mi Cuenta"
  if (userRole === 1) {
    var menu = document.getElementById('userDropdownMenu');
    var miCuentaItem = document.createElement('a');
    miCuentaItem.classList.add('dropdown-item');
    miCuentaItem.href = '<?php echo base_url.'admin/?page=user' ?>';
    miCuentaItem.innerHTML = '<span class="fa fa-user"></span> Mi Cuenta';
    menu.insertBefore(miCuentaItem, menu.firstChild);
  }
</script>
