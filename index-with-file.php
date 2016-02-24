<?php
	$domain = "http://www.yellowpages.com";
	
	
	$page_count = 1;
	
	
	if(!empty($_REQUEST["file"])){
		$locations = file($_REQUEST["file"].".php");

	} else {	
		$location = $_REQUEST["location"];
		$locations[] = $location;
		$encoded_location = urlencode($location);
		if(empty($encoded_location)){
			echo "enter location ?location=[location]";
			exit;	
		}
	}
	
	foreach($locations as $location){	
		$dir = "html/";
		$list = array();
		
		if(!preg_match(",", $location)){
			if(!empty($_REQUEST["file"])){
				$location = $location.",".$_REQUEST["file"];
			} else {
				$location = $location.",TX";
			}
		}
		
		$location_clean = preg_replace('/[^a-zA-Z0-9\s]/', '', strtolower($location)); 
		$location_clean = preg_replace('!\s+!', '-', $location_clean); 
	
		$encoded_location = urlencode($location);
		if(empty($encoded_location)){
			echo "enter location ?location=[location]";
			exit;	
		}
		
		$search_kw = urlencode($_REQUEST["search"]);
		$search_kw_clean = preg_replace('/[^a-zA-Z0-9\s]/', '', strtolower($search_kw)); 
		$search_kw_clean = preg_replace('!\s+!', '-', $search_kw_clean); 
		
		$search_kw = urlencode($_REQUEST["search"]);
		$search_kw = urlencode($_REQUEST["search"]);
		
		if(!file_exists($dir.$location_clean)){
			mkdir($dir.$location_clean);
			chmod($dir.$location_clean, 0777);
			if(!file_exists($dir.$location_clean."/".$search_kw_clean)){
				mkdir($dir.$location_clean."/".$search_kw_clean);
				$dir = $dir.$location_clean."/".$search_kw_clean."/";
				chmod($dir, 0777);
			}
		} else {
			//$dir = $dir.$location_clean."/";
			$dir = $dir.$location_clean."/".$search_kw_clean."/";
		}
		
		$home = $dir."home.html";
		$page = $home;
		
		if(empty($_REQUEST["search"])){
			$_REQUEST["search"] = "discount store";
		}
			
		
		
		for($page_count = 1; $page_count <= 20; $page_count++){
			if($page_count > 1){
				//http://www.yellowpages.com/search?search_terms=discount%20store&geo_location_terms=75006&page=2
				$page = $dir."page".$page_count.".html";
				//$domain."/search?search_terms=discount%20stores%2C%20dallas%20%2Ctx&geo_location_terms=75006"
				if(!file_exists($page)){
					$data = file_get_contents($domain."/search?search_terms=$search_kw&geo_location_terms=$encoded_location&page=".$page_count);
					$myfile = fopen($page, "w+") or die($page." Unable to open file!");
					fwrite($myfile, $data);
					fclose($myfile);	
				} else {
					$data = file_get_contents($page);		
				}
				
			} else {
				$page = $home;
				//echo $domain."/search?search_terms=$search_kw&geo_location_terms=$encoded_location";
				//exit;
				//$domain."/search?search_terms=discount%20stores%2C%20dallas%20%2Ctx&geo_location_terms=75006"
				if(!file_exists($page)){
					$data = file_get_contents($domain."/search?search_terms=$search_kw&geo_location_terms=$encoded_location");
					$myfile = fopen($page, "w+") or die($page ." Unable to open file!");
					fwrite($myfile, $data);
					fclose($myfile);	
				} else {
					$data = file_get_contents($page);		
				}
			}
			
			
			
			$stores = explode('<div class="info">',$data);
		
			unset($stores[0]);
			$n = 0;
			foreach($stores as $store){
			
				$value=preg_match_all('/class=\"business-name\">(.*?)<\/a>/s',$store,$business);
				$business_name_raw = $business[1][0];
				$business_info = array();
				
				$business_info[] = $business_name_raw;
				
				// remove all non alphanumeric characters except spaces
				$clean =  preg_replace('/[^a-zA-Z0-9\s]/', '', strtolower($business_name_raw)); 
				
				// replace one or multiple spaces into single dash (-)
				$business_name =  preg_replace('!\s+!', '-', $clean); 
				$business_info[] = $business_name;
		
				$value=preg_match_all('/<a href=\"(.*?)\"/s',$store,$links);
		
				//primary link exists in index 0
				if(!file_exists($dir.$business_name.".html")){
					$fetch_url = $domain . $links[1][0];
					
				} else {
					$fetch_url = $dir.$business_name.".html";
				}
				
				//fetch contents
				$data = file_get_contents($fetch_url);
				
				//Store in file to avoid repeat fetching
				if(!file_exists($dir.$business_name.".html")){
					$myfile = fopen($dir.$business_name.".html", "w+") or die("Unable to open file!");
					fwrite($myfile, $data);
					fclose($myfile);
				}
				
				
				//parse the store html
				//echo "<pre>";
						
				//get address
				//<p class="street-address">10201 Plano Rd Ste 114, </p>
				$value = preg_match_all('/<span itemprop=\"streetAddress\" class=\"street-address\">(.*?)<\/span>/s',$store,$address);
				//print_r($address);
				$business_info[] = $address[1][0];
				
				//get state
				//<p class="city-state">
				$value = preg_match_all('/<span itemprop=\"addressLocality\" class=\"locality\">(.*?)<\/span>/s',$store,$city);
				//print_r($city);
				$business_info[] = $city[1][0];
				
				//get state
				//<span itemprop=\"addressRegion\">
				$value = preg_match_all('/<span itemprop=\"addressRegion\">(.*?)<\/span>/s',$store,$state);
				//print_r($state);
				$business_info[] = $state[1][0];
				
				//get zip
				//<span itemprop="postalCode">
				$value = preg_match_all('/<span itemprop=\"postalCode\">(.*?)<\/span>/s',$store,$zip);
				//print_r($zip);
				$business_info[] = $zip[1][0];
				
				//get phone number
				//<p class="phone">(214) 342-1100</p>
				$value = preg_match_all('/<div itemprop=\"telephone\" class=\"phones phone primary\">(.*?)<\/div>/s',$store,$phone);
				//print_r($phone);
				$business_info[] = $phone[1][0];
					
				$business_data = explode('<div class="business-card clearfix"', $data);
				
				//get website
				//<a class="custom-link" target="_blank" rel="nofollow" data-analytics="{&quot;adclick&quot;:true,&quot;events&quot;:&quot;event7,event6&quot;,&quot;category&quot;:&quot;8000134&quot;,&quot;impression_id&quot;:&quot;c62a08a0-5619-4211-891f-00ced7708682&quot;,&quot;listing_id&quot;:&quot;12932205&quot;,&quot;item_id&quot;:-1,&quot;listing_type&quot;:&quot;free&quot;,&quot;ypid&quot;:&quot;12932205&quot;,&quot;content_provider&quot;:&quot;MDM&quot;,&quot;srid&quot;:&quot;8f1d74a4-a9b3-4cb7-8f1c-6ea040c757cf&quot;,&quot;item_type&quot;:&quot;listing&quot;,&quot;lhc&quot;:&quot;8000134&quot;,&quot;ldir&quot;:&quot;IDS&quot;,&quot;rate&quot;:0,&quot;click_id&quot;:6,&quot;target&quot;:&quot;website&quot;,&quot;act&quot;:2,&quot;dku&quot;:&quot;http://www.dallascarpetoutlet.com&quot;,&quot;supermedia&quot;:true,&quot;LOC&quot;:&quot;http://www.dallascarpetoutlet.com&quot;}" href="http://www.dallascarpetoutlet.com" data-impressed="1">Visit Website</a>
				$value = preg_match_all('/\"LOC\":\"(.*?)\"}\' rel=\"nofollow\" target=\"_blank\" class=\"custom-link\"/s',$business_data[0],$website);
				//$value = preg_match_all('/class=\"(.*?)\">Visit Website/s',$business_data[0],$website);
				//$value = preg_match_all('/href=\"(.*?)\" data-impressed=\"1\">Visit Website/s',$business_data[0],$website);
				$business_info[] = $website[1][0];
				
				
				//get email
				//<a class="email-business" data-analytics="{&quot;adclick&quot;:true,&quot;events&quot;:&quot;event7,event6&quot;,&quot;category&quot;:&quot;8000134&quot;,&quot;impression_id&quot;:&quot;c62a08a0-5619-4211-891f-00ced7708682&quot;,&quot;listing_id&quot;:&quot;12932205&quot;,&quot;item_id&quot;:-1,&quot;listing_type&quot;:&quot;free&quot;,&quot;ypid&quot;:&quot;12932205&quot;,&quot;content_provider&quot;:&quot;MDM&quot;,&quot;srid&quot;:&quot;8f1d74a4-a9b3-4cb7-8f1c-6ea040c757cf&quot;,&quot;item_type&quot;:&quot;listing&quot;,&quot;lhc&quot;:&quot;8000134&quot;,&quot;ldir&quot;:&quot;IDS&quot;,&quot;rate&quot;:0,&quot;click_id&quot;:3,&quot;target&quot;:&quot;email&quot;,&quot;dku&quot;:&quot;/dallas-tx/mip/dallas-carpet-outlet-discount-flooring-store-12932205?lid=12932205&quot;,&quot;act&quot;:5}" rel="nofollow" href="mailto:gregg@dallascarpetoutlet.com" data-impressed="1">Email Business</a>
				$value = preg_match_all('/<a href=\"mailto:(.*?)\"/s',$business_data[0],$email);
				$business_info[] = $email[1][0];
				
				$list[] = implode("|", $business_info)."\n";
				
				//echo "</pre>";
				//echo "<br>\n";
			
				$n++;
				
			}
			
		}
		
		@unlink("list-$location_clean-$search_kw_clean.csv");
		$myfile = fopen("list-$location_clean-$search_kw_clean.csv", "w+") or die("Unable to open file!");
		fwrite($myfile, implode("|", $list));
		fclose($myfile);
		//count($stores);
	}
?>