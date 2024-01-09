<?php

require_once("vendor/autoload.php"); #uses at least rospdf/pdf-php
require_once("pdf_generator.php");

# may need to install bcmath (sudo apt install php-bcmath)

$help = "Tamarack song book maker\n";
$help .= "Usage: php create_pdf.php [flags] --ccli [ccli #] --version [name]\n\n";
$help .= " -t\t\tif set, make a tamarack version (with logo)\n";
$help .= " -c\t\tmake chordbook\n";
$help .= " -s\t\tmake songbook\n";
$help .= " -p\t\tmake projector book\n\n";
$help .= " --ccli\t\tccli number\n";
$help .= " --version\tversion name\n";
$help .= " --json\t\tfile name (default is songs.json)\n\n";

if (PHP_SAPI == 'cli') { # if used from command line
	$options = getopt("thcsp", array("ccli:", "version:", "json:"));
	if (isset($options['h']))
		die($help);
	$type = 's';
	if (isset($options['s']) && !isset($options['c']) && !isset($options['p']))
		$type = 's';
	elseif (!isset($options['s']) && isset($options['c']) && !isset($options['p']))
		$type = 'c';
	elseif (!isset($options['s']) && !isset($options['c']) && isset($options['p']))
		$type = 'p';
	else
		die("please enter one and only one type of book. -h for help\n");
	$json_loc = 'songs.json';
	if (isset($options['json']))
		$json_loc = $options['json'];
	$songbook = json_decode(file_get_contents($json_loc), true);
	$version = '';
	if (isset($options['version']))
		$version = $options['version'];
	$ccli = 0;
	if (isset($options['ccli']))
		$ccli = $options['ccli'];
	$t = false;
	if (isset($options['t']))
		$t = true;

	$gen = new PDFGenerator($songbook, $ccli, $version, $t);

	if ($type == 's') {
		ob_start();
		$gen->songbook_pdf();
		$stream = ob_get_clean();
		file_put_contents("songbook.pdf", $stream);
	} elseif ($type == 'c') {
		ob_start();
		$gen->chordbook_pdf();
		$stream = ob_get_clean();
		file_put_contents("chordbook.pdf", $stream);
	}
} else { # for http requests
	$songbook = json_decode(file_get_contents('songs.json'), true); # probably should be pulling from git here?
	$gen = new PDFGenerator($songbook, $_REQUEST['ccli'], $_REQUEST['versionName'], $_REQUEST['tamarackVersion']);
	if ($_REQUEST['type'] == 'songbook') {
		$gen->songbook_pdf();
	} elseif ($_REQUEST['type'] == 'chordbook') {
		$gen->chordbook_pdf();
	}
}

