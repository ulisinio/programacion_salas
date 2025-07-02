<?php if($_settings->chk_flashdata('success')): ?>
<script>
    $(function(){
        showModalAlert("<?php echo $_settings->flashdata('success') ?>", "Éxito");
    });
</script>
<?php endif; ?>

<!-- Tippy.js -->
<link rel="stylesheet" href="https://unpkg.com/tippy.js@6/dist/tippy.css" />
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>

<style>
#calendar {
    width: 100%;
    height: 550px;
      font-size: 0.8rem; /* tamaño de texto más pequeño */
}
.filters {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}
</style>

<div class="card card-outline card-primary">

    <div class="card-body">
        <div class="container-fluid">
            <div class="filters">
                <select id="buildingFilter" class="form-control">
                    <option value="">Todos los edificios</option>
                    <option value="Corporativo">Corporativo</option>
                    <option value="Showroom">Showroom</option>
                </select>
                <select id="roomFilter" class="form-control">
                    <option value="">Todas las salas</option>
                    <?php 
                    $hall_qry = $conn->query("SELECT * FROM `assembly_hall` where status =1 order by `room_name` asc");
                    while($row = $hall_qry->fetch_assoc()):
                    ?>
                        <option value="<?php echo $row['id'] ?>"><?php echo $row['room_name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para formulario de reserva -->
<div class="modal fade" id="reservationModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="reservationForm">
      <input type="hidden" name="id" id="schedule_id" value="">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Nueva Reserva</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="assembly_hall_id">Sala</label>
            <select name="assembly_hall_id" id="assembly_hall_id" class="form-control select2" required>
                <option value=""></option>
                <?php 
                $hall_qry = $conn->query("SELECT * FROM `assembly_hall` where status =1 order by `room_name` asc");
                while($row = $hall_qry->fetch_assoc()):
                ?>
                    <option value="<?php echo $row['id'] ?>"><?php echo $row['room_name'] ?></option>
                <?php endwhile; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="reserved_by">Reserva a Nombre de:</label>
            <input type="text" class="form-control" name="reserved_by" id="reserved_by" required>
          </div>
          <div class="form-group">
            <label for="datetime_start">Fecha y Hora de inicio:</label>
            <input type="datetime-local" class="form-control" name="datetime_start" id="datetime_start" required>
          </div>
          <div class="form-group">
            <label for="datetime_end">Fecha y Hora de fin:</label>
            <input type="datetime-local" class="form-control" name="datetime_end" id="datetime_end" required>
          </div>
          <div class="form-group">
            <label for="schedule_remarks">Observaciones:</label>
            <textarea rows="3" class="form-control" name="schedule_remarks" id="schedule_remarks"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <button type="button" class="btn btn-danger" id="deleteReservationBtn" style="display:none;">Eliminar Reserva</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal de alerta personalizada -->
<div class="modal fade" id="modalAlert" tabindex="-1" role="dialog" aria-labelledby="modalAlertTitle" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalAlertTitle">Aviso</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center" id="modalAlertBody"></div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Confirmar Eliminación</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        ¿Estás seguro de querer eliminar esta reserva?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Eliminar</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
      </div>
    </div>
  </div>
</div>

<?php
$sched_qry = $conn->query("SELECT s.*, a.room_name, a.location FROM `schedule_list` s inner join assembly_hall a on a.id = s.assembly_hall_id");
$sched_data = array();
while($row = $sched_qry->fetch_assoc()):
    $row = array_map(function($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }, $row);
    $sched_data[] = $row;
endwhile;
$sched = json_encode($sched_data);
$sched = addslashes($sched);
?>

<script>
var scheds = $.parseJSON('<?php echo $sched ?>');

function showModalAlert(message, title = 'Aviso') {
    $('#modalAlertTitle').text(title);
    $('#modalAlertBody').html(message);
    $('#modalAlert').modal('show');
}

$(function(){
    $('.select2').select2({placeholder: "Selecciona una opción"});

    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        locale: 'es',
        themeSystem: 'bootstrap',
        selectable: true,
        slotMinTime: "08:00:00",
        slotMaxTime: "20:00:00",
        selectAllow: function(selectInfo) {
            var start = selectInfo.start.getTime();
            var end = selectInfo.end.getTime();
            var room = $('#roomFilter').val();
            if (!room) return false;
            for(var i=0; i < scheds.length; i++){
                if(scheds[i].assembly_hall_id !== room) continue;
                var evStart = new Date(scheds[i].datetime_start).getTime();
                var evEnd = new Date(scheds[i].datetime_end).getTime();
                if(start < evEnd && end > evStart){
                    return false;
                }
            }
            return true;
        },
        select: function(info) {
            if (!$('#roomFilter').val()) {
                showModalAlert("Debes seleccionar una sala antes de reservar.", "Atención");
                calendar.unselect();
                return;
            }
            resetForm();
            $('#modalTitle').text('Nueva Reserva');
            $('#deleteReservationBtn').hide();
            $('#datetime_start').val(moment(info.start).format('YYYY-MM-DDTHH:mm'));
            $('#datetime_end').val(moment(info.end).format('YYYY-MM-DDTHH:mm'));
            $('#assembly_hall_id').val($('#roomFilter').val()).trigger('change');
            $('#reservationModal').modal('show');
        },
        eventClick: function(info) {
            resetForm();
            var ev = scheds.find(e => e.id == info.event.id);
            if(ev){
                $('#modalTitle').text('Editar Reserva');
                $('#schedule_id').val(ev.id);
                $('#assembly_hall_id').val(ev.assembly_hall_id).trigger('change');
                $('#reserved_by').val(ev.reserved_by);
                $('#datetime_start').val(moment(ev.datetime_start).format('YYYY-MM-DDTHH:mm'));
                $('#datetime_end').val(moment(ev.datetime_end).format('YYYY-MM-DDTHH:mm'));
                $('#schedule_remarks').val(ev.schedule_remarks);
                $('#deleteReservationBtn').show();
                $('#reservationModal').modal('show');
            }
        },
        eventDidMount: function(info) {
            tippy(info.el, {
                content: `
                    <strong>${info.event.title}</strong><br>
                    Inicio: ${moment(info.event.start).format('DD/MM/YYYY HH:mm')}<br>
                    Fin: ${moment(info.event.end).format('DD/MM/YYYY HH:mm')}<br>
                    Observaciones: ${info.event.extendedProps.remarks || 'N/A'}
                `,
                allowHTML: true,
                placement: 'top',
                theme: 'light-border',
            });
        },
        events: function(fetchInfo, successCallback) {
            var events = scheds.filter(ev => {
                var building = $('#buildingFilter').val();
                var room = $('#roomFilter').val();
                return (!building || ev.location === building) && (!room || ev.assembly_hall_id === room);
            }).map(ev => {
                return {
                    id: ev.id,
                    title: ev.room_name + ' - ' + ev.reserved_by,
                    start: ev.datetime_start,
                    end: ev.datetime_end,
                    backgroundColor: '#007bff',
                    borderColor: '#007bff',
                    extendedProps: {
                        remarks: ev.schedule_remarks
                    }
                };
            });
            successCallback(events);
        }
    });

    calendar.render();

    $('#buildingFilter').on('change', function() {
        var selectedBuilding = $(this).val();
        $('#roomFilter').html('<option value="">Todas las salas</option>');

        <?php 
        $hall_qry = $conn->query("SELECT * FROM `assembly_hall` where status =1 order by `room_name` asc");
        $halls = [];
        while($row = $hall_qry->fetch_assoc()):
            $halls[] = $row;
        endwhile;
        ?>
        var allRooms = <?php echo json_encode($halls) ?>;

        allRooms.forEach(function(hall) {
            if (!selectedBuilding || hall.location === selectedBuilding) {
                $('#roomFilter').append(`<option value="${hall.id}">${hall.room_name}</option>`);
            }
        });

        calendar.refetchEvents();
    });

    $('#roomFilter').on('change', function() {
        calendar.refetchEvents();
    });

    $('#reservationForm').on('submit', function(e){
        e.preventDefault();
        start_loader();
        $.ajax({
            url: _base_url_ + 'classes/Master.php?f=save_schedule',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(resp){
                if(resp.status === 'success'){
                    $('#reservationModal').modal('hide');
                    showModalAlert("Reserva guardada correctamente", "Éxito");
                    location.reload();
                } else {
                    showModalAlert("Error al guardar", "Error");
                    location.reload();
                }
                end_loader();
            },
            error: function(err){
                console.log(err);
                showModalAlert("Error de conexión", "Error");
                location.reload();
                end_loader();
            }
        });
    });

    $('#deleteReservationBtn').click(function(){
        $('#confirmDeleteModal').modal('show');
    });

    $('#confirmDeleteBtn').click(function(){
        start_loader();
        $.ajax({
            url: _base_url_ + 'classes/Master.php?f=delete_sched',
            method: 'POST',
            data: {id: $('#schedule_id').val()},
            dataType: 'json',
            success: function(resp){
                if(resp.status === 'success'){
                    $('#reservationModal').modal('hide');
                    $('#confirmDeleteModal').modal('hide');
                    showModalAlert("Reserva eliminada correctamente", "Eliminado");
                    location.reload();
                } else {
                    showModalAlert("Error al eliminar la reserva", "Error");
                }
                end_loader();
            },
            error: function(){
                showModalAlert("Error de conexión", "Error");
                end_loader();
            }
        });
    });

    function resetForm(){
        $('#reservationForm')[0].reset();
        $('#schedule_id').val('');
        $('#assembly_hall_id').val('').trigger('change');
    }
});
</script>
