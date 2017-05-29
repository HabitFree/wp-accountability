<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class HfAccountabilityForm extends HfForm {
    protected $elements;
    private $Goals;

    public function __construct($actionUrl, Hf_iGoals $Goals) {
        $this->Goals        = $Goals;
        $this->elements     = array();
        $this->elements[]   = '<form action="'.$actionUrl.'" method="post" class="report-cards">';
    }

    public function populate($goalSubs) {

        $this->elements[]  = "<div class='card-wrapper'>";

        foreach ($goalSubs as $sub) {
            $this->elements[] = $this->Goals->goalCard($sub);
        }

        $this->elements[] = "</div>";

        $this->elements[] = '<p><input class="submit" type="submit" name="submit" value="Submit" /></p>';
    }
} 