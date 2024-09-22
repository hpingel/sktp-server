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

    //only used in case of text input handling like in pui\TextInput
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

?>