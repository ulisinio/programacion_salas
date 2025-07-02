<?php if($_settings->chk_flashdata('success')): ?>
<script>
    alert_toast("<?php echo $_settings->flashdata('success') ?>",'success')
</script>
<?php endif; ?>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Administrar Salas y Vehículos</h3>
    </div>
    <div class="card-body">
        <!-- Pestañas -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <!-- Pestaña Salas -->
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="list-salas-tab" data-toggle="tab" href="#list-salas" role="tab" aria-controls="list-salas" aria-selected="true">Lista de Salas</a>
            </li>
            <!-- Pestaña Vehículos -->
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="list-vehicles-tab" data-toggle="tab" href="#list-vehicles" role="tab" aria-controls="list-vehicles" aria-selected="false">Lista de Vehículos</a>
            </li>
        </ul>

        <div class="tab-content mt-3" id="myTabContent">
            <!-- Pestaña de Lista de Salas -->
            <div class="tab-pane fade show active" id="list-salas" role="tabpanel" aria-labelledby="list-salas-tab">
                <div class="text-right mb-2">
                    <a href="?page=assembly_hall/manage_assembly" class="btn btn-flat btn-primary">
                        <span class="fas fa-plus"></span> Añadir Sala
                    </a>
                </div>
                <table class="table table-bordered table-striped">
                    <colgroup>
                        <col width="5%">
                        <col width="20%">
                        <col width="20%">
                        <col width="25%">
                        <col width="10%">
                        <col width="10%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Ubicación</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
                        $qry = $conn->query("SELECT * FROM `assembly_hall` ORDER BY `room_name` ASC");
                        while($row = $qry->fetch_assoc()):
                        ?>
                        <tr>
                            <td class="text-center"><?php echo $i++; ?></td>
                            <td><?php echo $row['room_name'] ?></td>
                            <td><?php echo $row['location'] ?></td>
                            <td><?php echo $row['description'] ?></td>
                            <td class="text-center">
                                <?php 
                                switch($row['status']):
                                    case '1':
                                        echo '<span class="badge badge-success">Activa</span>';
                                        break;
                                    case '0':
                                        echo '<span class="badge badge-danger">Inactiva</span>';
                                        break;
                                    default:
                                        echo '<span class="badge badge-secondary">Desconocido</span>';
                                        break;
                                endswitch;
                                ?>
                            </td>
<td align="center">
    <a href="?page=assembly_hall/manage_assembly&id=<?php echo $row['id'] ?>" class="text-primary mr-2" title="Editar">
        <i class="fa fa-edit"></i>
    </a>
    <a href="javascript:void(0)" class="text-danger delete_data" data-id="<?php echo $row['id'] ?>" title="Eliminar">
        <i class="fa fa-trash"></i>
    </a>
</td>

                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

<!-- Pestaña de Lista de Vehículos -->
<div class="tab-pane fade" id="list-vehicles" role="tabpanel" aria-labelledby="list-vehicles-tab">
    <div class="text-right mb-2">
        <a href="?page=assembly_hall/manage_vehicles" class="btn btn-flat btn-primary">
            <span class="fas fa-plus"></span> Añadir Vehículo
        </a>
    </div>
    <table class="table table-bordered table-striped">
        <colgroup>
    <col width="5%">
    <col width="12%">
    <col width="12%">
    <col width="10%">
    <col width="15%">
    <col width="10%">
    <col width="10%">
    <col width="13%">
    <col width="13%">
        </colgroup>
        <thead>
            <tr>
                <th>#</th>
                <th>Marca</th>
                <th>Versión</th>
                <th>Modelo</th>
                <th>No. Serie</th>
                <th>Placas</th>
                <th>Color</th>
                <th>Estado</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $i = 1;
            $qry = $conn->query("SELECT * FROM `vehicles` ORDER BY `marca` ASC");
            while($row = $qry->fetch_assoc()):
            ?>
            <tr>
                <td class="text-center"><?php echo $i++; ?></td>
                <td><?php echo $row['marca'] ?></td>
                <td><?php echo $row['version'] ?></td>
                <td><?php echo $row['modelo'] ?></td>
                <td><?php echo $row['no_serie'] ?></td>
                <td><?php echo $row['placas'] ?></td>
                <td><?php echo $row['color'] ?></td>
                <td class="text-center">
    <?php 
    switch($row['estado']):
        case 'Activo':
            echo '<span class="badge badge-success">Activo</span>';
            break;
        case 'En mantenimiento':
            echo '<span class="badge badge-warning">En mantenimiento</span>';
            break;
        default:
            echo '<span class="badge badge-secondary">Desconocido</span>';
            break;
    endswitch;
    ?>
</td>

<td align="center">
    <a href="?page=assembly_hall/manage_vehicles&id=<?php echo $row['id'] ?>" class="text-primary mr-2" title="Editar">
        <i class="fa fa-edit"></i>
    </a>
    <a href="javascript:void(0)" class="text-danger delete_data_vehicle" data-id="<?php echo $row['id'] ?>" title="Eliminar">
        <i class="fa fa-trash"></i>
    </a>
</td>

            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Script para eliminar y DataTable -->
<script>
$(document).ready(function(){
    $('.delete_data').click(function(){
        _conf("¿Estás seguro de que deseas eliminar esta sala?\n\nEsto eliminará todas las reservas asociadas.", "delete_assembly_hall", [$(this).attr('data-id')]);
    });
    $('.delete_data_vehicle').click(function(){
        _conf("¿Estás seguro de que deseas eliminar este vehículo?", "delete_vehicle", [$(this).attr('data-id')]);
    });
    $('.table').dataTable();
});

function delete_assembly_hall(id){

    start_loader();
    $.ajax({
        url: _base_url_ + "classes/Master.php?f=delete_assembly_hall",
        method: "POST",
        data: {id: id},
        dataType: "json",
        error: function(err) {
            console.log(err);
            alert_toast("No se pudo eliminar la sala.", 'error');
            end_loader();
        },
        success: function(resp) {
            if(resp.status == 'success'){
                alert_toast("Sala eliminada exitosamente.", 'success');
                location.reload();
            } else {
                alert_toast(resp.msg || "Error al eliminar.", 'error');
                end_loader();
            }
        }
    });
}


function delete_vehicle(id){
    start_loader();
    $.ajax({
        url: _base_url_ + "classes/Master.php?f=delete_vehicle",
        method: "POST",
        data: {id: id},
        dataType: "json",
        error: function(err) {
            console.log(err);
            alert_toast("No se pudo eliminar el vehículo.", 'error');
            end_loader();
        },
        success: function(resp) {
            if(resp.status == 'success'){
                alert_toast("Vehículo eliminado exitosamente.", 'success');
                location.reload();
            } else {
                alert_toast(resp.msg || "Error al eliminar.", 'error');
                end_loader();
            }
        }
    });
}
</script>
<script>
$(document).ready(function(){
    // Leer el parámetro 'tab' de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');

    if(tab === 'vehicles'){
        // Cambiar pestaña a "Lista de Vehículos"
        $('#list-salas-tab').removeClass('active');
        $('#list-salas').removeClass('show active');

        $('#list-vehicles-tab').addClass('active');
        $('#list-vehicles').addClass('show active');
    } else {
        // Por defecto mostrar "Lista de Salas" (opcional)
        $('#list-salas-tab').addClass('active');
        $('#list-salas').addClass('show active');

        $('#list-vehicles-tab').removeClass('active');
        $('#list-vehicles').removeClass('show active');
    }
});
</script>
