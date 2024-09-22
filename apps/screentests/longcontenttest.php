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

class longcontenttest extends \lib\sktpBaseScreen{

	private $controller;

	function __construct( $controller ){
		parent::__construct( false );
		$this->controller = $controller;
		$this->registerSessionVar( "clength", 1);
	}

	public function renderCompleteScreen(){
		$this->enforceClearScreen();
		$this->addColorCharsetChunk(
				2, //border color
				0, //background color
				true //lower case charset / upper case charset
		);
		$this->drawTitleBar("Long content test screen");
		$this->drawHorizontalLine( 1 );
        $this->updateContent();
		$this->drawHorizontalLine( 23 );
		$this->addCenteredF5F7ChunkY(24,"7","CRSR-Right Next");
		$this->oScreen->print();
	}

	public function updateScreen(){
		$this->enforceScreenUpdate();
        $this->updateContent();
        $this->oScreen->print();
	}

    private function updateContent(){
        $c = $this->getSessionVar("clength");
        if ($c== 0) $c=1;
        if ($c> 1000) $c=1;
        $d=$c+1;
        $offset = 80;
		$this->addNormalChunkXY( "Length: ".$d." " , 1, 22, "2");
        $ls = str_repeat(chr(rand(65,90)),$d);
        $this->addScreenCodeChunk(
            $ls,
            $offset,
            "1"
        );
        $this->addScreenCodeChunk(
            chr(30),
            $d+$offset+39,
            "2"
        );
        if ($c > 800 )
            $this->setSessionVar("clength", 1);
        else if ($c < 512 )
            $this->setSessionVar("clength", $c *2);
        else
            $this->setSessionVar("clength", $c +32);
    }

	public function handleKeypress($key, $enforceClear){
		if ($this->isScreenExitKeypress($key)){
			$this->controller->setStartScreen();
			return true; //screen has changed
		}
		else switch ($key) {
			case self::PETSCII_KEY["a"]:
            case self::PETSCII_KEY["crsr_right"]:
                $this->updateScreen();
            break;
        }
        return false;
	}
}
?>