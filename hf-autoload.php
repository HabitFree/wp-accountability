<?php

function hfAutoload( $path ) {
    foreach ( scandir( $path ) as $entry ) {
        if ( ! in_array( $entry, [".",".."] ) ) {
            if ( is_file( "$path/$entry" ) ) {
                require_once "$path/$entry";
            } else if ( is_dir( "$path/$entry" ) ) {
                hfAutoload( "$path/$entry" );
            }
        }
    }
}

$root = dirname( __FILE__ );

hfAutoload( "$root/interfaces" );
hfAutoload( "$root/abstractClasses" );
hfAutoload( "$root/classes" );