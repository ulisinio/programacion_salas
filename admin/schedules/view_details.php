<?php
require_once('../../config.php');

// Establecer el locale a español para que las fechas se muestren en español
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');

// Si el ID de la reserva está presente, obtenemos los detalles
if(isset($_GET['id']) && $_GET['id'] > 0){
    $qry = $conn->query("SELECT s.*,a.room_name FROM `schedule_list` s inner join assembly_hall a on a.id = s.assembly_hall_id where s.id = '{$_GET['id']}' ");
    if($qry->num_rows > 0){
        foreach($qry->fetch_assoc() as $k => $v){
            $$k=$v;
        }
    }
}

// Calcular la duración en horas y minutos
$start_time = strtotime($datetime_start);
$end_time = strtotime($datetime_end);
$duration_seconds = $end_time - $start_time;
$duration_hours = floor($duration_seconds / 3600);
$duration_minutes = floor(($duration_seconds % 3600) / 60);
$duration = $duration_hours . " horas " . $duration_minutes . " minutos";
?>

<style>
#uni_modal .modal-content>.modal-footer{
    display:none;
}
#uni_modal .modal-body{
    padding:0 !important;
}
</style>

<div class="container-fluid p-2">
    <p><b>Nombre de la sala:</b> <?php echo $room_name ?></p>
    <p><b>Reservado para:</b> <?php echo ucwords($reserved_by) ?></p>
    <p><b>Fecha y hora de inicio:</b> <?php echo strftime("%d de %B de %Y, %H:%M", strtotime($datetime_start)) ?></p>
    <p><b>Fecha y hora de fin:</b> <?php echo strftime("%d de %B de %Y, %H:%M", strtotime($datetime_end)) ?></p>
    <p><b>Duración:</b> <?php echo $duration ?></p> <!-- Mostrar la duración calculada -->
    <p><b>Observaciones:</b><br> <span><?php echo $schedule_remarks ?></span></p>
</div>

<div class="modal-footer">
    <button type="button" id="update" class="btn btn-primary btn-flat" data-id="<?php echo $_GET['id'] ?>">Editar</button>
    <button type="button" id="delete" class="btn btn-danger btn-flat" data-id="<?php echo $_GET['id'] ?>">Eliminar</button>
    <button type="button" class="btn btn-secondary btn-flat" data-dismiss="modal">Cerrar</button>
</div>

<script>
    $(function(){
        $('#update').click(function(){
            uni_modal("Editar Reserva","schedules/edit_schedule.php?id=<?php echo $_GET['id'] ?>")
        })
        $('#delete').click(function(){
            _conf("Esta acción eliminará la reserva de forma permanente. ¿Deseas continuar?","delete_sched",[$(this).attr('data-id')])
        })
    })

    function delete_sched($id){
        start_loader();
        $.ajax({
            url:_base_url_+"classes/Master.php?f=delete_sched",
            method:"POST",
            data:{id: $id},
            dataType:"json",
            error:err=>{
                console.log(err)
                alert_toast("Ha ocurrido un error.",'error');
                end_loader();
            },
            success:function(resp){
                if(typeof resp == 'object' && resp.status == 'success'){
                    location.reload();
                }else{
                    alert_toast("Ha ocurrido un error.",'error');
                    end_loader();
                }
            }
        })
    }
</script>
