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

class overlayBasicContentChunks extends charsetConversionUtilities{

    protected
		$oChunks,
        $oScreen,
		$typeIs264,
		$nativeTEDColorsFor264,
		$screenCharsetIsLowerCase;

    function __construct( $debug, $is264, $enforceScreencode, $nativeTEDColors = false ){
        $this->typeIs264 = $is264;
        $this->oScreen = new screen($debug);
		$this->oChunks = new basicContentChunks($debug, $is264, $enforceScreencode, $nativeTEDColors);
		$this->screenCharsetIsLowerCase = true;
		$this->nativeTEDColorsFor264 = $nativeTEDColors;
    }

	protected function enforceClearScreen(){
		$this->addColorCharsetChunk(0, 0, $this->screenCharsetIsLowerCase); //defaults
		$this->oScreen->enforceClearScreen();
	}

	protected function enforceScreenUpdate(){
		$this->oScreen->enforceScreenUpdate();
	}

	protected function isCharsetLowerCase(){
		return $this->screenCharsetIsLowerCase;
	}

    public function addNormalChunk($chunk, $pos, $color, $noMap = false){
        $this->oScreen->add($this->oChunks->getNormalChunk($chunk, $pos, $color, $noMap));
    }

	public function addCharRepeatChunk($char, $pos, $count, $color){
        $this->oScreen->add($this->oChunks->getCharRepeatChunk($char, $pos, $count, $color));
    }

	public function addScreenCodeChunk($chunk, $pos, $color){
		$this->oScreen->add($this->oChunks->getScreenCodeChunk($chunk, $pos, $color));
	}

	public function addColorCharsetChunk($borderColor, $backgroundColor, $isCharsetLowerCase){
		$this->screenCharsetIsLowerCase = $isCharsetLowerCase;
		$this->oScreen->setColorsAndCharset($this->oChunks->getColorCharsetChunk($borderColor, $backgroundColor, $isCharsetLowerCase));
	}

	public function addVerticalScreenCodeCharChunk($chars, $startpos, $repeatcount, $color){
        $this->oScreen->add($this->oChunks->getVerticalScreenCodeCharChunk($chars, $startpos, $repeatcount, $color));
    }

	public function addPaintBrushChunk($colorlist, $startpos, $repeatcount, $gap){
        $this->oScreen->add($this->oChunks->getPaintBrushChunk($colorlist, $startpos, $repeatcount, $gap));
    }

	protected function enableAutoScreenRefresh( $timeout ){
		$this->oScreen->setScreenRefresh($this->oChunks->getAutoScreenRefreshChunk( $timeout ));
	}

	protected function addTFTImageURL( $url ){
		$this->oScreen->setTFTImageChunk($this->oChunks->getTFTImageURL($url));
	}

	protected function addTFTImage( $image ){
		$this->oScreen->setTFTImageChunk($this->oChunks->getTFTImage($image));
	}
}

?>