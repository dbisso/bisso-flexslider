<?php
    /*
        Artificially delay the loading of images to simulate a slow connection
        <img src="/slowimage.php?img=realimage.jpg" />

        Via https://gist.github.com/738877
    */

    $image = strip_tags( $_GET['img'] );

    // delay between 1 and 3 seconds
    usleep( rand( 1000000, 3000000 ) );

    header( "Location: $image" );
    exit;
?>