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

namespace pui;

class TextInput {

	private
		$screen,
		$maxLength,
		$sessionVar,
		$crsposVar,
		$allowedKeyCategories,
		$debug;

	function __construct(
		\lib\sktpBaseScreen $screen,
		$var,
		$maxLength,
		$initialContent = "",
		$allowedKeyCategories = array (
			"alphabetStandard",
			"alphabetUppercase",
			"numbers",
			"interpunction",
			"gfx",
			//"colorToggle",
		),
		$debug = false
	){
		$this->sessionVar = $var;
		$this->maxLength = $maxLength;
		$this->screen = $screen;
		$this->crsposVar = $this->sessionVar."_crsrpos";
		$this->screen->registerSessionVar( $this->sessionVar, $initialContent );
		$this->screen->registerSessionVar( $this->crsposVar, strlen($initialContent) );
		$this->allowedKeyCategories = $allowedKeyCategories;
		$this->debug=$debug;
	}

	public function setContent($content){
		$this->screen->setSessionVar($this->sessionVar, $content);
		$this->screen->setSessionVar($this->crsposVar, strlen($content));
	}

	public function getContent(){
		return $this->screen->getSessionVar($this->sessionVar);
	}

	public function getContentAsAscii(){
		return $this->screen->strAscii2Petscii($this->screen->getSessionVar($this->sessionVar));
	}


	public function getContentWithCursor(){
		$cnt = $this->screen->petsciiString2Screencode($this->getContent());
		$cpos = $this->screen->getSessionVar($this->crsposVar);
		$length = $this->screen->getSessionVarStrLength($this->sessionVar);
		if ( $cpos === $this->maxLength || $cpos === $length )
			return $cnt.chr(160);
		else if ( $length === 0 )
			return chr(160);
		$cnt = substr( $cnt, 0, $cpos). chr( ord($cnt[$cpos])+128). substr( $cnt, $cpos+1);
		return $cnt;
	}

	//sessionvar is the name of the sessionvar where the textinput is stored
	public function handlePetsciiTextInput($key){
		if ($this->handleKeypress($key))
			return true;

		$input = $this->screen->getSessionVar($this->sessionVar);
		//ignore textinput if it exceeds the available spaces in input field
		if ( strlen($input) > $this->maxLength -1 ){
			return true;
		}

		$dk = hexdec($key); //decimal version of hex key
		//debug raw key
		if ($this->debug){
			$dbgY = 22;
			$this->screen->addNormalChunkXY( $key."  ", 3, $dbgY, "3");
			$this->screen->addNormalChunkXY( $dk."  ", 33, $dbgY, "3");
			$this->screen->addNormalChunkXY( "     ", 37, $dbgY, "1");
		}

		$keyCategory = "";
		if ( is_array($this->allowedKeyCategories)){
			if (		($dk >=  65 && $dk <=  90) ||	$dk == 32)
				$keyCategory = "alphabetStandard";
			else if (
							($dk >= 193 && $dk <= 218) || //has gfx in uppercase mode
							($dk >=  97 && $dk <= 122)) //has gfx in uppercase mode
				$keyCategory = "alphabetUppercase";
			else if ($dk >=  48 && $dk <=  57)
				$keyCategory = "numbers";
			else if (
							($dk >=  33 && $dk <=  47) ||
							($dk >=  58 && $dk <=  64) ||
					 		($dk >=  91 && $dk <=  93))
				$keyCategory = "interpunction";
			else if (
							($dk >=  93 && $dk <=  96) ||
							($dk >= 123 && $dk <= 127) ||
							($dk >= 219 && $dk <= 223) || // same as above
							($dk >= 160 && $dk <= 191))
				$keyCategory = "gfx";
			else if (in_array($dk, array(5,18,28,30,31,144,146,149,150,151,152,153,154,155,156,158,159 )))
			 	$keyCategory = "colorToggle"; //TODO this is invisible control stuff!
		}

		if ( $this->allowedKeyCategories === true || in_array( $keyCategory, $this->allowedKeyCategories))
		//if (($dk >= 32 && $dk <= 127) || ($dk >= 160 && $dk < 255))
		{
/*
			if ($switch){
			if ( $dk > 64 && $dk < 91 )
				$dk += 32;
			else if ( $dk > 95 && $dk < 128 )
				$dk -= 32;
			else if ($dk >= 193 && $dk <= 218)
				$dk -= 128;
			}
			else{
				*/
			if ($dk >= 193 && $dk <= 218)
				$dk -= 96;
//			else
			 if ($dk >= 219 && $dk <= 233)
				$dk -= 96;
			else if ($dk == 192 )
				$dk = 96;
//			}
			//debug converted key
			if ($this->debug)
				$this->screen->addNormalChunkXY( $dk."  ", 37, $dbgY, "1");

			$cpos = $this->screen->getSessionVar($this->crsposVar);
			$input = substr( $input, 0, $cpos). chr( $dk). substr( $input, $cpos);
			$this->screen->setSessionVar($this->sessionVar, $input);
			$this->screen->increaseSessionVar($this->crsposVar);
			return true;
		}
		return false;
	}

	public function handleKeypress($key){
		switch ($key) {
			case $this->screen::PETSCII_KEY["instdel"]:
				$input = $this->screen->getSessionVar($this->sessionVar);
				$cpos = $this->screen->getSessionVar($this->crsposVar);
				if ($cpos >0){
					if ($cpos === $this->screen->getSessionVarStrLength($this->sessionVar)){
						if ($cpos === 1)
							$this->screen->setSessionVar($this->sessionVar, "");
						else
							$this->screen->setSessionVar($this->sessionVar, substr( $input, 0, $cpos-1));
					}
					else
						$this->screen->setSessionVar($this->sessionVar, substr( $input, 0, $cpos-1). substr( $input, $cpos));
					$this->screen->decreaseSessionVar($this->crsposVar);
				}
				return true;
			case $this->screen::PETSCII_KEY["crsr_left"]:
			  if ( $this->screen->getSessionVar($this->crsposVar) > 0)
					$this->screen->decreaseSessionVar($this->crsposVar,1);
				return true;
				break;
			case $this->screen::PETSCII_KEY["crsr_right"]:
				if (
					( $this->screen->getSessionVar($this->crsposVar) < $this->maxLength) &&
					(( $this->screen->getSessionVar($this->crsposVar) < $this->screen->getSessionVarStrLength($this->sessionVar)))
					)
					$this->screen->increaseSessionVar($this->crsposVar,1);
				return true;

				break;

			default:
		}
		return false;
	}

}
?>
