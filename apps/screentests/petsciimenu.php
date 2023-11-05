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

require_once('lib/puiList.php');

class petsciimenu extends sktpBaseScreen{

	private
		$controller,
		$list,
		$listactions;

	function __construct( $controller ){
		parent::__construct( false );
		$this->registerSessionVar( "exampleVar", 0);
		$this->controller = $controller;

		$listitems = array(
			"Advent Calendar" => "A",
//			"Graphics slideshow test" => "X",
			"Animation test" => "P",
			"Charset display test" => "C",
			"Multiline text wrap test" => "W",
			"Talking face" => "T",
			"VIC-II colors" => "V",
			"Presenter" => "O",
			"QR-Code test" => "Q",
			"Page skeleton" => "S",
			"Page skeleton with list" => "I"
		);

		$this->listactions = array(
			"A" => array( "screentests", "adventcalendar" ),
			"C" => array( "screentests", "petsciicharset"),
//			"X" => array( "screentests", "petsciislideshow"),
			"P" => array( "screentests", "petsciianimation"),
			"Q" => array( "screentests", "petsciiqrcode"),
			"T" => array( "screentests", "petsciiballoonface"),
			"W" => array( "screentests", "petsciiwrappedtext"),
			"S" => array( "screentests", "screenexample"),
			"I" => array( "screentests", "screenexample_with_list"),
			"O" => array( "screentests", "petsciipresenter"),
			"V" => array( "screentests", "vic2colors")
		);

		$this->list = new puiList( $this, 6, 9, 28, 9, 0, true);
		$type =""; $action="";$key="";

		$c = 1;
		foreach ($listitems as $key => $name)
		{
			$this->list->addListItem( $key, $type, $name, $name);
		}
	}

	public function renderCompleteScreen(){
		$this->enforceClearScreen();
		$this->addColorCharsetChunk(
				4, //border color
				4, //background color
				true //lower case charset / upper case charset
		);
		$this->drawTitleBar("PETSCII Experiments and Tests");
		$this->drawHorizontalLine( 1 );
		$this->list->renderCompleteList();
		//$this->addNormalChunkXY( "Hello world!", 13, 10, "2");
		//$this->addNormalChunkXY( "This is a skeleton for your own screen", 1, 12, "2");
		$this->drawHorizontalLine( 23 );
		$this->addCenteredF5F7ChunkY(24);

		print $this->getCurrentScreen();
	}

	public function updateScreen(){
		$this->enforceScreenUpdate();
		//in here, only update those parts that need to be updated
		print $this->getCurrentScreen();
	}

	public function handleKeypress($key, $enforceClear){
		$pageChange = false;
		if ( $this->list->handleKeypress($key)){
			$this->list->updateSelection();
			$this->enforceScreenUpdate();
			print $this->getCurrentScreen();
			return false;
		}
		else switch ($key) {
			case self::PETSCII_KEY["return"]:
				$listaction = $this->list->getSelectedAction();
				if ( $listaction !== "")
					return $this->handleListAction( $listaction );
				break;
			case self::PETSCII_KEY["a"]:
			case self::PETSCII_KEY["c"]:
			case self::PETSCII_KEY["x"]:
			case self::PETSCII_KEY["p"]:
			case self::PETSCII_KEY["q"]:
			case self::PETSCII_KEY["w"]:
			case self::PETSCII_KEY["t"]:
			case self::PETSCII_KEY["o"]:
			case self::PETSCII_KEY["v"]:
				return $this->handleListAction( chr(hexdec($key)) );
			case self::PETSCII_KEY["arrow_left"]:
			case self::PETSCII_KEY["F5"]:
				$this->setAppScreen("menu","welcome");
				return true; //screen has changed
			default:
					$this->updateScreen();
		}
		return $pageChange;
	}

	private function handleListAction( $listaction ){
		if (array_key_exists($listaction, $this->listactions)){
			$this->setAppScreen(
				$this->listactions[$listaction][0],
				$this->listactions[$listaction][1]
			);
			return true; //screen has changed
		}
		return false;
	}

}
?>
