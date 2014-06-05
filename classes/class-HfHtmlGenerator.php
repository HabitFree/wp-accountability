<?php

class HfHtmlGenerator implements Hf_iDisplayCodeGenerator {

    function HfHtmlGenerator() {
    }

    function progressBar($percent, $label) {
        return '<div class="meter">
					<span class="label">'.$label.'</span>
					<span class="progress" style="width: '.$percent.'%">'.$label.'</span>
				</div>';
    }
}