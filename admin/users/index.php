<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Lista de Usuarios</h3>
        <div class="card-tools">
            <a href="?page=users/manage_user" class="btn btn-flat btn-primary">
                <span class="fas fa-plus"></span> Crear Nuevo
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="container-fluid">

            <?php if (isset($_settings) && $_settings->chk_flashdata('success')): ?>
                <script>
                    $(function(){
                        alert_toast("<?= $_settings->flashdata('success') ?>", 'success');
                    });
                </script>
            <?php endif; ?>

            <table class="table table-bordered table-striped">
                <colgroup>
                    <col width="5%">
                    <col width="10%"> <!-- reducido -->
                    <col width="10%"> <!-- reducido -->
                    <col width="15%">
                    <col width="10%">
                    <col width="15%">
                    <col width="15%">
                    <col width="10%">
                </colgroup>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Creado</th>
                        <th>Última Modificación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $stmt = $conn->prepare("SELECT * FROM users ORDER BY id ASC");
                    $stmt->execute();
                    $qry = $stmt->get_result();
                    while ($row = $qry->fetch_assoc()):
                    ?>
                        <tr>
                            <td class="text-center"><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['firstname']); ?></td>
                            <td><?php echo htmlspecialchars($row['lastname']); ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td class="text-center">
                                <?php if ($row['role'] == 1): ?>
                                    <span class="badge badge-primary">Administrador</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Usuario</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php
                                    $created_at = date("d/m/Y h:i A", strtotime($row['created_at']));
                                    echo htmlspecialchars($created_at);
                                ?>
                            </td>
                            <td class="text-center">
                                <?php
                                    $updated_at = date("d/m/Y h:i A", strtotime($row['updated_at']));
                                    echo htmlspecialchars($updated_at);
                                ?>
                            </td>
                            <td class="text-center">
                                <a href="?page=users/manage_user&id=<?php echo htmlspecialchars($row['id']); ?>" class="text-primary mx-1" title="Editar">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <?php if ($row['id'] != 1): ?>
                                    <a href="javascript:void(0)" class="text-danger mx-1 delete_data" data-id="<?php echo htmlspecialchars($row['id']); ?>" title="Eliminar">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <i class="fa fa-trash text-muted mx-1" title="Eliminar (deshabilitado)"></i>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    $('.delete_data').click(function(){
        var userId = $(this).attr('data-id');
        if (isNaN(userId) || userId <= 0) {
            alert("ID de usuario no válido.");
            return;
        }
        _conf("Esta acción eliminará al usuario de forma permanente. ¿Deseas continuar?", "delete_user", [userId]);
    });
});

function delete_user(id) {
    start_loader();
    $.ajax({
        url: _base_url_ + "classes/usuaritos.php?f=delete_user",
        method: "POST",
        data: {id: id},
        dataType: "json",
        error: function(err) {
            console.log(err);
            alert_toast("Ocurrió un error.", 'error');
            end_loader();
        },
        success: function(resp) {
            if (resp.status === 'success') {
                location.reload();
            } else {
                alert_toast(resp.message || "Ocurrió un error.", 'error');
                end_loader();
            }
        }
    });
}
</script>
