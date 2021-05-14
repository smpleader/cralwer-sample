<?php

require_once 'deere.com.php'; 

try
{
    $try = new Reader;
    
    // 1 start the enpoint
    $try->start($try->keywords);

    echo '<h1>Result start</h1>';
    echo '<pre>';
    var_dump(
        $try->getResult()
    );
    echo '</pre>';

    // 2 read a list 
    $try->find($try->keywords, $try->list_url, []);
    echo '<h1>Result find</h1>';
    echo '<pre>';
    var_dump(
        $try->getResult()
    );
    echo '</pre>';

    // 3 read a detail
    echo '<h1>Result collect</h1>';
    echo '<pre>';
    var_dump(
        $try->collect($try->keywords, $try->detail_url, [])
    );
    echo '</pre>';
 
    echo '<p>-- DONE --</p>';

} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
