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
    SELECT id, maintenance_role, management_role, admin_role  
    FROM user
    WHERE id = :id;
');

$role_requete->execute([
    'id' => $_SESSION['id']
]);
$role_result = $role_requete->fetch(PDO::FETCH_ASSOC);

if ($role_result && $role_result['maintenance_role'] == 1) {
    // Si le rôle maintenance_role est ok, procédez au reste du code :*/

    // Récupérer les informations des réservations et de la maintenance
    $booking_requete = $website_pdo->prepare('
        SELECT b.id, b.start_date_time, b.housing_id, h.title AS housing_title, hi.image
        FROM booking b
        JOIN housing h ON b.housing_id = h.id
        JOIN housing_image hi ON h.id = hi.housing_id
    ');
    $booking_requete->execute();
    $booking_result = $booking_requete->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les informations de la maintenance
    $maintenance_requete = $website_pdo->prepare('
        SELECT m.id, m.title, m.housing_id, m.status, GROUP_CONCAT(mn.content SEPARATOR "<br>") AS notes
        FROM maintenance m
        LEFT JOIN maintenance_note mn ON m.id = mn.maintenance_id
        WHERE DATE_FORMAT(m.schedule_date, "%Y-%m") = :currentMonth
        GROUP BY m.id
    ');
    $currentMonth = date('Y-m');
    $maintenance_requete->bindParam(':currentMonth', $currentMonth, PDO::PARAM_STR);
    $maintenance_requete->execute();
    $maintenance_result = $maintenance_requete->fetchAll(PDO::FETCH_ASSOC);
    

    $title = "Checklist";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Checklist</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h2><?php echo $title?></h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Date de début</th>
            <th>Logement</th>
            <th>Image</th>
            <th>Status</th>
            <th>Titre</th>
            <th>Entretien de surface</th>
            <th>Vérifications techniques</th>
            <th>Notes</th>
            <th>Valider</th>
            <th>Autres notes</th>
        </tr>
        <?php foreach ($maintenance_result as $maintenance) { ?>
            <tr>
                <td><?php echo $maintenance['id']; ?></td>
                <td><?php echo $maintenance['start_date_time']; ?></td>
                <td><?php echo $maintenance['housing_title']; ?></td>
                <td><img src="<?php echo $maintenance['image']; ?>" alt="Image du logement"></td>
                <td><?php echo $maintenance['status']; ?></td>
                <td><?php echo $maintenance['title']; ?></td>
                <td>
                    <form action="" method="post">
                        <?php for ($i = 1; $i <= 6; $i++) { ?>
                            <input type="checkbox" name="surface_maintenance[]" value="<?php echo $i; ?>"> <?php echo $i; ?>
                        <?php } ?>
                </td>
                <td>
                    <?php for ($i = 1; $i <= 2; $i++) { ?>
                        <input type="checkbox" name="technical_check[]" value="<?php echo $i; ?>"> <?php echo $i; ?>
                    <?php } ?>
                </td>
                <td><?php echo $maintenance['notes']; ?></td>
                <td>
                    <input type="text" name="maintenance_note">
                    <button type="submit" name="submit">Valider</button>
                </td>
                <td>
                    <button type="button" class="notes-button">Autres notes</button>
                </td>
                    </form>
            </tr>
        <?php } ?>
    </table>

    <script>
        var notesButtons = document.getElementsByClassName('notes-button');
        for (var i = 0; i < notesButtons.length; i++) {
            notesButtons[i].addEventListener('click', function() {
                var notesRow = this.parentNode.parentNode.getElementsByClassName('notes')[0];
                notesRow.classList.toggle('show');
            });
        }
    </script>
</body>
</html>

<?php
    // Traitement du formulaire
    if (isset($_POST['submit'])) {
        $surfaceMaintenance = $_POST['surface_maintenance'];
        $technicalCheck = $_POST['technical_check'];
        $maintenanceNote = $_POST['maintenance_note'];
        

        // Vérifier si toutes les cases d'entretien de surface sont cochées
        $isSurfaceMaintenanceComplete = count($surfaceMaintenance) == 6;

        // Modifier le statut de la maintenance en fonction des cases cochées
        $maintenanceStatus = ($isSurfaceMaintenanceComplete && count($technicalCheck) > 0) ? 'fait' : 'en cours';

        // Mettre à jour le statut de la maintenance dans la base de données
        $updateMaintenanceStatus = $website_pdo->prepare('
            UPDATE maintenance
            SET status = :status
            WHERE id = :maintenanceId
        ');

        foreach ($maintenance_result as $maintenance) {
            $maintenanceId = $maintenance['id'];
            $updateMaintenanceStatus->execute([
                'status' => $maintenanceStatus,
                'maintenanceId' => $maintenanceId
            ]);
        }

        // Enregistrer la note de maintenance dans la table maintenance_note
        $maintenanceNoteRequete = $website_pdo->prepare('
            INSERT INTO maintenance_note (maintenance_id, user_id, content)
            VALUES (:maintenanceId, :userId, :content)
        ');

        $userId = $_SESSION['id'];

        foreach ($maintenance_result as $maintenance) {
            $maintenanceId = $maintenance['id'];
            $maintenanceNoteRequete->execute([
                'maintenanceId' => $maintenanceId,
                'userId' => $userId,
                'content' => $maintenanceNote
            ]);
        }
    }
/*}
else {
    echo "Vous n'avez pas les droits pour continuer.";
    exit;
}*/
?>