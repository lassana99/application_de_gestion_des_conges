<h2>Bienvenue sur l'<?php echo $_settings->info('name') ?></h2>
<hr class="bg-light">
<?php if($_settings->userdata('type') != 3): ?>
<div class="row">
    <!-- Départements -->
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-building"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Nombre de département</span>
                <span class="info-box-number text-right">
                    <?php 
                        $department = $conn->query("SELECT id FROM `department_list` ")->num_rows;
                        echo number_format($department);
                    ?>
                </span>
            </div>
        </div>
    </div>
    <!-- Postes -->
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-lightblue elevation-1"><i class="fas fa-th-list"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Nombre de poste</span>
                <span class="info-box-number text-right">
                    <?php 
                        $designation = $conn->query("SELECT id FROM `designation_list`")->num_rows;
                        echo number_format($designation);
                    ?>
                </span>
            </div>
        </div>
    </div>
    <!-- Employés -->
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Nombre d'employé</span>
                <span class="info-box-number text-right">
                    <?php 
                        $employees = $conn->query("SELECT id FROM `users`")->num_rows;
                        echo number_format($employees);
                    ?>
                </span>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<!-- Bloc unique pour l'employé connecté -->
<div class="row">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Nombre d'heure cumulé</span>
                <span class="info-box-number text-right">
                    <?php 
                        // Calcul du total d'heures pour l'utilisateur connecté uniquement
                        $user_id = $_settings->userdata('id');
                        $total_hours = 0;
                        $result = $conn->query("SELECT start_hour, end_hour FROM work_hours WHERE user_id = $user_id");
                        while($row = $result->fetch_assoc()){
                            $start = explode(':', $row['start_hour']);
                            $end = explode(':', $row['end_hour']);
                            $startMinutes = intval($start[0]) * 60 + intval($start[1]);
                            $endMinutes = intval($end[0]) * 60 + intval($end[1]);
                            $diff = ($endMinutes - $startMinutes) / 60;
                            if($diff < 0) $diff += 24;
                            $total_hours += $diff;
                        }
                        echo number_format($total_hours, 2, ',', ' ');
                    ?>
                </span>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
