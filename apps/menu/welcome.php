<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2023-2024 Henning Pingel
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

namespace apps\menu;

class welcome extends \lib\sktpBaseScreen{

	private 
		$list,
		$listactions,
		$controller;

	function __construct( $controller ){
		parent::__construct( false );
		$this->controller = $controller;

		if ($this->isClientOn264() && $this->isClientWiC64()){
			$listItems = array(
//				"Petscii Slidehow" => "S",
				"Petscii Experiments" => "P",
/*
				"RSS Reader" => "F",
				"Arena" => "A",
				"CSDb Launcher" => "C",
				"CSDb Remote Access (Kick)" => "R",
*/
				"System information" => "I"
			);
		}
		else if ($this->isClientWiC64()){
			$listItems = array(
/*
				"CSDb Launcher" => "C",
				"CSDb Remote Access (Kick)" => "R",
				"HVSC Browser" => "H",
*/
				"WiC64 Portal" => "W",
//				"Arena" => "A",				
				"Petscii Experiments" => "P",
/*				
				"RSS Reader" => "F",
				"Modarchive (Sidekick64)" => "M",
*/
				"System information" => "S"
			);
		}
		else
			$listItems = array(
/*			
				"CSDb Launcher" => "C",
				"CSDb Remote Access (Kick)" => "R",
				"HVSC Browser" => "H",
				"Modarchive" => "M",
				"RSS Reader" => "F",
				"Arena" => "A",
*/
				"Petscii Experiments" => "P",
				"System information" => "S",
//				"Download SKTP4WiC64" => "W"
			);
		$this->listactions = array(
/*		
			"C" => array( "csdbBrowser", "menu" ),
			"H" => array( "csdbBrowser", "hvscBrowser"),
			"M" => array( "modarchive", "modarchivemenu"),
			"F" => array( "rssFeeds", "rss2binMenu"),
			"A" => array( "arena", "nickname"),
*/			
			"P" => array( "screentests", "petsciimenu"),
			"S" => array( "menu", "systeminfo"),
//			"R" => array( "menu", "remoteaccess")
			//"M" => array( "multiscreendemo", "demo")
		);
//		$this->listactions["W"] = array( "menu", "wic64Portal");

		if ($this->isClientOn264() && $this->isClientWiC64()){
			$this->listactions = array(
/*
				"C" => array( "csdbBrowser", "menu" ),
				"S" => array( "csdbBrowser", "petsciislideshow"),
				"F" => array( "rssFeeds", "rss2binMenu"),
				"A" => array( "arena", "nickname"),
*/
				"P" => array( "screentests", "petsciimenu"),
				"I" => array( "menu", "systeminfo")
//				"R" => array( "menu", "remoteaccess")
			);
		}
		$this->list = new \pui\ScrollableList( $this, 6, 9, 28, count($this->listactions), 0,true);
		$type =""; $action="";$key="";

		$c = 1;
		foreach ($listItems as $key => $name)
		{
			$this->list->addListItem( $key, $type, $name, $name);
		}
	}

	public function renderCompleteScreen(){
		$this->enforceClearScreen();
		//FIXME get rid of template
		$this->renderPetsciiBackgroundTemplate("Welcome",25, 5, 19,
			($this->isClientWiC64() ? false : 
				( $this->isClientOn264() ? 
					array("HELP Sidekick menu"): array("F7 Sidekick menu")
				)
			) 	
		);
		$x=6;

		if (isset($_SESSION["errorMSG"]) && $_SESSION["errorMSG"] !== ""){
			$this->addNormalChunkXY( "Error: " . $_SESSION["errorMSG"], 5, 4, "2");
			$_SESSION["errorMSG"] = "";
		}

		if ($_SESSION["sktpv"] < 2)
		{
			$this->addNormalChunkXY( "Warning", $x, 9, "1");
			$this->addNormalChunkXY( "Your Sidekick64 network",$x, 10, "7");
			$this->addNormalChunkXY( "kernel is too old to use", $x, 11, "7");
			$this->addNormalChunkXY( "this SKTP server. Please", $x, 12, "7");
			$this->addNormalChunkXY( "update your network kernel!", $x, 13, "7");
		}
		else
		{
			$this->addNormalChunkXY( "Main menu", $x, 7, "1");
			$this->list->renderCompleteList();
		}
		$this->addNormalChunkXY( "User: " .$_SESSION["username"], $x, count($this->listactions)+11, "7");

		/*
		$this->addNormalChunkXY( "SKTP client version: " .$_SESSION["sktpv"] ." smlt:" . intval(ini_get("session.gc_maxlifetime")/60), $x, 16, "7");
		if ($_SESSION["password"] !== "")
			$this->addNormalChunkXY( "Password was sent.", $x, 17, "7");

		$this->addNormalChunkXY( "Machine type: " .$_SESSION["type"], $x, 18, "7");
		*/
		$this->addColorCharsetChunk(0,0,true);

		$this->oScreen->print();
	}

	public function handleKeypress($key, $enforceClear){
		$pageChange = false;

		if ( $this->list->handleKeypress($key)){
			$this->list->updateSelection();
			$this->enforceScreenUpdate();
			$this->oScreen->print();
			return false;
		}
		else switch ($key) {
			case self::PETSCII_KEY["return"]:
					$listaction = $this->list->getSelectedAction();
					if ( $listaction !== "")
						return $this->handleListAction( $listaction );
					break;
			case self::PETSCII_KEY["c"]:
			case self::PETSCII_KEY["h"]:
			case self::PETSCII_KEY["s"]:
			case self::PETSCII_KEY["f"]:
			case self::PETSCII_KEY["p"]:
			case self::PETSCII_KEY["a"]:
			case self::PETSCII_KEY["m"]:
			case self::PETSCII_KEY["r"]:
			case self::PETSCII_KEY["w"]: //wic64 portal
				return $this->handleListAction( chr(hexdec($key)) );
			default:
				if ( $enforceClear )
				{
					$this->renderCompleteScreen();
				}
				else{
					$this->enforceScreenUpdate();
					$this->oScreen->print();
				}
		}
		return $pageChange;
	}

	private function handleListAction( $listaction ){
		if (array_key_exists($listaction, $this->listactions)){
			$this->list->focusEntry($listaction);
			$this->setAppScreen(
				$this->listactions[$listaction][0],
				$this->listactions[$listaction][1]
			);
			$_SESSION["jumpback_app"] = "menu";
			$_SESSION["jumpback_screen"] = "welcome";
			return true; //screen has changed
		}
		return false;
	}

}
?>
