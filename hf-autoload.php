<?php

function hfAutoload( $folder ) {
    foreach ( scandir( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . $folder ) as $filename ) {
        $path = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $filename;
        if ( is_file( $path ) ) {
            require_once $path;
        }
    }
}

hfAutoload( 'interfaces' );
hfAutoload( 'abstractClasses' );
hfAutoload( 'classes' );