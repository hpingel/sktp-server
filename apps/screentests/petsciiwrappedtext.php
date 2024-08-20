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

class petsciiwrappedtext extends sktpBaseScreen{

	private $controller;

	function __construct( $controller ){
		parent::__construct( false );
		$this->registerSessionVar( "exampleVar", 0);
		$this->controller = $controller;
	}

	public function renderCompleteScreen(){
		$this->enforceClearScreen();
		$this->addColorCharsetChunk(
				0, //border color
				0, //background color
				true //lower case charset / upper case charset
		);
		$this->drawTitleBar("Multiline text wrapping test");

		$text = "This is a long text that will be automatically wrapped and displayed unpadded in multiple lines. Wrapping ".
						"should take care of filling each line nicely with content.";
		$wt = new puiWrappedText( $this, 0, 1, 20, 14, $text, "border-color:6 font-color:3");
		$wt ->render();

		$text2 = "This is a long text that will be automatically wrapped and displayed padded in multiple lines. Wrapping ".
		"should take care of filling each line nicely with content.";

		$wt2 = new puiWrappedText( $this, 20, 1, 20, 15, $text2, "linePadded border-color:2 font-color:A");
		$wt2 ->render();
		$text3 = "This is a short text that will be wrapped automatically and displayed inverse.";
		$wt2 = new puiWrappedText( $this, 0, 15, 18, 8, $text3, "inverse font-color:1");
		$wt2 ->render();

		$text4 = "This is a short text that is padded + inverse.";
		$wt2 = new puiWrappedText( $this, 19, 16, 21, 7, $text4, "inverse padded font-color:1");
		$wt2 ->render();

		$this->addCenteredF5F7ChunkY(24);
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
		$this->updateScreen();
		return false;
	}
}
?>
