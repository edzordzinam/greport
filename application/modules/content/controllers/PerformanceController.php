<?php

class Content_PerformanceController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $chart = new Highcharts_Highchart();

        $chart->chart->renderTo = "container";
        $chart->chart->type = "spline";
        $chart->chart->marginRight = 10;

        $chart->chart->events->load = new Highcharts_HighchartJsExpr("function() {
                var series = this.series[0];
                setInterval(function() {
                    var x = (new Date()).getTime(),
                        y = Math.random();
                        series.addPoint([x, y], true, true);
                }, 1000); }");

        $chart->title->text = "Live random data";
        $chart->xAxis->type = "datetime";
        $chart->xAxis->tickPixelInterval = 150;
        $chart->yAxis->title->text = "Value";

        $chart->yAxis->plotLines[] = array('value' => 0,
                'width' => 1,
                'color' => "#808080");

        $chart->tooltip->formatter = new Highcharts_HighchartJsExpr("function() {
                            return '<b>'+ this.series.name +'</b><br/>'+
                            Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', this.x) +'<br/>'+
                            Highcharts.numberFormat(this.y, 2); }");

        $chart->legend->enabled = false;
        $chart->exporting->enabled = false;
        $chart->series[0]->name = "Random data";

        $chart->series[0]->data = new Highcharts_HighchartJsExpr("(function() {
                                        var data = [],
                                            time = (new Date()).getTime(),
                                            i;

                                        for (i = -19; i <= 0; i++) {
                                            data.push({
                                                x: time + i * 1000,
                                                y: Math.random()
                                            });
                                        }
                                        return data; })()");

        $globalOptions = new Highcharts_HighchartOption();
        $globalOptions->global->useUTC = false;

        $this->view->chart = $chart;
    }


}

