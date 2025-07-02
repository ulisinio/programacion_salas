<?php
if (isset($_GET['id']) && $_GET['id'] > 0) {
    $qry = $conn->query("SELECT * from `vehicles` where id = '{$_GET['id']}' ");
    if ($qry->num_rows > 0) {
        foreach ($qry->fetch_assoc() as $k => $v) {
            $$k = $v;
        }
    }
}
?>
<div class="card card-outline card-info">
    <div class="card-header">
        <h3 class="card-title"><?php echo isset($id) ? "Actualizar " : "Registrar Nuevo " ?>Vehículo:</h3>
    </div>
    <div class="card-body">
        <form action="" id="vehicle-form">
            <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">

            <div class="row">
                <div class="form-group col-md-6">
                    <label for="marca" class="control-label">Marca:</label>
                    <input type="text" name="marca" class="form-control" placeholder="Ingresa la marca" value="<?php echo isset($marca) ? $marca : '' ?>" required>
                </div>

                <div class="form-group col-md-6">
                    <label for="version" class="control-label">Versión:</label>
                    <input type="text" name="version" class="form-control" placeholder="Ingresa la versión" value="<?php echo isset($version) ? $version : '' ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6">
                    <label for="modelo" class="control-label">Modelo (Año):</label>
                    <input type="number" name="modelo" class="form-control" placeholder="Ej. 2024" min="1900" max="2099" value="<?php echo isset($modelo) ? $modelo : '' ?>" required>
                </div>

                <div class="form-group col-md-6">
                    <label for="no_serie" class="control-label">Número de Serie:</label>
                    <input type="text" name="no_serie" class="form-control" placeholder="Ingresa el número de serie" value="<?php echo isset($no_serie) ? $no_serie : '' ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6">
                    <label for="placas" class="control-label">Placas:</label>
                    <input type="text" name="placas" class="form-control" placeholder="Ingresa las placas" value="<?php echo isset($placas) ? $placas : '' ?>" required>
                </div>

                <div class="form-group col-md-6">
                    <label for="color" class="control-label">Color:</label>
                    <input type="text" name="color" class="form-control" placeholder="Ingresa el color" value="<?php echo isset($color) ? $color : '' ?>" required>
                </div>
            </div>

            <!-- Campo estado -->
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="estado" class="control-label">Estado del Vehículo:</label>
                    <select name="estado" class="form-control" required>
                        <option value="" disabled <?php echo !isset($estado) ? 'selected' : '' ?>>Selecciona el estado</option>
                        <option value="Activo" <?php echo (isset($estado) && $estado == 'Activo') ? 'selected' : '' ?>>Activo</option>
                        <option value="En mantenimiento" <?php echo (isset($estado) && $estado == 'En mantenimiento') ? 'selected' : '' ?>>En mantenimiento</option>
                    </select>
                </div>
            </div>

        </form>
    </div>
    <div class="card-footer">
        <button class="btn btn-flat btn-primary" form="vehicle-form">Guardar</button>
        <a class="btn btn-flat btn-default" href="?page=assembly_hall">Cancelar</a>
    </div>
</div>

<script>
$(document).ready(function(){
    $('#vehicle-form').submit(function(e){
        e.preventDefault();
        var _this = $(this);
        $('.err-msg').remove();
        start_loader();
        $.ajax({
            url: _base_url_ + "classes/Master.php?f=save_vehicle",
            data: new FormData(_this[0]),
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            dataType: 'json',
            error: err => {
                console.log(err);
                alert_toast("Ocurrió un error", 'error');
                end_loader();
            },
            success: function(resp){
                if(typeof resp === 'object' && resp.status === 'success'){
                    location.href = "./?page=assembly_hall";
                } else if(resp.status === 'failed' && !!resp.msg){
                    var el = $('<div>').addClass("alert alert-danger err-msg").text(resp.msg);
                    _this.prepend(el);
                    el.show('slow');
                    $("html, body").animate({ scrollTop: _this.closest('.card').offset().top }, "fast");
                    end_loader();
                } else {
                    alert_toast("Ocurrió un error", 'error');
                    end_loader();
                    console.log(resp);
                }
            }
        });
    });
});
</script>
