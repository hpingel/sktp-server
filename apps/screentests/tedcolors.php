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

class tedcolors extends sktpBaseScreen{

	private $controller,
        $pc;

	function __construct( $controller ){
		parent::__construct( false );
		$this->controller = $controller;
        $this->allowNativeTEDColors();
	}

	public function renderCompleteScreen(){
        $this->enforceClearScreen();
		$this->addColorCharsetChunk(
				0, //border color
				0, //background color
				true //lower case charset / upper case charset
		);
		$this->drawTitleBar("           *** TED Colors ***");

        if ( !$this->isClientOn264()){
            $this->addNormalChunkXY( "This screen only works on Commodore 16, 116 and Plus/4", 1, 10, "2");
            $this->addCenteredF5F7ChunkY(24,"1");
        }
        else{
            $y = 6;
            $height = 2;
            $this->addScreenCodeChunk(str_repeat(chr(160), 80*$height*2), 40*$y,"0" );
            for ( $a=0 ; $a < 16; $a++){
                for ( $l=0 ; $l < 8; $l++){

                $this->addPaintBrushChunk(
                    str_repeat(chr($a+16*$l),$height),
                    $y*40+$a*$height +4+ ($l*40), ($height-1), 40-$height);
                }
            }
            $this->drawBox(3,$y-1,34,2+4*$height,"1");
            $this->addCenteredF5F7ChunkY(24,"E1");
        }
		print $this->getCurrentScreen();
	}

	public function updateScreen(){
		$this->enforceScreenUpdate();
		//in here, only update those parts that need to be updated
		print $this->getCurrentScreen();
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