# Days of War Live Gameserver Status Banner
![Example1](https://dowserverstatus.net/dow.91te.de/21015/banner.png)
![Example2](https://dowserverstatus.net/dow.91te.de/22015/banner.png)

The DoW Live Gameserver Status Banner highly customizable and yet still works right out of the box if you're fine with the default settings!

###INSTALLATION:
**Download [the latest version](https://github.com/FeuerSturm/serverstatus/releases/latest)**, extract it and upload the contents
to your webserver.

###USAGE:
By default the script requires the ip address and query port of the DoW gameserver you want to show
a live status banner for, the format is as follows:

    url/to/serverstatus/status.php?ip=<ip address here>&port=<query port here>

Example:

    https://yourdomain.com/serverstatus/status.php?ip=31.186.250.10&port=27015

I think you get the idea!

###CUSTOMIZATION:
If you're not happy with the default settings/look, you can customize the status banner to your likings,
just edit the "**config.php**" in the "config" folder with a text editor (I'd recommend [NotePad++](https://notepad-plus-plus.org/)) and you'll find all possible options!

Here's some stuff that you can edit:
* ability to bind status banner to single gameserver so it's not needed to supply ip and port via URL
* change images for game logo, default background for unknown maps, error logo, error background
* change font used and font size for data/error messages
* enable/disable GeoIP features to display country flag according to gameserver's location, can be set manually as well
* choose between simplified and normal map names, i.e. simply "Thunder" instead of "dow_thunder_dayrain"
* show either game or query port in the status banner
* change font & shadow colors for all different texts
* set max length of server name before cropping it
* adjust cache time (default 60seconds, min 10sec, max 300sec)
* enable/disable gameserver IP filter to prevent others from using your hosting to display their gameservers
* change the texts for error messages and descriptions, so you can translate them to your language

I've added a lot of comments to the settings, so I hope it's easy to understand what to change, if not, just ask!


###CREDITS:

    based on PHP-Source-Query Library by xPaw - https://github.com/xPaw/
    included DoW game logo created by Switz [3rd MAR] - http://3rdmarines.org/
    included error & lock images by FreeIconPNG - http://www.freeiconspng.com
    included country flag icons by Mark James - http://www.famfamfam.com
    included font "SpecialElite" by Astigmatic - http://www.astigmatic.com/
    GeoIP features by ARTIA INTERNATIONAL S.R.L. - http://ip-api.com
    Days of War by Driven Arts - http://drivenarts.github.io/



