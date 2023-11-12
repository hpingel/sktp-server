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

class puiWrappedText {

	private
		$x,
		$y,
		$width,
		$height,
		$text,
		$wrappedTextArray,
		$screen,
		$borderStyle,
		$linesUsed;

	function __construct( sktpBaseScreen $screen, $x, $y, $width, $height, $text, $borderStyle = "line"){
		$this->screen = $screen;
		$this->x = $x;
		$this->y = $y;
		$this->width = $width;
		$this->height = $height;
		$this->borderStyle = $borderStyle;
		$this->text = $text;
		$wrappedTextArray = array();
		$this->linesUsed = false;
	}

	private function byteValueParse($cmd, $style){
		$pos =stripos($style, $cmd);
		if ( $pos !== false){
			return substr($style, $pos + strlen($cmd),1);
		}
		return false;
	}

	public function render( $repaintAll = false){
		$this->linesUsed = false;

		$unWrappedTextArray = explode( "\n", $this->text);//preserve returns
		$colorBorder = "F";
		$colorText = "D";
		$inverse = stripos($this->borderStyle, "inverse") !== false;
		$borderless = stripos($this->borderStyle, "borderless") !== false;
		$centered = stripos($this->borderStyle, "centered") !== false;
		$colorOverrule = $this->byteValueParse("font-color:", $this->borderStyle);
		if ($colorOverrule !== false)
			$colorText = $colorOverrule;
		$colorOverrule = $this->byteValueParse("border-color:", $this->borderStyle);
		if ($colorOverrule !== false)
			$colorBorder = $colorOverrule;
	
		if ($borderless)
		{
			$wrapWidth = $this->width;
			$xStart = $this->x;
			$currY = $this->y;
		}
		else{
			$wrapWidth = $this->width-2;
			$xStart = $this->x + 1;
			$currY = $this->y+1;
		}

		if (stripos($this->borderStyle, "padded") !== false ){
			$wrapWidth -=2;
			$xStart +=1;
			$currY += 1;
	  }
		if ($wrapWidth < 4)
			return;

		$wrappedTextArray = array();
		foreach( $unWrappedTextArray as $chunk){
			foreach( explode( "\n", wordwrap($chunk,$wrapWidth,"\n",true)) as $element)
			 	$wrappedTextArray[] = $element;
		}
		if (count($wrappedTextArray) == 0 && !$repaintAll)
			return;
		$maxDrawableLines = $this->height - ($borderless ? 0:2);
		while (count($wrappedTextArray) < $maxDrawableLines && $repaintAll){
			$wrappedTextArray[] = "";
		}
		$this->linesUsed = count($wrappedTextArray);

		if (!$borderless)
		{
			if ($inverse)
				$this->screen->drawBoxFull($this->x,$this->y,$this->width ,$this->height,$colorText, true);
			else
				$this->screen->drawBox($this->x,$this->y,$this->width ,$this->height,$colorBorder);
		}
		//if ($this->linesUsed <= $maxDrawableLines )
		$currentLine=0;
		{
			foreach ($wrappedTextArray as $line){
				$currentLine++;
				if ( $currentLine > $maxDrawableLines)
					break;
				$leftpad = "";
				if ($centered){
					$leftpad = str_repeat($inverse?chr(160):" ",intval(($wrapWidth - strlen($line))/2));
					$rightpad = $leftpad;
					if (strlen($line)/2 !== intval( strlen($line)/2 ))
						$rightpad .= $inverse?chr(160):" ";
				}
				else
					$rightpad = str_repeat($inverse?chr(160):" ",intval($wrapWidth - strlen($line)));
				$content = $this->screen->asciiString2Screencode($line, true, $inverse);
				$this->screen->addScreenCodeChunkXY($leftpad. $content.$rightpad, $xStart, $currY++, $colorText);
			}
			if ( $inverse ){
				while  ( $currentLine  < $maxDrawableLines ){
					$currentLine++;
					$line = str_repeat(chr(160),$wrapWidth);
					$content = $this->screen->asciiString2Screencode($line, true, $inverse);
					$this->screen->addScreenCodeChunkXY($content, $xStart, $currY++, $colorText);

				}
			}

		}
	}

	public function getLinesUsed(){
		return $this->linesUsed;
	}
}

?>
