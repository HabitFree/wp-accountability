<?php

class HfHtmlGenerator {

    function HfHtmlGenerator() {
    }

    function roundToMultiple($number, $multiple) {
        return round($number/$multiple) * $multiple;
    }

    function progressBar($percent, $label) {
        return '<div class="meter">
					<span class="label">'.$label.'</span>
					<span class="progress" style="width: '.$percent.'%">'.$label.'</span>
				</div>';
    }

    function donutChart($percent, $label) {
        $percent = $this->roundToMultiple($percent, 5);
        return '<div class="half_pie">
				    <div class="half_part_pie_one half_bar_color half_percentage" data-percentage="'.$percent.'"></div>
				    <div class="half_part_pie_two"></div>
				    <div class="half_part_pie_three"></div>	<span class="half_pie_icon iconfont-android"></span>
				</div>';
    }
}