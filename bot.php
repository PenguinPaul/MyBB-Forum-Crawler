<?php
/**
- Copyright 2012 Paul H. - http://github.com/PenguinPaul
- Do whatever you want as long as you leave this notice intact.
*/

//the forum to crawl, no trailing slash
define("SITEURL","http://localhost/mybb");

//your mybbuser cookie
define("MYBBUSER_COOKIE","");



//get the last URL we were at

//get a URL to pick a link from
if(isset($_GET['url']))
{
	//the user specified one
	$url = $_GET['url'];
} else {
	//get one from the previous oage
	$url = file_get_contents("currenturl.txt");
}

$forbidden = "editpost.php,managegroup.php,modcp.php,moderation.php,newreply.php,newthread.php,polls.php,printthread.php,private.php,ratethread.php,report.php,reputation.php,sendthread.php,usercp.php,warnings.php";

//get all the links on the page
function urlLooper($url)
{
	$urlArray = array();

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);

	$regex='|<a.*?href="(.*?)"|';
	preg_match_all($regex,$result,$parts);
	$links=$parts[1];
	foreach($links as $link){
		array_push($urlArray, $link);
	}
	curl_close($ch);
	
	//this array is only links that are in the site.
	$finalUrlArray[] = SITEURL;
	foreach($urlArray as $value)
	{
		//make relative links the full path
		$pos = strpos($value, "http://");
		if ($pos === false) {
			$value = SITEURL."/".$value;
		}
		
		//add to the array
		$finalUrlArray[] = $value;
	}

	return $finalUrlArray; 
}


//pick a URL
function getUrl()
{
	global $ua,$burl;
	
	shuffle($ua);
	//output the link we're looking at ATM
	echo $ua[0]."<br />";
	
	$forbidden = explode(",", $forbidden);
	if(in_array($ua[0], $forbidden))
	{
		//forbidden, get annother one
		$burl = getUrl();
	}

	//is this URL internal?  We don't want to go to external links.
	$pos = strpos($ua[0], SITEURL);
	
	if ($pos === false) {
		//external, get another one
	    $burl = getUrl();
	} else {
		//internal, return
	    return $ua[0];
	}
}



//get the URL's we can choose from
$ua = urlLooper($url);
//pick one
getUrl();

//visit it
exec('curl -b "mybbuser='.MYBBUSER_COOKIE.'" '.$ua[0]);

//write the URL to the log
$fh = fopen("currenturl.txt", "w+");
fwrite($fh, $ua[0]);
?>