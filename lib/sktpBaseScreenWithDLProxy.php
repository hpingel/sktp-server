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

class sktpBaseScreenWithDLProxy extends \lib\sktpBaseScreen{

	function __construct($debug){
		parent::__construct($debug);
		$this->registerSessionVar( "fileproxy", array());
		$this->registerSessionVar( "uid", 0);
		$this->registerSessionVar( "download", "");
		$this->registerSessionVar( "filename", "");
	}

	protected function getSKTPProxyURL($payloadURL, $filename){
		$proxyFileList = $this->getSessionVar( "fileproxy" ); 
		$payloadMD5 = md5($payloadURL);
		$proxyFileList[ $payloadMD5 ] = $payloadURL;
		$this->setSessionVar( "fileproxy", $proxyFileList);
		$httpStyle = array_key_exists("HTTPS",$_SERVER) ? "https" : "http";
		$isStandardPort = $_SERVER["SERVER_PORT"] == 80 || $_SERVER["SERVER_PORT"] == 443;
		$port = $isStandardPort ? "" : ":". $_SERVER["SERVER_PORT"];
		$uid = strval(random_int(100000, 999999));
		$tempurl = $httpStyle . "://".$_SERVER["SERVER_NAME"]. $port. "/sktp.php?s=" . $_GET["sessionid"] . "&k=";
		$urlSuffix = "";
		if ( $this->isClientWiC64()){
			//for WiC64 we want to keep the URL as short as possible
			$uid = substr($uid,0,3);
			$tempUrl = "!";
			$urlSuffix = "&x=/.prg";
		}
		$this->setSessionVar( "uid", $uid);
		$this->setSessionVar( "download", $payloadMD5);
		$this->setSessionVar( "filename", $filename);
		return $tempUrl ."02&u=".$uid. $urlSuffix;
	}

	protected function proxyDownloadThroughSKTP(){
		if (isset($_GET["u"]) && $this->getSessionVar("uid") == $_GET["u"])
		{
			$download = $this->getSessionVar("download");
			$proxyFileList = $this->getSessionVar( "fileproxy" );
			if (array_key_exists($download, $proxyFileList)){
				$url = $proxyFileList[ $download ];
				$payload = file_get_contents( $url );
				$filename = $this->getSessionVar("filename");
				if($this->isClientWiC64() && substr(strtolower($filename),-4)===".sid"){
					$tmpSidLocation = "";
					$filePathPrefix = "appCache/csdbBrowser/psidProxy/";                
					$tempFolder = $filePathPrefix . md5( $url )."/";
					if ( !is_dir($tempFolder) )
						mkdir($tempFolder, 0700, true);
			
					file_put_contents($tempFolder.$filename,$payload);
					$psidFilename = substr($filename,0,strlen($filename)-3)."prg";
					if (PHP_OS == "WINNT")
						$psidBinary = "C:/psid64-1.3-win64/psid64.exe";
					else
						$psidBinary = "/usr/bin/psid64";
					$hvscSonglengthCacheFile = "appCache/hvsc/Songlengths.md5";

					if ( !isset($_SESSION["psid64theme"]) || $_SESSION["psid64theme"] == "")
						$_SESSION["psid64theme"] = "default";
		
					$cmd = escapeshellcmd($psidBinary.' -c '.
						'-p /var/www/sidid/sidid.cfg '.
						'-t '. $_SESSION["psid64theme"] . ' '.
						'-s '.$hvscSonglengthCacheFile . 
						' -o ' . $tempFolder.$psidFilename. 
						' '. $tempFolder.$filename . ''
					);
					$output = array();
					$rc = null;
					exec($cmd, $output, $rc);
					$payload = file_get_contents( $tempFolder.$psidFilename);
					$filename = $psidFilename;		
				}

				$this->setSessionVar( "uid", 0);
				$this->setSessionVar( "download", "");
				$this->setSessionVar( "filename", "");
		
				if (strlen($payload) > 0 ){
					header('Content-Disposition: attachment; filename="'. addslashes($filename).'"');
					header('Content-Length: '.strlen($payload));
					print $payload;
				}
				else{
					print "ERROR payload not there.\n";
				}
			}
			else{
				print "ERROR not in proxy file list.\n";
			}
		}
		else{
			print "ERROR param download missing.\n";
		}
	}
}

?>
