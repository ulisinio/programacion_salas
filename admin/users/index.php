<div class="card card-outline card-primary">
	<div class="card-header">
		<h3 class="card-title">Lista de Usuarios</h3>
		<div class="card-tools">
			<a href="?page=users/manage_user" class="btn btn-flat btn-primary"><span class="fas fa-plus"></span> Crear Nuevo</a>
		</div>
	</div>
	<div class="card-body">
		<div class="container-fluid">
			<table class="table table-bordered table-striped">
				<colgroup>
					<col width="5%">
					<col width="25%">
					<col width="25%">
					<col width="25%">
					<col width="10%">
					<col width="10%">
				</colgroup>
				<thead>
					<tr>
						<th>ID</th>
						<th>Nombre</th>
						<th>Apellido</th>
						<th>Usuario</th>
						<th>Rol</th>
						<th>Acción</th>
					</tr>
				</thead>
				<tbody>
					<?php 
					$i = 1; // Variable para enumerar
					// Obtener los usuarios ordenados por ID
					$qry = $conn->query("SELECT * from `users` order by `id` asc ");
					while($row = $qry->fetch_assoc()):
					?>
						<tr>
							<td class="text-center"><?php echo $row['id']; ?></td> <!-- Mostrar ID de la base de datos -->
							<td><?php echo $row['firstname']; ?></td>
							<td><?php echo $row['lastname']; ?></td>
							<td><?php echo $row['username']; ?></td>
							<td class="text-center">
								<?php 
								// Mostrar el rol como "Usuario" o "Administrador"
								if($row['role'] == 1):
									echo '<span class="badge badge-primary">Administrador</span>';
								else:
									echo '<span class="badge badge-secondary">Usuario</span>';
								endif;
								?>
							</td>
							<td class="text-center">
								<!-- Botón de acción con un menú desplegable -->
								<button type="button" class="btn btn-flat btn-default btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown">
									Acción
								</button>
								<div class="dropdown-menu" role="menu">
									<!-- Enlace para editar -->
									<a class="dropdown-item" href="?page=users/manage_user&id=<?php echo $row['id'] ?>"><span class="fa fa-edit text-primary"></span> Editar</a>
									<div class="dropdown-divider"></div>
									<!-- Enlace para eliminar -->
									<a class="dropdown-item delete_data" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>"><span class="fa fa-trash text-danger"></span> Eliminar</a>
								</div>
							</td>
						</tr>
					<?php endwhile; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<!-- Script para eliminar un usuario -->
<script>
$(document).ready(function(){
    $('.delete_data').click(function(){
        // Eliminar la confirmación estándar, solo se llama a _conf()
        _conf("¿Estás seguro de eliminar este usuario?", "delete_user", [$(this).attr('data-id')]);
    });
});
	function delete_user($id){
		start_loader();
		$.ajax({
			url: _base_url_ + "classes/usuaritos.php?f=delete_user",
			method: "POST",
			data: {id: $id},
			dataType: "json",
			error: function(err){
				console.log(err);
				alert_toast("Ocurrió un error.", 'error');
				end_loader();
			},
			success: function(resp){
				if (typeof resp == 'object' && resp.status == 'success') {
					location.reload();
				} else {
					alert_toast("Ocurrió un error.", 'error');
					end_loader();
				}
			}
		});
	}
</script>
