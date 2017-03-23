<!DOCTYPE html>
<?php
/*
* Name: eCultura API
* URI: https://github.com/monsegaard/aktivkommune-api
* Description: API for exporting Locations/Buildings from AktivKommune
* Author: Arild M. Halvorsen @ Monsegaard
* Author URI: https://github.com/monsegaard
* Version: 0.1
* License: GLPv2
*/

// header('Content-Type: application/json');
header('Content-Type: application/json; Charset="UTF-8"');
include_once("system.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$con = pg_connect("host=".DB_HOST. " port=" .DB_PORT. " dbname=" .DB_NAME. " user=" .DB_USER. " password=" .DB_PASS);
pg_set_client_encoding($con, "UTF8");

    $sql = "SELECT
            bb_building.id as building_id,
            bb_building.name as building_name,
            bb_building.description as description,
            bb_building.street as street,
            bb_building.zip_code as zip_code,
            bb_building.city as city,
            bb_building.district as district,
            bb_building.email as email,
            bb_building.phone as phone,
            bb_building.homepage as homepage
            FROM bb_building
            WHERE bb_building.active=1
            ORDER BY bb_building.id;";

    $result = pg_query($con, $sql) or die('Query failed: ' . pg_last_error());

    $loc_array = array();

    $file = fopen('loc_ecultura.csv', 'w');
    fputcsv($file, array('Id', 'Name', 'Description', 'Street', 'Zip_code', 'City','District', 'Country', 'Email', 'Phone', 'Homepage', 'ImageURL', 'BookingURL'));

    while ($row = pg_fetch_row($result)) {

        $building_id = $row[0];
        $building_name = $row[1];
        $description = strip_tags($row[2], '<p><a>');
        $street = $row[3];
        $zip_code = $row[4];
        $city = $row[5];
        $district = $row[6];
        $country = "Norge";
        $email = strtolower($row[7]);
        $phone = $row[8];
        $homepage = $row[9];
        $images_url = 'https://aktiv.fjell.kommune.no/?menuaction=bookingfrontend.uidocument_building.download&id?' .$building_id. '&filter_owner_id=2';
        $booking_url = 'https://aktiv.fjell.kommune.no/?menuaction=bookingfrontend.uibuilding.show&id=' .$building_id;

        // if ( $organization_number = '952304283') {

            $loc_array[] = array('id' => $building_id,
                'organization_name' => $building_name,
                'description' => $description,
                'street' => $street,
                'zip_code' => $zip_code,
                'city' => $city,
                'district' => $district,
                'country' => $country,
                'email' => $email,
                '$phone' => $phone,
                '$homepage' => $homepage,
                'images_url' => $images_url,
                'booking-url' => $booking_url);
        // }

        fputcsv($file, array( $building_id, $building_name, $description, $street, $zip_code, $city, $district, $country, $email, $phone, $homepage, $images_url, $booking_url ));

    }

    pg_close($con);

    fclose($file);

    // echo json_encode($loc_array);

    $file = fopen('loc_ecultura.json', 'w');
    fwrite($file, json_encode($loc_array));
    fclose($file);

    $str = file_get_contents('loc_ecultura.json');
    $json = json_decode($str, true);

    $keys = array_keys($json);

    for($i = 0; $i < count($json); $i++) {
        echo $keys[$i] . "{<br>";
        foreach($json[$keys[$i]] as $key => $value) {
            echo $key . " : " . $value . "<br>";
        }
        echo "}<br>";
    }

?>
