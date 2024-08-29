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

class puiPetsciiCanvas{

    private
        $screen,
        $invisibleInk,
		$charsetIsLowercase;

	function __construct( sktpBaseScreen $screen ){
		$this->screen = $screen;
		$this->charsetIsLowercase = false;
    }

	public function drawFullscreenPicture($file, $compressed = false){
		$prg = file_get_contents($file);
		$d = array(
			2087 => array(
				87
			),
			2098 => array(
				98
			),
			2115 => array(
				91
			),
			2499 => array(
				429
			),
			2758 => array(
				651
			),
			10193 => array(
				8193
			)
		);
		$l = strlen($prg);
		if ( array_key_exists($l,$d) )
		{
			$start = $d[$l][0];
		}
/*
		file size  char start   color start
		2098          98           1098
		2115          91           1091
		2499         429           1429
		2758         651           1651
		10193       6145           7145
*/
		else
		{
			$this->screen->addColorCharsetChunk(0, 0, true);
			$this->screen->addNormalChunkXY( "File size check failed.", 1, 11, "7");
			$this->screen->addNormalChunkXY( "PRG file has wrong size:".strlen($prg), 1, 12, "7");
			$this->screen->addNormalChunkXY( "File will not be displayed.", 1, 13, "7");
			return;
		}
		$colpos = strpos( $prg, chr(141). chr(32). chr(208) . chr(169));
		if ($colpos !== false){
			$case = ord(substr($prg, $colpos-6, 1));
			$bordercol = ord(substr($prg, $colpos-1, 1));
			$bgcol = ord(substr($prg, $colpos+4, 1));
			$this->screen->addColorCharsetChunk($bordercol, $bgcol, $case == 23);
			$this->charsetIsLowercase = $case == 23;
		}
		else if ($l === 2499||$l === 2115){
			$bordercol = ord(substr($prg, 427, 1));
			$bgcol = ord(substr($prg, 428, 1));
			$this->screen->addColorCharsetChunk($bordercol, $bgcol, false);
			$this->charsetIsLowercase = false;
		}
		else if ($l === 2758){
			$bgcol = 0;
			$this->screen->addColorCharsetChunk( 0, 0, false);
			$this->charsetIsLowercase = false;
		}
		else{
			$bgcol = 2;
			$this->screen->addColorCharsetChunk(2, 2, false); //red screen indicates problem
			$this->charsetIsLowercase = false;
		}
	$this->invisibleInk = dechex($bgcol);
		$chars = substr($prg, $start,1000);
	if ($compressed )
		$this->compressedPaintbrushChunks( substr($prg, $start,1000), false);
	else
		$this->screen->addScreenCodeChunk( $chars, 0, dechex($bgcol));
		
		$start +=1000;
		if ($_SESSION["sktpv"] < 3)
		{
			$colors1 = substr($prg, $start,255);
			$colors2 = substr($prg, $start+255,255);
			$colors3 = substr($prg, $start+510,255);
			$colors4 = substr($prg, $start+765,235);
			$this->screen->addPaintBrushChunk( $colors1, 0, 0, 0);
			$this->screen->addPaintBrushChunk( $colors2, 255, 0, 0);
			$this->screen->addPaintBrushChunk( $colors3, 510, 0, 0);
			$this->screen->addPaintBrushChunk( $colors4, 765, 0, 0);
		}
		else{
			if ($compressed)
				$this->compressedPaintbrushChunks( substr($prg, $start,1000), true);
			else
				$this->screen->addPaintBrushChunk( substr($prg, $start,1000), 0, 0, 0);

		}
	}


	public function isCharsetLowercase(){
		return $this->charsetIsLowercase;
	}

    private function compressedPaintbrushChunks( $chunk, $mode=true ){
        $pos = 0;
        $lastPos = 0;
        $chaosChunkStart = 0;
        $lastColor = "";
        $minimum = $mode ? 14:11;
        while ( $pos < strlen($chunk)){
            while ( substr($chunk,$pos,1) !== $lastColor && $pos < strlen($chunk)){
                if ($chaosChunkStart === false)
                    $chaosChunkStart = $pos-1;
                $lastPos = $pos;
                $lastColor = substr($chunk,$pos,1);
                $pos++;
            } 
            while ( substr($chunk,$pos,1) === $lastColor && $pos < strlen($chunk) && $pos - $lastPos < 255){
                $pos++;
            }
            if ($pos - $lastPos > $minimum){
                if ($chaosChunkStart !== false && $lastPos - $chaosChunkStart > 0){
                    if ($mode)
                        $this->screen->addPaintBrushChunk( substr($chunk,$chaosChunkStart, $lastPos - $chaosChunkStart), $chaosChunkStart, 0, 0);
                    else{
                        $this->screen->addScreenCodeChunk( substr($chunk,$chaosChunkStart, $lastPos - $chaosChunkStart), $chaosChunkStart, $this->invisibleInk);
                    }
                    $chaosChunkStart = false;
                }
                if ($mode)
                    $this->screen->addPaintBrushChunk( substr($chunk,$lastPos,1), $lastPos, $pos - $lastPos-1, 0);
                else{
					list($pc, $reverse) = $this->scrCode2Petscii(substr($chunk,$lastPos,1));
					$color = $reverse ? strval(dechex(hexdec(strtolower($this->invisibleInk))+128)) : $this->invisibleInk;
					$this->screen->addCharRepeatChunk( $pc, $lastPos, $pos - $lastPos, $color);
                }
                $chaosChunkStart = false;
                $lastPos = $pos;
            }
            else if ($pos == strlen($chunk)){
                if ($mode)
                    $this->screen->addPaintBrushChunk( substr($chunk,$chaosChunkStart, $pos - $chaosChunkStart), $chaosChunkStart, 0, 0);
                else{
                    $this->screen->addScreenCodeChunk( substr($chunk,$chaosChunkStart, $pos - $chaosChunkStart), $chaosChunkStart, $this->invisibleInk);
                }
            $chaosChunkStart = false;
            }
        }
    }

	private function scrCode2Petscii($c){
		$o = ord($c);
		$r = false;
		if ($o > 127 ){
			$r = true;
			$o-=128;
		}
		if ($o < 32) $o += 64;
		else if ($o < 64) $o = $o;
		else if ($o < 96) $o += 32;
		else if ($o < 128) $o += 64;
		return array(chr($o),$r);
	}
}

?>