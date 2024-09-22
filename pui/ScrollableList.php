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

class ScrollableList{

	private
		$x,
		$y,
		$width,
		$height,
		$items,
		$screen,
		$padLeft,
		$padLeftStr,
		$frame,
		$title;

	function __construct( \lib\sktpBaseScreen $screen, $x, $y, $width = 40, $height = 5, $padLeft = 1,$frame = false, $title ="" ){
		$this->items = array();
		$this->screen = $screen;
		$this->x = $x;
		$this->y = $y;
		$this->width = $width;
		$this->height = $height;
		$this->padLeft = $padLeft;
		$this->padLeftStr = str_repeat(" ", $this->padLeft);
		$this->frame = $frame;
		$this->title = $title;
		$this->screen->registerSessionVar( "listSelection", 0);
		$this->screen->registerSessionVar( "listSelectionOld", 0);
		$this->screen->registerSessionVar( "listScrollPos", 0);
		$this->screen->registerSessionVar( "listScrollPosOld", 0);
		$this->screen->registerSessionVar( "stateStack", array());
	}

	public function pushToStateStack(){
		$a = $this->screen->getSessionVar("stateStack");
		$a[] = array(
			$this->screen->getSessionVar( "listSelection" ),
			$this->screen->getSessionVar( "listScrollPos" )
		);
		$this->screen->setSessionVar("stateStack", $a);
	}

	public function popStateStack(){
		$a = $this->screen->getSessionVar("stateStack");
		if ( count($a) == 0){
			$this->screen->setSessionVar( "listSelection", 0);
			$this->screen->setSessionVar( "listScrollPos", 0);
		}
		else{
			$b = array_pop($a);
			$this->screen->setSessionVar( "listSelection", $b[0]);
			$this->screen->setSessionVar( "listScrollPos", $b[1]);
			$this->screen->setSessionVar("stateStack", $a);
		}
		$this->screen->setSessionVar( "listSelectionOld", $this->screen->getSessionVar("listSelection"));
		$this->screen->setSessionVar( "listScrollPosOld", $this->screen->getSessionVar("listScrollPos"));
	}

	public function addListItem( $name, $type, $action, $key ){
		$this->items[] = array(
			"name" => $name,
			"type" => $type,
			"action" => $action,
			"key" => $key
		);
	}

	public function deleteListItems($clean = false){
		$this->items = array();
		if ($clean){
			$this->screen->setSessionVar( "listSelection", 0);
			//$this->screen->setSessionVar( "listSelectionOld", 0);
			$this->screen->setSessionVar( "listScrollPos", 0);
			//$this->screen->setSessionVar( "listScrollPosOld", 0);
		}
	}

	public function renderCompleteList(){
		$this->renderList( true );
	}

	public function updateSelection(){
		$this->renderList( false );
	}

	public function getItemCount(){
		return count($this->items);
	}

	public function getHeight(){
		return $this->height;
	}

	public function setTitle( $title ){
		$this->title = $title;
	}

	public function getSelectedAction(){
		return $this->items[ $this->screen->getSessionVar("listSelectionOld") ]["action"];
	}

	public function scrollDownPossible(){
		return ( $this->getItemCount() - $this->screen->getSessionVar("listScrollPos") > $this->height);
	}

	public function scrollUpPossible(){
		return ( $this->screen->getSessionVar("listScrollPos") > 0);
	}

	private function clearListCanvas(){
		for ($row = $this->y; $row < $this->height + $this->y; $row ++){
			$this->screen->addCharRepeatChunk(" ", ($row*40)+$this->x, $this->width, "0");
		}
	}

	private function renderList( $modeComplete) {
		$scrollPos = $this->screen->getSessionVar("listScrollPos");
		$selection = $this->screen->getSessionVar("listSelection");
		if ( $this->screen->getSessionVar("listScrollPosOld") <> $scrollPos )
			$modeComplete = true;
		if ($modeComplete){
			//$this->clearListCanvas();
			if ( $scrollPos > 0 )
				$visibleItemCount = $this->height; //always full
			else
				$visibleItemCount = ( count($this->items) < $this->height ) ? count($this->items) : $this->height;
			for ($c=$scrollPos; $c < $scrollPos+$visibleItemCount; $c++)
				$this->renderListItem($c, $c === $selection, $scrollPos);
//			for ($d=0; $d < $this->height - $visibleItemCount; $d++)
//				$this->screen->addCharRepeatChunk("*", (($c+$d)*40)+$this->x, $this->width, "0");

			if ($this->frame){
				$this->screen->drawBox($this->x - 1,$this->y-1,$this->width + 2 ,$this->height+2,"A");
				if ($this->getItemCount() > $this->height)
					$this->drawSelectPosition($selection+1);
				if ($this->title !== "")
					$this->screen->addNormalChunkXY( $this->title, $this->x +intval(($this->width-strlen($this->title))/2),$this->y-1,"D");

				//add scroll position marker
				if ( $this->getItemCount() > $this->getHeight()){
					$pages = intval($this->getItemCount()/ $this->height + 0.5);
					$barHeight = ($this->height-2)/$pages;
					$barHeightInt = intval($barHeight); 
					if ($barHeightInt == 0) $barHeightInt = 1;

					if ( !$this->scrollDownPossible()){
						$relativeScrollPos = $this->height - $barHeightInt;
					}
					else if ( !$this->scrollUpPossible()){
						$relativeScrollPos = 0;
					}
					else{
						$currentPage = intval(($scrollPos / $this->height));
						$relativeScrollPos = 1 + intval(($currentPage)*$barHeight);
						if ( $relativeScrollPos < 0 ) $relativeScrollPos = 0;
					}
					$pos = $this->x + $this->width + 40*($this->y+ $relativeScrollPos);
					//don't paint the scroll position marker into nirvans
					if ($pos < 999 - 40*$barHeightInt)
						$this->screen->addVerticalScreenCodeCharChunk(chr(93+128), $pos, $barHeightInt, "A");
				}
			}
//			$this->screen->addNormalChunkXY( " sel:". $selection. "/  sc: " . $scrollPos." / ".$this->getItemCount() . "/h:".$this->height." " , 4, $this->y+$this->height, "A");
		}
		else if ( $this->screen->getSessionVar("listScrollPosOld") === $scrollPos ){
			$this->renderListItem($this->screen->getSessionVar("listSelectionOld"), false, $scrollPos);
			$this->renderListItem($selection, true, $scrollPos);
			if ($this->frame && $this->getItemCount() > $this->height){
				$this->drawSelectPosition($selection+1);
			}

//			$this->screen->addNormalChunkXY( " sel:". $selection. "/  sc: " . $scrollPos." / ".$this->getItemCount() . "/h:".$this->height." " , 4, $this->y+$this->height, "A");
		}
 		$this->screen->setSessionVar("listSelectionOld", $selection);
		$this->screen->setSessionVar("listScrollPosOld", $scrollPos);
 	}

	public function focusEntry($listaction){
		foreach ($this->items as $no => $data){
			if ($data["key"] === $listaction){
				$this->screen->setSessionVar("listSelection", $no);
				break;
			}
		}
	}

	private function drawSelectPosition($selection){
		$s = " ".$selection."/".$this->getItemCount()." ";
		$this->screen->addNormalChunkXY( $s, $this->x + $this->width - strlen($s) -1, $this->y+$this->height, "A");
	}

	private function renderListItem($line, $selected, $scrollOffset){
		$content = "";
		$color="1";
		if ( array_key_exists($line, $this->items)){
			$item = $this->items[$line];
			$reduce = ( $item["key"] !== "" )? 2:0;
			if ( strlen($item["name"]) > $this->width-$reduce)
				$item["name"] = substr($item["name"],0,$this->width-$reduce);

			$color = ($item["action"] === "") ? "1" : "7";// white or yellow
			if ($selected)
				$color = strval(dechex(hexdec(strtolower($color))+128));
			$linepadChar = $item["key"] !== "" ? ".":" ";
			if ( $item["key"] !== "" )
				$content =
					str_pad( $item["name"] ." ", $this->width-1 - 2*$this->padLeft - strlen($item["key"]), ".", STR_PAD_RIGHT).
			 		" ". $item["key"];
			else
				$content = str_pad( $item["name"] , $this->width - 2*$this->padLeft, " ", STR_PAD_RIGHT);
		}
		$override_padLeft = $this->padLeft;
/*
		$padding = "";
		if ( $this->padLeft > 0)
			if (!$selected){
				$override_padLeft = 0;
				$padding = str_repeat(" ", $this->padLeft );
				$content = $padding . $content. $padding;
			}
			else{
				$this->screen->addNormalChunkXY( $padding, $this->x , $this->y+$line-$scrollOffset, "0");
				$this->screen->addNormalChunkXY( $padding, $this->x+$this->width-1, $this->y+$line-$scrollOffset, "0");
			}
*/
		$this->screen->addNormalChunkXY( $content, $this->x +$override_padLeft, $this->y+$line-$scrollOffset, $color);
	}

	public function handleKeypress($key){
		$key_pageNext = $this->screen->isClientOn264() ? $this->screen::PETSCII_KEY["F2"] : $this->screen::PETSCII_KEY["F3"]; 
		switch ($key) {
			case $this->screen::PETSCII_KEY["crsr_up"]:
					$scrollUp = ( $this->screen->getSessionVar("listSelection") == $this->screen->getSessionVar("listScrollPosOld") ) && $this->scrollUpPossible();
					if ( $this->screen->getSessionVar("listSelection") > 0 || $scrollUp)
					{
						$this->screen->decreaseSessionVar("listSelection", 1);
						$this->screen->setSessionVar("listScrollPos", $this->screen->getSessionVar("listScrollPosOld") - ($scrollUp ? 1 : 0));
					}
					return true;
					break;
			case $this->screen::PETSCII_KEY["crsr_down"]:
					$scrollDown = ( $this->screen->getSessionVar("listSelection") - $this->screen->getSessionVar("listScrollPosOld") > $this->getHeight()-2) && $this->scrollDownPossible();
					if ( $this->screen->getSessionVar("listSelection") < $this->getItemCount()-1 || $scrollDown)
					{
						$this->screen->increaseSessionVar("listSelection", 1);
						$this->screen->setSessionVar("listScrollPos", $this->screen->getSessionVar("listScrollPosOld") + ($scrollDown ? 1 : 0));
					}
					return true;

					break;
			case $key_pageNext:
					if ( $this->getItemCount() < $this->getHeight()){
						return false;
					}
					else if ($this->screen->getSessionVar("listScrollPos") + 2*$this->getHeight() < $this->getItemCount()){
						$this->screen->increaseSessionVar("listScrollPos", $this->getHeight());
						$this->screen->setSessionVar("listSelection", $this->screen->getSessionVar("listScrollPos"));
						return true;
					}
					else if ($this->screen->getSessionVar("listScrollPos") > $this->getItemCount() - 2*$this->getHeight()){
						$this->screen->setSessionVar("listScrollPos", $this->getItemCount() - $this->getHeight() );
						if ( $this->screen->getSessionVar("listScrollPos") != $this->screen->getSessionVar("listScrollPosOld")){
							$this->screen->setSessionVar("listSelection", $this->screen->getSessionVar("listScrollPos"));
						}
						return true;
					}
					else {
						$this->screen->setSessionVar("listSelection", $this->getItemCount()-1);
						$this->screen->setSessionVar("listScrollPos", $this->getItemCount() - $this->getHeight() );
						return true;
					}
					break;
			case $this->screen::PETSCII_KEY["F1"]:
					if ($this->screen->getSessionVar("listScrollPos") > $this->getHeight()){
						$this->screen->decreaseSessionVar("listScrollPos", $this->getHeight());
						$this->screen->setSessionVar("listSelection", $this->screen->getSessionVar("listScrollPos"));
						return true;
					}
					else if ($this->screen->getSessionVar("listScrollPos") > 0){
						$this->screen->setSessionVar("listScrollPos", 0);
						$this->screen->setSessionVar("listSelection", 0);
						return true;
					}
					break;
			default:
		}
		return false;
	}
}

?>
