<?php

interface Hf_iMarkupGenerator {
    public function progressBar($percent, $label);

    public function generateTabs($contents, $defaultTabNumber);
}