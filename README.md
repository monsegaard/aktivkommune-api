# AktivKommune API
API for exporting data from AktivKommune in json or csv format

  - Organizations
  - Events
  - Buildings/Locations
  - Allocations

To use the API:

First edit the system.php. Customize this file it fits your requirements

Run org_json.php, events_json.php, allocation_json.php or locations_json.php with *curl_exec($ch)* on the url server.

Example PHP code to exctract json data from url:

```php
        $ch = curl_init("http://something.com/org_json.php");

        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      
        if ( $retcode = 200 ) {
            echo 'Execute <strong>org_json.php</strong> on external server ... <br>';
        }
        
        curl_close($ch);

        $str = file_get_contents('http://something.com/org_ecultura.json');
        $json = json_decode($str, true);

        var_dump($json);
```