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

class basicContentChunks extends charsetConversionUtilities{

    protected
        $enforceScreenCodeOverNormal,
		$typeIs264,
		$nativeTEDColorsFor264,
		$screenCharsetIsLowerCase;

    function __construct( 
		$debug, 
		$is264,
		$enforceScreencode,
		$nativeTEDColors
	){
        $this->typeIs264 = $is264;
        $this->nativeTEDColorsFor264 = $is264 ? $nativeTEDColors : false;
        $this->enforceScreenCodeOverNormal = $enforceScreencode;
		$this->screenCharsetIsLowerCase = true;
    }
	
    /*
     SKTP chunk type: 0, normal chunk, addNormalChunk
        for performance reasons use screencode chunk instead of
        normal chunk when the assembler client is rendering the
        sktp screens, assembler client then can put chars out faster
        the workload is shifted over to the PHP side
    */

    public function getNormalChunk($chunk, $pos, $color, $noMap = false){
        if ($noMap){
            $type = 0;
        }
		else if ($this->enforceScreenCodeOverNormal){
            $type = 2;
			$inverse = false;
			$colorVal = hexdec($color);
			if ($colorVal >= 128){
				$color = strval(dechex(hexdec(strtolower($color))-128));
				$inverse = true;
			}
			$chunk = $this->asciiString2Screencode($chunk, true, $inverse);
		}
		else{
            $type = 0;
			if ($this->screenCharsetIsLowerCase)
				$chunk = $this->strAscii2Petscii($chunk);
		}
        return $this->getGenericChunk($type, $chunk, $pos, strlen($chunk), $color);
	}

    /* SKTP chunk type: 1, char repeat chunk, addCharRepeatChunk
    */

    public function getCharRepeatChunk($char, $pos, $count, $color){
        return $this->getGenericChunk(1, $char, $pos, $count, $color);
	}

    /* SKTP chunk type: 2, screen code chunk, addScreenCodeChunk
    */

	public function getScreenCodeChunk($chunk, $pos, $color){
		return $this->getGenericChunk(2, $chunk, $pos, strlen($chunk), $color);
	}

    /* SKTP chunk type: 3, enable auto screen refresh, enableAutoScreenRefresh
    */

	public function getAutoScreenRefreshChunk( $timeout ){
		return chr(3).chr($timeout);
	}

    /* SKTP chunk type: 4, color charset chunk, addColorCharsetChunk
        implemented in method getCurrentScreen() as it can only occur once in the screens
    */

	public function getColorCharsetChunk($borderColor, $backgroundColor, $isCharsetLowerCase){
		if( $this->typeIs264 && !$this->nativeTEDColorsFor264){
			$backgroundColor = $this->vic2tedColorMatchingSingle($backgroundColor);
			$borderColor = $this->vic2tedColorMatchingSingle($borderColor);
		}
		$this->screenCharsetIsLowerCase = $isCharsetLowerCase;
		return
			chr(4).
			chr($borderColor+1).
			chr($backgroundColor +1).
			($isCharsetLowerCase ? chr(1):chr(2));
	}

    /* SKTP chunk type: 5, vertical screencode char chunk, addVerticalScreenCodeCharChunk
        implemented in method getCurrentScreen() as it can only occur once in the screens
    */

	public function getVerticalScreenCodeCharChunk($chars, $startpos, $repeatcount, $color){
		//length 6+chars
		if( $this->typeIs264 && !$this->nativeTEDColorsFor264 )
			$color=$this->vic2tedColorMatching($color);
		else
			$color=chr(hexdec($color));

		return chr(5).
            chr(strlen($chars)).				//charcount
            chr($startpos%256). 				//lsb startpos
            chr(intval($startpos/256)).         //msb startpos
            $color.
            chr($repeatcount).					//repeatcount
            $chars;								//chars
	}

    /* SKTP chunk type: 6, paintbrush chunk, addPaintBrushChunk
    */
	public function getPaintBrushChunk($colorlist, $startpos, $repeatcount, $gap){
		//if( $_SESSION["sktpv"] > 1)
		$posMSB = intval($startpos/256);
		$length = strlen($colorlist);
		$lengthMSB = intval($length/256);
		if (($lengthMSB & 2) == 2) $posMSB = $posMSB + 32; //bit 5 / 512
		if (($lengthMSB & 1) == 1) $posMSB = $posMSB + 16; //bit 4 / 256

		$colorlist = str_replace(chr(0),chr(16), $colorlist);
		if( $this->typeIs264 && !$this->nativeTEDColorsFor264 )
			$colorlist = $this->vic2tedColorMatchingOrd($colorlist);

		return chr(6).
            chr($length % 256 ).  //charcount
            chr($startpos%256).   //lsb startpos
            chr($posMSB).         //msb startpos
            chr($gap).            //gap between repeats
            chr($repeatcount).    //repeatcount
            $colorlist;           //chars
	}

    /* SKTP chunk type: 7, url for tft image, addTFTImageURL
        sktpv >= 4
    */

	public function getTFTImageURL( $url ){
		$filename="image.tga";
		$saveflag = "0";
		return
			chr(7) .
			chr(strlen($url)).
			chr(strlen($filename)).
			$saveflag.
			$url.
			$filename;
	}

    /* SKTP chunk type: 8, embedded tft image, addTFTImageURL
        sktpv >= 4
    */

    public function getTFTImage($image){
		$length = strlen($image);
		return
			chr(8) .
			chr(intval($length/65536)).
			chr(intval($length/256)).
			chr($length % 256).
			$image;
	}

    //helper method used by chunk type 0 + 1 + 2

    public function getGenericChunk($type, $str, $pos, $length, $color){
		//length: 5 + string length
		//if ($pos > 999) $pos = 999;
		//if ($length > 1000){ $length = 1000; }
		$posMSB = intval($pos/256);
		$lengthMSB = intval($length/256);
		if (($lengthMSB & 2) == 2) $posMSB = $posMSB + 32; //bit 5 / 512
		if (($lengthMSB & 1) == 1) $posMSB = $posMSB + 16; //bit 4 / 256
		//error_log ("length: " . $length . " bits " . ($lengthMSB & 1) . " ". ($lengthMSB & 2) . " p " . $posMSB . " pos ". intval($pos/256) . " l " . $lengthMSB,0 );

		if( $this->typeIs264 && !$this->nativeTEDColorsFor264){
			$color = hexdec($color);
			$reverse = ( $color > 127);
			if ($reverse)  $color -= 128;
			$color=$this->vic2tedColorMatchingSingle($color);
			$color = chr($color + ($reverse ? 128:0));
		}
		else
			$color=chr(hexdec($color));
        return
            chr($type).
            chr($length % 256).
            chr($pos % 256).
            chr($posMSB).
            $color.
            $str;
	}

	//see https://www.forum64.de/index.php?thread/122351-farbmapping-von-vic-ii-farben-zu-ted-farben-bei-portierung-von-c64-screens-zu-c1
	protected function vic2tedColorMatching($colors){
		$converted = "";
		for ($z = 0; $z < strlen($colors); $z++){
			$converted .= chr($this->vic2tedColorMatchingSingle(hexdec($colors[$z])));
		}
		return $converted;
	}

	protected function vic2tedColorMatchingSingle($color){
		if ( !is_int($color)) die("We expect integer!");
		// mikes palette
		//avoid 0 for black, use 16 instead
		if ($color == 16) $color = 0;
		$palette = array( 16,113,18,83,43,69,13,105,41,9,66,17,49,101,61,81);
		// macBacon 0, 113, 34, 99, 68, 69, 38, 103, 72, 25, 66, 17, 49, 101, 86, 81
		return $palette[$color];

	}

	private function vic2tedColorMatchingOrd($colors){
		$converted = "";
		for ($z = 0; $z < strlen($colors); $z++){
			$converted .= chr($this->vic2tedColorMatchingSingle(ord($colors[$z])));
		}
		return $converted;
	}
}
?>