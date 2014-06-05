<?php

class HfAccountabilityForm extends HfForm {
    protected $elements;
    private $Goals;

    public function __construct($actionUrl, $Goals) {
        $this->Goals        = $Goals;
        $this->elements     = array();
        $this->elements[]   = '<form action="'.$actionUrl.'" method="post">';
    }

    public function populate($goalSubs) {

        foreach ($goalSubs as $sub) {
            $this->elements[] = $this->Goals->generateGoalCard($sub);
        }

        $this->elements[] = '<p></p><input class="submit" type="submit" name="submit" value="Submit" /></p>';
    }
} 