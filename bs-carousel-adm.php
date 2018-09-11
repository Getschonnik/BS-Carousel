<?php
global $_plugins, $_globals;

	// necessary accesslevel
	$accessLevel = "manager";

	// get current language
	$getLang = getLanguage();
	
	// prepare staticLanguage
	$translate = new staticLanguage($getLang);
	
	// read plugin informations from pluginCache
	try {
		$admCarousel = $_plugins->findFromCache($_plugins->cache, "BS Carousel");	
		if(empty($admCarousel)) {
			generateError("error", $_pluginsLang['plugin']." ".$_pluginsLang['error'], "Carousel ADM(0): ".$_pluginsLang['plugins_cache_failure']);
			return false;
		}
	} CATCH(Exception $e) {
		generateError("error", $_pluginsLang['plugin']." ".$_pluginsLang['error'], "Carousel ADM(0): ".$_pluginsLang['plugins_cache_failure']);
		return false;
	}

	// load language file from plugin path
	$getLang = new language("carousel", $admCarousel['path']."languages/");
	$lang = $getLang->load();
	
	// check accesslevel
	if(!isset($_SESSION['userid'])) {
		generateError("warning", $_pluginsLang['plugin']." ".$_pluginsLang['error'], "Carousel ADM(x): ".$lang['access-denied']);
		return false;
	} else {
		if(!@$_globals['user']->getAccess($_SESSION['userid'], $accessLevel)) {
			generateError("warning", $_pluginsLang['plugin']." ".$_pluginsLang['error'], "Carousel ADM(x): ".$lang['access-denied']);
			return false;
		}
	}
	
	// show title
	$titleData = array();
	$titleData['{administration}'] = $lang['Administration'];
	$showTitle = new template("bs-carousel-adm", $admCarousel['path']."templates/");
	echo $showTitle->showTemplate("title", $titleData);

// SaveEdit
	if(isset($_POST['editID'])) {
		$id = intval($_POST['editID']);
		if($stmt = @$_globals['sql']->query("SELECT * FROM `".$_globals['pfx']."plugin_bs-carousel` WHERE `carouselID` = ?", array("i",$id))) {
			$id = $stmt['carouselID'];
			$link = $stmt['link'];
		} else {
			generateError("error", $lang['Error'], "Carousel ADM(4.1): ".$lang['cannot-read-iddata']);
			return false;	
		}

		$targetDir = $admCarousel['path']."images/";
		$targetFile = $targetDir . basename($_FILES["fileToUpload"]["name"]);
		if(!empty($_FILES["fileToUpload"]["name"]) && $link != "images/".$targetFile) {
			if(file_exists($targetDir.basename($_FILES["fileToUpload"]["name"]))) {
				unlink($targetDir.basename($_FILES["fileToUpload"]["name"]));
			}
			$run = 1; $up = 0;
			$imageFileType = strtolower(pathinfo($targetFile,PATHINFO_EXTENSION));
			
			if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
				generateError("error", $lang['Error'], "Carousel ADM(4.2): ".$lang['this-extentsions-only']);
				$run = 0;
				return false;
			}		
			
			if ($run == 0) {
				generateError("error", $lang['Error'], "Carousel ADM(4.2): ".$lang['not-uploaded']);
				return false;
			} else {
				if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetFile)) {		
					echo $lang['upload-success'];
				$link = "images/".basename($_FILES["fileToUpload"]["name"]);
					$up = 1;
				} else {
					echo $lang['upload-failed'];
					echo $lang['info_upload-failed'];
				}
			}
		}
		
		if($stmt = @$_globals['sql']->query("UPDATE `db_plugin_bs-carousel` SET `title` = ?, `title_color` = ?, `text` = ?, `text_color` = ?, `link` = ? WHERE `db_plugin_bs-carousel`.`carouselID` = ?;
", array("sssssi",$_POST['titleedit'], $_POST['title_coloredit'], $_POST['textedit'], $_POST['text_coloredit'], $link, $id))) {
			echo $lang['update-success'];
		} else {
			generateError("error", $lang['Error'], "Carousel ADM(4.1): ".$lang['cannot-read-iddata']);
			return false;	
		}
		
		
		
		
		
		
		redirect("index.php?p=bs-carousel-adm", 5);
		return false;
	}
	
	
// Edit 
	if(isset($_GET['edit'])) {
		$id = intval(trim($_GET['edit']));
		$editData = array();
		if($stmt = @$_globals['sql']->query("SELECT * FROM `".$_globals['pfx']."plugin_bs-carousel` WHERE `carouselID` = ?", array("i",$id))) {
				$editData['{titlevalue}'] = $stmt['title'];
				$editData['{titlecolorvalue}'] = $stmt['title_color'];
				$editData['{textvalue}'] = $stmt['text'];
				$editData['{textcolorvalue}'] = $stmt['text_color'];
			} else {
				generateError("error", $lang['Error'], "Carousel ADM(4.0): ".$lang['cannot-read-iddata']);
				return false;
			}	
		$editData['{id}'] = $id;
		$editData['{Save}'] = $lang['Save'];
		$editData['{Edit}'] = $lang['Edit'];
		$editData['{Title}'] = $lang['Title'];
		$editData['{Title_color}'] = $lang['Title_color'];
		$editData['{Text}'] = $lang['Text'];
		$editData['{Text_color}'] = $lang['Text_color'];
		$editData['{Choose}'] = $lang['Choose'];
		$editData['{Upload}'] = $lang['Upload'];
		$showTitle = new template("bs-carousel-adm", $admCarousel['path']."templates/");
		echo $showTitle->showTemplate("adm-edit", $editData);		
		return false;
	}
	
// New 
	if(isset($_GET['new'])) {	
		$newData = array();
		$newData['{Save}'] = $lang['Save'];
		$newData['{New}'] = $lang['New'];
		$newData['{Title}'] = $lang['Title'];
		$newData['{Title_color}'] = $lang['Title_color'];
		$newData['{Text}'] = $lang['Text'];
		$newData['{Text_color}'] = $lang['Text_color'];
		$newData['{Choose}'] = $lang['Choose'];
		$newData['{Upload}'] = $lang['Upload'];
		$showTitle = new template("bs-carousel-adm", $admCarousel['path']."templates/");
		echo $showTitle->showTemplate("adm-new", $newData);			
		return false;
	}	
	
// New, save
	if(isset($_POST['title'])) {
		
		$targetDir = $admCarousel['path']."images/";
		$targetFile = $targetDir . basename($_FILES["fileToUpload"]["name"]);
		$run = 1; $up = 0;
		$imageFileType = strtolower(pathinfo($targetFile,PATHINFO_EXTENSION));
		
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
			generateError("error", $lang['Error'], "Carousel ADM(2): ".$lang['this-extentsions-only']);
			$run = 0;
			return false;
		}		
		
		if ($run == 0) {
			generateError("error", $lang['Error'], "Carousel ADM(2): ".$lang['not-uploaded']);
			return false;
		} else {
			if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetFile)) {		
				echo $lang['upload-success'];
				$up = 1;
			} else {
				echo $lang['upload-failed'];
				echo $lang['info_upload-failed'];
			}
		}
		$title = "";
		if(isset($_POST['title'])) {
			$title = htmlspecialchars($_POST['title']);
		}
		$title_color = "";
		if(isset($_POST['title_color'])) {
			$title_color = htmlspecialchars($_POST['title_color']);
		}
		$text = "";
		if(isset($_POST['text'])) {
			$text = htmlspecialchars($_POST['text']);
		}
		$text_color = "";
		if(isset($_POST['text_color'])) {
			$text_color = htmlspecialchars($_POST['text_color']);
		}
		
		if(empty($_FILES["fileToUpload"]["name"])) {
			$up = 0;
		} else {
			$link = "images/".basename( $_FILES["fileToUpload"]["name"]);
		}
		
		if($up==1) {
			if($stmt = @$_globals['sql']->query("INSERT INTO `".$_globals['pfx']."plugin_bs-carousel` (`carouselID`, `title`, `title_color`, `text`, `text_color`, `link`, `sort`) VALUES (NULL, ?, ?, ?, ?, ?, '0');", array("sssss",$title, $title_color, $text, $text_color, $link))) {
				echo $lang['save-success'];
			} else {
				generateError("error", $lang['Error'], "Carousel ADM(3.1): ".$lang['request-aborted']);
				return false;
			}			
		} else {
			generateError("error", $lang['Error'], "Carousel ADM(3): ".$lang['request-aborted']);
			return false;
		}
		redirect("index.php?p=bs-carousel-adm", 5);
		return false;
	}

// Delete
	if(isset($_GET['del'])) {
		$id = intval($_GET['del']);
		if($stmt = @$_globals['sql']->query("SELECT link FROM `db_plugin_bs-carousel` WHERE `carouselID` =?", array("i", $id))) {
			if(@unlink($admCarousel['path'].$stmt['link'])) {
				echo $lang['delete-file-success'];
			} else {
				echo $lang['delete-file-failed'];
			}		
		}
		
		
		if($stmt = @$_globals['sql']->query("DELETE FROM `".$_globals['pfx']."plugin_bs-carousel` WHERE `carouselID` = ?", array("i",$id))) {
			echo $lang['delete-success'];
		} else {
			echo $lang['delete-failed'];
		}
		redirect("index.php?p=bs-carousel-adm", 5);
		return false;	
	}
	
// Sort
	if(isset($_POST['postsort'])) {
		$cnt = count($_POST['carouselID']);	
		for($i=0; $i<$cnt; $i++) {
			echo $_POST['carouselID'][$i] . " - " . $_POST['sort'][$i]." <br />";
			$id = $_POST['carouselID'][$i]; 
			$srt = $_POST['sort'][$i];
			$stmt = $_globals['sql']->query("UPDATE `".$_globals['pfx']."plugin_bs-carousel` SET `sort` = ? WHERE `carouselID` = ?;", array("ii", $srt, $id));
		}
	}
		
// overview	
	
	// form (sort) start
	$showOverviewStart = new template("bs-carousel-adm", $admCarousel['path']."templates/");
	echo $showOverviewStart->showTemplate("overview_form_start", array());	
	

	
	// button new
	if($_globals['user']->getAccess($_SESSION['userid'], $accessLevel)) {
		$nbtn = array();
		$nbtn['{New}'] = $lang['New'];
		
		$showBtn0 = new template("bs-carousel-adm", $admCarousel['path']."templates/");
		echo $showBtn0->showTemplate("adm-btn-new", $nbtn);	
	} 
	
	// load entries from database
	if($stmt = @$_globals['sql']->query("SELECT * FROM `".$_globals['pfx']."plugin_bs-carousel` WHERE 1 ORDER BY sort", array(), true)) {
		for($i=0; $i<count($stmt); $i++) {
			$titleData = array();
			$titleData['$select_option'] = "";
			$optData = array();
			for($j=0; $j<count($stmt); $j++) {
			$optData['{selected}'] = "";
				if($j==$stmt[$i]['sort']) {
					$optData['{selected}'] = "selected";
				}
				$optData['{nr}'] = $j;
				$SoptData = new template("bs-carousel-adm", $admCarousel['path']."templates/");
				$titleData['$select_option'] .= $SoptData->showTemplate("select-option", $optData);
			}
			
			$titleData['$imgPath'] = $admCarousel['path'].$stmt[$i]['link'];
			$titleData['{id}'] = $stmt[$i]['carouselID'];
			
			// staticLanguage
			$ttl = $stmt[$i]['title'];
			$translate->detectLanguages($ttl);
			$titleData['{title}'] = $translate->getTextByLanguage($ttl);
			// staticLanguage
			$st = $stmt[$i]['text'];
			$translate->detectLanguages($st);
			$titleData['{shorttext}'] = $translate->getTextByLanguage($st);			
		
			// adm buttons
			if($_globals['user']->getAccess($_SESSION['userid'], $accessLevel)) {
				$ebtn = array();
				$ebtn['{id}'] = $stmt[$i]['carouselID'];
				$ebtn['{Edit}'] = $lang['Edit'];
				
				$showBtn1 = new template("bs-carousel-adm", $admCarousel['path']."templates/");
				$titleData['$adm-btn-edit'] = $showBtn1->showTemplate("adm-btn-edit", $ebtn);	
			} else {
				$titleData['$adm-btn-edit'] = "";
			}
			if($_globals['user']->getAccess($_SESSION['userid'], $accessLevel)) {
				$dbtn = array();
				$dbtn['{id}'] = $stmt[$i]['carouselID'];
				$dbtn['{Delete}'] = $lang['Delete'];
				
				$showBtn2 = new template("bs-carousel-adm", $admCarousel['path']."templates/");
				$titleData['$adm-btn-delete'] = $showBtn2->showTemplate("adm-btn-delete", $dbtn);	
			} else {
				$titleData['$adm-btn-delete'] = "";
			}
		
			$showOverview = new template("bs-carousel-adm", $admCarousel['path']."templates/");
			echo $showOverview->showTemplate("overview", $titleData);			
			
		}	
		
		// form (sort) end
		$endData = array();
		$endData['{Sort}'] = $lang['Sort'];			
		$showOverviewEnd = new template("bs-carousel-adm", $admCarousel['path']."templates/");
		echo $showOverviewEnd->showTemplate("overview_form_end", $endData);	
	
	} else {
		echo $lang['no-entries'];
		return false; 
	}
	
	



/*
INSERT INTO `db_plugin_bs-carousel` (`carouselID`, `title`, `title_color`, `text`, `text_color`, `link1`, `sort`) VALUES ('1', '{[en-EN]}The first title {[de-DE]}Der erste Titel', '#000000', '{[en-EN]}A text can stay here {[de-DE]}Ein text kann hier stehen', '#ffffff', '/images/1.jpg', '0');
*/

	// show footer
	$titleData = array();
	$titleData['{Author}'] = $lang['Author'];
	$titleData['{version}'] = $lang['version'];
	$showTitle = new template("bs-carousel-adm", $admCarousel['path']."templates/");
	echo $showTitle->showTemplate("footer", $titleData);

	

?>