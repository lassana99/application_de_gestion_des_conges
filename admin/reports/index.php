<?php if($_settings->chk_flashdata('success')): ?>
<script>
	alert_toast("<?php echo $_settings->flashdata('success') ?>",'success')
</script>
<?php endif;?>

<?php 
// Calcul des heures cumulées pour l'utilisateur connecté sur toute la période
$user_id = $_settings->userdata('id');
$total_hours = 0;
$query = $conn->query("SELECT start_hour, end_hour FROM work_hours WHERE user_id = $user_id");
while($row = $query->fetch_assoc()){
    $start = explode(':', $row['start_hour']);
    $end = explode(':', $row['end_hour']);
    $startMinutes = intval($start[0]) * 60 + intval($start[1]);
    $endMinutes = intval($end[0]) * 60 + intval($end[1]);
    $diff = ($endMinutes - $startMinutes) / 60;
    if($diff < 0) $diff += 24;
    $total_hours += $diff;
}
$total_hours = round($total_hours, 2);

$message = '';
if($total_hours >= 352){
    $message = "<div class='alert alert-success mt-3'>Vous avez accumulé <strong>{$total_hours}</strong> heures, donc <strong>vous avez droit à un congé</strong>.</div>";
} else {
    $message = "<div class='alert alert-warning mt-3'>Vous avez accumulé <strong>{$total_hours}</strong> heures, donc <strong>vous n'avez pas droit à un congé</strong>.</div>";
}
?>

<div class="card card-outline card-primary">
	<div class="card-header">
		<h3 class="card-title">Rapport de vos heures de travail</h3>
	</div>
	<div class="card-body">
		<?php echo $message; ?>
	</div>
</div>
