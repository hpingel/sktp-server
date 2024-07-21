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


class wic64Portal extends sktpBaseScreen{

	function __construct( $controller ){
		parent::__construct( false );
		$this->controller = $controller;
	}

	public function renderCompleteScreen(){
//		$this->enforceClearScreen();
		$x=7;$y = 6;

//		$this->addNormalChunkXY( "Launching WiC64 Portal...", $x, $y++, "1");
//		$this->addNormalChunkXY( $sktpserver, $x, $y++, "7");
//		$this->addColorCharsetChunk(0,0,true);

		if ($this->isClientWiC64()){
	        $url = "http://x.wic64.net/menue.prg";
			$dl = false;
		}
		else{
			$dl = true;
			$url = $this->getURLToThisServer( "sktp4wic64/sktp-v0.24.prg" );
		}
        $this->controller->setStartScreen();
        return $this->handleDownloadSimple($url, $dl); //don't save on SD

//		print $this->getCurrentScreen();
	}

    //duplicate from kick.php, FIXME, move this to other location
    private function getURLToThisServer( $suffix ){
        $httpStyle = array_key_exists("HTTPS",$_SERVER) ? "https" : "http";
        $isStandardPort = $_SERVER["SERVER_PORT"] == 80 || $_SERVER["SERVER_PORT"] == 443;
        $port = $isStandardPort ? "" : ":". $_SERVER["SERVER_PORT"];
        return $httpStyle . "://".$_SERVER["SERVER_NAME"]. $port."/" . $suffix;
    }

	public function handleKeypress($key, $enforceClear){
		if ($this->isScreenExitKeypress($key)){
			$this->controller->setStartScreen();
			return true; //screen has changed
		}
		else if ( $enforceClear )
		{
			$this->renderCompleteScreen();
		}
		else{
			$this->enforceScreenUpdate();
			print $this->getCurrentScreen();
		}
		return false;
	}
}
?>
