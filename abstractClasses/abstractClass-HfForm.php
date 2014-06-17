<?php

abstract class HfForm {
    protected $elements;

    public function __construct($actionUrl) {
        $this->elements = array();
        $this->elements[] = '<form action="'.$actionUrl.'" method="post">';
    }

    public function getHtml() {
        $html = '';
        foreach ($this->elements as $element) {
            $html .= $element;
        }
        $html .= '</form>';
        return $html;
    }
} 