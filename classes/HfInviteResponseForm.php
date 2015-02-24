<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class HfInviteResponseForm extends HfForm {
    public function getOutput() {
        $this->makeForm();

        $html = '';
        foreach ($this->elements as $element) {
            $html .= $element;
        }
        $html .= '</form>';
        return $html;
    }

    private function makeForm()
    {
        $this->addInfoMessage("Looks like you're responding to an invite. What would you like to do?");
        $this->addSubmitButton('accept', 'Accept invitation');
        $this->addSubmitButton( 'ignore', 'Ignore invitation' );
    }
} 