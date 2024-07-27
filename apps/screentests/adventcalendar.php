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

class adventcalendar extends sktpBaseScreen{

	private
		$controller,
		$theme,
		$cheatMode;

	function __construct( $controller ){
		parent::__construct( false );
		$this->registerSessionVar( "focus", 12);
		$this->registerSessionVar( "focus_old", 12);
		$this->registerSessionVar( "doors_opened", array());
		$this->registerSessionVar( "theme", "snow");
		$this->registerSessionVar( "doors", 0);
		$this->controller = $controller;
		$this->cheatMode = date("z") < 320;

		$this->setTheme();

		if ($this->getSessionVar("doors") === 0){
			$sdDays = range(1,9);
			$ddDays = range(10,24);
			$xsum = 0;
			$doordata = array();
			for ($d = 0; $d <24; $d++){

				//widths
				if ($d % 8 == 0){
						$widths = array(4, 3, 4, 5, 4, 5, 4, 4 );
						$xsum = 0;
				}

				$c = count($widths);
				$r = random_int(0,$c-1);
				$w = $widths[$r];
				$widths[$r] = $widths[$c-1];
				array_pop($widths);

				//single digit width door?
				$n = 0;
				if ($w%2>0 ){//boxes with uneven char width get single digits
					$x = random_int(0, count($sdDays)-1);
					$n = $sdDays[$x];
					$sdDays[$x] = $sdDays[count($sdDays)-1];
					array_pop($sdDays);
				}

				//double digit days
				if ($n === 0){
					$c = count($ddDays);
					$r = random_int(0,$c-1);
					$n = $ddDays[$r];
					$ddDays[$r] = $ddDays[$c-1];
	 				array_pop($ddDays);
				}

				$doordata[] = array( "number" => $n, "width" => $w, "xpos" => $xsum, "yoffset" => 0 );
				$xsum += $w +1;
			}

			//yoffset - shake it!
			for ($x = 0; $x <8; $x++){
				$y = random_int(0,1);
				$dir = random_int(0,1) ? -1:1;
				$doordata[$x+$y*8]["yoffset"] = $dir;
				if ( $y == 0)
				{
					$dir = random_int(0,1) ? -1:1;
					$doordata[$x+16]["yoffset"] = $dir;
				}
			}
			$this->setSessionVar( "doors", $doordata);
		}

		$this->csdbSurprise = array(
				187535, //01 the night it snowed
				151259, //02 winter is coming
				126396, //03 christmas spirit
				160805, //04 IK Fujiyama In The Snow
				195872, //05 WinterMage and SnowBloodMan
				198381, //06 Babbo Maiale
				173062, //07 Genesis Christmas Demo 2018
				153516, //08 winter girl
				152315, //09 bonzai christmassy
				 52412, //10 Seasons Greetings Original Commodore Demo D64
				198279, //11 Hyperborea
				9999991, //12 divert to hvsc browser
			 	183752, //13 https://csdb.dk/release/?id=183752

				198284, //14 Xmas 2020 Is Here! (Last Christmas Remix)   [2020]
				198278, //15 I want to believe Atlantis
				198287, //16 Gloggdata
				198280, //17 Fatzone XMAS Demo 2020

			 	//geht nicht 126452, //18 Xmas Petscii Demo   [2013]

			 	125975, //18 Christmas (Graphics
				184428, //19  santa 2019

//geht nicht			 	198362, //19 Xmas Santas Hokuto Night Slideshow
				103785, //20 Christmas 2011, scrolltext lagweilig
				212283, //21 Merry Twistmas
				212282, //22 Dutch White
				212295, //23
				212312 //snowman prg

//113365 santa leaving factory
//114286 santa can fly
//184428 santa 2019

		);
	}

	private function setTheme(){
		$themes = array(
			"snow" => array(
				"borderColor" => 3,
				"bgcolor" => 3,
				"doorColor" => 1,
				"selectionColor" => 7,
				"alreadyOpenColor" => 2,
				"fontColor" => 2,
				"roundedCorners" => true
			),
			"classic" => array(
				"borderColor" => 2,
				"bgcolor" => 2,
				"doorColor" => 5,
				"selectionColor" => 8,
				"alreadyOpenColor" => 9,
				"fontColor" => 7,
				"roundedCorners" => false
			),
			"tuxedo" => array(
				"borderColor" => 0,
				"bgcolor" => 0,
				"doorColor" => 4,
				"selectionColor" => 1,
				"alreadyOpenColor" => 3,
				"fontColor" => 3,
				"roundedCorners" => false
			)
		);
		$this->theme = $themes[ $this->getSessionVar("theme")];
	}

	public function renderCompleteScreen(){
		$this->enforceClearScreen();
		$this->addColorCharsetChunk(
				$this->theme["borderColor"], //border color
				$this->theme["bgcolor"], //background color
				true //lower case charset / upper case charset
		);
		$this->addNormalChunkXY( "Advent Calendar", 13, 0, $this->theme["fontColor"]);
		$this->addCenteredF5F7ChunkY(23,$this->theme["fontColor"]);

		$this->addCenteredScreenCodeChunkY(
				$this->getScreenCodeFormattedKeyLegend("SPACE Open Door")." ".
				$this->getScreenCodeFormattedKeyLegend("T Theme"),
				24,$this->theme["fontColor"]
		);

		for ($n = 0; $n <24; $n++)
			$this->paintBox($n);

		//$this->addNormalChunkXY("Theme:" . $this->getSessionVar("theme"), 0,0,"1");
		print $this->getCurrentScreen();
	}

	public function updateScreen(){
		$this->enforceScreenUpdate();
		$this->paintBox( $this->getSessionVar("focus"));
		$this->paintBox( $this->getSessionVar("focus_old"));
		//$this->addNormalChunkXY("Theme:" . $this->getSessionVar("theme"), 0,0,"1");

		print $this->getCurrentScreen();
	}

	private function paintBox( $n ){
		$y = intval($n/8);
		$door = $this->getSessionVar("doors")[$n];
		$open = in_array($door["number"], $this->getSessionVar("doors_opened"));
		if ( $n === $this->getSessionVar("focus"))
			$boxcolor = dechex($this->theme["selectionColor"]);
		else if ( $open ){
			$boxcolor = dechex($this->theme["alreadyOpenColor"]);
			$open = true;
		}
		else
			$boxcolor = dechex($this->theme["doorColor"]);

		$xpos = $door["xpos"];
		$width = $door["width"];
		if ($open){
			if (!$this->theme["roundedCorners"])
				$this->drawBoxFull($xpos+1, 3+$y*7+$door["yoffset"], $width-1, 5, $boxcolor, $this->theme["roundedCorners"]);
			else
				$this->drawBoxFull($xpos, 3+$y*7+$door["yoffset"], $width, 5, $boxcolor, $this->theme["roundedCorners"]);
			$this->addVerticalScreenCodeCharChunk(chr(160), $xpos + 40*(3+$y*7+$door["yoffset"]), 5, dechex($this->theme["doorColor"]));
		}
		else
			$this->drawBoxFull($xpos, 3+$y*7+$door["yoffset"], $width, 5, $boxcolor, $this->theme["roundedCorners"]);

		//number label on door
		$color = strval(dechex($boxcolor+128));
		$xoffset = intval(($door["width"]+1)/3);
		$this->addNormalChunkXY( $door["number"], $door["xpos"]+$xoffset, 3+$y*7+2+$door["yoffset"], $color);
	}

	public function handleKeypress($key, $enforceClear){
		$pageChange = false;
		if ($this->isScreenExitKeypress($key)){
			$this->controller->setStartScreen();
			return true; //screen has changed
		}
		else switch ($key) {
			case self::PETSCII_KEY["crsr_up"]:
				if ( $this->getSessionVar("focus") >7){
					$this->setSessionVar("focus_old", $this->getSessionVar("focus"));
					$this->decreaseSessionVar("focus",8);
					$this->updateScreen();
					return false;
				}
				break;
			case self::PETSCII_KEY["crsr_left"]:
				if ($this->getSessionVar("focus") % 8 != 0){
					$this->setSessionVar("focus_old", $this->getSessionVar("focus"));
					$this->decreaseSessionVar("focus",1);
					$this->updateScreen();
					return false;
				}
				break;
			case self::PETSCII_KEY["crsr_right"]:
				if (($this->getSessionVar("focus")+1) % 8 != 0){
					$this->setSessionVar("focus_old", $this->getSessionVar("focus"));
					$this->increaseSessionVar("focus",1);
					$this->updateScreen();
					return false;
				}
				break;
			case self::PETSCII_KEY["crsr_down"]:
				if ( $this->getSessionVar("focus") <16){
					$this->setSessionVar("focus_old", $this->getSessionVar("focus"));
					$this->increaseSessionVar("focus",8);
					$this->updateScreen();
					return false;
				}
				break;
			case self::PETSCII_KEY["t"]:
				$activeTheme = $this->getSessionVar("theme");
				if ($activeTheme === "snow" )
					$this->setSessionVar("theme", "classic");
				else if ($activeTheme === "classic" )
					$this->setSessionVar("theme", "tuxedo");
				else
					$this->setSessionVar("theme", "snow");
				$this->setTheme();
				$this->renderCompleteScreen();
				return false;
			case self::PETSCII_KEY["space"]:
			case self::PETSCII_KEY["return"]:
			case self::PETSCII_KEY["joystickbutton"]:
				$door = $this->getSessionVar("focus");
				$requestedDay = $this->getSessionVar("doors")[$door]["number"];
				if ($requestedDay > date("j") && !$this->cheatMode){
					$this->enforceScreenUpdate();
					print $this->getCurrentScreen();
					return false;
				}
				$doorsOpened = $this->getSessionVar("doors_opened");
				if ( !in_array($requestedDay, $doorsOpened)){
					$doorsOpened[] = $requestedDay;
					$this->setSessionVar("doors_opened", $doorsOpened);
				}
				$surpriseID = $this->csdbSurprise[$requestedDay-1];

				$_SESSION["jumpback_app"] = "screentests";
				$_SESSION["jumpback_screen"] = "adventcalendar";
				$_SESSION["application"] = "csdbBrowser";

				if ($surpriseID === 9999991){
					$_SESSION["screen"] = "hvscBrowser";
				}
				else{
					$_SESSION["csdbid"] = $surpriseID;
					$_SESSION["screen"] = "singleRelease";
				}
				return true; //screen has changed
			default:
					$this->updateScreen();
					return false;
		}
		$this->enforceScreenUpdate();
		print $this->getCurrentScreen();
		return $pageChange;
	}
}
?>
