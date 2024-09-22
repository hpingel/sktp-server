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

namespace lib;

class screen {

    private
        $debug,
        $currentScreen,
        $screenType,
        $screenRefresh,
        $tftImage,
        $updateScreenColors,
		$colorsCharsetChunk;

    function __construct( $debug ){
		$this->currentScreen = "";
		$this->screenRefresh = "";
		$this->tftImage      = "";
		$this->debug = $debug;
		$this->screenType = chr(0);
		$this->colorsCharsetChunk = "";
		$this->updateScreenColors = false;
    }

    public function add($content){
        $this->currentScreen .= $content;
    }

    public function setScreenRefresh($refresh){
        $this->screenRefresh = $refresh;
    }

	public function enforceClearScreen()
	{
		$this->screenType = chr(0);
	}

	public function enforceScreenUpdate()
	{
		$this->screenType = chr(1);
	}

    public function setColorsAndCharset($chunk){
        $this->colorsCharsetChunk = $chunk;
        $this->updateScreenColors = true;
    }

    public function setTFTImageChunk($chunk){
        $this->tftImage = $chunk;
    }

	public function print(){
		print $this->getCurrentScreen();
	}

	private function getCurrentScreen(){
		if ( $this->debug )
		{
			print "<hr/><pre>\n";
			print "DEBUG";
			print "</pre>\n";
		}

		$colors = "";
		if ($this->updateScreenColors || $this->screenType == chr(0))
		{
			$this->updateScreenColors = false;
			$colors = $this->colorsCharsetChunk;
		}
		if( $_SESSION["sktpv"] < 2){
			$this->screenRefresh = "";
			$colors = "";
		}

		if( $_SESSION["sktpv"] < 4){
			$this->tftImage = "";
		}

		return $this->screenType . $colors. $this->currentScreen . $this->screenRefresh. $this->tftImage;
	}
}

?>