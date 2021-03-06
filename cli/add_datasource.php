#!/usr/bin/php -q
<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2018 The Cacti Group                                 |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDTool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
 | This code is designed, written, and maintained by the Cacti Group. See  |
 | about.php and/or the AUTHORS file for specific developer information.   |
 +-------------------------------------------------------------------------+
 | http://www.cacti.net/                                                   |
 +-------------------------------------------------------------------------+
*/

/* do NOT run this script through a web browser */
if (!isset($_SERVER['argv'][0]) || isset($_SERVER['REQUEST_METHOD']) || isset($_SERVER['REMOTE_ADDR'])) {
	die('<br><strong>This script is only meant to run at the command line.</strong>');
}

$no_http_headers = true;

include(dirname(__FILE__) . "/../include/global.php");
include_once("../lib/utility.php");
include_once("../lib/template.php");

/* process calling arguments */
$parms = $_SERVER["argv"];
array_shift($parms);

unset($host_id);
unset($graph_template_id);
unset($data_template_id);

foreach($parms as $parameter) {
	@list($arg, $value) = @explode("=", $parameter);

	switch ($arg) {
		case "--host-id":
			$host_id = trim($value);
			if (!is_numeric($host_id)) {
				echo "ERROR: You must supply a valid host-id to run this script!\n";
				exit(1);
			}
			break;
		case "--data-template-id":
			$data_template_id = $value;
			if (!is_numeric($data_template_id)) {
				echo "ERROR: You must supply a numeric data-template-id!\n";
				exit(1);
			}
			break;
		case "-h":
		case "-v":
		case "-V":
		case "--version":
		case "--help":
			display_help();
			exit;
		default:
			print "ERROR: Invalid Parameter " . $parameter . "\n\n";
			display_help();
			exit;
	}
}

if (!isset($host_id)) {
	echo "ERROR: You must supply a valid host-id!\n";
	exit(1);
}

if (!isset($data_template_id)) {
	echo "ERROR: You must supply a valid data-template-id!\n";
	exit(1);
}

//Following code was copied from data_sources.php->function form_save->save_component_data_source_new

$save["id"] = "0";
$save["data_template_id"] = $data_template_id;
$save["host_id"] = $host_id;

$local_data_id = sql_save($save, "data_local");

change_data_template($local_data_id, $data_template_id);

/* update the title cache */
update_data_source_title_cache($local_data_id);

/* update host data */
if (!empty($host_id)) {
	push_out_host($host_id, $local_data_id);
}

/*  display_version - displays version information */
function display_version() {
	$version = get_cacti_version();
	echo "Cacti Add Data Source, Version $version, " . COPYRIGHT_YEARS . "\n\n";
}

function display_help() {
	display_version();
	echo "usage: add_datasource.php --host-id=[ID] --data-template-id=[ID]\n\n";
	echo "Cacti utility for adding datasources via a command line interface.\n\n";
	echo "--host-id=id - The host id\n";
	echo "--data-template-id=id - The numerical ID of the data template to be added\n";
}

