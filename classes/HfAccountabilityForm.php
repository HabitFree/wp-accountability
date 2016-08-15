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

        $this->elements[] = "<script type=\"text/javascript\" src=\"https://www.gstatic.com/charts/loader.js\"></script>
           <script type=\"text/javascript\">
              google.charts.load('current', {'packages':['gauge']});
        
              function drawChart(id, health) {
                var data = google.visualization.arrayToDataTable([
                  ['Label', 'Value'],
                  ['Health', 0]
                ]);
        
                var options = {
                  width: 400, height: 120,
                  redFrom: 0, redTo: 15,
                  yellowFrom:15, yellowTo: 85,
                  greenFrom:85, greenTo: 100,
                  minorTicks: 5
                };
        
                var chart = new google.visualization.Gauge(document.getElementById(id));
        
                chart.draw(data, options);
        
                data.setValue(0, 1, health);
                chart.draw(data, options);
              }</script>";

        $this->elements[]  = "<div class='card-wrapper'>";

        foreach ($goalSubs as $sub) {
            $this->elements[] = $this->Goals->goalCard($sub);
        }

        $this->elements[] = "</div>";

        $this->elements[] = '<p><input class="submit" type="submit" name="submit" value="Submit" /></p>';
    }
} 