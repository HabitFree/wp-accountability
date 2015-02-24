<?php

abstract class HfForm {
    protected $elements;
    protected $markupGenerator;

    public function __construct($actionUrl, Hf_iMarkupGenerator $markupGenerator) {
        $this->elements = array();
        $this->elements[] = '<form action="'.$actionUrl.'" method="post">';

        $this->markupGenerator = $markupGenerator;
    }

    public function addTextBox($name, $label, $defaultValue, $isRequired) {
        if ($isRequired) {
            $this->elements[] =
                '<p><label for="'.$name.'"><span class="required">*</span> '.$label.': <input type="text" name="'.$name.'" value="'.$defaultValue.'" required /></label></p>';
        } else {
            $this->elements[] =
                '<p><label for="'.$name.'">'.$label.': <input type="text" name="'.$name.'" value="'.$defaultValue.'" /></label></p>';
        }
    }

    public function addSubmitButton($name, $label) {
        $this->elements[] = '<p><input type="submit" name="'.$name.'" value="'.$label.'" /></p>';
    }

    public function addPasswordBox($name, $label, $isRequired) {
        if ($isRequired) {
            $this->elements[] =
                '<p><label for="'.$name.'"><span class="required">*</span> '.$label.': <input type="password" name="'.$name.'" required /></label></p>';
        } else {
            $this->elements[] =
                '<p><label for="'.$name.'">'.$label.': <input type="password" name="'.$name.'" /></label></p>';
        }

    }

    public function addInfoMessage($message) {
        $this->elements[] = '<p class="info">'.$message.'</p>';
    }

    public function getOutput() {
        $html = '';
        foreach ($this->elements as $element) {
            $html .= $element;
        }
        $html .= '</form>';
        return $html;
    }
} 