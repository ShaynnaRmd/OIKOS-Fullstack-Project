<?php
session_start();
require '../inc/pdo.php';
require '../inc/functions/token_function.php';

/*// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    echo "Vous devez être connecté pour accéder à cette page.";
    exit;
}

// Vérifier les autorisations d'accès (rôle d'entretien)
$role_requete = $website_pdo->prepare('
SELECT id, maintenance_role
FROM user
WHERE id = :id;
');

$role_requete->execute([
    'id' => $_SESSION['id']
]);
$role_result = $role_requete->fetch(PDO::FETCH_ASSOC);

if ($role_result && $role_result['maintenance_role'] == 1) {
    $maintenanceRole = $role_result['maintenance_role'];
    // Si le rôle maintenance_role est ok -> proceed le reste du code :*/

    $currentMonth = date('Y-m');

    $maintenance_requete = $website_pdo->prepare('
        SELECT DISTINCT m.id, m.status, m.title, m.schedule_date, m.housing_id, hi.image, h.title AS housing_title
        FROM maintenance m
        JOIN housing_image hi ON m.housing_id = hi.housing_id  
        JOIN housing h ON m.housing_id = h.id
        WHERE DATE_FORMAT(m.schedule_date, "%Y-%m") = :currentMonth
    ');
    $maintenance_requete->bindParam(':currentMonth', $currentMonth, PDO::PARAM_STR);
    $maintenance_requete->execute();
    $maintenance_result = $maintenance_requete->fetchAll(PDO::FETCH_ASSOC);

    $housing_id = array();
    for ($i = 0; $i < count($maintenance_result); $i++) {
        array_push($housing_id, $maintenance_result[$i]['housing_id']);
    }

    $title = "Checklist: ";
/*} else {
    echo "Vous n'avez pas les droits pour continuer.";
    exit;
}*/

// Traitement du formulaire après soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maintenanceNote = $_POST['maintenance_note'];
    $status = 'à faire';

    // Vérifier si toutes les cases sont cochées
    $isChecked = true;
    foreach ($_POST['maintenance_check'] as $check) {
        if (empty($check)) {
            $isChecked = false;
            break;
        }
    }

    // Mettre à jour le statut en fonction de la validation
    if ($isChecked) {
        $status = 'fait';
        // Supprimer la maintenance si le mois actuel se termine
        $currentMonthEnd = date('Y-m-t');
        if ($currentMonthEnd === $currentMonth) {
            // Supprimer la maintenance
            $deleteMaintenanceQuery = $website_pdo->prepare('DELETE FROM maintenance WHERE id = :maintenanceId');
            $deleteMaintenanceQuery->bindParam(':maintenanceId', $_POST['maintenance_id'], PDO::PARAM_INT);
            $deleteMaintenanceQuery->execute();
        }
    } else {
        $status = 'en cours';
    }

    // Mettre à jour le statut et la note dans la table de maintenance
    $updateMaintenanceQuery = $website_pdo->prepare('UPDATE maintenance SET status = :status WHERE id = :maintenanceId');
    $updateMaintenanceQuery->bindParam(':status', $status, PDO::PARAM_STR);
    $updateMaintenanceQuery->bindParam(':maintenanceId', $_POST['maintenance_id'], PDO::PARAM_INT);
    $updateMaintenanceQuery->execute();

    // Insérer la note de maintenance
    $insertNoteQuery = $website_pdo->prepare('INSERT INTO maintenance_note (maintenance_id, user_id, content) VALUES (:maintenanceId, :userId, :content)');
    $insertNoteQuery->bindParam(':maintenanceId', $_POST['maintenance_id'], PDO::PARAM_INT);
    $insertNoteQuery->bindParam(':userId', $_SESSION['id'], PDO::PARAM_INT);
    $insertNoteQuery->bindParam(':content', $maintenanceNote, PDO::PARAM_STR);
    $insertNoteQuery->execute();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails de l'entretien</title>
    <style>
        /* Ajouter CSS */

    </style>
</head>
<body>
    <h2><?php echo $title ?></h2>
    <form action="" method="POST">
        <?php foreach ($maintenance_result as $maintenance) { ?>
            <h3>Booking ID: <?php echo $maintenance['id']; ?></h3>
            <h4>Logement: <?php echo $maintenance['housing_title']; ?></h4>
            <img src="<?php echo $maintenance['image']; ?>" alt="Image du logement">

            <input type="hidden" name="maintenance_id" value="<?php echo $maintenance['id']; ?>">

            <div class="maintenance-details-section">
                <h4>Entretien de surface</h4>
                <input type="checkbox" name="maintenance_check[]" value="Nettoyage"> Nettoyage<br>
                <input type="checkbox" name="maintenance_check[]" value="Peinture"> Peinture<br>
                <input type="checkbox" name="maintenance_check[]" value="Réparation"> Réparation<br>
                <!-- Ajouter d'autres cases à cocher pour l'entretien de surface si nécessaire -->
            </div>

            <div class="maintenance-details-section">
                <h4>Vérifications techniques</h4>
                <input type="checkbox" name="maintenance_check[]" value="Plomberie"> Plomberie<br>
                <input type="checkbox" name="maintenance_check[]" value="Électricité"> Électricité<br>
                <!-- Ajouter d'autres cases à cocher pour les vérifications techniques si nécessaire -->
            </div>

            <div class="maintenance-details-section">
                <h4>Autres notes</h4>
                <textarea name="maintenance_note" rows="4" cols="50"></textarea>
            </div>

            <input type="submit" name="submit" value="Valider">
        <?php } ?>
    </form>
</body>
</html>
