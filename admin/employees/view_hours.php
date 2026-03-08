<?php
if(!isset($conn)){
    require_once('../../config.php');
}
?>
<h3>Consulter les heures de travail</h3>
<form id="view-hours-form" class="form-inline mb-3">
    <select name="user_id" class="form-control mr-2" required>
        <option value="">-- Employé --</option>
        <?php
        $users = $conn->query("SELECT id, CONCAT(lastname, ' ', firstname) as name FROM users ORDER BY lastname");
        while($u = $users->fetch_assoc()): ?>
            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
        <?php endwhile; ?>
    </select>
    <input type="date" name="date_start" class="form-control mr-2" required>
    <input type="date" name="date_end" class="form-control mr-2" required>
    <button class="btn btn-info" type="submit">Afficher</button>
    <!-- Nouveau bouton -->
    <button type="button" id="btn-verif-conge" class="btn btn-success ml-2" disabled>Vérification du droit au congé</button>
</form>
<!-- Zone d'affichage du message -->
<div id="droit-conge-message" class="mb-2"></div>
<table class="table table-bordered" id="hours-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Plage horaire</th>
            <th>Durée (heures)</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>
<script>
let totalHeuresTravail = 0; // Variable globale pour stocker le total

$('#view-hours-form').on('submit', function(e){
    e.preventDefault();
    // Désactive le bouton de vérification pendant le chargement
    $('#btn-verif-conge').prop('disabled', true);
    $('#droit-conge-message').html('');
    $.post('/CONGE_SECTO/classes/Master.php?f=get_work_hours', $(this).serialize(), function(resp){
        let r = {};
        try {
            let jsonStart = resp.indexOf('{');
            let json = resp.substring(jsonStart);
            r = JSON.parse(json);
        } catch(e) {
            alert('Erreur de réponse du serveur : ' + resp);
            return;
        }
        let rows = '';
        let total = 0;
        if(r.status === 'success'){
            r.data.forEach(function(item){
                let start = item.start_hour.split(':');
                let end = item.end_hour.split(':');
                let startMinutes = parseInt(start[0]) * 60 + parseInt(start[1]);
                let endMinutes = parseInt(end[0]) * 60 + parseInt(end[1]);
                let diff = (endMinutes - startMinutes) / 60;
                if(diff < 0) diff += 24;
                total += diff;
                rows += `<tr>
                    <td>${item.work_date}</td>
                    <td>${item.start_hour} - ${item.end_hour}</td>
                    <td>${diff.toFixed(2)}</td>
                </tr>`;
            });
            rows += `<tr>
                <td colspan="2" class="text-right"><strong>Total</strong></td>
                <td><strong>${total.toFixed(2)}</strong></td>
            </tr>`;
            // Active le bouton de vérification si au moins une ligne
            $('#btn-verif-conge').prop('disabled', false);
        } else {
            rows = `<tr><td colspan="3" class="text-center">Aucune donnée disponible.</td></tr>`;
            $('#btn-verif-conge').prop('disabled', true);
        }
        $('#hours-table tbody').html(rows);
        totalHeuresTravail = total; // Stocke le total pour la vérification
    }).fail(function(xhr, status, error){
        alert('Erreur AJAX : ' + error + "\nURL appelée : " + this.url);
    });
});

// Gestion du clic sur le bouton de vérification
$('#btn-verif-conge').on('click', function(){
    let message = '';
    if(totalHeuresTravail >= 352){
        message = `<div class="alert alert-success">Cet employé a droit au congé</div>`;
    } else {
        message = `<div class="alert alert-danger">Cet employé n'a pas droit au congé</div>`;
    }
    $('#droit-conge-message').html(message);
});
</script>
