<?php
	$domain = "http://www.yellowpages.com/";
	$home = "html/home.html";
	if(!file_exists($home)){
		$data = file_get_contents("http://www.yellowpages.com/search?search_terms=discount%20stores%2C%20dallas%20%2Ctx&geo_location_terms=75006");
		exit;
		$myfile = fopen($home, "w+") or die("Unable to open file!");
		fwrite($myfile, $data);
		fclose($myfile);
		
		
		
	} else {
		$data = file_get_contents("html/home.html");		
	}
	
	$stores = explode('<div class="info">',$data);
	
	//link in between
	/* <div class="media-thumbnail"><a href="***" data-analytics= */
	$links = preg_match_all('/<a [^>]*href="(.+)"/', $html, $match);
		
	foreach($stores as $store){
		$value=preg_match_all('/<a href=\"(.*?)\"/s',$store,$links);
		
	}
	echo $data;
	//count($stores);
?>