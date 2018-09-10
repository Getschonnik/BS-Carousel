<?php
global $_plugins, $_globals;

	// get current language
	$csl = getLanguage();
	
	// set language to read
	$translateCaptions = new staticLanguage($csl);
	
	// read plugin informations from pluginCache
	try {
		$myCarousel = $_plugins->findFromCache($_plugins->cache, "BS Carousel");	
	} CATCH(Exception $e) {
		generateError("error", $_pluginsLang['plugin']." ".$_pluginsLang['error'], $_pluginsLang['plugins_cache_failure']);
		return false;
	}
	
	// load language file
	$_carouselLangClass = new language("carousel", $myCarousel['path']."languages/");
	$_carouselLang = $_carouselLangClass->load();
	
	// COUNT items
	$itemsCount = 0;
	if($stmt = @$_globals['sql']->query("SELECT * FROM `".$_globals['pfx']."plugin_bs-carousel` WHERE 1 ORDER BY sort", array(), true)) {
		$itemsCount = count($stmt);
	} else {
		generateError("warning", $_pluginsLang['plugin'], "Carousel ADM(0): ".$_carouselLang['no-entries'].'&nbsp;<a href="index.php?p=bs-carousel-adm">'.$_carouselLang['New'].'</a>');
		return false;
	}
	
	// set empty innerItem
	$myInnerItems = "";
	
	for($runs=0; $runs<$itemsCount; $runs++) {
	
		// set CaptionBlock if necessary																						
		if(isset($stmt[$runs]['title'])) { 
		
					$db_title	=	$stmt[$runs]['title'];
					$translateCaptions->detectLanguages($db_title);
					$caption['{title}'] = $translateCaptions->getTextByLanguage($db_title);
					if(empty($stmt[$runs]['title_color'])) {
						$caption['{title_color}'] = "#000000";
					} else {
						$caption['{title_color}'] = $stmt[$runs]['title_color'];
					}
					if(empty($stmt[$runs]['title_color'])) {
						$caption['{text_color}'] = "#000000";
					} else {
						$caption['{text_color}'] = $stmt[$runs]['text_color'];
					}
			
					$db_info	=	$stmt[$runs]['text'];	
					$translateCaptions->detectLanguages($db_info);
					$caption['{message}'] = $translateCaptions->getTextByLanguage($db_info);		
		
			$show_caption = new template("bs-carousel", $myCarousel['path']."templates/");
			$myCaptionBlock = $show_caption->showTemplate("captionBlock", $caption);
		} else {
			$myCaptionBlock = "";
		}
		
		// set Indicators
		$myIndicators = "";
		$indicatorData = array();
		for($i=0; $i<$itemsCount; $i++) {
			if($i==0) { $indicatorData['$isActive'] = 'class="active"'; } else { $indicatorData['$isActive'] = ""; }
			$indicatorData['$number'] = $i;
			$show_indicators = new template("bs-carousel", $myCarousel['path']."templates/");
			$myIndicators .= $show_indicators->showTemplate("indicator", $indicatorData);
		}
		
		// set innerItems																								
			$innerData = array();
			if($runs==0) { $innerData['$isActive'] = "active"; } else { $innerData['$isActive'] = ""; }
			
			if(strpos($stmt[$runs]['link'], 'http') !== false) {
				$innerData['$imagePath'] = $stmt[$runs]['link'];
			} else {
				$innerData['$imagePath'] = $myCarousel['path'].$stmt[$runs]['link'];	
			}
			
			
			$innerData['{altName}'] = $caption['{title}'];
			$innerData['$captionBlock'] = $myCaptionBlock;
			$show_innerItems = new template("bs-carousel", $myCarousel['path']."templates/");
			$myInnerItems .= $show_innerItems->showTemplate("innerItems", $innerData);
		
	}
	
	// add admin button 
	$adm = "";
	if(isset($_SESSION['userid'])) {
		if($_globals['user']->getAccess(intval($_SESSION['userid']), "manager")) {
			$preparedData = array();
			$preparedData['{edit}'] = $_carouselLang['Edit'];
			$preparedData['$innerItems'] = $myInnerItems;
			$show_mycarousel = new template("bs-carousel", $myCarousel['path']."templates/");
			$adm = $show_mycarousel->showTemplate("adminButton", $preparedData);
		} 
	}
	
	// show prepared carousel
	$preparedData = array();
	$preparedData['$indicators'] = $myIndicators;
	$preparedData['$innerItems'] = $myInnerItems;
	$preparedData['$adm'] = $adm;
	$show_mycarousel = new template("bs-carousel", $myCarousel['path']."templates/");
	echo $show_mycarousel->showTemplate("main", $preparedData);

	?>