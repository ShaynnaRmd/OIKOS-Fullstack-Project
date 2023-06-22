<?php
session_start();
require '../../inc/pdo.php';
$method = filter_input(INPUT_SERVER, "REQUEST_METHOD");

if(isset($_GET['booking_id'])){
    $booking_ID = $_GET['booking_id'];
}

$get_booking_details = $website_pdo -> prepare('
    SELECT DISTINCT b.housing_id, b.price, b.start_date_time, b.end_date_time, b.booking_date_time,
    h.title, h.place,h.district, h.number_of_pieces, h.area, h.price, h.description, h.capacity, h.type, DATEDIFF(b.end_date_time,b.start_date_time)
    FROM booking b
    JOIN housing h ON h.id = b.housing_id
    WHERE b.user_id = :user_id AND b.id = :booking_ID
');

$get_booking_details->execute([
    ':user_id'=> $_SESSION['id'],
    ':booking_ID'=> $booking_ID
]);

$booking_details = $get_booking_details->fetch(PDO::FETCH_ASSOC);

$verify_date_booking = $website_pdo -> prepare('
    SELECT DATEDIFF(start_date_time, CURDATE()) AS days_remaining
    FROM booking
    WHERE id = :booking_ID
');

$verify_date_booking->execute([
    ':booking_ID'=> $booking_ID
]);

$date_booking = $verify_date_booking->fetch(PDO::FETCH_ASSOC);

    if($method == "POST"){
        $request_delete_booking = $website_pdo -> prepare('
        DELETE FROM booking 
        WHERE user_id = :user_id
        AND id = :booking_id;
        ');

        $request_delete_booking -> execute ([
            ':user_id'=> $_SESSION['id'],
            ':booking_id' => $booking_ID
        ]);
        header('Location: booking_history.php');
        exit;
    }


if($booking_details){
    $title = $booking_details['title'];
    $district = $booking_details['district'];
    $place = $booking_details['place'];
    $number_of_pieces = $booking_details['number_of_pieces'];
    $area = $booking_details['area'];
    $price = $booking_details['price'];
    $description = $booking_details['description'];
    $capacity = $booking_details['capacity'];
    $type = $booking_details['type'];
    $start_date_time = $booking_details['start_date_time'];
    $end_date_time = $booking_details['end_date_time'];
    $booking_date_time = $booking_details['booking_date_time'];
    $nb_day_booking = $booking_details['DATEDIFF(b.end_date_time,b.start_date_time)'];

    $heart_icon = '../../assets/images/heart.svg';
    $menu_icon = '../../assets/images/menu.svg';
    $account_icon = '../../assets/images/account.svg';   

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/booking_history.css">
    <link rel="stylesheet" href="../../assets/css/font.css">
    <title>Détail réservation</title>
</head>
<body>
    <nav>
        <div class='logo'>
            <div class='logo-txt'>
                <a href=""><p>OIKOS</p></a>
            </div>
        </div>
        <div class='icon'>
            <div class="icon-heart"><img src=<?= $heart_icon ?> alt=""></div>
            <div class="icon-account-menu">
                <div class="icon-menu"><img src=<?= $menu_icon ?> alt=""></div>
                <div class="icon-account"><img src=<?= $account_icon ?> alt=""></div>
            </div>
        </div>
    </nav>
    <ul>
        <h1><?php echo $title  ?></h1>
        <li><B>Adresse:</B><?php echo $place ?></li>
        <li><B>District:</B><?php echo $district ?></li>
        <li><B>Nombre de pièces:</B><?php echo $number_of_pieces ?> pièces</li>
        <li><B>Superficie:</B><?php echo $area ?> mètre carré</li>
        <li><B>Prix:</B><?php echo $price ?>€</li>
        <li><B>Description:</B><?php echo $description ?></li>
        <li><B>Capacité:</B><?php echo $capacity ?> personnes</li>
        <li><B>Type:</B><?php echo $type ?></li>
        <li><B>Début du séjour:</B><?php echo $start_date_time ?></li>
        <li><B>Fin du séjour:</B><?php echo $end_date_time ?></li>
        <li><B>Nombre de jour:</B><?php echo $nb_day_booking  ?> jours</li>
        <li><B>Réservation fait le:</B><?php echo $booking_date_time ?></li>
    </ul>
    <?php if(isset($date_booking) && $date_booking['days_remaining'] > 6){ ?>
        <form method = "POST">
            <input type="submit" value="Annuler votre réservation">
        </form>
    <?php }?>
    <a href="./booking_history.php"><button>Retour</button></a>
    <a href="../clients_messagerie/index.php?booking_id=<?= $booking_ID?>"><button><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chat-text-fill" viewBox="0 0 16 16">
  <path d="M16 8c0 3.866-3.582 7-8 7a9.06 9.06 0 0 1-2.347-.306c-.584.296-1.925.864-4.181 1.234-.2.032-.352-.176-.273-.362.354-.836.674-1.95.77-2.966C.744 11.37 0 9.76 0 8c0-3.866 3.582-7 8-7s8 3.134 8 7zM4.5 5a.5.5 0 0 0 0 1h7a.5.5 0 0 0 0-1h-7zm0 2.5a.5.5 0 0 0 0 1h7a.5.5 0 0 0 0-1h-7zm0 2.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1h-4z"/>
</svg></button></a>
</body>
</html>