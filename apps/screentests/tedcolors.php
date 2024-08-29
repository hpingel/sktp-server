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

class tedcolors extends sktpBaseScreen{

	private $controller,
		$pc;

	function __construct( $controller ){
		parent::__construct( 
			false, //debugmode 
			"", //suffix
			true //native ted colors
		);
		$this->controller = $controller;
	}

	public function renderCompleteScreen(){
		$this->enforceClearScreen();

		if ( !$this->isClientOn264()){
				$this->addColorCharsetChunk(
					0, //border color
					0, //background color
					true //lower case charset / upper case charset
			);
			$this->drawTitleBar("           *** TED COLORS ***");
			$this->addNormalChunkXY( "This screen only works on Commodore 16, 116 and Plus/4", 1, 10, "2");
			$this->addCenteredF5F7ChunkY(24,"1");
		}
		else{
			$this->addColorCharsetChunk(
				0, //border color
				0, //background color
				false //lower case charset / upper case charset
			);
			$this->drawTitleBar("           *** TED COLORS ***");
			$y = 6;
			$height = 2;
			$order = array( 7,14,4,13,6,16,11,8,10,9,3,12,5,15,2,1 );
			$boxline = chr(32). str_repeat(chr(120+127).chr(81+127),19) . chr(32);
			$boxline .= chr(32).str_repeat(chr(33+127).chr(104+127),19) . chr(32);
			$this->addScreenCodeChunk(
				str_repeat($boxline, 4*$height),
				40*$y-80,
				"0"
			);
			
			for ( $a=0 ; $a < 15; $a++){
				for ( $l=0 ; $l < 8; $l++){
					$this->addPaintBrushChunk(
						str_repeat(chr($order[$a]-1+(16*$l)),$height),
						$y*40+$a*$height +5+ ($l*80)-80,
						$height,
						40-$height);
				}
			}
			//$this->drawBox(4,$y-3,32,2+8*$height,"1");
			$this->addCenteredF5F7ChunkY(24,"E1", "", true);
		}
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