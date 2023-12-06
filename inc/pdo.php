<?php
    $website_engine = "mysql";
    $host_website = "193.203.168.33";

    $website_port = 3306; 
    $website_bdd = "u305110207_oikos";
    $website_user = "u305110207_djedjeledev";
    $website_password = "Dj€dj€l€d€vd@t@b@s€høstïnG1";

    $website_dsn = "$website_engine:host=$host_website:$website_port;dbname=$website_bdd";
    $website_pdo = new PDO($website_dsn, $website_user, $website_password);
