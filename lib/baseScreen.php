<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2023 Henning Pingel
*  All rights reserved
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
*/

require_once("lib/basicContentChunks.php");

class sktpBaseScreen extends overlayBasicContentChunks{

	const PETSCII_KEY = [
		"special_02_proxy_download" => "02", //used internal to try to trigger proxy download

		"home" => "13", //dec 19
		"instdel" => "14", //<-- hex (dec 20)
		"space" => "20",
		"plus" => "2B",
		"comma" => "2C",
		"minus" => "2D",
		"fullstop" => "2E",
		"clear" => "93", // dec 147
		//147 clear    19 home    20 delete
		"joystickbutton" => "5C", //dec 92 pound
		"0" => "30",
		"1" => "31",
		"2" => "32",
		"3" => "33",
		"4" => "34",
		"5" => "35",
		"6" => "36",
		"7" => "37",
		"8" => "38",
		"9" => "39",

		"a" => "41",
		"b" => "42",
		"c" => "43",
		"d" => "44",
		"e" => "45",
		"f" => "46",
		"g" => "47",
		"h" => "48",
		"i" => "49",
		"j" => "4A",
		"k" => "4B",
		"l" => "4C",
		"m" => "4D",
		"n" => "4E",
		"o" => "4F",
		"p" => "50",
		"q" => "51",
		"r" => "52",
		"s" => "53",
		"t" => "54",
		"u" => "55",
		"v" => "56",
		"w" => "57",
		"x" => "58",
		"y" => "59",
		"z" => "5A",
		"arrow_upwards" => "5E",
		"arrow_left" => "5F",
		"return" => "0D",

		"F1" => "85",
		"F3" => "86",
		"F5" => "87",
		"F7" => "88",//assign Plus/4 HELP
		"F2" => "89",
		"F4" => "8A",
		"F6" => "8B",
		"F8" => "8C",//this is F7 on Plus/4

		"*" => "2A",
		"crsr_up" 		=> "91", //145
		"crsr_down" 	=> "11", //17
		"crsr_left" 	=> "9D", //157
		"crsr_right" 	=> "1D", //29

		"plus4_escape" 	=> "1B", //027
		"plus4_help" 	=> "88", //this is F7 on C64
		"plus4_f7" 		=> "8C", //this is F8 on C64
	];

	private
		$registeredKeys,
		$sessionVars,
		$sessionVarPrefix;

	function __construct( $debug, $subscreenSuffix = "", $nativeTEDColorsFor264 = false ){
		parent::__construct(
			$debug, 
			intval($_SESSION["type"]) == 264, 
			$this->isClientWiC64(),
			$nativeTEDColorsFor264
		);
		$this->registeredKeys = array();
		$this->sessionVars = array();
		$this->sessionVarPrefix = $_SESSION["application"] . "_" . $_SESSION["screen"] . $subscreenSuffix;
		if (!isset($_SESSION[$this->sessionVarPrefix]))
			$_SESSION[$this->sessionVarPrefix] = array();
	}

	public function isClientOn264(){
		return $this->typeIs264;
	}

    protected function isClientWiC64(){
		return (isset($_SESSION["clientDevice"]) && $_SESSION["clientDevice"] === "wic");
	}

	public function isSessionVarRegistered($key){
		$b=isset($_SESSION[$this->sessionVarPrefix][$key]);
		if ($b && !in_array($key, $this->sessionVars))
			$this->sessionVars[] = $key;
		return $b;
	}

	public function setAppScreen( $app, $screen){
		$_SESSION["application"] = $app;
		$_SESSION["screen"] = $screen;
	}

	public function registerSessionVar( $key, $value){
		if (!in_array($key, $this->sessionVars))
			$this->sessionVars[] = $key;
		else
			die("session var was already registered");
		if (!isset($_SESSION[$this->sessionVarPrefix][$key]))
			$_SESSION[$this->sessionVarPrefix][$key] = $value;
	}

	public function registerSessionVars( $keysValues ){
		foreach ($keysValues as $key => $value){
			$this->registerSessionVar( $key, $value);
		}
	}

	protected function unsetRegisteredSessionVars(){
		//foreach ($this->sessionVars as $key)
		//	unset($_SESSION[$key]);
		$_SESSION[$this->sessionVarPrefix] = array();
		$this->sessionVars = array();
	}

	public function getSessionVar( $key ){
		if ( !in_array( $key, $this->sessionVars))
			die("session var $key does not exist");
		return $_SESSION[$this->sessionVarPrefix][$key];
	}

	public function getSessionVarStrLength( $key ){
		if ( !in_array( $key, $this->sessionVars))
			die("session var $key does not exist");
		return strlen($_SESSION[$this->sessionVarPrefix][$key]);
	}

	public function setSessionVar( $key, $value ){
		if ( !in_array( $key, $this->sessionVars))
			die("session var $key does not exist");
		$_SESSION[$this->sessionVarPrefix][$key] = $value;
	}

	public function decreaseSessionVar( $key, $amount=1 ){
		if ( !in_array( $key, $this->sessionVars))
			die("session var $key does not exist");
		$_SESSION[$this->sessionVarPrefix][$key] = $_SESSION[$this->sessionVarPrefix][$key] - abs($amount);
	}

	public function increaseSessionVar( $key, $amount=1 ){
		if ( !in_array( $key, $this->sessionVars))
			die("session var $key does not exist");
		$_SESSION[$this->sessionVarPrefix][$key] = $_SESSION[$this->sessionVarPrefix][$key] + abs($amount);
	}

	//unused?
	protected function registerKey( $key, $callbackFunc){
		$this->registeredKeys[$key] = $callbackFunc;
	}

	//unused?
	protected function isKeyRegistered( $key ){
		return array_key_exists( $key, $this->registeredKeys);
	}

	// convenience methods for normal chunk

	public function addNormalChunkXY($chunk, $x, $y, $color){
		$this->addNormalChunk($chunk, $x + 40 * $y, $color);
	}

	public function addPaddedNormalChunkXY($chunk, $x, $y, $width, $color){
		if ( strlen($chunk) > $width)
			$chunk = substr($chunk, 0, $width-3)."...";
		else
			$chunk = str_pad($chunk, $width, " ");
		$this->addNormalChunk($chunk, $x + 40 * $y, $color);
	}

	public function addNormalChunkXYNoMap($chunk, $x, $y, $color){
		$this->addNormalChunkNoMap($chunk, $x + 40 * $y, $color);
	}

	//so that chr value stays unchanged
	public function addNormalChunkNoMap($chunk, $pos, $color){
		$this->addNormalChunk($chunk, $pos, $color, true);
	}

	public function addScreenCodeChunkXY($chunk, $x, $y, $color){
		$this->addScreenCodeChunk($chunk, $x + 40 * $y, $color);
	}

	public function addCenteredScreenCodeChunkY($chunk, $y, $color){
		$x= intval( (40-strlen($chunk))/2 );
		$this->addScreenCodeChunkXY($chunk, $x, $y, $color);
	}

	//keystroke convenience method

	protected function isScreenExitKeypress($key){
		if ( $this->isClientOn264() )
		{
			return ( 
				$key == self::PETSCII_KEY["plus4_escape"] ||
				$key == self::PETSCII_KEY["plus4_f7"]
			);
		}
		else
			return ( 
				$key == self::PETSCII_KEY["arrow_left"] ||
				$key == self::PETSCII_KEY["F5"]
			);
	}

	protected function addCenteredF5F7ChunkY($y = 24, $color = "7", $prefix = "", $uppercase = false){
		$exitKeyLegend = " Sidekick menu"; 
		if ( $this->isClientOn264() ){
			$f5back = "ESC/F7 Back";
			$exitKey = "HELP";
			$arrowLeft = "";
		}
		else{
			$f5back = "/F5 Back";
			$exitKey = "F7"; 
			$arrowLeft = chr(31);
		}

		if ($uppercase){
			$f5back = strtoupper($f5back);
			$prefix = strtoupper($prefix);
			$exitKey = strtoupper($exitKey);
			$exitKeyLegend = strtoupper($exitKeyLegend);
		}

		$c ="";
		if ($prefix !== ""){
			$prefixes = explode(";",$prefix);
			foreach ($prefixes as $index=>$prefix){
				$c .= $this->getScreenCodeFormattedKeyLegend($prefix)." ";
				//in case that arrow left is used in prefix, we get rid of it
				//in context of F5
				if (substr($prefix,0,1) === $arrowLeft ){
					$arrowLeft = "";
					$f5back = ltrim( $f5back,"/");
				}
			}
		}
		$c .= $this->getScreenCodeFormattedKeyLegend($arrowLeft.$f5back);
		if (!$this->isClientWiC64())
		{
			$minLength = strlen($c) + strlen($exitKey) +1;
			if ( $minLength < 38){
				if ( $minLength + strlen($exitKeyLegend) > 28){
					$exitKeyLegend = $uppercase ? " EXIT":" Exit";
				}
				$c .= " " .$this->getScreenCodeFormattedKeyLegend($exitKey.$exitKeyLegend);
			}
		}
		$color = $this->nativeTEDColorsFor264 ? "7E": $color;

		$this->addCenteredScreenCodeChunkY($c, $y, $color);
	}

	protected function drawTitleBar( $section )
	{
		$color = $this->nativeTEDColorsFor264 ? "7B": "7";
		$this->addNormalChunkXY($section, 1, 0, $color);
	}

	protected function drawHorizontalLine( $y )
	{
		$color = $this->nativeTEDColorsFor264 ? "1E": "B";
		$this->addCharRepeatChunk(chr(96),   $y*40, 40, $color);
	}

	protected function getScreenCodeFormattedKeyLegend($keyLegend){
		$firstBlank = strpos($keyLegend," ",1);
		$result = "";
		for ($z=0; $z < $firstBlank; $z++){
			$char = substr($keyLegend,$z,1);
			$inverse = $char == "/" ? 0 : 128;
			$result .= chr($this->asciiChar2ScreencodeOrd($this->isCharsetLowerCase(), $char) + $inverse);
		}
		$result.= $this->asciiString2Screencode(substr($keyLegend, $firstBlank));
		return $result;
	}

	protected function sktpToUpper($str){
		return strtoupper($str);
	}

	public function drawBox($x, $y, $width, $height, $color ="3", $inverse = false, $rounded = false){
		$i = $inverse ? 128:0;
		if ($width < 2) die ("width is smaller than 2!");
		if ($height < 2) die ("width is smaller than 2!");
		if ($rounded)
			$corners = array( 108, 123, 124, 126);
		else
			$corners = array( 176-64, 174-64, 173-64, 189-64);

		$horizline = str_repeat(chr(64+$i),$width-2);
		$this->addScreenCodeChunkXY( chr($corners[0]+$i) . $horizline . chr($corners[1]+$i) , $x, $y, $color);
		$this->addScreenCodeChunkXY( chr($corners[2]+$i) . $horizline . chr($corners[3]+$i) , $x, $y + $height-1, $color);
		if ($height > 2){
			$this->addVerticalScreenCodeCharChunk(chr(93+$i), $x + 40*($y+1)          , $height-2, $color);
			$this->addVerticalScreenCodeCharChunk(chr(93+$i), $x + 40*($y+1) +$width-1, $height-2, $color);
		}
	}

	public function drawBoxFull($x, $y, $width, $height, $color ="3", $rounded = false){
		if ($width < 2) die ("width is smaller than 2!");
		if ($height < 2) die ("width is smaller than 2!");

		if ($rounded)
			$corners = array( 108, 123, 124, 126);
		else
			$corners = array( 160, 160, 160, 160);

		$block = str_repeat(chr(160),$width-2);
		$this->addScreenCodeChunkXY( chr($corners[0]) . $block . chr($corners[1]) , $x, $y, $color);
		$this->addScreenCodeChunkXY( chr($corners[2]) . $block . chr($corners[3]) , $x, $y + $height-1, $color);
		if ($height > 2){
			$this->addVerticalScreenCodeCharChunk($block.chr(160).chr(160), $x + 40*($y+1), $height-2, $color);
		}
	}

	protected function getFilename( $url){
		return $this->getLastPart( $url, "/" );
	}

	protected function getExtension( $url ){
		return $this->getLastPart( $url, "." );
	}

	private function getLastPart( $url, $char ){
		if ( strlen($url) === 0 ){
			return "";
		}
		$chunks = explode( $char, $url );
		if ( count($chunks) < 2 ){
			return $url;
		}
		return end($chunks);
	}

	protected function getFileAppType( $filename )
	{
		$extension = strtolower($this->getExtension( $filename ));
		if ($extension ==="prg" ||
				$extension ==="sid" ||
				$extension ==="crt" ||
				$extension ==="d64"
		){
			if ($this->isClientWiC64() && $extension !== "prg" && $extension !== "sid")
				return false;
			return "standard";
		}
		else if (
				$extension ==="mod" ||
				$extension ==="wav" ||
				$extension ==="ym"
		)
			return ($_SESSION["sktpv"] > 3) ? "audioplayer" : false; //unscharfes messer
		else if (
				$extension ==="it" ||
				$extension ==="xm"
		)
			return ($_SESSION["sktpv"] > 4) ? "audioplayer" : false;
		else if (
				$extension ==="7z" ||
				$extension ==="rar" ||
				$extension ==="gz" ||
				$extension ==="tgz" ||
				$extension ==="bz2" ||
				$extension ==="tar" ||
				$extension ==="zip"
		)
			return "archive";
		return false;
	}

	protected function buildDownloadCommand( $url, $filename, $save ){
		$save = $_SESSION["sktpv"] > 2 ? $save : true; //backward compatibility
		$fullscreen = "";
		$urllength = strlen($url);
		$filenamelength = strlen($filename);
		if ( $urllength <= 255 && $filenamelength <= 255 )
		{
			if ( $this->getFileAppType( $filename ) !== false )
				$fullscreen = chr(2). chr($urllength) . chr($filenamelength). chr($save?1:0). $url . $filename;
			else
				$this->renderNoLaunchErrorMsg(array($this->getExtension($filename)),20,2);
		}
		return $fullscreen;
	}

	//this is only used by rss feed download code
	protected function handleDownloadSimple($download, $save = true){
		$retVal = false;
		$save = $_SESSION["sktpv"] > 2 ? $save : true; //backward compatibility
		switch ($this->getFileAppType($download)){
			case "standard":
			case "audioplayer":
				list($dlLink,$filename) = $this->encodeDownloadLinkSimple($download);
				$fullscreen = $this->buildDownloadCommand(
					$dlLink,
					$filename,
					$save );
				if ($fullscreen !== "")
				{
					print $fullscreen;
				}
				break;
			/*
			case "archive":
				$_SESSION["screen"] = "archive";
				$_SESSION["csdb_archive_name"] = $download;
				$_SESSION["csdb_archive_type"] = strtolower($this->getExtension($download));
				$retVal = true;
				break;
			*/
			default:
				$this->renderNoLaunchErrorMsg(array($this->getExtension($download)),20,2);
				break;
		}
		return $retVal;
	}

	protected function renderNoLaunchErrorMsg($nolaunch, $x, $y){
		$this->enforceScreenUpdate();
		$this->addNormalChunkXY(
			"Can't launch " . implode(",",$nolaunch) ,$x, $y, "A");
		$this->oScreen->print();
	}

	public function encodeDownloadLinkSimple($x){
		$retval = array();
		$doc = trim($x);
		$chunks = explode("/", $x);
		$filename = urldecode($chunks[count($chunks)-1]);
		$retval = array($x, $filename);
		return $retval;
	}

	public function renderPetsciiBackgroundTemplate( $title, $rows, $cstart = 16, $cend = 19, $legend = false ){
		$title_justify = intval((40-strlen($title))/2);
/*
		$bgchar = $_SESSION["type"] == 64 ? chr(127):chr(127);
		$bgchar3 = str_repeat($bgchar,3);
		$bgchar6 = $bgchar3 . $bgchar3;
		//$this->enforceClearScreen();
		$this->addCharRepeatChunk($bgchar,   5, 255, "3");
		$this->addCharRepeatChunk($bgchar, 260, 255, "3");
		$this->addCharRepeatChunk($bgchar, 520, 255, "3");
		$this->addCharRepeatChunk($bgchar, 775, 225, "3");
		$this->addNormalChunkXY  ($bgchar3,  0,   0, "B");
		for ($y = 0; $y < $rows; $y++ ){
				$this->addNormalChunkXY( $bgchar,   3, $y, "C").
				$this->addNormalChunkXY( $bgchar,   4, $y, "F").
				$this->addNormalChunkXY( $bgchar,  35, $y, "F").
				$this->addNormalChunkXY( $bgchar,  36, $y, "C").
				$this->addNormalChunkXY( (($y == $rows -1 ) ? $bgchar3 : $bgchar6) , 37, $y, "B");
		}
		$this->addCharRepeatChunk("-", 40 * 1, 40, "2");
		$this->addCharRepeatChunk(" ", 40 * 2, 40, "2");
		$this->addCharRepeatChunk("-", 40 * 3, 40, "2");
		$this->addCharRepeatChunk(" ", 40 *  5 + 6, 28, "7");
		for ($y = $cstart; $y <= $cend; $y++ ){
			$this->addCharRepeatChunk(" ", 40 * $y + 6, 28, "7");
		}
		$this->addCharRepeatChunk(" ", 40 * 21 + 6, 28, "7");
		$this->addCharRepeatChunk(" ", 40 * 22 + 6, 28, "7");
*/
		$this->addNormalChunkXY( $title, $title_justify, 2, "7");
		if ($legend !== false)
			if ( $title !== "Welcome") //hack to have no legend on first page
				$this->addCenteredF5F7ChunkY(22,"7");
		else{
			foreach ($legend as $index => $part)
				$legend[$index] = $this->getScreenCodeFormattedKeyLegend( $part );
			$this->addCenteredScreenCodeChunkY(implode(" ",$legend ), 22,"7");
		}

		//$this->addCharRepeatChunk(" ", 40 * 23 + 6, 28, "7");

		//$this->addCharRepeatChunk(" ", $rows * 40, 40, "3");
	}
}
?>