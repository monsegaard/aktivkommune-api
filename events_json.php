<?php
/*
* Name: eCultura API
* URI: https://github.com/monsegaard/aktivkommune-api
* Description: API for exporting Events from AktivKommune
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
            bb_event.building_name,
            bb_event.description,
            bb_event.from_ as date_time_from,
            bb_event.to_ as date_time_to,
            bb_event.from_ as fromtime,
            bb_event.to_ as totime,
            bb_event.contact_name as contact,
            bb_event.contact_email as email,
            bb_event.contact_phone as phone,
            bb_event.customer_organization_name as organization_name,
            bb_event.customer_organization_id as organization_id,
            bb_event.customer_organization_number as organization_number
            FROM bb_event
            WHERE bb_event.from_  >= CURRENT_DATE AND
            bb_event.to_ > CURRENT_TIMESTAMP AND
            bb_event.active=1 AND
            bb_event.is_public=1
            ORDER BY DATE(bb_event.from_)
            ASC LIMIT 300";

    $result = pg_query($con, $sql);

    $month_name = array(1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des');

    $evt_array = array();

    $file = fopen('events_aktiv_fjell.csv', 'w');
    fputcsv($file, array('Description', 'Location', 'From_Date', 'From_Time', 'To_Date', 'To_Time', 'Organization'));

    while ($row = pg_fetch_row($result)) {

        $description = strip_tags($row[1], '<p><a>');
        $location = $row[0];
        $event_date_time_from = $row[2];
        $event_date_time_to = $row[3];
        $event_from_time = substr($row[4],11,5); // 2016-03-07 19:30:00
        $event_to_time = substr($row[5],11,5);
        $event_day = substr($row[2],8,2);
        $event_year = substr($row[2],0,4);
        $event_month_name = $month_name[1];

        $event_month_name = $month_name[(int)substr($row[2],5,2)];

        $contact = $row[6];
        $email = strtolower($row[7]);
        $phone = $row[8];
        $organization_name = $row[9];
        $organization_id = $row[10];
        $organization_number = $row[11];


        // if ( $organization_number = '952304283') {

            $evt_array[] = array('beskrivelse' => $description,
                'sted' => $location,
                'dato_tid_fra' => $event_date_time_from,
                'dato_tid_til' => $event_date_time_to,
                'fra' => $event_from_time,
                'til' => $event_to_time,
                'dag' => $event_day,
                'aar' => $event_year,
                'mnd' => $event_month_name,
                'kontakt' => $contact,
                'e-post' => $email,
                'Telefon' => $phone,
                'Organisasjon' => $organization_name,
                'Organisasjon ID' => $organization_id,
                'Organisasjons Nr' => $organization_number);

        // }

        // fputcsv($file, array('Description', 'Location', 'From', 'To', 'Organization'));
        fputcsv($file, array( $description , $location, $event_date_time_from, $event_from_time, $event_date_time_to, $event_to_time, $organization_name ));

    }

    pg_close($con);

    fclose($file);

    echo json_encode($evt_array);

    $file = fopen('events_ecultura.json', 'w');
    fwrite($file, json_encode($evt_array));
    fclose($file);

?>
