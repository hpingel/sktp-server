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

namespace apps\screentests;

class vic2colors extends \lib\sktpBaseScreen{

	private $controller,
        $pc;

	function __construct( $controller ){
		parent::__construct( false );
		$this->controller = $controller;
	}

	public function renderCompleteScreen(){
        $this->enforceClearScreen();
		$this->addColorCharsetChunk(
				0, //border color
				0, //background color
				true //lower case charset / upper case charset
		);
		$this->drawTitleBar("        *** VIC-II  Colors ***");

        for ($z=0; $z<4; $z++){
            $this->addVerticalScreenCodeCharChunk(chr(233), 40+2*$z, 12, "7" );
            $this->addVerticalScreenCodeCharChunk(chr(160), 41+2*$z, 12, "7" );
            $this->addVerticalScreenCodeCharChunk(chr(233), 41+32+2*$z, 12, "7" );
            $this->addVerticalScreenCodeCharChunk(chr(160), 41+31+2*$z, 12, "7" );
        }

        //white 2 gray left
		$this->addPaintBrushChunk(
			chr(11).chr(11).
            chr(12).chr(12).
            chr(15).chr(15).
            chr(1).chr(1),
			40, 2, 32
        );

        //white 2 gray right
        $this->addPaintBrushChunk(
            chr(1).chr(1).
            chr(15).chr(15).
            chr(12).chr(12).
            chr(11).chr(11),
            72, 2, 32
        );

        //blue tones
		$this->addPaintBrushChunk(
            chr(6).chr(6).
            chr(4).chr(4).
            chr(14).chr(14).
            chr(3).chr(3),
			160, 2, 32
        );

        //blue tones right
		$this->addPaintBrushChunk(
            chr(3).chr(3).
            chr(14).chr(14).
            chr(4).chr(4).
            chr(6).chr(6),
			192, 2, 32
        );

        //brown / red tones
		$this->addPaintBrushChunk(
			chr(9).chr(9).
            chr(2).chr(2).
            chr(8).chr(8).
            chr(10).chr(10),
			280, 2, 32
        );

        //brown / red tones right
		$this->addPaintBrushChunk(
            chr(10).chr(10).
            chr(8).chr(8).
            chr(2).chr(2).
			chr(9).chr(9),
			312, 2, 32
        );

        //yellow/green tones
		$this->addPaintBrushChunk(
			chr(5).chr(5).
            chr(3).chr(3).
            chr(13).chr(13).
            chr(7).chr(7),
			400, 4, 32
        );

        //yellow/green tones right
		$this->addPaintBrushChunk(
            chr(7).chr(7).
            chr(13).chr(13).
            chr(3).chr(3).
			chr(5).chr(5),
			432, 4, 32
        );
        
        $y = 15;
        $height = 4;
        $this->addScreenCodeChunk(str_repeat(chr(160), 80*$height), 40*$y,"0" );
        for ( $a=0 ; $a < 16; $a++){
            $this->addPaintBrushChunk(
                str_repeat(chr($a),$height),
                $y*40+$a*$height +4+ ($a>7? 128:0), ($height-1), 40-$height);
        }
        $this->drawBox(3,$y-1,34,2+2*$height,"F");       
		$this->addCenteredF5F7ChunkY(24,"F");
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