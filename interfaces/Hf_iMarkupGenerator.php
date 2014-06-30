<?php

interface Hf_iMarkupGenerator {
    public function progressBar($percent, $label);

    public function generateTabs($contents, $defaultTabNumber);

    public function makeParagraph($content);

    public function makeLink($target, $content);

    public function makeList($items);

    public function makeError($content);
}