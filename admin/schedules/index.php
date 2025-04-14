<?php if($_settings->chk_flashdata('success')): ?>
<script>
    alert_toast("<?php echo $_settings->flashdata('success') ?>",'success')
</script>
<?php endif;?>

<style>
#calendar {
    width: 100%;
    height: 550px; /* Ajusta el alto según tus necesidades */
}
#selectAll{
    top: 0;
}
</style>

<!-- Card for Reservation Form -->
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Reservar Sala</h3>
    </div>
    <div class="card-body">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div id="calendar"></div> <!-- Aquí se renderiza el calendario -->
                </div>
                <div class="col-md-4">
                    <div class="callout border-0">
                        <h5><b>Nueva Reserva</b></h5>
                        <hr>
                        <form action="" id="add_sched">
                            <input type="hidden" name="id" value="">
                            <div class="form-group">
                                <label for="assembly_hall_id" class="control-label">Seleccionar Sala</label>
                                <select name="assembly_hall_id" id="assembly_hall_id" class="custom-select select2">
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
                                <label for="reserved_by" class="control-label">Reserva a Nombre de:</label>
                                <input type="text" class="form-control" name="reserved_by" id="reserved_by" required>
                            </div>
                            <div class="form-group">
                                <label for="datetime_start" class="control-label">Fecha y Hora de inicio:</label>
                                <input type="datetime-local" class="form-control" name="datetime_start" id="datetime_start">
                            </div>
                            <div class="form-group">
                                <label for="datetime_end" class="control-label">Fecha y Hora de fin:</label>
                                <input type="datetime-local" class="form-control" name="datetime_end" id="datetime_end">
                            </div>
                            <div class="form-group">
                                <label for="schedule_remarks" class="control-label">Observaciones:</label>
                                <textarea rows="3" class="form-control" name="schedule_remarks" id="schedule_remarks"></textarea>
                            </div>
                            <div class="form-group d-flex w-100 justify-content-end">
                                <button class="btn btn-flat btn-primary btn-sm mr-2">Guardar</button>
                                <button class="btn btn-flat btn-light btn-sm" type="reset">Resetear</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Obtener las reservas
$sched_qry = $conn->query("SELECT s.*, a.room_name, s.reserved_by FROM `schedule_list` s inner join assembly_hall a on a.id = s.assembly_hall_id ");
$sched_data = array();

while($row = $sched_qry->fetch_assoc()):
    // Limpiar los valores de caracteres especiales
    $row['room_name'] = htmlspecialchars($row['room_name'], ENT_QUOTES, 'UTF-8');
    $row['reserved_by'] = htmlspecialchars($row['reserved_by'], ENT_QUOTES, 'UTF-8');
    $row['datetime_start'] = htmlspecialchars($row['datetime_start'], ENT_QUOTES, 'UTF-8');
    $row['datetime_end'] = htmlspecialchars($row['datetime_end'], ENT_QUOTES, 'UTF-8');
    $row['schedule_remarks'] = htmlspecialchars($row['schedule_remarks'], ENT_QUOTES, 'UTF-8');

    $sched_data[] = $row;
endwhile;

// Convertir los datos a JSON y escapar comillas para evitar problemas
$sched = json_encode($sched_data);
$sched = addslashes($sched);  // Escapar comillas para evitar problemas en JavaScript
?>

<script>
var scheds = $.parseJSON('<?php echo $sched ?>');
console.log(scheds);  // Revisa el formato del JSON en la consola

$(function(){
    // Establecer la fecha mínima para los campos de fecha (no permitir seleccionar la fecha actual ni pasadas)
    var today = new Date();
    
    // Formatear la fecha y hora actual en el formato adecuado para los campos datetime-local
    var yyyy = today.getFullYear();
    var mm = today.getMonth() + 1;  // Los meses comienzan desde 0
    var dd = today.getDate();
    var hh = today.getHours();
    var min = today.getMinutes();

    // Asegurarse de que los meses y días tengan dos dígitos (formato correcto para datetime-local)
    if (mm < 10) mm = '0' + mm;
    if (dd < 10) dd = '0' + dd;
    if (hh < 10) hh = '0' + hh;
    if (min < 10) min = '0' + min;

    // Crear el formato de la fecha y hora en el formato adecuado para datetime-local
    var currentDateTime = yyyy + '-' + mm + '-' + dd + 'T' + hh + ':' + min;

    // Asignar el valor mínimo para los inputs
    document.getElementById('datetime_start').setAttribute('min', currentDateTime);
    document.getElementById('datetime_end').setAttribute('min', currentDateTime);

    // Inicializar el formulario de reserva
    $('#add_sched').submit(function(e){
        e.preventDefault();
        start_loader();
        $('#add_sched .err-msg').remove();

        // Obtener los valores de los campos de inicio y fin de la reserva
        var datetime_start = $('#datetime_start').val();
        var datetime_end = $('#datetime_end').val();

        // Convertir los valores a objetos Date
        var startDate = new Date(datetime_start);
        var endDate = new Date(datetime_end);

        // Validar que las fechas no sean inválidas
        if (endDate <= startDate) {
            var el = $('<div class="err-msg alert alert-danger mb-1">')
                el.text("La fecha y hora del horario no son válidas.");
            $('#add_sched').prepend(el);
            el.show('slow');
            end_loader();
            return false;
        }

        // Comprobar que el nuevo horario no se solape con reservas existentes
        var conflictFound = false;
        scheds.forEach(function(reservation) {
            var reservedStart = new Date(reservation.datetime_start);
            var reservedEnd = new Date(reservation.datetime_end);

            // Verificar si el nuevo horario se solapa con alguna reserva existente
            if ((startDate < reservedEnd && endDate > reservedStart)) {
                conflictFound = true;
            }
        });

        if (conflictFound) {
            var el = $('<div class="err-msg alert alert-danger mb-1">')
                el.text("El horario entra en conflicto con otros horarios.");
            $('#add_sched').prepend(el);
            el.show('slow');
            end_loader();
            return false;
        }

        // Si no hay conflictos, proceder con el envío del formulario
        $.ajax({
            url:_base_url_+'classes/Master.php?f=save_schedule',
            method:"POST",
            data: $(this).serialize(),
            dataType:"json",
            error: err => {
                console.log(err);
                end_loader();
                alert_toast("Ha ocurrido un error", "error");
            },
            success: function(resp){
                if(resp.status == 'success'){
                    location.reload();
                } else if(resp.status == 'failed' && !!resp.err_msg){
                    var el = $('<div class="err-msg alert alert-danger mb-1">')
                        el.text(resp.err_msg);
                    $('#add_sched').prepend(el);
                    el.show('slow');
                } else {
                    console.log(resp);
                    alert_toast("Ha ocurrido un error", "error");
                }
                end_loader();
            }
        });
    });

    // Configuración de Select2
    $('.select2').select2({placeholder: "Por favor selecciona una sala"});

    // Configuración del calendario con FullCalendar
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        locale: 'es', // Establecer idioma a español
        themeSystem: 'bootstrap',
        events: function(event, successCallback) {
            var events = [];
            Object.keys(scheds).map(k => {
                events.push({
                    title: scheds[k].room_name + ' - ' + scheds[k].reserved_by, // Muestra el nombre de la sala y quien la reservó
                    start: moment(scheds[k].datetime_start).format("YYYY-MM-DD HH:mm"),
                    end: moment(scheds[k].datetime_end).format("YYYY-MM-DD HH:mm"),
                    backgroundColor: 'var(--success)',
                    borderColor: 'var(--primary)',
                    'data-id': scheds[k].id
                });
            });
            successCallback(events);
        },
        eventClick: function(info) {
            var sched_id = info.event.extendedProps['data-id'];
            console.log(sched_id);
            uni_modal("Detalles de la Reserva", "schedules/view_details.php?id=" + sched_id);
        },
        editable: true,
        selectable: true,
    });

    calendar.render();  // Renderizar el calendario
});
</script>
