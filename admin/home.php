<h1 class="mb-3">Bienvenido al Sistema de Reservas de Salas y Vehículos</h1>
<p class="lead">Administra y reserva espacios y vehículos de manera rápida y sencilla.</p>
<hr>

<?php
$sched_arr = array();
?>
<div class="row">
    <?php if ($_settings->userdata('role') == 1): ?>
        <div class="col-lg-4 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
                <div class="inner text-white">
                    <h3><?php echo $conn->query("SELECT * FROM `assembly_hall` ")->num_rows; ?></h3>
                    <p>Total de Salas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-door-open"></i>
                </div>
                <a href="./?page=assembly_hall" class="small-box-footer text-white">
                    Más información <i class="fas fa-arrow-circle-right text-white"></i>
                </a>
            </div>
        </div>
    <?php endif; ?>
    <!-- ./col -->

    <div class="col-lg-4 col-6">
        <!-- small box -->
        <div class="small-box bg-success">
            <div class="inner text-white">
                <h3><?php echo $conn->query("SELECT * FROM `schedule_list`")->num_rows; ?></h3>
                <p>Total de Reservas de Salas</p>
            </div>
            <div class="icon">
                <i class="fa fa-calendar-week"></i>
            </div>
            <a href="./?page=schedules" class="small-box-footer text-white">
                Más Información <i class="fas fa-arrow-circle-right text-white"></i>
            </a>
        </div>
    </div>
    <!-- ./col -->

    <?php if ($_settings->userdata('role') == 1): ?>
        <div class="col-lg-4 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
                <div class="inner text-white">
                    <h3><?php echo $conn->query("SELECT * FROM `users`")->num_rows; ?></h3>
                    <p>Total de Usuarios</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="./?page=users" class="small-box-footer text-white">
                    Más Información <i class="fas fa-arrow-circle-right text-white"></i>
                </a>
            </div>
        </div>
    <?php endif; ?>
    <!-- ./col -->

    <div class="col-lg-4 col-6">
        <!-- small box -->
        <div class="small-box bg-secondary">
            <div class="inner text-white">
                <h3>Reportes</h3>
                <p>Ver y generar reportes de reservas</p>
            </div>
            <div class="icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <a href="./?page=report" class="small-box-footer text-white">
                Más Información <i class="fas fa-arrow-circle-right text-white"></i>
            </a>
        </div>
    </div>
    <!-- ./col -->

    <?php if ($_settings->userdata('role') == 1): ?>
        <div class="col-lg-4 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
                <div class="inner text-white">
                    <h3><?php echo $conn->query("SELECT * FROM `vehicles`")->num_rows; ?></h3>
                    <p>Total de Vehículos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-car-side"></i>
                </div>
                <a href="./?page=assembly_hall&tab=vehicles" class="small-box-footer text-white">
                    Más Información <i class="fas fa-arrow-circle-right text-white"></i>
                </a>
            </div>
        </div>
    <?php endif; ?>
    <!-- ./col -->

    <!-- NUEVA CARD: Total de Recorridos (VISIBLE PARA TODOS) -->
    <div class="col-lg-4 col-6">
        <!-- small box -->
        <div class="small-box bg-primary">
            <div class="inner text-white">
                <h3><?php echo $conn->query("SELECT * FROM `vehicle_reservations`")->num_rows; ?></h3>
                <p>Total de Recorridos</p>
            </div>
            <div class="icon">
                <i class="fas fa-shuttle-van"></i>
            </div>
            <a href="./?page=vehicles" class="small-box-footer text-white">
                Más Información <i class="fas fa-arrow-circle-right text-white"></i>
            </a>
        </div>
    </div>
    <!-- ./col -->
</div>
