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

class utf8downgrader {

	protected
		$mbPresent;

	function __construct(){
		$this->mbPresent = extension_loaded('mbstring');
	}

	public function utf8_2_ascii( $text )
	{
		if ( !$this->mbPresent )
			return $text;
		$text = $this->detectAndRemoveUTF8Doubled( $text );
		if ( mb_detect_encoding( $text ) === 'UTF-8' )
		{
			setlocale(LC_ALL, 'en_GB.utf8');
			setlocale(LC_CTYPE, 'en_GB.utf8');
			$res = @iconv("UTF-8", "ascii//TRANSLIT", $text );
			if ( $res !== false )
				$text = $res;
			else{
				$res = utf8_encode($text);
				$text = @iconv("UTF-8", "ascii//TRANSLIT", $res );
			}
		}
		return $text;
	}

	private function detectAndRemoveUTF8Doubled( $text )
	{
		if ( !$this->mbPresent )
			return $text;
		//try to detect insane double utf-8 encoding
		while (  mb_detect_encoding( utf8_decode( $text ) ) === 'UTF-8' )
		{
			$res = utf8_decode($text);
			if ( $res !== "" ) $text = $res;
		}
		return $text;
	}

}
?>