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

class petsciianimation extends sktpBaseScreen{

	private $controller;

	function __construct( $controller ){
		parent::__construct( false );
		$this->registerSessionVar( "hpos", 12);
		$this->registerSessionVar( "dir", 1);
		$this->registerSessionVar( "refreshSpeed", 1);
		$this->controller = $controller;
	}

	public function renderCompleteScreen(){
		$this->enforceClearScreen();
		$this->addColorCharsetChunk(14, 6, true);
		$this->drawTitleBar("Screen test: Petscii Logo");
		$this->drawHorizontalLine( 1 );
		$this->drawHorizontalLine( 22 );
		$this->addCenteredF5F7ChunkY(24,"7", "+/- Delay");
		$this->updateLogo();
	}

	public function updateScreen(){
		$this->enforceScreenUpdate();
		$this->updateLogo();
	}

	public function updateLogo(){
		$this->enableAutoScreenRefresh($this->getSessionVar("refreshSpeed"));
		$logolines = array(
			[ 32,108,160,123,160, 32,254,225,251,236, 97,160,226,160,123, 32],
			[ 32,160, 97,126,160,254,126, 32,225, 97, 32,160, 32,254,126, 32],
			[ 32,124,160,123,160,251,123, 32,225, 97, 32,160,226,126, 32],
			[ 32,252,160,126,160, 32,251,123,225, 97, 32,160, 32 ],
			[ 32,124,126, 32,226, 32, 32,226,124,126, 32,226, 32 ]
		);

		$colorpalette = array( "1", "3", "f", "e", "c");
		for ($z = 0; $z < 5; $z++){
			$color = $colorpalette[$z];
			$line = "";
			for ($y = 0; $y < count($logolines[$z]); $y++){
				$line .= chr($logolines[$z][$y]);
			}
			$this->addScreenCodeChunkXY( $line, $this->getSessionVar("hpos"), $z + 2, $color);
			$this->addScreenCodeChunkXY( $line, 25-$this->getSessionVar("hpos"), $z + 7, $color);
			$this->addScreenCodeChunkXY( $line, $this->getSessionVar("hpos"), $z + 12, $color);
			$this->addScreenCodeChunkXY( $line, 25-$this->getSessionVar("hpos"), $z + 17, $color);
		}
		$this->addNormalChunkXY("Refresh delay: " . str_pad($this->getSessionVar("refreshSpeed"),2,"0",STR_PAD_LEFT),12,23,"1");
		print $this->getCurrentScreen();

		$this->setSessionVar("hpos", $this->getSessionVar("hpos") + $this->getSessionVar("dir"));
		if ( $this->getSessionVar("hpos") <= 0 || $this->getSessionVar("hpos") > 24)
		{
			 $this->setSessionVar("dir", - $this->getSessionVar("dir"));
		}
	}

	public function handleKeypress($key, $enforceClear){
		$pageChange = false;
		$decKey = hexdec($key);
		switch ($key) {
			case self::PETSCII_KEY["plus"]:
				if ($this->getSessionVar("refreshSpeed") < 40)
					$this->increaseSessionVar("refreshSpeed");
				$this->updateScreen();
				break;
			case self::PETSCII_KEY["minus"]:
				if ($this->getSessionVar("refreshSpeed") > 1)
					$this->decreaseSessionVar("refreshSpeed");
				$this->updateScreen();
				break;

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
