<?php

// get the dir we want to analyse
$curdir = "./";
if(isset($_GET['dir']) == true) {
	
	// calculate the optimal relative dir from 2 absolute paths
	$curdir = getOptimalPath();
}

// create breadcrumb bar
$breadcrumbHTML = createBreadCrumbBar($curdir);

// get the contents
$data = dirContents($curdir);

// show the contents visually
$dirContentHTML = generateContentsHTML($data);

// combine breadcrumb and dirContent
$totalHTML = $breadcrumbHTML . $dirContentHTML;


function createBreadcrumbBar($path) {
	// get the path items in arr
	$arr = explode("/", $path);
	array_pop($arr);
	
	$urlList = buildBreadcrumbUrlList($arr);
	
	$textContent = $arr;
	$breadcrumbHTML = createBreadcrumbHTML($textContent, $urlList);
	
	return $breadcrumbHTML;
}

function buildBreadcrumbUrlList($arr) {
	$urlList = [];
	$url = "";
	foreach($arr as $value) {
		$url .= $value . "/";
		array_push($urlList, $url);
	}
	
	return $urlList;
}

function createBreadcrumbHTML($textContent, $urlList) {
	$HTMLStr = '<div class="c">';
	$len = count($textContent);
	for($i = 0; $i<$len; $i++) {
		$text = $textContent[$i];
		$url = $urlList[$i];
		$itemHTML = createBreadcrumbItemHTML($text, $url);
		$HTMLStr .= $itemHTML;
		if($i < $len-1) {
			$HTMLStr .= '<b>/</b>';
		}
	}
	
	$HTMLStr .= '</div>';
	return $HTMLStr;
}

function createBreadcrumbItemHTML($text, $url) {
	$itemHTML = '<form>';
	$itemHTML .= '<button name="dir" value="'.$url.'">';
	$itemHTML .= $text;
	$itemHTML .= '</button></form>';
	return $itemHTML;
}

// grab the data we will use
function dirContents($path) {
	$arr = scandir($path);
	$arr2 = [];
	foreach($arr as $name) {
		$isdir = is_dir($path."/".$name);
		$item = array("isdir"=>$isdir, "name"=>$name);
		array_push($arr2, $item);
	}
	
	$arr3 = [
		"path" => $path,
		"contents" => $arr2
	];
	
	return $arr3;
}

// generates content html
function generateContentsHTML($data) {
	$HTMLStr = "";
	$contents = $data["contents"];
	$path = $data["path"];
	
	foreach($contents as $item) {
		$itemHTMLString = generateItemHTML($path, $item);
		$HTMLStr .= $itemHTMLString;
	}
	
	return $HTMLStr;
}

// generates html for 1 item
function generateItemHTML($path, $item) {
	$itemHTMLString = "";
	$itemHTMLString .= '<div>';
	$wholePath = $path .= $item["name"];
	
	$linkArr = createLinkHTML($wholePath, $item["isdir"]);
	$beginLink = $linkArr[0];
	$endLink = $linkArr[1];
	
	$itemHTMLString .= $beginLink;
	
	$itemHTMLString .= generateIcon($item["isdir"]);
	$itemHTMLString .= generateName($item["name"]);
	
	$itemHTMLString .= $endLink;
	
	$itemHTMLString .= "</div>";
	return $itemHTMLString;
}

// generates a form / anchor structure depending on file or dir
function createLinkHTML($path, $dir) {
	if($dir) {
		$beginLink = '<form><button name="dir" value="'.$path.'">';
		$endLink = '</button></form>';
	} else {
		$beginLink = '<a href="'.$path.'">';
		$endLink = '</a>';
	}
	
	return [
		$beginLink, 
		$endLink
	];
}

// generates an icon
function generateIcon($dir) {
	$class = "";
	if($dir) {
		$class .= "d";
	} else {
		$class .= "f";
	}
	
	$iconStr = '<i class="'.$class.'"></i>';
	return $iconStr;
}

// generates a name
function generateName($name) {
	$nameStr = $name;
	return $nameStr;
}


function getOptimalPath() {
	$base = "./";
	$cur = $_GET['dir'] . "/";
	$absbase = realpath($base);
	$abscur = realpath($cur);
	
	$rel = getRelativePath($absbase, $abscur);
	
	// add ./ if it does not start with it already
	if(substr($rel, 0, 2) != "./") { 
		$rel = "./" . $rel;
	}
	return $rel;
}


// calculates a relative path from 2 absolute paths
// I ripped this from stackoverflow: https://stackoverflow.com/questions/2637945/getting-relative-path-from-absolute-path-in-php
function getRelativePath($from, $to)
{
    // some compatibility fixes for Windows paths
    $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
    $to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;
    $from = str_replace('\\', '/', $from);
    $to   = str_replace('\\', '/', $to);

    $from     = explode('/', $from);
    $to       = explode('/', $to);
    $relPath  = $to;

    foreach($from as $depth => $dir) {
        // find first non-matching dir
        if($dir === $to[$depth]) {
            // ignore this directory
            array_shift($relPath);
        } else {
            // get number of remaining dirs to $from
            $remaining = count($from) - $depth;
            if($remaining > 1) {
                // add traversals up to first matching dir
                $padLength = (count($relPath) + $remaining - 1) * -1;
                $relPath = array_pad($relPath, $padLength, '..');
                break;
            } else {
                $relPath[0] = './' . $relPath[0];
            }
        }
    }
    return implode('/', $relPath);
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Directory lister</title>
<style>
body,button{font-size:16px}body{margin:.5em;font-family:sans-serif}.c button,.c form{display:inline-block;width:auto;border:0}div{margin-bottom:.25em}a,button{display:block;padding:.5em;text-decoration:none;color:#000;background:0 0;text-align:left;border:0;border-color:#fff}button{width:100%}form{margin:0}a:focus,a:hover,button:focus,button:hover{outline:0;cursor:pointer;background:#ccc;border-color:#ccc}i,i.d:after{background:#000}i{vertical-align:top;margin-right:.5em;display:inline-block}i:after{content:"";display:block;height:0;border-style:solid;border-color:transparent #fff transparent transparent}i.d{top:.25em;width:1.5em;height:1em;margin-bottom:.25em}i.d:after{top:-.25em;width:.5em;border-width:0 .25em .25em 0}i.f{width:1em;height:1.25em}i.f:after{left:.6em;width:0;border-width:0 .4em .4em 0}i,i:after{position:relative;border-right-color:inherit}b{margin:0 .5em}
</style>
</head>
<body>
<?php
	// display contents
	echo $totalHTML;
?>
</body>
</html>