<?php

// $Header: /cvsroot/tikiwiki/tiki/tiki-pv_chart.php,v 1.5 2003-08-18 08:42:22 redflo Exp $

// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

//Include the code
include ("lib/phplot.php");

require_once ('tiki-setup.php');

if ($feature_stats != 'y') {
	die;
}

if ($tiki_p_view_stats != 'y') {
	die;
}

//Define the object
$graph = new PHPlot;

//Set some data
if (!isset($_REQUEST["days"]))
	$_REQUEST["days"] = 7;

$example_data = $tikilib->get_pv_chart_data($_REQUEST["days"]);
$graph->SetDataValues($example_data);
//$graph->SetPlotType('bars');
$graph->SetPlotType('lines');
$graph->SetYLabel(tra('pageviews'));
$graph->SetXLabel(tra('day')); 
//Draw it
$graph->DrawGraph();

?>
