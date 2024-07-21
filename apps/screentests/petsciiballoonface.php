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

require_once('lib/puiWrappedText.php');

class petsciiballoonface extends sktpBaseScreen{

	private $controller;

	function __construct( $controller ){
		parent::__construct( false );
		$this->registerSessionVar( "exampleVar", 0);
		$this->controller = $controller;
	}

	public function renderCompleteScreen(){
		$this->enforceClearScreen();
		$this->addColorCharsetChunk(
				0, //border color
				0, //background color
				true //lower case charset / upper case charset
		);


		$this->faceWithWordBalloon();

/*
		$this->addCenteredF5F7ChunkY(24);
*/
		print $this->getCurrentScreen();
	}

	private function faceWithWordBalloon(){
		$balloontext = "Hello world!\n\nI currently don't have much more on my mind to say.\n\nMaybe on another day.\n\nGo and play a retro game or two.";
		$epilog = "Friendly PETSCII face sends out greetings to the world";

		$this->drawFace(0, 2, $balloontext);
		$wt2 = new puiWrappedText( $this, 0, 22, 40, 5, $epilog, "borderlesss centered");
		$wt2 ->render();
	}

	private function drawFace($xOffset, $yOffset, $text){
			$eyesWideOpen = true;
			$inverse = true;

			//face background
			//left ear
			$this->drawBox($xOffset+1,$yOffset+8,3,4,"A", !$inverse, true);
			$this->drawBoxFull($xOffset+7,$yOffset+15,10,4,"A", $inverse, true); //chin
			$this->drawBoxFull($xOffset+5,$yOffset+2,14,4,"A", $inverse, true); //foreheac
			$this->drawBoxFull($xOffset+3,$yOffset+4,17,13,"A", $inverse, true);//main

			//hair/cap background
			$this->drawBoxFull($xOffset+2,$yOffset-1,18,5,"6", $inverse, true);
			$this->addCharRepeatChunk("=", $xOffset + 6 +40*( $yOffset+3), 16, "E");

			//mouth
			$this->drawBox($xOffset+9,$yOffset+14,6,3,"2", $inverse, true);
			$this->addScreenCodeChunkXY(chr(116+128).chr(116+128), $xOffset + 11,$yOffset+15, "1");
			$this->addScreenCodeChunkXY(chr(160), $xOffset + 10,$yOffset+15, "0");
			$this->addScreenCodeChunkXY(chr(160), $xOffset + 13,$yOffset+15, "0");

			//nose
			$this->drawBox($xOffset+11,$yOffset+9,6,5,"A", $inverse, true);
			$this->addScreenCodeChunkXY(chr(160).chr(160),$xOffset+12,$yOffset+9,"8");

			//eyes
			$eyeAlign = 1; // 0,1,2
			$this->drawBoxFull($xOffset+6,$yOffset+7,5,3,"1", $inverse, true);
			$this->drawBoxFull($xOffset+15,$yOffset+7,5,3,"1", $inverse, true);
			$this->addScreenCodeChunkXY(chr(160),$xOffset+7+$eyeAlign,$yOffset+8,"6");
			$this->addScreenCodeChunkXY(chr(160),$xOffset+16+$eyeAlign,$yOffset+8,"6");
			if (!$eyesWideOpen)
				$this->addPaintBrushChunk(chr(15).chr(15).chr(15).chr(15).chr(15), $xOffset+6 + 40*($yOffset+7), 2, 4);

			//eyebrows
			$this->addCharRepeatChunk(chr(184), $xOffset + 6 +40*( $yOffset+4), 4, "A");
			$this->addCharRepeatChunk(chr(184), $xOffset + 15 +40*( $yOffset+4), 4, "A");

			//glasses
			$colGlasses ="E";
			$this->drawBox($xOffset+5,$yOffset+6,7,5,$colGlasses, $inverse, true);
			$this->drawBox($xOffset+14,$yOffset+6,7,5,$colGlasses, $inverse, true);
			$this->addScreenCodeChunkXY(chr(192),$xOffset+21,$yOffset+7,$colGlasses);
			$this->addScreenCodeChunkXY(chr(192).chr(192),$xOffset+12,$yOffset+7,$colGlasses);
			$this->addScreenCodeChunkXY(chr(192).chr(192).chr(192),$xOffset+2,$yOffset+7,$colGlasses);

			//speech bubble pointer
			$this->addScreenCodeChunkXY( chr(47),$xOffset+22, $yOffset+14, "1");
			$this->addScreenCodeChunkXY( chr(47),$xOffset+21, $yOffset+15, "1");

			$wt2 = new puiWrappedText( $this, $xOffset+23, $yOffset, 17, 18, $text, "inverse padded centered font-color:1");
			$wt2 ->render();
	}

	public function updateScreen(){
		$this->enforceScreenUpdate();
		//in here, only update those parts that need to be updated
		print $this->getCurrentScreen();
	}

	public function handleKeypress($key, $enforceClear){
		$pageChange = false;
		if ($this->isScreenExitKeypress($key)){
			$this->controller->setStartScreen();
			return true; //screen has changed
		}
		else switch ($key) {
			default:
					$this->updateScreen();
		}
		return $pageChange;
	}
}
?>
