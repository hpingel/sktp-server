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

class petsciicharset extends sktpBaseScreen{

	private $controller;

	function __construct( $controller ){
		parent::__construct( false );
		$this->registerSessionVar( "lowercase", true );
		$this->controller = $controller;
	}

	public function renderCompleteScreen(){
		$lc = $this->getSessionVar("lowercase");
		$this->setCase($lc);
		$this->enforceClearScreen();
		$this->addColorCharsetChunk(0, 0, $lc);
		$title = "Screen test (Charset: ". ($lc ? "Mixed case" : "Upper case + GFX").")";
		if (!$lc)
			$title = strtoupper($title);
		$this->drawTitleBar($title);

		$this->drawHorizontalLine( 1 );

		$y = $this->renderCharBlock(3, 32, 0);
		$y = $this->renderCharBlock($y, 64, -64);
		$y = $this->renderCharBlock($y, 96, -32);
		$y = $this->renderCharBlock($y, 160, -64);

		$this->drawHorizontalLine( 23 );
		$this->addCenteredF5F7ChunkY(24,"7", "T Toggle Case",!$lc);

		$this->drawBox(34, 2, 2, 2);
		$this->drawBox(34, 4, 3, 3);
		$this->drawBox(34, 7, 4, 4);
		$this->drawBox(34, 11, 5, 5);
		$this->drawBox(34, 16, 6, 6);

		print $this->getCurrentScreen();
	}

	private function renderCharBlock($y, $start, $scrCodeOffset){
		$reverse = strval(dechex(hexdec(strtolower("5"))+128));
		$cnt = "";
		$scrcodeline = "";
		$stop = $start+31;
		for ($z = $start; $z<= $stop; $z++ ){
			$cnt .= chr( $z );
			$scrcodeline .= chr($z + $scrCodeOffset + (($z+$scrCodeOffset)==64?32:0)); //workaround for @ chr(0)
			//turns @ into space to avoid problems
		}
		$this->addNormalChunkXY(
			"PETSCII $start-$stop,SCREENCODE ".
			($start+$scrCodeOffset) ."-". ($stop+$scrCodeOffset), 1, $y++, "F");
		$this->addNormalChunkXYNoMap( $cnt, 1, $y++, $reverse);
		$this->addNormalChunkXYNoMap( $cnt, 1, $y++, "1");
		$this->addScreenCodeChunkXY( $scrcodeline, 1, $y++, "3");
		return $y+1;
	}

	public function handleKeypress($key, $enforceClear){
		if ($this->isScreenExitKeypress($key)){
			$this->controller->setStartScreen();
			return true; //screen has changed
		}
		else switch ($key) {
			case self::PETSCII_KEY["t"]:
				$this->setSessionVar("lowercase", !$this->getSessionVar("lowercase"));
				$this->renderCompleteScreen();
				break;
			default:
					$this->renderCompleteScreen();
		}
		return false;
	}
}
