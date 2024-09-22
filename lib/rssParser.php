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

class rssParser {

	protected
		$filePathPrefix,
		$wasRefreshed,
		$u8d;

	function __construct($filePathPrefix){
		$this->filePathPrefix = $filePathPrefix;
		if ( !is_dir($this->filePathPrefix) )
			mkdir($this->filePathPrefix, 0700, true);
		$this->wasRefreshed = false;
		$this->u8d = new utf8downgrader();
	}

	public function wasRefreshed(){
		return $this->wasRefreshed;
	}

	public function getTimestampRefreshed($file){
		clearstatcache();
		return filectime($this->filePathPrefix.$file);
	}

	public function parseRSS( $url, $file, $patience = 300 ){
		$result = array();
		if (
			!extension_loaded('simplexml')||
			!extension_loaded('dom')||
			!extension_loaded('libxml')
		)
			return $result;

		clearstatcache();
		if (!file_exists($this->filePathPrefix.$file) || (time() - filemtime($this->filePathPrefix.$file) > $patience)){
			$this->wasRefreshed = true;
			$rss=file_get_contents($url);
			file_put_contents($this->filePathPrefix.$file, $rss);
			$xml = simplexml_load_file($this->filePathPrefix.$file);
			$limit = count($xml->channel->item);
			$channelTitle = strval($xml->channel->title);
			//print "$channelTitle\n";
			$rssFlavor = "csdb";
			if ( substr($channelTitle,0,5) == "TMA: ")
				$rssFlavor = "mod";
			//$limit = 10;

			for($i = 0; $i < $limit; $i++){
				$type = array();
				$title = $xml->channel->item[$i]->title;
				$link = $xml->channel->item[$i]->link;
				if ($rssFlavor === "mod")
				{
					$typeB = strrpos( $title, "(" );
					$typeE = strrpos( $title, ")", $typeB );
					if ( $typeB > 0 && $typeE > 0)
					{
						$type[] = substr($title, $typeB+1, $typeE - $typeB -1);
						$title = substr($title, 0, $typeB);
					}
				}
				$dlLink = "";
				if ($rssFlavor !== "csdb" ) //|| $this->urlHasValidCSDBPrefix( strval($link) ))
					$dlLink = strval($link);

				$description = $xml->channel->item[$i]->description;
				$pubDate = $xml->channel->item[$i]->pubDate;
				$dc = $xml->channel->item[$i]->children("http://purl.org/dc/elements/1.1/");
				$by_f64 = strval($dc->creator);
				if ($by_f64 === "") $by_f64 = strval($xml->channel->item[$i]->author);
				//print "dc: ".$by_f64."\n";
				if ($rssFlavor === "mod")
				{
					$x = explode("?", $dlLink);
					if (count($x) > 2)
						$guid = $x[1];
					else
						$guid = "";
				}
				else
				{
					$guid_link = $xml->channel->item[$i]->guid;
					$id = explode("=", $guid_link);
					if ( count($id) > 1){
						$id = explode("&", $id[1]);
						$guid = $id[0];
					}
					else
						$guid = strval($guid_link);
					//print " guid: $guid_link\n";
				}
				// set error level
				$internalErrors = libxml_use_internal_errors(true);

				$doc = new \DOMDocument();
				$doc->loadHTML($description);
				// Restore error level
				libxml_use_internal_errors($internalErrors);

				$imgTag = $doc->getElementsByTagName("img");
				$imgSource = "";
				if ( $imgTag && $imgTag[0] && $imgTag[0]->attributes){
					foreach ( $imgTag[0]->attributes as $attr){
						if ($attr->name == "src")
							$imgSource = $attr->value;
					}
				}

				$a = $doc->getElementsByTagName("a");
				$filename = "";
				$by = array();
				if ($by_f64 !== "") $by[]= $by_f64;
				$id = "";
				foreach ( $a as $e)
				{
					//print_r($e);
					foreach ($e->attributes as $x)
						if ( $x->name === "href" )
						{
							/*
							if ($rssFlavor === "csdb")
							{
								if (strpos($x->value,"scener") !== false ||
									strpos($x->value,"group") !== false )
										$by[] = trim(strval($e->textContent));
								else if (strpos($x->value,"rrelease_type") !== false )
									$type[] = trim(strval($e->textContent));
							}
							else */
							if ($rssFlavor === "mod")
							 	if ( $this->urlHasValidModarchivePrefix($x->value))
									$dlLink = $x->value;

							//print "by: $x->value\n";
						}
						else if ($x->name ==="title" )
						{
							if ( $rssFlavor === "mod")
								if ($filename === "")
									$filename = $x->value;
								else
									$by[] = $x->value;
						}
				}

				$size = "";
				if ($rssFlavor === "mod")
				{
					$ii = preg_match("/.*?<\/a> \((.*?B)\)<\/b>/", $description, $match);
					if ( count($match) === 2)
					{
						//print($match[1]."\n");
						$size = $match[1];
					}
				}

				$result[] = array(
					"author" => $this->u8d->utf8_2_ascii(html_entity_decode(implode(", ", $by), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401)),
					"type" => html_entity_decode(implode(", ", $type)),
					"url" => $dlLink,
					"screenshoturl" => $imgSource,
					"filename" => html_entity_decode($filename),
					"title" => $this->u8d->utf8_2_ascii( html_entity_decode(strval($title), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401)),
					"date" => html_entity_decode(strval($pubDate)),
					"description" => $this->u8d->utf8_2_ascii(html_entity_decode(strip_tags(
							str_replace(array("<br /><br />","<br />","<br>"), "\n", $description)
					))),
					"id" => $guid,
					"size" => $size
				);
			}
			file_put_contents($this->filePathPrefix.$file.".cached", serialize($result));
			//print_r($result);
		}
		else
			$result = unserialize(file_get_contents($this->filePathPrefix.$file.".cached"));
		return $result;
	}

	protected function urlHasValidModarchivePrefix( $url ){
		return substr($url,0,50) === "https://api.modarchive.org/downloads.php?moduleid=";
	}
}
?>
