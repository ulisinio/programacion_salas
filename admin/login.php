<?php require_once('../config.php') ?>
<!DOCTYPE html>
<html lang="en" class="" style="height: auto;">
  <?php require_once('inc/header.php') ?>
<body class="hold-transition login-page ">
  <script>
    start_loader()
  </script>
  <div class="container mb-4">
    <h2 class="text-center"><?php echo $_settings->info('name') ?></h2>
  </div>
<div class="login-box">
  <!-- /.login-logo -->
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <a href="./" class="h1"><b>Login</b></a>
    </div>
    <div class="card-body">
      <form id="login-frm" action="" method="post">
        <div class="input-group mb-3">
          <input type="text" class="form-control" name="username" placeholder="Usuario">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" name="password" id="password" placeholder="Contraseña">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-eye" id="toggle-password" style="cursor: pointer;"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-8">
            <!-- <a href="<?php echo base_url ?>">Go to Website</a> -->
          </div>
          <!-- /.col -->
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">Iniciar Sesión</button>
          </div>
          <!-- /.col -->
        </div>
      </form>
      <!-- /.social-auth-links -->
    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>

<script>
  $(document).ready(function(){
    end_loader();
    
    // Función para alternar la visibilidad de la contraseña
    $('#toggle-password').click(function() {
      var passwordField = $('#password');
      var type = passwordField.attr('type') === 'password' ? 'text' : 'password';
      passwordField.attr('type', type);

      // Alternar el icono entre ojo cerrado y abierto
      $(this).toggleClass('fas fa-eye fas fa-eye-slash');
    });
  })
</script>

</body>
</html>
