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

class systeminfo extends sktpBaseScreen{

	function __construct( $controller ){
		parent::__construct( false );
		$this->controller = $controller;
	}

	public function renderCompleteScreen(){
		$sktpserver = $_SERVER["SERVER_NAME"];//. ":". $_SERVER["SERVER_PORT"];
		$this->enforceClearScreen();
		$this->renderPetsciiBackgroundTemplate("System info",25, 6, 19);

		$x=7;$y = 6;

		$this->addNormalChunkXY( "SKTP Server", $x, $y++, "1");
		$this->addNormalChunkXY( $sktpserver, $x, $y++, "7");
		//$this->addNormalChunkXY( "Hostname: " . $sktpserver, 3, 3, "7");
		$y++;
		$this->addNormalChunkXY( "User: " .$_SESSION["username"], $x, $y++, "7");
		$this->addNormalChunkXY( "SKTP client version: " .$_SESSION["sktpv"], $x, $y++, "7");
//		$this->addNormalChunkXY( "Session lifetime: " . intval(ini_get("session.gc_maxlifetime")/60)." mins", $x, $y++, "7");
//		$this->addNormalChunkXY( "Session age: " . (time() - $_SESSION["startedAt"]) . " seconds", $x, $y++, "7");

		if ($_SESSION["password"] !== "")
			$this->addNormalChunkXY( "Password was sent.", $x, $y++, "7");

		$this->addNormalChunkXY( "Machine type: " .$_SESSION["type"], $x, $y++, "7");

//    $y++;

		$imv = "-";
		if ( extension_loaded('imagick') )
		{
			$v = Imagick::getVersion();
			preg_match('/ImageMagick ([0-9]+\.[0-9]+\.[0-9]+)/', $v['versionString'], $v);
			$imv = $v[1];
		}
		$this->addNormalChunkXY( "PHP MBString    : " . (extension_loaded('mbstring')?"OK":"-"), $x, $y++, "7");
		$this->addNormalChunkXY( "PHP SimpleXML   : " . (extension_loaded('simplexml')?"OK":"-"), $x, $y++, "7");
		$this->addNormalChunkXY( "PHP DOM         : " . (extension_loaded('dom')?"OK":"-"), $x, $y++, "7");
		$this->addNormalChunkXY( "PHP libXML      : " . (extension_loaded('libxml')?"OK":"-"), $x, $y++, "7");
		$this->addNormalChunkXY( "PHP PDO SQLite  : " . (extension_loaded('pdo_sqlite')?"OK":"-"), $x, $y++, "7");
		$this->addNormalChunkXY( "PHP Zip         : " . (extension_loaded('zip')?"OK":"-"), $x, $y++, "7");
		$this->addNormalChunkXY( "PHP ImageMagick : " . $imv, $x, $y++, "7");

		$this->addCenteredF5F7ChunkY(24);
		$this->addColorCharsetChunk(0,0,true);
		$this->oScreen->print();
	}

	public function handleKeypress($key, $enforceClear){
		if ($this->isScreenExitKeypress($key)){
			$this->controller->setStartScreen();
			return true; //screen has changed
		}
		else if ( $enforceClear ){
			$this->renderCompleteScreen();
		}
		else{
			$this->enforceScreenUpdate();
			$this->oScreen->print();
		}
		return false;
	}
}
?>