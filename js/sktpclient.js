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

var sktpUrl = "./sktp.php?",
	sktpv = 5,
	sessionID = false,
	screenData = false,
	screenChunks = new Array(),
	pointer = false,
	screenClean = false,
	screenbytes = new Array(),
	screencolor = new Array(),
	keyRepeated = 0,
	isTimeOutActive = false,
	isCharsetLowerCase = true,
	tga = false,
	debug = false,
	screenColorBackground = false;

var chunkTypeDocumentation ={
	0 : "normal/ascii",
	1 : "repeat",
	2 : "screencode",
	3 : "meta_refresh",
	4 : "colorcharset",
	5 : "vertical repeat screencode",
	6 : "paintbrush",
	7 : "tft_image_url",
	8 : "tft_image_inline"
}

/* pepto */
var colors64 = {
	0 : "#000000",
	1 : "#ffffff",
	2 : "#68372B",
	3 : "#70A4B2",
	4 : "#6F3D86",
	5 : "#588D43",
	6 : "#352879",
	7 : "#B8C76F",
	8 : "#6F4F25",
	9 : "#433900",
	10 : "#9A6759",
	11 : "#444444",
	12 : "#6C6C6C",
	13 : "#9AD284",
	14 : "#6C5EB5",
	15 : "#959595"
};

function getSessionId( tgai ){
	var client = "sk";
	debug = (window.location.hash === "#debug");
	if (!debug){
		debug = (window.location.hash === "#debugwic");
		if (debug) client = "wic";
	}
	tga = tgai;
	var oReq = new XMLHttpRequest();
	oReq.open("GET", sktpUrl + "session=new&sktpv="+sktpv+"&type=64&username=webtester&f="+client);
	oReq.addEventListener("load", startSession);
	oReq.send();
}

function startSession(){
	sessionID = this.responseText;
	document.addEventListener('keydown', logKey);
	getScreen();
}

function logKey(e) {
	var kc = e.keyCode;
	if (e.repeat && keyRepeated < 4){
		keyRepeated++;
		return;
	}
	keyRepeated = 0;
	if (kc == 60 && e.shiftKey)
		kc = 62;
	else if (e.shiftKey && kc == 48)
		kc = 61;
	else if (e.shiftKey && kc > 48 && kc < 58){//0-9
		kc -= 16;
	}
	else if (kc > 64 && kc < 91){//a-z
		if (e.shiftKey) kc += 32;
	}
	else switch (kc) {
		case 27: //escape
			kc = 95;
			break;
		case 8: //backspace
			kc = 20;
			break;
		case 112: //F1
			kc = 133;
			break;
		case 113://F2
			kc = 137;
			break;
		case 114://F3
			kc = 134;
			break;
		case 115://F4
			kc = 138;
			break;
		case 116://F5
			kc = 135;
			break;
		case 118://F6
			kc = 139;
			break;
		case 119://F7
			kc = 136;
			break;
		case 120://F8
			kc = 140;
			break;
		case 38: //crsr_up
			kc = 145;
			break;
		case 40: //crsr down
			kc = 17;
			break;
		case 37: //crsr_left
			kc = 157;
			break;
		case 39: //crsr_right
			kc = 29;
			break;
		case 163: //hash
			kc = e.shiftKey ? 39 : 35 ;
			break;
		case 171: //plus
		case 187: //plus
			kc = e.shiftKey ? 42 : 43 ;
			break;
		case 173: //minus
			kc = 45;
			break;
		case 188: //komma
			kc = e.shiftKey ? 59 : 44 ;
			break;
		case 190: //fullstop
			kc = e.shiftKey ? 58 : 46 ;
			break;
		case 192: //hochkomma
			kc = 39;
			break;
		default:
	}
	var keyHex = keyHexHelper(kc);
	//console.log(e.key + " " + e.keyCode + " " + kc + " " + keyHex + " " + keyHex.length);
	getScreen(keyHex);
	if ( e.keyCode > 125 || e.keyCode < 121) //F9-F12
		e.preventDefault();
}

function handleClick(){
	var target = event.target.id;
	var key = 0;
	switch (target){
		case "key_f1":
			key = 133;
			break;
		case "key_f3":
			key = 134;
			break;
		case "key_f5":
			key = 135;
			break;
		case "key_crsr_up":
			key = 145;
			break;
		case "key_crsr_down":
			key = 17;
			break;
		case "key_crsr_left":
			key = 157;
			break;
		case "key_crsr_right":
			key = 29;
			break;
		case "key_arrow_left":
			key = 95;
			break;
		case "key_return":
			key = 13;
			break;
		case "key_c":
			key = 67;
			break;
		case "key_d":
			key = 68;
			break;
		case "key_f":
			key = 70;
			break;
		case "key_p":
			key = 80;
			break;
		case "key_q":
			key = 81;
			break;
		case "key_s":
			key = 83;
			break;
		case "key_t":
			key = 84;
			break;
		case "key_x":
			key = 88;
			break;

		default:
	}
	getScreen(keyHexHelper(key));
}


function keyHexHelper(kc){
	var keyHex = kc.toString(16);
	keyHex = keyHex.toUpperCase();
	if (keyHex.length == 1)
		keyHex = "0" + keyHex;
	return keyHex;
}

function getScreen(key = ""){
	var oReq = new XMLHttpRequest();
	var url = sktpUrl + "sessionid=" + sessionID;
	//var url = sktpUrl + "s =" + sessionID; //shorthand
	if (key !== "")
		url+= "&key="+key;
	oReq.open("GET", url, true);
	oReq.responseType = "arraybuffer";
	oReq.onload = function (oEvent) {
		isCharsetLowerCase = true;
		var arrayBuffer = oReq.response; // Note: not oReq.responseText
		var tmpChunk = false;
		if (arrayBuffer) {
			screenData = new Uint8Array(arrayBuffer);
			if ( screenData.byteLength == 0){
				console.error("Error: Empty screen.");
				return;
			}
			if (debug)
				console.log("[[screen]] byteLength: " + screenData.byteLength + " screenType:" + screenData[0]);
			screenClean = screenData[0] == 0;
			screenChunks = new Array();
			pointer = 1;
			if ( screenData[0] > 3){
				console.log("skipping parsing, unknown screen type");//:" + screenData);
				var error = "";
				for (var x=0; x<screenData.length;x++)
					error += String.fromCharCode(screenData[x]);
				console.log(error);
			}
			else if ( screenData[0] == 3)
			{
				getSessionId(); //session id has timed out
			}
			else if ( screenData[0] != 2)
			{
				while (pointer < screenData.byteLength){
					tmpChunk =  getScreenChunk();
					if (tmpChunk !== false)
						screenChunks.push( tmpChunk );
				}
				renderScreen();
			}
			else{
				offerFileDownload();
			}
		}
	};
	oReq.send(null);
};

function getFiledataChunk(chunk)
{
	chunk.url_length      = screenData[ pointer++ ];
	chunk.filename_length = screenData[ pointer++ ];
	chunk.save_flag       = parseInt(screenData[ pointer++ ]);
	chunk.url ="";
	chunk.filename ="";
	for (var z = 0; z < chunk.url_length; z++)
		chunk.url += String.fromCharCode(screenData[z+pointer]);
	pointer += chunk.url_length;
	for (z = 0; z < chunk.filename_length; z++)
		chunk.filename += String.fromCharCode(screenData[z+pointer]);
	pointer += chunk.filename_length;
	return chunk;
}

function offerFileDownload(chunk){
	var chunk = getFiledataChunk({});
	chunkDebugData(chunk);
	var question = "Do you want to download '" + chunk.filename + "' from\n" + chunk.url + "?";
	if ( confirm( question )){
		window.location.href = chunk.url;
	}
	getScreen();
}

function replaceTFTScreenImageInline(chunk){
	var l =
		screenData[ pointer++ ] *65536 +
		screenData[ pointer++ ] *256 +
		screenData[ pointer++ ];
	chunk.length = l;
	var buffer = new Array();
	for (var z = 0; z < l; z++)
		buffer.push( screenData[z+pointer]);
	const u8a = new Uint8Array(buffer);
	tga.load( u8a );
	document.getElementById("tga_container").style.display="block";
	document.getElementById("tga_container").replaceChild(tga.getCanvas(),document.getElementsByTagName("canvas")[0])
	const elem = document.getElementById('tgadownloadlink');
	elem.href = window.URL.createObjectURL(new Blob([u8a], {type: 'image/tga'}));
	elem.download = "sidekick64Ready.tga";
	pointer += l;
	chunkDebugData(chunk);
}

function replaceTFTScreenImageFromURL(chunk){
	var chunk = getFiledataChunk(chunk);
	document.getElementById("tga_container").style.display="block";
	tga.open(chunk.url, () => {
	document.getElementById("tga_container").replaceChild(tga.getCanvas(),document.getElementsByTagName("canvas")[0]);
	});
	const elem = document.getElementById('tgadownloadlink');
  	elem.href = chunk.url;
	chunkDebugData(chunk);
}

/*
function sc2petscii(x){
	if (x < 32)
		x += 64;
	else if (x < 64 ) x = x;
	else if (x < 94 ) x +=128;
	else if (x < 128 ) x +=64;
	else if (x < 160 ) x -=128;
	else if (x < 192 ) x -=64;
	else if (x < 224 ) x -=64;
	return x;
}
*/
function getScreenChunk(){
	var chunk = {}, z;
	chunk.startpos= pointer;
	chunk.type    = parseInt(screenData[pointer++]);
	if (chunk.type == 8)
	{
		replaceTFTScreenImageInline(chunk);
	}
	else if (chunk.type == 7)
	{
		replaceTFTScreenImageFromURL(chunk);
	}
	else if (chunk.type <= 2 || chunk.type >= 5)
	{
		//type, length, screenposL, screenposH, color/gap, repeatcount, content
		chunk.length  = parseInt(screenData[ pointer++ ]);
		chunk.pos     = screenData[ pointer++ ];
		var posMSB = screenData[ pointer++ ];
		chunk.pos += (posMSB & 15) *256;
		if (chunk.type < 3 || chunk.type == 6)
			chunk.length += (posMSB & 16 ) *16 + (posMSB & 32 ) *16;
//		console.log(" - chunk type: " + chunk.type + " length: " + chunk.length);
		chunk.color   = screenData[ pointer++ ];
		if (chunk.type == 6 )
			chunk.gap = chunk.color;
		if (chunk.type >= 5 )
			chunk.repeat = screenData[ pointer++ ];
		chunk.content = new Array();
		if (chunk.type == 0)
		{
			for (z = 0; z < chunk.length; z++)
				chunk.content[z] = screenData[z+pointer];
			pointer += chunk.length;
		}
		else if (chunk.type == 1) //repeat
		{
			for (z = 0; z < chunk.length; z++)
				chunk.content[z] = screenData[pointer];
			pointer ++;
		}
		else if (chunk.type == 2 || chunk.type == 5 || chunk.type == 6)
		{
			for (z = 0; z < chunk.length; z++)
				chunk.content[z] = parseInt(screenData[z+pointer]);
			pointer += chunk.length;
		}
		else{
			console.error("unknown type <2:" + chunk.type + " / " + parseInt(chunk.type));
		}
		
		chunkDebugData(chunk);
	}
	else if (chunk.type === 3){
		chunk.timeout  = parseInt(screenData[ pointer++ ]);
		if (!isTimeOutActive){
			window.setTimeout( function(){isTimeOutActive = false; getScreen(keyHexHelper(0));},chunk.timeout*20);
			isTimeOutActive = true;
		}
		chunkDebugData(chunk);
	}
	else if (chunk.type === 4){
		chunk.colorBorder = screenData[ pointer++ ]-1;
		chunk.colorBackground = screenData[ pointer++ ]-1;
		isCharsetLowerCase = (screenData[ pointer++ ] == 1);
		chunk.isCharsetLowerCase = isCharsetLowerCase;
		var domscreenBackground = document.getElementById("screen");
		domscreenBackground.style = "background-color: "+ colors64[chunk.colorBackground] +
			 "; border-color: " + colors64[chunk.colorBorder] + ";";
		chunkDebugData(chunk);
		screenColorBackground = chunk.colorBackground;
	}
	else {
		console.error("unknown type:" + chunk.type + " / " + parseInt(chunk.type));
		pointer++;
		return false; 
	}
	return chunk;
}

function chunkDebugData(chunk){
	if (!debug) return;
	var s = "";
	for (var a in chunk){
		s += a + ": " + chunk[a] + ", ";
	}
	console.log(
		"[" + chunkTypeDocumentation[chunk.type] + "] "+ s
	);
}

function renderScreen(){
	if ( screenbytes.count == 0 || screenClean)
	{
		screenbytes = new Array();
		screencolor = new Array();
		var space = petsciimap(32);
		for (var z= 0; z < 1000; z++){
			screenbytes[z] = space;
			screencolor[z] = 1;
		}
	}
	// 60928 EE00 uppercase screencode base
	// 61184 EF00 mixed case screencode base
	var screenCodeUBase = isCharsetLowerCase ? 61184 : 60928;

	for ( var z=0; z < screenChunks.length; z++ ){

		if ( screenChunks[z].type == 0 ) //normal chunk
		{
			for ( var c=0; c < screenChunks[z].length; c++ ){
				screenbytes[ screenChunks[z].pos + c] = petsciimap(parseInt( screenChunks[z].content[c]));
				screencolor[ screenChunks[z].pos + c] = screenChunks[z].color;
			}
		}
		else if ( screenChunks[z].type == 1 ) //repeat chunk
		{
			for ( var c=0; c < screenChunks[z].length; c++ ){
				screenbytes[ screenChunks[z].pos + c] = petsciimap(parseInt( screenChunks[z].content[c]));
				screencolor[ screenChunks[z].pos + c] = screenChunks[z].color;
			}
		}
		else if ( screenChunks[z].type == 2 ){ //screencode chunk
			for ( var c=0; c < screenChunks[z].length; c++ ){
				screenbytes[ screenChunks[z].pos + c] =
					 "&#" + (screenCodeUBase + screenChunks[z].content[c])+ ";";
				screencolor[ screenChunks[z].pos + c] = screenChunks[z].color;
			}
		}
		else if ( screenChunks[z].type == 5 ){ //screencode vertical repeat chunk
			for ( var r=0; r < screenChunks[z].repeat; r++ ){
				for ( var c=0; c < screenChunks[z].length; c++ ){
					screenbytes[ screenChunks[z].pos + c + 40 * r] =
						 "&#" + (screenCodeUBase + screenChunks[z].content[c])+ ";";
					screencolor[ screenChunks[z].pos + c + 40 * r] = screenChunks[z].color;
				}
			}
		}
		else if ( screenChunks[z].type == 6 ){ //paintbrush chunk
			var pbpos, oldScreenColor;
			for ( var r=0; r < screenChunks[z].repeat +1; r++ ){
				for ( var c=0; c < screenChunks[z].length; c++ ){
					pbpos = screenChunks[z].pos + c + (r * (screenChunks[z].gap + screenChunks[z].length));
					//console.log("paintbrush r" + r + " pbpos " + pbpos);
					oldScreenColor = screencolor[ pbpos];
					screencolor[ pbpos] = parseInt(screenChunks[z].content[c]);
					if (screencolor[ pbpos]==16) screencolor[ pbpos]=0;
					if (oldScreenColor>127) screencolor[ pbpos] += 128;
				}
			}
		}
	}

	var domscreen = document.getElementById("screen");
	var buffer = "";
	var lastColor = false;
	var lastInverse = false;
	for (var z=0; z < 1000; z++){
		if ( screencolor[z] !== lastColor)
		{
			if (lastColor !== false)
				buffer +="</span>";

			var color = parseInt(screencolor[z]);
			var inverse = color > 127;
			color = inverse ? color -128 : color;

			if (inverse)
			buffer +='<span class="c64col_inverse_'+ color +' c64col_'+screenColorBackground+'">';
			else
				buffer +='<span class="c64col_'+ color +'">';
			lastColor = screencolor[z];
		}

		buffer += screenbytes[z];
		//linebreak after 40 chars
		if ( parseInt((z+1)/40) === (z+1)/40)
			buffer += "<br/>";
	}
	buffer += "</span>";
	domscreen.innerHTML = buffer;
}

function petsciimap(b){
	if ( b == 92) //pound key
	{
		return "&#163;";
	}
	else if ( b == 94) //arrow up
	{
		return "&#8593;";
	}
	else if ( b == 95 ) //arrow left = escape
	{
		return "&#x2190;";//"&#8592;";
	}
	else if ( b == 96) //horizontal line
	{
		return "&#9472;";
	}
	else if ( b < 123)
	{
		var tmpByte = parseInt(b);
		if ( isCharsetLowerCase){
			if ( tmpByte > 96 && tmpByte < 123)
				tmpByte -= 32;
			else if ( tmpByte > 64 && tmpByte < 91)
				tmpByte += 32;
		}
		else{
			if ( tmpByte > 96 && tmpByte < 123)
				return "&#" + ( 60993 -97  + parseInt(b)) + ";";
		}
		return escapeHtml(String.fromCharCode(tmpByte));
	}
	else
	{
		 return "&#" + (57344 + parseInt(b) + (isCharsetLowerCase ? 256:0)) + ";";
	}
}

function escapeHtml(text) {
  var map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };

  return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}
