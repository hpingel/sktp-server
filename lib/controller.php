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

class controller {

	private
		$appName,
		$validScreens,
		$errorMSG;

	function __construct($appName, $validScreens){
		$this->appName = $appName;
		$this->validScreens = $validScreens;
		$this->errorMSG = "";
	}

	public function getStartScreenName(){
		return $this->validScreens[0];
	}

	public function setStartScreen(){
		$_SESSION["screen"] = $this->getStartScreenName();
	}

	public function getErrorMessage(){
		return $this->errorMSG;
	}

	public function launch($key, $enforceClear){
		$this->errorMSG = "";
		if ( !isset( $_SESSION["screen"] )){
			$this->errorMSG = "Screen name info missing.";
			return false;
		}
		$screen = $_SESSION["screen"];
		if (!in_array( $screen, $this->validScreens)){
			$this->errorMSG = "Screen name is invalid.";
			return false;
		}

		$class = "apps/".$this->appName."/".$screen.".php";
		$class2 = "apps\\".$this->appName."\\".$screen;
		if (!file_exists($class)){
			$this->errorMSG = "Class file not found.";
			return false;
		}
		//require_once($class);
//		$oScreen = new $screen($this);
		$oScreen = new $class2($this);
		if ( $oScreen === false ){
			$this->errorMSG = "Instance returned false.";
			return false;
		}

		if ($key === ""){
			$s = $_SESSION["application"]."/".$_SESSION["screen"];
			$oScreen->renderCompleteScreen();
		}
		else if ( $oScreen->handleKeypress($key, $enforceClear)){
			if ( $_SESSION["application"] === $this->appName)
				$this->launch("", true); //screen switch caused by keypress
			else
				return true; //application has changed, we need full screen of new app
		}
		return false;
	}
}

?>