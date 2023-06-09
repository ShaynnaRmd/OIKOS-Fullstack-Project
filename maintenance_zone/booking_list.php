<?php
session_start();
require '../inc/pdo.php';
require '../inc/functions/token_function.php';

// Récupérer le mois actuel
$currentMonth = date('m-Y');

// Vérifier si un mois différent a été sélectionné
if (isset($_GET['month'])) {
    $selectedMonth = $_GET['month'];
} else {
    $selectedMonth = $currentMonth;
}

// Requête pour récupérer les dates de réservation de chaque logement
$reservationQuery = "
    SELECT housing.id AS housing_id, housing.title, booking.start_date_time, booking.end_date_time
    FROM housing
    LEFT JOIN booking ON housing.id = booking.housing_id
    WHERE DATE_FORMAT(booking.start_date_time, '%Y-%m') >= :selectedMonth
    ORDER BY housing.id, booking.start_date_time
";
$reservationStmt = $website_pdo->prepare($reservationQuery);
$reservationStmt->bindParam(':selectedMonth', $selectedMonth, PDO::PARAM_STR);
$reservationStmt->execute();
$reservations = $reservationStmt->fetchAll(PDO::FETCH_ASSOC);

// Tableau pour stocker les dates de réservation de chaque logement
$housingReservations = [];

// Organiser les dates de réservation par logement
foreach ($reservations as $reservation) {
    $housingId = $reservation['housing_id'];
    
    if (!isset($housingReservations[$housingId])) {
        $housingReservations[$housingId] = [
            'title' => $reservation['title'],
            'dates' => []
        ];
    }
    
    $housingReservations[$housingId]['dates'][] = [
        'start_date' => $reservation['start_date_time'],
        'end_date' => $reservation['end_date_time']
    ];
}

// Affichage du tableau des réservations par logement
echo "<h2>Booking à venir</h2>";
echo $selectedMonth;

// Affichage des flèches pour passer d'un mois à un autre
echo '<a href="?month=' . date('Y-m', strtotime($selectedMonth . ' -1 month')) . '">&lt; Mois précédent</a> | ';
echo '<a href="?month=' . date('Y-m', strtotime($selectedMonth . ' +1 month')) . '">Mois suivant &gt;</a>';

if (count($housingReservations) > 0) {
    echo "<table>";
    echo "<tr>";
    echo "<th>ID du logement</th>";
    echo "<th>Logement</th>";
    echo "<th>Dates de réservation</th>";
    echo "</tr>";

    foreach ($housingReservations as $housingId => $housingReservation) {
        echo "<tr>";
        echo "<td>" . $housingId . "</td>";
        echo "<td>" . htmlspecialchars($housingReservation['title']) . "</td>";
        echo "<td>";
        
        foreach ($housingReservation['dates'] as $reservationDate) {
            $startDate = date('j M', strtotime($reservationDate['start_date']));
            $endDate = date('j M', strtotime($reservationDate['end_date']));
            echo "Début du séjour : " . $startDate . " - " . "Fin du séjour: " . $endDate . "<br>";
        }
        
        echo "</td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "Pas encore de réservation pour ce mois-ci.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vision des dates de réservation par logement du mois</title>
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
</body>
</html>