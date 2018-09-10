<?php
global $_globals;
if(file_exists("includes/plugins/BS Carousel/locked.txt")) {
	echo "Already installed";
} else {
	if($stmt = @$_globals['sql']->query("CREATE TABLE `" . $_globals['pfx']. "plugin_bs-carousel` (
	  `carouselID` int(11) NOT NULL AUTO_INCREMENT,
	  `title` varchar(255) NOT NULL default '',
	  `title_color` varchar(255) NOT NULL default '',
	  `text` varchar(255) NOT NULL default '',
	  `text_color` varchar(255) NOT NULL default '',
	  `link` varchar(255) NOT NULL default '',
	  `sort` int(2) NOT NULL default '0',
	  PRIMARY KEY  (`carouselID`)
	) AUTO_INCREMENT=1
	  DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci", array(), true)) {
	$fp = fopen("includes/plugins/BS Carousel/locked.txt", 'w');
	fwrite($fp, ' ');
	fclose($fp);
		echo "Done / Fertig";
	} else {
		generateError("warning", $_pluginsLang['plugin'], "Carousel ADM(i): ");
		return false;
	}
} 
 ?>