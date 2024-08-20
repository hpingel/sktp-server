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

class charsetConversionUtilities {

	//only use this on screens with lowercase charset!
	public function strAscii2Petscii( $str ){
		$str2= "";
		for ($z=0;$z<strlen($str);$z++){
			$a = ord(substr($str,$z,1));
			if ($a >64 && $a < 91)
				$a = $a + 32;
			else if ($a >96 && $a < 123)
				$a = $a - 32;
			else if ($a == 95 ) $a = 164; // imitate underscore, replace arrowleft
			$str2 .= chr($a);
		}
		return $str2;
	}

	protected function asciiChar2ScreencodeOrd($isLowercase, $char, $ascii = true){
		$val = ord($char);
		if ( $isLowercase && $ascii && $val >= 97 && $val <= 127 ) //lowercase letters
			$val -= 96;
		else if ( !$ascii && $val >= 97 && $val <= 127 ) //lowercase letters
			$val -= 32;
		else if ( $ascii && $val == 95)
			$val = 100; // imitate non-existing underscore with petscii "underscore"
		else if ( $isLowercase && $ascii && $val >= 91 && $val <= 96 ) //lowercase letters
			$val -= 64;
		else if ( !$isLowercase && $ascii && $val >= 65 && $val <= 95 ) //uppercase letters
			$val -= 64;
		else if ( !$ascii && $val >= 64 && $val <= 90 ) //uppercase letters
			$val -= 64;
		else if ( !$ascii && $val >= 91 && $val <= 95 ) //uppercase letters
			$val -= 64;
		else if ( $val >= 160 && $val <= 191 ) //uppercase letters
			$val -= 64;
		//else if ( !$ascii && $val >= 192)
		//	$val -= 96;
		return $val;
	}

	protected function asciiChar2Screencode($char, $ascii = true, $inverse = false){
		$val = $this->asciiChar2ScreencodeOrd($this->screenCharsetIsLowerCase, $char, $ascii);
		return chr($val + ($inverse ? 128 : 0));
	}

	public function asciiString2Screencode($str, $ascii = true, $inverse = false){
		if (strlen($str) === 0)
		  return "";
		$strArray = str_split($str,1);
		$str="";
		foreach ( $strArray as $char)
			$str.= $this->asciiChar2Screencode($char, $ascii, $inverse);
		return $str;
	}

    //only used in case of text input handling like in puiTextInput
	public function petsciiString2Screencode($str){
		return $this->asciiString2Screencode($str, false);
	}

	/* seems to be unused

	public function strPetscii2Ascii( $str ){
		$str2= "";
		for ($z=0;$z<strlen($str);$z++){
			$a = ord(substr($str,$z,1));
			if ($a >64 && $a < 91)
				$a = $a - 32;
			else if ($a >96 && $a < 123)
				$a = $a + 32;
			else if ($a == 164 ) $a = 95; // imitate underscore, replace arrowleft
			$str2 .= chr($a);
		}
		return $str2;
	}
*/
}


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