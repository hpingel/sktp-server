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

require_once("lib/dbConnection.php");
require_once('lib/puiQRCode.php');
require_once('lib/puiWrappedText.php');
require_once("apps/csdbBrowser/lib/baseScreenWithParser.php");

class remoteaccess extends csdbBaseScreenWithParser	{
	
	private
        $url,
		$db,
		$shortenedSession;

	function __construct( $controller ){
		parent::__construct( false );
		$this->controller = $controller;
        $this->shortenedSession = substr($_GET["sessionid"], 0, 10);
        if ( !is_dir("appCache/arena"))
            @mkdir("appCache/arena");
		$this->registerSessionVar( "refreshcount", 1);
		$this->registerSessionVar( "remoteKeyOffered", false);

        $httpStyle = array_key_exists("HTTPS",$_SERVER) ? "https" : "http";
		if ( $_SERVER["SERVER_NAME"] !== "localhost")
			$httpStyle = "https";
        $isStandardPort = $_SERVER["SERVER_PORT"] == 80 || $_SERVER["SERVER_PORT"] == 443;
        $port = $isStandardPort ? "" : ":". $_SERVER["SERVER_PORT"];
        $this->url = $httpStyle . "://".$_SERVER["SERVER_NAME"]. $port."/kick.php?key=".$this->shortenedSession;

    }

	function __destruct(){
		$this->db = null;
	}

	private function setValue ($key, $value){
		$sql =
			"UPDATE intstorage SET value=". $this->db->quote($value).
			" WHERE" .
			" scenario = " . $this->db->quote("remotelink") .
			" AND sessionid = " . $this->db->quote($this->shortenedSession).
			" AND key = " . $this->db->quote($key);
		$sqlr = $this->db->query($sql);
	}


	public function renderCompleteScreen(){
		$this->enforceClearScreen();
		$this->renderPetsciiBackgroundTemplate("CSDb Remote Access via WebApp",20, 6, 19);

		$x=15;$y = 3;

//		$this->addNormalChunkXY( "".$this->shortenedSession, $x, 21, "1");
//		$this->addNormalChunkXY( "URL: ".$this->url, 0, $y++, "1");
        $y++;

        $qr = new puiQRCode($this);
        $qrData = $qr->generateQRCodeData($this->url);
        $qr->drawQRCode($qr->getPetsciiQRCodeData($qrData, true),"center","center","F");

		$wt2 = new puiWrappedText( $this, 0, 21, 40, 4, $this->url, "borderless centered");
		$wt2 ->render();

		$this->addCenteredF5F7ChunkY(24);

		$this->addColorCharsetChunk(0,0,true);
        $this->updateScreen();
		print $this->getCurrentScreen();
	}

    public function updateScreen(){
		$y=23;$x=11;
		$this->enableAutoScreenRefresh(100);
//		$this->addNormalChunkXY( "Refresh-Count: " . str_pad($this->getSessionVar("refreshcount"),2,"0",STR_PAD_LEFT),$x,$y++,"1");
		$y++;
		$this->increaseSessionVar("refreshcount");
    }

	public function handleKeypress($key, $enforceClear){
		$pageChange = false;
		switch ($key) {
			case self::PETSCII_KEY["arrow_left"]:
			case self::PETSCII_KEY["F5"]:
				$this->controller->setStartScreen();
				return true; //screen has changed
			default:
				$url = $this->checkForLaunchURL();
				if ( $url !== ""){
					$key = false;
					return $this->handleDownload(
						$url,
						$key === self::PETSCII_KEY["s"]
					);
				}
				else if ( $enforceClear )
				{
					$this->renderCompleteScreen();
				}
				else{
					$this->enforceScreenUpdate();
                    $this->updateScreen();
					print $this->getCurrentScreen();
				}
		}
		return $pageChange;
	}

	private function checkForLaunchURL(){
		$url = "";
		$this->db = dbConnection::getInstance();
		if ( !$this->getSessionVar( "remoteKeyOffered")){
			$this->db->beginTransaction();
			$this->db->insert( "intstorage", array(
				"sessionid" => $this->shortenedSession,
				"scenario" => "remotelink",
				"key" => "connected",
				"value" => 0
			), true );
			$this->db->commit();
			$this->setSessionVar( "remoteKeyOffered", true);
		}

		$sqlr = $this->db->query(
			"SELECT value FROM textstorage WHERE" .
			" scenario = " . $this->db->quote("remotelaunch") .
			" AND sessionid = " . $this->db->quote($this->shortenedSession).
			" AND key = " . $this->db->quote("url")
		);

		$tmp = array();
		foreach ( $sqlr as $r){
			$tmp[]= $r["value"];
		}

		if ( count($tmp) === 1 && $r[0] !== ""){
			$url =  $r[0];
			$sqlr = $this->db->query(
				"DELETE FROM textstorage WHERE" .
				" scenario = " . $this->db->quote("remotelaunch") .
				" AND sessionid = " . $this->db->quote($this->shortenedSession).
				" AND key = " . $this->db->quote("url")
			);
		}
		return $url;
	}
}
?>
