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

class screenexample_with_list extends sktpBaseScreen{

	private
		$controller,
		$list;

	function __construct( $controller ){
		parent::__construct( false );
		$this->registerSessionVar( "exampleVar", 0);
		$this->controller = $controller;

		$listitems = array(
			"Menu item #1" => "mi1",
			"Menu item #2" => "mi2",
			"Menu item #3" => "mi3",
			"Menu item #4" => "mi4",
			"Menu item #5" => "mi5",
			"Menu item #6" => "mi6",
			"Menu item #7" => "mi7",
			"Menu item #8" => "mi8",
			"Menu item #9" => "mi9",
			"Menu item #10" => "mi10"
		);

		$this->list = new puiList( $this, 7, 16, 26, 5, 1);
		$type =""; $action="";$key="";

		$c = 1;
		foreach ($listitems as $key => $name)
		{
			$this->list->addListItem( $key, $type, $name, strval($c++));
		}
	}

	public function renderCompleteScreen(){
		$this->enforceClearScreen();
		$this->addColorCharsetChunk(
				11, //border color
				12, //background color
				true //lower case charset / upper case charset
		);
		$this->drawTitleBar("Example screen with list");
		$this->drawHorizontalLine( 1 );
		$this->drawBox(6,15,28,7,"1");
		$this->addNormalChunkXY( " Scroll list demo ", 11, 15, "1");
		$this->list->renderCompleteList();
		$this->addNormalChunkXY( "Hello world!", 13, 10, "2");
		$this->addNormalChunkXY( "This is a skeleton for your own screen", 1, 12, "2");
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
			case self::PETSCII_KEY["arrow_left"]:
			case self::PETSCII_KEY["F5"]:
				$this->controller->setStartScreen();
				return true; //screen has changed
			default:
					$this->updateScreen();
		}
		return $pageChange;
	}
}
?>
