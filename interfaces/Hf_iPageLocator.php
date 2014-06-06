<?php

interface Hf_iPageLocator {
    function getCurrentPageUrl();

    function getUrlByTitle( $title );
}