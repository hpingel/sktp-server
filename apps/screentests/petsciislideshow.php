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

class petsciislideshow extends sktpBaseScreen{

	private $controller;

	function __construct( $controller ){
		parent::__construct( false );
		$this->registerSessionVar( "pic", 0);
		$this->controller = $controller;
	}

	public function renderCompleteScreen(){
		$this->enforceClearScreen();
		$this->enableAutoScreenRefresh(15);
		$this->addColorCharsetChunk(0, 0, false);

		$filenames =  array(
			//todo refactor this for public release
		);
		$pic = $this->getSessionVar("pic");
		$this->drawFullscreenPicture("apps/screentests/artwork/" . $filenames[ $pic ]);
		$this->setSessionVar("pic", ($pic +1 >4) ? 0: $pic + 1);


		print $this->getCurrentScreen();
	}

	public function handleKeypress($key, $enforceClear){
		$pageChange = false;
		$decKey = hexdec($key);
		switch ($key) {
			/*
			case self::PETSCII_KEY["crsr_up"]:
					break;
			case self::PETSCII_KEY["crsr_down"]:
					break;
			*/
			case self::PETSCII_KEY["arrow_left"]:
			case self::PETSCII_KEY["F5"]:
				$this->controller->setStartScreen();
				return true; //screen has changed
			default:
				$this->renderCompleteScreen();
		}
		return $pageChange;
	}
}
