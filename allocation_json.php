<?php
/*
* Name: eCultura API
* URI: https://github.com/monsegaard/aktivkommune-api
* Description: API for exporting Allocations from AktivKommune
* Author: Arild M. Halvorsen @ Monsegaard
* Author URI: https://github.com/monsegaard
* Version: 0.1
* License: GLPv2
*/

    // header('Content-Type: application/json');
    include_once("system.php");
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $con = pg_connect("host=".DB_HOST. " port=" .DB_PORT. " dbname=" .DB_NAME. " user=" .DB_USER. " password=" .DB_PASS);
    pg_set_client_encoding($con, "UTF8");

        $sql = "SELECT
                bb_allocation.from_ as date_time_from,
                bb_allocation.to_ as date_time_to,
                bb_resource.name as recource_name,
                bb_building.name as name
                FROM bb_allocation as A, bb_building as B, bb_resource as R
                INNER JOIN bb_allocation_resource
                ON bb_allocation_resource.allocation_id = A.id
                INNER JOIN bb_resource
                ON R.id = bb_allocation_resource.resource_id
                INNER JOIN bb_building
                ON B.id = bb_resource.building_id
                WHERE bb_allocation.from_  >= CURRENT_TIMESTAMP AND
                bb_allocation.to_ > CURRENT_TIMESTAMP AND
                bb_allocation.active=1
                ORDER BY DATE(bb_allocation.from_)
                ASC LIMIT 300";

    $result = pg_query($con, $sql);

    $month_name = array(1 => "Jan", 2 => "Feb", 3 => "Mar", 4 => "Apr", 5 => "Mai", 6 => "Jun", 7 => "Jul", 8 => "Aug", 9 => "Sep", 10 => "Okt", 11 => "Nov", 12 => "Des");

    $evt_array = array();

    while ($row = pg_fetch_row($result)) {

        $building = $row[0];
        $allo_date_time_from = $row[1];
        $allo_date_time_to = $row[2];
        $recource_name = $row[3];
        $location = $row[4];
        $organization_name = $row[5];


        // if ( $organization_number = '952304283') {

            $evt_array[] = array('bygning'=> $building,
                'ressurs'=> $recource_name,
                'sted'=> $location,
                'dato_tid_fra'=> $allo_date_time_from,
                'dato_tid_til'=> $allo_date_time_to,
                'Organisasjon'=> $organization_name);

        // }

    }

    echo json_encode($evt_array);

    pg_close($con);

    $fp = fopen('events_ecultura.json', 'w');
    fwrite($fp, json_encode($evt_array));
    fclose($fp);

?>
