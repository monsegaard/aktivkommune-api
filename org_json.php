<!DOCTYPE html>
<?php
/*
* Name: eCultura API
* URI: https://github.com/monsegaard/aktivkommune-api
* Description: API for exporting Organizations from AktivKommune
* Author: Arild M. Halvorsen @ Monsegaard
* Author URI: https://github.com/monsegaard
* Version: 0.1
* License: GLPv2
*/

header('Content-Type: application/json; Charset="UTF-8"');
include_once('system.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

    $con = pg_connect("host=".DB_HOST. " port=" .DB_PORT. " dbname=" .DB_NAME. " user=" .DB_USER. " password=" .DB_PASS);
    pg_set_client_encoding($con, "UTF8");

    $sql = "SELECT
            bb_organization.id as organization_id,
            bb_organization.name as organization_name,
            bb_organization.organization_number as organization_number,
            bb_organization.homepage as homepage,
            bb_organization.street as street,
            bb_organization.zip_code as zip_code,
            bb_organization.district as district,
            bb_organization.city as city,
            bb_organization.description as description,
            bb_organization.email as email,
            bb_organization.phone as phone,
            bb_organization.activity_id as activity_id,
            bb_activity.name as activity_name,
            bb_activity.parent_id as parent_id
            FROM bb_organization
            INNER JOIN bb_activity ON bb_organization.activity_id = bb_activity.id
            WHERE bb_organization.active=1 AND
            bb_organization.show_in_portal=1
            ORDER BY bb_organization.id;";

    $result = pg_query($con, $sql) or die('Query failed: ' . pg_last_error());

    $org_array = array();

    $file = fopen( 'org_ecultura.csv', 'w' );
    fputcsv($file, array('Id', 'Name', 'Org_Number', 'Homepage', 'Street', 'Zip_Code', 'District', 'City', 'Description', 'Email', 'Phone', 'ActivityID', 'Activity', 'ImageURL'));

    while ($row = pg_fetch_row($result)) {

        $organization_id = $row[0];
        $organization_name = $row[1];
        $organization_number = $row[2];
        $homepage = $row[3];
        $street = $row[4];
        $zip_code = $row[5];
        $district = $row[6];
        $city = $row[7];
        $description = strip_tags($row[8], '<p><a>');
        $email = strtolower($row[9]);
        $phone = $row[10];
        $activity_id = $row[11];
        $activity = $row[12];
        $images_url = 'http://'. $_SERVER['SERVER_NAME'].'/images/org/'. $organization_id. '-800x450.jpeg';

            $org_array[] = array('id' => $organization_id,
                'organization_number' => $organization_number,
                'name' => $organization_name,
                'homepage' => $homepage,
                'phone' => $phone,
                'email' => $email,
                'street' => $street,
                'zip_code' => $zip_code,
                'district' => $district,
                'city' => $city,
                'description' => $description,
                'activity_id' => $activity_id,
                'activity' => $activity,
                'images_url' => $images_url);

        fputcsv($file, array( $organization_id, $organization_number, $organization_name, $homepage, $phone, $email, $street, $zip_code, $district, $city, $description, $activity_id, $activity, $images_url));

    }

    pg_close($con);

    fclose($file);

    // echo json_encode($org_array);

    $file = fopen('org_ecultura.json', 'w');
    fwrite($file, json_encode($org_array, JSON_PRETTY_PRINT));
    fclose($file);

    $str = file_get_contents('org_ecultura.json');
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
