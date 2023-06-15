<?php
$_SESSION['id'] = 1;
require '../inc/pdo.php';

$heart_icon = '../assets/images/heart.svg';
$menu_icon =   '../assets/images/menu.svg';
$account_icon = '../assets/images/account.svg';
$link_favorite = '../client_zone/profile/favorites.php';

$path = 'http://localhost/OIKOS-Fullstack-Project/uploads/';


if(isset($_SESSION['id'])) {
    $user_info = $website_pdo->prepare(
        'SELECT * FROM user WHERE id = :id;'
    );
    $user_info->execute([
        ':id' => $_SESSION['id']
    ]);
    $result_user_info = $user_info->fetch(PDO::FETCH_ASSOC);
    if($result_user_info){
    $mail = $result_user_info['mail'];
    $lastname = $result_user_info['lastname'];
    $pp_image = $result_user_info['pp_image'];
    }

}

if(isset($_SESSION['id'])) {
    $housing_info = $website_pdo->prepare(
        'SELECT * FROM housing WHERE id = :id;'
    );
    $housing_info->execute([
        ':id' => $_SESSION['id']
    ]);
    $result_housing_info = $housing_info->fetch(PDO::FETCH_ASSOC);
    if($result_housing_info){
    $id_housing = $result_housing_info['id'];
    $title = $result_housing_info['title'];
    $place = $result_housing_info['place'];
    $number_of_pieces = $result_housing_info['number_of_pieces'];
    }
}

if(isset($_SESSION['id'])) {
    $housing_img = $website_pdo->prepare(
        "SELECT hi.image, h.id as housing_id, h.title, h.place, h.number_of_pieces, h.area, h.price, h.description, h.capacity, h.type
        FROM housing h
        JOIN housing_image hi ON h.id = hi.housing_id
        ORDER BY hi.housing_id"

    );
    $housing_img->execute();
    $result_housing_img = $housing_img->fetchAll();

}

$recup_district = $website_pdo->prepare(
    "SELECT district from housing ORDER BY id DESC;"
);
$recup_district->execute();
$result_recup_district = $recup_district->fetchAll();

$search_housing = $website_pdo->prepare(
    'SELECT * FROM housing ORDER BY id DESC'
);

if (isset($_POST['submit_booking'])) {
    $search_district_name = $_POST['district_name'];
    $search_capacity = $_POST['capacity_search'];

    $search_housing = $website_pdo->prepare(
        'SELECT * FROM housing WHERE district LIKE :district_name AND capacity >= :capacity ORDER BY id DESC'
    );

    $search_housing->execute([
        ':district_name' => '%' . $search_district_name . '%',
        ':capacity' => $search_capacity
    ]);
}
$search_housing->execute();
$result_search_housing = $search_housing->fetchAll();
// ////afficher les images qui correspondent
// if(isset($_SESSION['id'])) {
//     $housing_img = $website_pdo->prepare(
//         "SELECT hi.image, h.id as housing_id, h.title, h.place,h.district, h.number_of_pieces, h.area, h.price, h.description, h.capacity, h.type
//         FROM housing h
//         JOIN housing_image hi ON h.id = hi.housing_id
//         ORDER BY hi.housing_id"

//     );
//     $housing_img->execute();
//     $result_housing_img = $housing_img->fetchAll();

// }
if(isset($_SESSION['id'])) {
    $housing = $website_pdo->prepare("
    SELECT * FROM housing");
}
$housing->execute();
$result_housing = $housing->fetchAll();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/font.css">
    <link rel="stylesheet" href="../assets/css/header_publiczone.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/housing_list_publiczone.css">
    <script src="../assets/js/carousel.js"></script>
    <title>Document</title>
</head>
<body>
    <?php require '../inc/tpl/header_publiczone.php' ?>
    <div class="container-housinglist">
        <div class='input'>
        <form action="housing_result_search.php" method="POST">
            <div class="container-label-input">
                <label for="">Quartier</label>
                <select name="district_name">
                <?php foreach($result_recup_district as $rrd) :?>
                <option value="<?= $rrd['district']?>"><?= $rrd['district']?></option>
                <?php endforeach ?>
                </select>
            </div>
            <div class="separator"></div>
            <div class="container-label-input">
                <label for="">Arrivée</label>
                <input type="date" name="first_day_search">
            </div>
            <div class="separator">
            </div>
            <div class="container-label-input">
                <label for="">Départ</label>
                <input type="date" name="end_day_search">
            </div>
            <div class="separator">
            </div>
            <div class="container-label-input">
                <label for="">Voyageurs</label>
                <input type="number" name="capacity_search" min="1" max="20">
            </div>
            <div class="container-label-input">
            <input type="submit" value="Réserver" name="submit_booking">
            </div>
        </form>
        </div>
        <div class="house-list">
            <?php
        // $prev_housing_id = null;
        foreach ($result_housing as $row) {
            $housing_img = $website_pdo ->prepare(
                'SELECT image FROM housing_image WHERE housing_id = :id'
            );
            $housing_img->execute([
                ':id'=>$row['id']
            ]);
            $result_housing_img = $housing_img->fetchAll();
        // if ($row['housing_id'] !== $prev_housing_id) {
            // Affiche les informations de l'appartement une seule fois
            ?>
            <div class="house-item">
                <div class="house-img">
                    <div class="slider-nav">
                        <div class='arrow-left' onclick=previous()><img src="../assets/images/chevron-left.svg" alt=""></div>
                        <div class='arrow-right' onclick=next()><img src="../assets/images/chevron-right.svg" alt=""></div>
                    </div>
                    <div class="slider-content">
                        <?php 
                        foreach($result_housing_img as $img) {
                            ?>
                            <div class="slider-content-item">
                            <img src="<?= $path . $img['image'] ?>" alt="Housing_photo">
                            </div>

                        <?php } ?>
                    </div>
                </div>
                <div class="house-important">
                    <div class="house-important-top">
                        <div class="house-title"><h2><?= $row['title'] ?></h2></div>
                        <div class="house-district"><p><?= $row['district'] ?></p></div>
                    </div>
                    <div class="house-important-bottom">
                        <div class="house-area"><p><?= $row['number_of_pieces'] ?> Pièces - <?= $row['area'] ?> m²</p></div>
                        <div class="house-capacity"><p><?= $row['capacity'] ?> voyageurs</p></div>
                        <div class="house-icon">
                            <div class="icon-self">
                                <div class='icon-img'><img src="../assets/images/agreement.svg" alt=""></div>
                                <div class='icon-txt'><p>Meeting Room</p></div>
                            </div>
                            <div class="icon-self">
                                <div class='icon-img'><img src="../assets/images/piano.svg" alt=""></div>
                                <div class='icon-txt'><p>Piano</p></div>
                            </div>
                            <div class="icon-self">
                                <div class='icon-img'><img src="../assets/images/audio.svg" alt=""></div>
                                <div class='icon-txt'><p>Home Cinema</p></div>
                            </div>
                        </div>
                    </div> 
                </div>
                <div class="house-description-btn">
                    <div class="house-description"><p><?= $row['description'] ?></p></div>
                    <div class="house-btn-heart">
                        <div class="house-btn">
                            <a href="./housing.php?id=<?= $row['id']?>"><button>Voir Plus</button></a>
                        </div>
                        <div class="house-heart">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20.8401 4.60987C20.3294 4.09888 19.7229 3.69352 19.0555 3.41696C18.388 3.14039 17.6726 2.99805 16.9501 2.99805C16.2276 2.99805 15.5122 3.14039 14.8448 3.41696C14.1773 3.69352 13.5709 4.09888 13.0601 4.60987L12.0001 5.66987L10.9401 4.60987C9.90843 3.57818 8.50915 2.99858 7.05012 2.99858C5.59109 2.99858 4.19181 3.57818 3.16012 4.60987C2.12843 5.64156 1.54883 7.04084 1.54883 8.49987C1.54883 9.95891 2.12843 11.3582 3.16012 12.3899L4.22012 13.4499L12.0001 21.2299L19.7801 13.4499L20.8401 12.3899C21.3511 11.8791 21.7565 11.2727 22.033 10.6052C22.3096 9.93777 22.4519 9.22236 22.4519 8.49987C22.4519 7.77738 22.3096 7.06198 22.033 6.39452C21.7565 5.72706 21.3511 5.12063 20.8401 4.60987Z" stroke="#DD3F57" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <?php
                    }
            ?>
        </div>
    </div>
    <script src="../assets/js/header_public.js"></script>
</body>
</html>