<?php
if($_settings->chk_flashdata('success')): ?>
<script>
    $(function(){
        showModalAlert("<?php echo $_settings->flashdata('success') ?>", "Éxito");
    });
</script>
<?php endif; ?>

<style>
#calendar {
    width: 100%;
    height: 550px;
}
</style>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Reservar Vehículo</h3>
    </div>
    <div class="card-body">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="reservationModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <form id="reservationForm">
      <input type="hidden" name="id" id="schedule_id">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Nueva Reserva</h5>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="vehicle_id">Vehículo</label>
            <select name="vehicle_id" id="vehicle_id" class="form-control select2" required>
              <option value=""></option>
              <?php 
              $veh_qry = $conn->query("SELECT * FROM vehicles WHERE estado = 'Activo' ORDER BY marca ASC");
              while($row = $veh_qry->fetch_assoc()): ?>
                <option value="<?php echo $row['id'] ?>">
                  <?php echo $row['marca'] . ' - ' . $row['placas'] ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="reserved_by">Reservado por:</label>
            <input type="text" name="reserved_by" id="reserved_by" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="datetime_start">Inicio:</label>
            <input type="datetime-local" name="datetime_start" id="datetime_start" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="datetime_end">Fin:</label>
            <input type="datetime-local" name="datetime_end" id="datetime_end" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="reservation_notes">Observaciones:</label>
            <textarea name="reservation_notes" id="reservation_notes" rows="3" class="form-control"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <button type="button" class="btn btn-danger" id="deleteReservationBtn" style="display:none;">Eliminar</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<?php
$reservas_qry = $conn->query("SELECT r.*, v.marca, v.placas FROM vehicle_reservations r INNER JOIN vehicles v ON v.id = r.vehicle_id");
$reservas = array();
while($row = $reservas_qry->fetch_assoc()) {
    $row = array_map(function($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }, $row);
    $reservas[] = $row;
}
$reservas_json = addslashes(json_encode($reservas));
?>

<script>
var reservas = $.parseJSON('<?php echo $reservas_json ?>');

$(function(){
    $('.select2').select2({placeholder: "Selecciona un vehículo"});

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
        select: function(info) {
            resetForm();
            $('#datetime_start').val(moment(info.start).format('YYYY-MM-DDTHH:mm'));
            $('#datetime_end').val(moment(info.end).format('YYYY-MM-DDTHH:mm'));
            $('#reservationModal').modal('show');
        },
        eventClick: function(info) {
            var res = reservas.find(r => r.id == info.event.id);
            if(res){
                resetForm();
                $('#schedule_id').val(res.id);
                $('#vehicle_id').val(res.vehicle_id).trigger('change');
                $('#reserved_by').val(res.reserved_by);
                $('#datetime_start').val(moment(res.datetime_start).format('YYYY-MM-DDTHH:mm'));
                $('#datetime_end').val(moment(res.datetime_end).format('YYYY-MM-DDTHH:mm'));
                $('#reservation_notes').val(res.reservation_notes);
                $('#deleteReservationBtn').show();
                $('#reservationModal').modal('show');
            }
        },
        events: reservas.map(res => ({
            id: res.id,
            title: res.marca + ' - ' + res.placas + ' / ' + res.reserved_by,
            start: res.datetime_start,
            end: res.datetime_end,
            backgroundColor: '#28a745',
            borderColor: '#28a745'
        }))
    });

    calendar.render();

    $('#reservationForm').on('submit', function(e){
        e.preventDefault();
        start_loader();
        $.ajax({
            url: _base_url_ + 'classes/Master.php?f=save_vehicle_reservation',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(resp){
                if(resp.status === 'success'){
                    location.reload();
                } else {
                    showModalAlert("Error al guardar la reserva", "Error");
                }
                end_loader();
            },
            error: function(err){
                console.log(err);
                showModalAlert("Error de conexión", "Error");
                end_loader();
            }
        });
    });

    $('#deleteReservationBtn').on('click', function(){
        if(confirm("¿Eliminar esta reserva?")){
            start_loader();
            $.ajax({
                url: _base_url_ + 'classes/Master.php?f=delete_vehicle_reservation',
                method: 'POST',
                data: {id: $('#schedule_id').val()},
                dataType: 'json',
                success: function(resp){
                    if(resp.status === 'success'){
                        location.reload();
                    } else {
                        showModalAlert("Error al eliminar", "Error");
                    }
                    end_loader();
                },
                error: function(){
                    showModalAlert("Error de conexión", "Error");
                    end_loader();
                }
            });
        }
    });

    function resetForm(){
        $('#reservationForm')[0].reset();
        $('#schedule_id').val('');
        $('#vehicle_id').val('').trigger('change');
        $('#deleteReservationBtn').hide();
    }
});
</script>
