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

require_once('lib/php-qrcode/src/QR/QRbitstream.php');
require_once('lib/php-qrcode/src/QR/QRcode.php');
require_once('lib/php-qrcode/src/QR/QRencode.php');
require_once('lib/php-qrcode/src/QR/QRinput.php');
require_once('lib/php-qrcode/src/QR/QRinputItem.php');
require_once('lib/php-qrcode/src/QR/QRrawcode.php');
require_once('lib/php-qrcode/src/QR/QRsplit.php');
require_once('lib/php-qrcode/src/QR/QRspec.php');
require_once('lib/php-qrcode/src/QR/QRrs.php');
require_once('lib/php-qrcode/src/QR/QRrsItem.php');
require_once('lib/php-qrcode/src/QR/QRrsblock.php');
require_once('lib/php-qrcode/src/QR/QRtools.php');
require_once('lib/php-qrcode/src/QR/QRstr.php');
require_once('lib/php-qrcode/src/QR/FrameFiller.php');
require_once('lib/php-qrcode/src/QR/QRmask.php');

class puiQRCode {
	/*
	here we have four bits
	12
	48
	*/
	const PETSCII_QUARTER_BLOCKS = [
		0 => 32,
		1 => 126,
		2 => 124,
		3 => 226,
		4 => 123,
		5 => 97,
		6 => 255,
		7 => 236,
		8 => 108,
		9 => 127,
		10 => 225,
		11 => 251,
		12 => 98,
		13 => 252,
		14 => 254,
		15 => 160
	];

	private $baseScreen;

	public function __construct( sktpBaseScreen $baseScreen ){
		$this->baseScreen = $baseScreen;
	}

	public function generateQRCodeData($payload){
		$textData = \QR\QRcode::text($payload);
		return array( $textData, strlen($textData[0]), count($textData) );
	}

	public function getPetsciiQRCodeData( $dataArray, $small = false){
		list($text, $width, $height) = $dataArray;
		$resultData = array();
		if ($small){
			$xodd = ( $width / 2 !== intval( $width /2));
			$yodd = ( $height / 2 !== intval( $height /2));
			if ( $yodd){
				$text[] = "";
				$text[$height] = str_pad( "", $width, "0");
			}
			for ($y = 0; $y < count($text); $y+=2){
				$line = "";
				if ($xodd){
					$text[$y] = "0".$text[$y];
					$text[$y+1] = "0".$text[$y+1];
				}

				for ($x = 0; $x < strlen($text[$y]); $x+=2){
					$value =
						intval(substr( $text[$y  ],$x  ,1) === "0" ? 0 : 1)+
						intval(substr( $text[$y  ],$x+1,1) === "0" ? 0 : 2)+
						intval(substr( $text[$y+1],$x  ,1) === "0" ? 0 : 4)+
						intval(substr( $text[$y+1],$x+1,1) === "0" ? 0 : 8);
					$line .= chr(self::PETSCII_QUARTER_BLOCKS[$value]);
				}
				$resultData[] = $line;
			}
	 }
	 else
		foreach ( $text as $y=>$line ){
			$resultData[] = str_replace( array("0","1"), array(chr(32),chr(160)), $line);
		}
		return array( $resultData, strlen($resultData[0]), count($resultData));
	}

	public function drawQRCode($dataArray, $halign, $valign, $color){
		list($resultData, $width, $height) = $dataArray;

		if (intval($halign) === $halign)
			$xpad = $halign;
		else
			switch ($halign){
				case "center":
					$xpad = intval(( 40 - $width)  / 2);
					break;
				case "right":
					$xpad = 40 - $width;
					break;
					case "left":
					default:
						$xpad = 0;
						break;
			}

		if (intval($valign) === $valign)
			$ypad = $valign;
		else
			switch ($valign){
				case "center":
					$ypad = intval(( 25 - $height)  / 2);
					break;
				case "right":
					$ypad = 25 - $height;
					break;
				case "left":
				default:
					$ypad = 0;
					break;
			}

		foreach ( $resultData as $y => $line){
			$this->baseScreen->addScreenCodeChunk( $line, $xpad + ($y + $ypad)*40, $color);
		}
	}

}
?>
