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
    // Si le rôle maintenance_role est ok -> proceed le reste du code : */
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

    $title = "Tâches à venir pour le mois en cours: ";
    
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
            <th>Date prévue</th>
            <th>logement</th>
            <th>Image</th>
            <th>Statut</th>
            <th>Titre</th>
        </tr>
        <?php foreach ($maintenance_result as $maintenance) { ?>
            <tr>
                <td><?php echo $maintenance['id']; ?></td>
                <td><?php echo $maintenance['schedule_date']; ?></td>
                <td><?php echo $maintenance['housing_title']; ?></td>
                <td><img src="<?php echo $maintenance['image']; ?>" alt="Image du logement"></td>
                <td><?php echo $maintenance['status']; ?></td>
                <div class="link"><td><a href="../maintenance_zone/maintenance_details.php?id=<?php echo $maintenance['id']; ?>"><?php echo $maintenance['title']; ?><a/></td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
