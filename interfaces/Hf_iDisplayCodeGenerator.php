<?php

interface Hf_iDisplayCodeGenerator {
    public function progressBar($percent, $label);

    public function generateTabs($contents, $defaultTabNumber);
}