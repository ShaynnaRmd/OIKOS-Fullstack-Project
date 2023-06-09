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
    $maintenanceRole = $role_result['maintenance_role'];
    // Si le rôle maintenance_role est ok -> proceed le reste du code : */
    $maintenance_requete = $website_pdo->prepare('
    SELECT DISTINCT m.id, m.status, m.title, m.schedule_date, m.housing_id, hi.image
    FROM maintenance m
    JOIN housing_image hi ON m.housing_id = hi.housing_id    
');
$maintenance_requete->execute();
$maintenance_result = $maintenance_requete->fetchAll(PDO::FETCH_ASSOC);

$housing_id = array();
for ($i = 0; $i < count($maintenance_result); $i++) {
    array_push($housing_id, $maintenance_result[$i]['housing_id']);
}

$title = "Tâches à venir: ";




/*// L'utilisateur n'a pas le role neccessaire -> le rediriger vers l'acceuil ou qqch comme ça :
}else {
    echo "Vous n'avez pas les droits pour continuer.";
    exit;
    }*/
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des tâches à venir</title>
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
            <th>Statut</th>
            <th>Titre</th>
            <th>Date prévue</th>
            <th>ID Logement</th>
            <th>Image</th>
        </tr>
        <?php foreach ($maintenance_result as $maintenance) { ?>
            <tr>
                <td><?php echo $maintenance['id']; ?></td>
                <td><?php echo $maintenance['status']; ?></td>
                <td><?php echo $maintenance['title']; ?></td>
                <td><?php echo $maintenance['schedule_date']; ?></td>
                <td><?php echo $maintenance['housing_id']; ?></td>
                <td><img src="<?php echo $maintenance['image']; ?>" alt="Image du logement"></td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>