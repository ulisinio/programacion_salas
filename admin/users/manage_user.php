<?php
// Si existe un ID en la URL, se obtiene el usuario para actualizarlo
if(isset($_GET['id']) && $_GET['id'] > 0){
    $qry = $conn->query("SELECT * from `users` where id = '{$_GET['id']}' ");
    if($qry->num_rows > 0){
        foreach($qry->fetch_assoc() as $k => $v){
            $$k = $v;
        }
    }
}
?>
<div class="card card-outline card-info">
    <div class="card-header">
        <h3 class="card-title"><?php echo isset($id) ? "Actualizar " : "Crear Nuevo " ?> Usuario</h3>
    </div>
    <div class="card-body">
        <?php if (isset($id)) { // Solo mostrar la alerta si estamos editando un usuario ?>
            <!-- Alerta de información para la contraseña -->
            <div class="alert alert-info" role="alert">
                Si no deseas cambiar la contraseña, deja el campo vacío. 
                <i class="fas fa-exclamation-circle"></i> <!-- Icono de alerta -->
            </div>
        <?php } ?>

        <form action="" id="user-form">
            <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
            <div class="form-group">
                <label for="firstname" class="control-label">Nombre</label>
                <input type="text" name="firstname" id="firstname" class="form-control" value="<?php echo isset($firstname) ? $firstname : ''; ?>" required />
            </div>

            <div class="form-group">
                <label for="lastname" class="control-label">Apellido</label>
                <input type="text" name="lastname" id="lastname" class="form-control" value="<?php echo isset($lastname) ? $lastname : ''; ?>" required />
            </div>

            <div class="form-group">
                <label for="username" class="control-label">Usuario</label>
                <input type="text" name="username" id="username" class="form-control" value="<?php echo isset($username) ? $username : ''; ?>" required />
            </div>

            <div class="form-group">
                <label for="password" class="control-label">Contraseña</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control" value="" /> <!-- Contraseña vacía por defecto -->
                    <div class="input-group-append">
                        <span class="input-group-text" id="show-password">
                            <i class="fas fa-eye"></i> <!-- Icono de ojo para mostrar/ocultar -->
                        </span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="role" class="control-label">Rol</label>
                <select name="role" id="role" class="custom-select" required>
                    <option value="0" <?php echo isset($role) && $role == 0 ? "selected" : ""; ?>>Usuario</option>
                    <option value="1" <?php echo isset($role) && $role == 1 ? "selected" : ""; ?>>Administrador</option>
                </select>
            </div>
        </form>
    </div>
    <div class="card-footer">
        <button class="btn btn-flat btn-primary" form="user-form">Guardar</button>
        <a class="btn btn-flat btn-default" href="?page=users">Cancelar</a>
    </div>
</div>

<script>
    // Script para manejar el envío del formulario
    $(document).ready(function(){
        $('#user-form').submit(function(e){
            e.preventDefault();
            var _this = $(this);
            $('.err-msg').remove();  // Limpiar mensajes de error previos
            start_loader();  // Inicia el loader

            // Verificamos si el campo de contraseña está vacío
            var password = $('#password').val();
            if(password.trim() == "") {
                // Si la contraseña está vacía, no la incluimos en el envío del formulario
                // Lo que hacemos es eliminar el campo de la contraseña antes de enviar
                $("input[name='password']").val("");
            }

            $.ajax({
                url: _base_url_ + "classes/usuaritos.php?f=save_user",  // Ruta para guardar el usuario
                data: new FormData($(this)[0]),
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                dataType: 'json',
                error: function(err) {
                    console.log(err);  // Si ocurre un error en la llamada AJAX
                    alert_toast("Ocurrió un error", 'error');
                    end_loader();  // Finaliza el loader
                },
                success: function(resp) {
                    if (typeof resp == 'object' && resp.status == 'success') {
                        location.href = "?page=users";  // Redirigir a la lista de usuarios después de guardar
                    } else if (resp.status == 'failed' && !!resp.msg) {
                        var el = $('<div>');
                        el.addClass("alert alert-danger err-msg").text(resp.msg);
                        _this.prepend(el);
                        el.show('slow');
                        $("html, body").animate({ scrollTop: _this.closest('.card').offset().top }, "fast");
                        end_loader();  // Finaliza el loader
                    } else {
                        alert_toast("Ocurrió un error", 'error');
                        end_loader();  // Finaliza el loader
                        console.log(resp);
                    }
                }
            });
        });

        // Mostrar/ocultar la contraseña
        $('#show-password').click(function(){
            var passwordField = $('#password');
            var type = passwordField.attr('type');
            if(type == 'password') {
                passwordField.attr('type', 'text');
                $(this).find('i').removeClass('fas fa-eye').addClass('fas fa-eye-slash');
            } else {
                passwordField.attr('type', 'password');
                $(this).find('i').removeClass('fas fa-eye-slash').addClass('fas fa-eye');
            }
        });
    });
</script>
