<?php
require_once("vendor/autoload.php");
class PDFGenerator
{

	public $inchToPixel = 72;
	public $h = 792;
	public $w = 612;
	public $lm;
	public $rm;
	public $tm;
	public $bm;
	public $songbook;
	public $ccli;
	public $version_name;
	public $tamarack_version;
	public $tempPath;
	public $fontPath;

	function __construct($songbook, $ccli, $version_name, $tamarack_version)
	{
		$this->songbook = $songbook;
		$this->ccli = $ccli;
		$this->version_name = $version_name;
		$this->tamarack_version = $tamarack_version;
		$this->tempPath = dirname(__FILE__) . '/temp'; # can be changed by code that calls this class if necessary
		$this->fontPath = dirname(__FILE__) .'/fonts';
	}

	function newPage(&$pdf, &$x)
	{
		$pdf->newPage();
		$x = 0;
	}

	function rightText(&$pdf, $text, $size, $x)
	{
		$w = $pdf->getTextWidth($size, $text);
		return $x - $w;
	}

	function centerText(&$pdf, $text, $size, $xFrom, $xTo)
	{
		$w1 = $xTo - $xFrom;
		$mid = $xFrom + ($w1 / 2);
		$w2 = $pdf->getTextWidth($size, $text);
		return $mid - ($w2 / 2);
	}

	function outputSongChorded(&$pdf, &$x, $row, $onePage = true)
	{
		$xOld = $x;
		$n = $row['number'] . ".";
		$pdf->addText($this->rightText($pdf, $n, 12, 60), $this->h - $this->tm - $x, 12, $n);
		$pdf->addText(65, $this->h - $this->tm - $x, 12, $row['title']);
		$x += 12.5;
		if ($row['author']) {
			$pdf->addText(65, $this->h - $this->tm - $x, 7, "Written By: " . $row['author']);
			$x += 7.5;
		}
		if ($row['bible_reference']) {
			$pdf->addText(65, $this->h - $this->tm - $x, 7, "Scripture Reference: " . $row['bible_reference']);
			$x += 7.5;
		}
		if ($row['alternate_songbook']) {
			$pdf->addText(65, $this->h - $this->tm - $x, 7, $row['alternate_songbook']);
			$x += 7.5;
		}
		if ($this->tamarack_version && $row['theme_song_year']) {
			$pdf->addText(65, $this->h - $this->tm - $x, 7, "Camp Tamarack Theme Song " . $row['theme_song_year']);
			$x += 7.5;
		}
		if ($row['key']) {
			$pdf->addText(65, $this->h - $this->tm - $x, 7, "Key: " . $row['key']);
			$x += 7.5;
		}

		$x += 10.5;

		$pdf->selectFont('./fonts/Courier.afm');
		$lines = explode("\n", str_replace("\r\n", "\n", $row['chorded_song']));
		foreach ($lines as $line) {
			$pdf->addText($this->lm, $this->h - $this->tm - $x, 10, $line);
			$x += 10.5;
		}
		$pdf->selectFont('./fonts/Helvetica.afm');
		$x += 5;
		if ($row['copyright']) {
			$pdf->addText($this->lm, $this->h - $this->tm - $x, 7, html_entity_decode("&copy;") . ' ' . $row['copyright']);
			$x += 12;
		}

		if ($this->ccli && $row['ccli_covered']) {
			$pdf->addText($this->lm, $this->h - $this->tm - $x, 11, "CCLI {$this->ccli}");
			$x += 12;
		}

		if ($onePage && $x > $this->h - $this->tm - $this->bm) {
			$x = $xOld;
			return false;
		} else
			return true;
	}
	public function chordbook_pdf()
	{
		$pdf = new Cezpdf("letter"); // Page size 612 x 792, 72 Pixels/Inch

		if (!is_dir($this->tempPath))
			mkdir($this->tempPath); #pdf library wants a place to store things
		$pdf->tempPath = $this->tempPath;
		$pdf->fontPath = $this->fontPath;

		$pdf->selectFont('./fonts/Helvetica.afm');
		$this->lm = .5 * $this->inchToPixel;
		$this->rm = .5 * $this->inchToPixel;
		$this->tm = (1 * $this->inchToPixel) - 12;
		$this->bm = .5 * $this->inchToPixel;

		$pdf->selectFont('./fonts/Calibri_Bold.afm');
		$pdf->addText($this->centerText($pdf, "Young People's", 80, 0, $this->w), 650, 80, "Young People's");
		$pdf->addText($this->centerText($pdf, "Songbook", 80, 0, $this->w), 580, 80, "Songbook");

		if ($this->tamarack_version)
			$pdf->addJpegFromFile("logo2.jpg", $this->w / 2 - 200, 300, 400);

		$pdf->selectFont('./fonts/Calibri_Bold_Italic.afm');

		$pdf->addText($this->centerText($pdf, $this->version_name, 40, 0, $this->w), 50, 40, $this->version_name);
		if ($this->ccli)
			$pdf->addText($this->centerText($pdf, "CCLI {$this->ccli}", 10, 0, $this->w), 37, 10, "CCLI {$this->ccli}");

		$pdf->selectFont('./fonts/Helvetica.afm');

		$pdf->setLineStyle(1, 'round');
		$pdf->rectangle(10, 10, $this->w - 20, $this->h - 20);
		$pdf->rectangle(24, 24, $this->w - 48, $this->h - 48);
		$pdf->setLineStyle(5, 'round');
		$pdf->rectangle(17, 17, $this->w - 34, $this->h - 34);
		$pdf->setLineStyle(1);

		$pdf->line(10, 10, 24, 24);
		$pdf->line(10, $this->h - 10, 24, $this->h - 24);
		$pdf->line($this->w - 10, 10, $this->w - 24, 24);
		$pdf->line($this->w - 10, $this->h - 10, $this->w - 24, $this->h - 24);

		$this->newPage($pdf, $x);

		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText($this->centerText($pdf, "Table of Contents", 32, 0, $this->w), $this->h - 40, 32, "Table of Contents");
		$pdf->selectFont('./fonts/Helvetica.afm');

		$col = 0; #tracks which column of titles we are on
		$colmargin[0] = 50; # first page left
		$colmargin[1] = 50; # first page right
		$colmargin[2] = 0; # 2nd page left...
		$colmargin[3] = 0;
		$colmargin[4] = 0; # this is getting silly
		#$sql = "SELECT * FROM `songbook` WHERE `number`>'0' ORDER BY `title`";
		$song_titles = array();
		foreach ($this->songbook as $song) {
			if ($song['number'] != 0) {
				$song_titles[$song['number']] = $song['title'];
			}
		}
		asort($song_titles, SORT_STRING);

		foreach ($song_titles as $number => $title) {
			$n = $number . ".";
			$pdf->addText($this->rightText($pdf, $n, 14, 15 + $this->lm + (($col % 2) * 275)), $this->h - $this->tm - $x - $colmargin[$col], 14, $n);
			$t = $pdf->addTextWrap(20 + $this->lm + (($col % 2) * 275), $this->h - $this->tm - $x - $colmargin[$col], 14, $title, 220);
			if ($t) {
				$x += 15;
				$pdf->addTextWrap(40 + $this->lm + (($col % 2) * 275), $this->h - $this->tm - $x - $colmargin[$col], 14, $t, 200);
			}
			$x += 16.5;

			if ($x > $this->h - $this->tm - $this->bm - $colmargin[$col] - 20) {
				$col++;
				if ($col % 2 == 0)
					$this->newPage($pdf, $x);
				else
					$x = 0;
			}
		}

		$this->newPage($pdf, $x);

		$songbook_ordered = array();
		foreach ($this->songbook as $song) {
			if ($song['number'] != 0) {
				$songbook_ordered[$song['number']] = $song;
			}
		}
		ksort($songbook_ordered, SORT_NUMERIC);

		foreach ($songbook_ordered as $row) {
			if ($row['number'] > 1)
				$x += 25;
			$pdf->transaction("start");
			$r = $this->outputSongChorded($pdf, $x, $row);
			if ($r)
				$pdf->transaction("commit");
			else {
				$pdf->transaction("rewind");
				$this->newPage($pdf, $x);
				$r = $this->outputSongChorded($pdf, $x, $row, false);
				$pdf->transaction("commit");
			}
		}
		$pdf->ezStream();
	}

	private function outputSong(&$pdf, &$x, $row, $onePage = true)
	{
		$xOld = $x;
		$n = $row['number'] . ".";
		$pdf->addText($this->rightText($pdf, $n, 18, 72), $this->h - $this->tm - $x, 20, $n);
		$pdf->addText(77, $this->h - $this->tm - $x, 18, $row['title']);
		$x += 19;
		if ($row['author']) {
			$pdf->addText(77, $this->h - $this->tm - $x, 12, "Written By: " . $row['author']);
			$x += 13;
		}
		if ($row['bible_reference']) {
			$pdf->addText(77, $this->h - $this->tm - $x, 12, "Scripture Reference: " . $row['bible_reference']);
			$x += 13;
		}
		if ($row['alternate_songbook']) {
			$pdf->addText(77, $this->h - $this->tm - $x, 12, $row['alternate_songbook']);
			$x += 13;
		}
		if ($this->tamarack_version && $row['theme_song_year']) {
			$pdf->addText(77, $this->h - $this->tm - $x, 12, "Camp Tamarack Theme Song " . $row['theme_song_year']);
			$x += 13;
		}

		$x += 12;

		$lines = explode("\n", str_replace("\r\n", "\n", $row['unchorded_song']));
		foreach ($lines as $line) {
			$pdf->addText($this->lm, $this->h - $this->tm - $x, 17, $line);
			if ($line != "")
				$x += 18;
			else
				$x += 9;
		}
		$x += 10;
		if ($row['copyright']) {
			$pdf->addText($this->lm, $this->h - $this->tm - $x, 11, html_entity_decode("&copy;") . ' ' . $row['copyright']); # © 
			$x += 12;
		}
		if ($this->ccli && $row['ccli_covered']) {
			$pdf->addText($this->lm, $this->h - $this->tm - $x, 11, "CCLI {$this->ccli}");
			$x += 12;
		}

		if ($xOld != 0 && $onePage && $x > $this->h - $this->tm - $this->bm) {
			$x = $xOld;
			return false;
		} else
			return true;
	}

	function songbook_pdf()
	{
		$pdf = new Cezpdf("letter"); // Page size 612 x 792, 72 Pixels/Inch

		if (!is_dir('temp'))
			mkdir('temp'); #pdf library wants a place to store things
		$pdf->tempPath = dirname(__FILE__) . '/temp';
		$pdf->fontPath = $this->fontPath;

		$pdf->selectFont('./fonts/Helvetica.afm');

		$this->lm = .9 * $this->inchToPixel;
		$this->rm = .9 * $this->inchToPixel;
		$this->tm = 0 * $this->inchToPixel + 30;
		$this->bm = 0 * $this->inchToPixel + 10;


		$pdf->selectFont('./fonts/Calibri_Bold.afm');
		$pdf->addText($this->centerText($pdf, "Young People's", 80, 0, $this->w), 650, 80, "Young People's");
		$pdf->addText($this->centerText($pdf, "Songbook", 80, 0, $this->w), 580, 80, "Songbook");

		if ($this->tamarack_version)
			$pdf->addJpegFromFile("logo2.jpg", $this->w / 2 - 200, 300, 400);

		$pdf->selectFont('./fonts/Calibri_Bold_Italic.afm');

		$pdf->addText($this->centerText($pdf, $this->version_name, 40, 0, $this->w), 50, 40, $this->version_name);
		if ($this->ccli)
			$pdf->addText($this->centerText($pdf, "CCLI {$this->ccli}", 10, 0, $this->w), 37, 10, "CCLI {$this->ccli}");

		$pdf->selectFont('./fonts/Helvetica.afm');

		$pdf->setLineStyle(1, 'round');
		$pdf->rectangle(10, 10, $this->w - 20, $this->h - 20);
		$pdf->rectangle(24, 24, $this->w - 48, $this->h - 48);
		$pdf->setLineStyle(5, 'round');
		$pdf->rectangle(17, 17, $this->w - 34, $this->h - 34);
		$pdf->setLineStyle(1);

		$pdf->line(10, 10, 24, 24);
		$pdf->line(10, $this->h - 10, 24, $this->h - 24);
		$pdf->line($this->w - 10, 10, $this->w - 24, 24);
		$pdf->line($this->w - 10, $this->h - 10, $this->w - 24, $this->h - 24);

		$this->newPage($pdf, $x);
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText($this->centerText($pdf, "Table of Contents", 32, 0, $this->w), $this->h - 40, 32, "Table of Contents");
		$pdf->selectFont('./fonts/Helvetica.afm');

		$col = 0;
		$colmargin[0] = 50;
		$colmargin[1] = 50;
		$colmargin[2] = 0;
		$colmargin[3] = 0;
		$colmargin[4] = 0;

		$song_titles = array();
		foreach ($this->songbook as $song) {
			if ($song['number'] != 0) {
				$song_titles[$song['number']] = $song['title'];
			}
		}
		asort($song_titles, SORT_STRING);

		foreach ($song_titles as $number => $title) {
			$n = $number . ".";
			$pdf->addText($this->rightText($pdf, $n, 14, 15 + $this->lm + (($col % 2) * 275)), $this->h - $this->tm - $x - $colmargin[$col], 14, $n);
			$t = $pdf->addTextWrap(20 + $this->lm + (($col % 2) * 275), $this->h - $this->tm - $x - $colmargin[$col], 14, $title, 220);
			if ($t) {
				$x += 15;
				$pdf->addTextWrap(40 + $this->lm + (($col % 2) * 275), $this->h - $this->tm - $x - $colmargin[$col], 14, $t, 200);
			}
			$x += 16.5;

			if ($x > $this->h - $this->tm - $this->bm - $colmargin[$col] - 20) {
				$col++;
				if ($col % 2 == 0)
					$this->newPage($pdf, $x);
				else
					$x = 0;
			}
		}

		$this->newPage($pdf, $x);

		$songbook_ordered = array();
		foreach ($this->songbook as $song) {
			if ($song['number'] != 0) {
				$songbook_ordered[$song['number']] = $song;
			}
		}
		ksort($songbook_ordered, SORT_NUMERIC);

		foreach ($songbook_ordered as $row) {
			if ($row['number'] > 1)
				$x += 22;
			$pdf->transaction("start");
			$r = $this->outputSong($pdf, $x, $row);
			if ($r)
				$pdf->transaction("commit");
			else {
				$pdf->transaction("rewind");
				$this->newPage($pdf, $x);
				$r = $this->outputSong($pdf, $x, $row, false);
				$pdf->transaction("commit");
			}
		}
		$pdf->ezStream();
	}
}

