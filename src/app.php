<?php

require "../vendor/autoload.php";
$config = parse_ini_file("../config/config.ini");

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>

<html>
    <head>
        <style>
            td,th { border: 1px solid #999;}
            .holiday { background-color: red}
        </style>
    </head>
    <body>

    <?php
    $hapi = new HolidayAPI\v1($config['API_KEY']);
    $client = new Predis\Client();

    for ($i = 0; $i < 20; $i++) {
        echo (new \SampleApplication\Calendar($hapi, 2017, $client))->draw() . '<br>';
    }
    ?>

    </body>
</html>


