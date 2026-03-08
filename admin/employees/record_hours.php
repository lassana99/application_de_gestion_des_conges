<?php
// Liste des employés
$users = $conn->query("SELECT id, CONCAT(lastname, ' ', firstname) as name FROM users ORDER BY lastname");
?>
<h3>Enregistrer les heures de travail</h3>

<!-- Zone d'affichage des messages -->
<div id="work-hours-message"></div>

<form id="work-hours-form">
    <div class="form-group">
        <label>Employé</label>
        <select name="user_id" class="form-control" required>
            <option value="">-- Choisir --</option>
            <?php while($u = $users->fetch_assoc()): ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="form-group">
        <label>Date</label>
        <input type="date" name="work_date" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Heure de début</label>
        <input type="time" name="start_hour" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Heure de fin</label>
        <input type="time" name="end_hour" class="form-control" required>
    </div>
    <button class="btn btn-primary" type="submit">Enregistrer</button>
</form>

<script>
$('#work-hours-form').on('submit', function(e){
    e.preventDefault();
    // Nettoie les anciens messages
    $('#work-hours-message').html('');
    $.post('../classes/Master.php?f=save_work_hours', $(this).serialize(), function(resp){
        let r = {};
        try {
            let jsonStart = resp.indexOf('{');
            let json = resp.substring(jsonStart);
            r = JSON.parse(json);
        } catch(e) {
            showMessage('danger', 'Erreur de réponse du serveur : ' + resp);
            return;
        }
        if(r.status === 'success'){
            showMessage('success', 'Heures sauvegardées !');
            setTimeout(function(){ location.reload(); }, 2000); // Recharge après 2s
        } else {
            showMessage('danger', 'Erreur : ' + (r.err || 'Une erreur est survenue.'));
        }
    }).fail(function(xhr, status, error){
        showMessage('danger', 'Erreur AJAX : ' + error);
    });
});

// Fonction utilitaire pour afficher un message Bootstrap
function showMessage(type, message) {
    let alertHtml = `
        <div class="alert alert-`+type+` alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    `;
    $('#work-hours-message').html(alertHtml);
    // Disparition automatique après 4 secondes
    setTimeout(function(){
        $('.alert').alert('close');
    }, 4000);
}
</script>
