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

require_once("lib/baseScreen.php");

class sktpHandler {

	const maxSktpVersion = 5;

	private
		$debugphp,
		$apps,
		$client_session;

	function __construct(){
		$this->debugphp = false;
		$this->apps = array();
	}

	function main (){
		$enforceClear = false;
		if (isset($_GET["sessionid"]))
			session_id($_GET["sessionid"]);
		else if ( isset( $_GET["s"] ) ) //for shorter URLs for WiC64
		{
			session_id($_GET["s"]);
			$_GET["sessionid"] = $_GET["s"];
		}			
		session_start([
			'use_cookies' => 0
		]);

		$this->client_session = "";
		if ( isset( $_GET["session"] ) )
			$this->client_session = $_GET["session"];

		if ( $this->client_session !== "new" && !isset($_SESSION["screen"]))
		{
			print chr(3); //session timeout or non-existing session
			return;
		}
		else if ( $this->client_session === "new" || !isset($_SESSION["screen"]))
		{
			$_SESSION["startedAt"] = time();
			$rootApp = "menu";
			$enforceClear = true;
			$_SESSION["username"] = isset($_GET["username"]) ? trim($_GET["username"]) : "unknown";
			//sanity check for username
			if (strlen($_SESSION["username"]) > 16 || strlen($_SESSION["username"]) < 2){
				$_SESSION["username"] = "unknown";
			}
			else{
				$usertmp = $_SESSION["username"];
				$result = preg_replace("/[^a-zA-Z0-9]/", "", $usertmp);
				if ($result !== $usertmp)
					$_SESSION["username"] = "unknown";
			}

			$_SESSION["sktpv"] = isset($_GET["sktpv"]) ? intval($_GET["sktpv"]) : 1;

			//param f=wic indicates WiC64 as a client
			if ( isset( $_GET["f"] ) && $_GET["f"] === "wic" ) 
			{
				$_SESSION["clientDevice"] = "wic";
				$_SESSION["sktpv"] = 3;
			}			
			else
				$_SESSION["clientDevice"] = "sidekick";

			//sktpv sanity check
			if ( $_SESSION["sktpv"] > self::maxSktpVersion ) $_SESSION["sktpv"] = self::maxSktpVersion;

			$_SESSION["password"] = isset($_GET["password"]) ? $_GET["password"] : "";
			$_SESSION["type"] = isset($_GET["type"]) ? intval($_GET["type"]) : 0;
			if ( $_SESSION["type"] != 64 && $_SESSION["type"] != 128 && $_SESSION["type"] != 264 )
				$_SESSION["type"] = 64;
			$this->initializeApp($rootApp);
			$_SESSION["application"] = $rootApp;
			$_SESSION["screen"] = $this->apps[ $rootApp ]->getStartScreenName();
			print session_id();
			return;
		}
		$key = isset( $_GET["key"] ) ? $_GET["key"] : "";
		if ($key === "") //workaround for shorter urls (wic64)
			$key = isset( $_GET["k"] ) ? $_GET["k"] : "";
		if (strlen($key) > 2)
			$key = "";

		if ( isset($_GET["redraw"]) )
			$enforceClear = true;

		//start output
		header('Content-Type: application/octet-stream');
		$this->launch($key, $enforceClear);
	}

	function launch($key, $enforceClear){
		$this->initializeApp($_SESSION["application"]);
		if ( $this->apps[$_SESSION["application"]]->launch($key, $enforceClear)){
				//switch from one app to the other app
				$this->initializeApp($_SESSION["application"]);
				//allow deep-linking into different screens of csdb launcher (single view, hvsc)
				if ( $_SESSION["screen"] !== "adventcalendar" && !isset($_SESSION["jumpback_app"]))
					$_SESSION["screen"] = $this->apps[$_SESSION["application"]]->getStartScreenName();
				$this->launch("",true);
		}
		else{
			$e = $this->apps[$_SESSION["application"]]->getErrorMessage();
			if ($e !== ""){
				$_SESSION["application"] = "menu";
				$_SESSION["screen"] = "welcome";
				$_SESSION["errorMSG"] = $e;
				$this->launch("",true);
			}
		}

	}

	function initializeApp($app){
		if (!in_array($app, $this->apps))
		{
			switch ($app){
				case "menu":
					$this->apps[$app] = new controller($app, array(
						"welcome",
						"systeminfo",
						"remoteaccess",
						"wic64Portal"
					));
					break;
				case "csdbBrowser":
					$this->apps[$app] = new controller($app, array(
						"menu", //default screen
						"releaseList",
						"singleRelease",
						"zipProxy",
						"hvscBrowser",
						"psid64Themes",
						"search",
						"petsciislideshow"
					));
					break;
				case "rssFeeds":
					$this->apps[$app] = new controller($app, array(
						"rss2binMenu",
						"threads",
						"p4w",
						"commodorenews"
					));
					break;
				case "arena":
					$this->apps[$app] = new controller($app, array(
						"nickname",
						"chat",
						"lobby"
					));
					break;
				case "modarchive":
					$this->apps[$app] = new controller($app, array(
						"modarchivemenu",
						"latestuploads",
						"crewnominated"
					));
					break;
				case "screentests":
					$this->apps[$app] = new controller( $app, array(
						"petsciimenu", //default screen
						"petsciicharset",
						"petsciiqrcode",
						"petsciianimation",
						"screenexample",
						"screenexample_with_list",
						"adventcalendar",
						"petsciiwrappedtext",
						"petsciiballoonface",
						"petsciipresenter",
						"vic2colors",
						"tedcolors"
					));
					break;
/*
				case "multiscreendemo":
					require_once("apps/multiscreendemo/controller.php");
					$this->apps["multiscreendemo"] = new controllerMultiScreenDemo($this->debugphp);
					break;
*/
				default:
					die("unknown app");
			}
		}
	}
}

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
		if (!file_exists($class)){
			$this->errorMSG = "Class file not found.";
			return false;
		}
		require_once($class);
		$oScreen = new $screen($this);
		if ( $oScreen === false ){
			$this->errorMSG = "Instance returned false.";
			return false;
		}

		if ($key === "")
			$oScreen->renderCompleteScreen();
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