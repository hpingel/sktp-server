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

require_once('lib/puiWrappedText.php');

class petsciipresenter extends sktpBaseScreen{

	private $controller,
        $pc;

	function __construct( $controller ){
		parent::__construct( false );
		$this->controller = $controller;
        $this->pc = [
            0 => [ 
                "title" => "\nHELLO WORLD",
                "body" => 
                    "This is a test paragraph that contains a few words of meaningless text to entertain those who like to read the whole sentence.\n\n".
                    " * Let's try this out.\n\n".
                    " * And also this.\n\n".
                    " * And this."
            ]
        ];
		$this->registerSessionVar( "currentPage", 0);
	}

	public function renderCompleteScreen(){
        $page = $this->pc[$this->getSessionVar( "currentPage")];
        $this->enforceClearScreen();
		$this->addColorCharsetChunk(
				0, //border color
				0, //background color
				true //lower case charset / upper case charset
		);

		$wt = new puiWrappedText( 
            $this, 0, 1, 40, 3, 
            $page["title"], 
            "centered font-color:7 borderless inverse");
		$wt ->render();
		$wt = new puiWrappedText(
            $this, 0, 5, 40, 17, 
            $page["body"], 
            "font-color:7 borderless ");
		$wt ->render();

		$this->addPaintBrushChunk(
			chr(3).chr(5).chr(7).chr(5),
			1*40, 29, 0);

//		$this->addCenteredF5F7ChunkY(24);
		$this->oScreen->print();
	}

	public function updateScreen(){
		$this->enforceScreenUpdate();
		//in here, only update those parts that need to be updated
		$this->oScreen->print();
	}

	public function handleKeypress($key, $enforceClear){
		if ($this->isScreenExitKeypress($key)){
			$this->controller->setStartScreen();
			return true; //screen has changed
		}
		else switch ($key) {
			default:
				$this->updateScreen();
		}
		return false;
	}
}
?>