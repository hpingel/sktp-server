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

	require_once('lib/puiQRCode.php');

	class petsciiqrcode extends sktpBaseScreen{

		private $controller;

		function __construct( $controller ){
			parent::__construct( false );
			$this->controller = $controller;
	 	}

	 	public function renderCompleteScreen(){
	 		$this->enforceClearScreen();
	 		$this->addColorCharsetChunk(2, 0, true);
			$this->addNormalChunkXY( "PETSCII", 1, 1, "7");
			$this->addNormalChunkXY( "QR-Code", 1, 3, "7");
			$this->addScreenCodeChunkXY(
				$this->getScreenCodeFormattedKeyLegend(
					($this->isClientOn264()?"F7":"F5") . " Back"
				), 1,23,"7"
			);

			$qr = new puiQRCode($this);
			$qrData = $qr->generateQRCodeData( 'https://'.$_SERVER["SERVER_NAME"]);
			$qr->drawQRCode($qr->getPetsciiQRCodeData($qrData, true),"left","center","F");
			$qr->drawQRCode($qr->getPetsciiQRCodeData($qrData, false),"right","center","B");
			$this->oScreen->print();
	 	}

	 	public function handleKeypress($key, $enforceClear){
			if ($this->isScreenExitKeypress($key)){
				$this->controller->setStartScreen();
				return true; //screen has changed
			}
			$this->renderCompleteScreen();
	 		return false;
	 	}
}
?>
