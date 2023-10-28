# sktp-server
SKTP Server for Commodore 64

PHP application that allows to generate web based text screens for Commodore 64/128 (and Plus/4) and allows user interaction (keyboard, joystick) via Sidekick64 with network kernel or WiC64 (with specific sktp-client, see below). It sends URLs for launchable Payload (PRG, CRT, D64, etc.) to the network expansion for "Remote Code Execution" on the Commodore 64 (desired, not a security issue in this case ;) ).

SKTP stands for "Sidekick64 Transfer Protocol". The server and client talk via this simple binary protocol on top of HTTP.

The sktp-server is only useful with sktp-clients. Three different clients exist at this time:
* The C/C++ client within the Sidekick64 network kernel. This was the first client existing and is still the main client: https://github.com/hpingel/Sidekick64
* The Javascript client (which ist part of sktp-server sources. It is useful for development, testing and debugging.
* A new sktp-client in assembly language that can be used with the WiC64 at the moment but it might also become usable with the Sidekick64 network kernel: https://github.com/hpingel/sktp-client

More documentation will be added on demand (maybe :-)).

# SKTP-Apps 

The sktp-server may ask different "apps" with different purposes. Existing apps are:
* "Petscii experiments" - Test screens that illustrate the different options to compose the screen content 
* CSDB Browser/Launcher (sourcecode not yet public at this time)
* HVSC Browser/Launcher (sourcecode not yet public at this time)
* RSS-Feed-Reader (sourcecode not yet public at this time)
* "Arena-App" (Multiuser Mini-Game + Chat) (sourcecode not yet public at this time)
* Launcher for the Kick-WebApp (mobile app for touch devices) (sourcecode not yet public at this time)
* Main menu (lists all other apps)

# More Resources

Sidekick64 network kernel Readme: 
https://github.com/hpingel/Sidekick64/blob/net-rebase-on-v0.51d/README_network.md

SKTP introduction and discussion (German language)
https://www.forum64.de/index.php?thread/120021-demo-textbasiertes-web-browsing-für-sidekick64-sktp-browser


# 3rd party components used and distributed with sktp-server packages
* tga.js 1.1.1, Copyright (c) 2013-2020 Vincent Thibault, Inc., License: MIT, https://github.com/vthibault/tga.js
* C64 TrueType fonts by Style, https://style64.org/c64-truetype, License: https://style64.org/c64-truetype/license
* Unscii fonts by Viznut, http://viznut.fi/unscii/
* php-qrcode by technicalguru, https://github.com/technicalguru/php-qrcode

# 3rd party tools that are expected outside of the webroot
* PHP8.x, PHP modules: MBString, SimpleXML, DOM, libXML, PDO SQLite, Zip, ImageMagick
* 7zip (/usr/bin/7z includes rar support)
* PSID64 v1.3 (https://github.com/hermansr/psid64)

# License
sktp-server is licensed under GNU General Public License v3.0.
