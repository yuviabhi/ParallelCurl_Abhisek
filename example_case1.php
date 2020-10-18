
<?php

// This code hit parallely upto a limited no of thread which is give as 2nd param 'max_requests'  
// Have to control total hit with break statement 


require_once('parallelcurl_v2.php');
define ('SEARCH_URL_PREFIX', 'http://<URL>/rest-fs-v1/serveFile/');

if (!isset($argv[1])) {
  print_r('Provide filename of download list as 1st parameter');
  exit(0);
}

if (!file_exists($argv[1])) {
    print_r('File not found!');
    exit(0);
}



if (isset($argv[2])) {
    $max_requests = $argv[2];
} else {
    $max_requests = 10; // default 10 concurrently
}

$curl_options = array(
    CURLOPT_SSL_VERIFYPEER => FALSE,
    CURLOPT_SSL_VERIFYHOST => FALSE,
    CURLOPT_USERAGENT, 'Parallel Curl by Abhisek',
);

$parallel_curl = new ParallelCurl($max_requests, $curl_options);

$file = fopen($argv[1], 'r');
$break_count=1;
while (($line = fgetcsv($file)) !== FALSE) {
  $fileid = $line[0];
  if ($fileid != 'File ID') {
  	$search_url = SEARCH_URL_PREFIX.''.urlencode($fileid);    
    $parallel_curl->startRequest($search_url, 'on_request_done', $fileid);
    print_r($break_count.') '.SEARCH_URL_PREFIX.''.urlencode($fileid).PHP_EOL);	  	
    $break_count=$break_count+1;
    if ($break_count >35) // total 10 files will be processed
    	break;
  }
}
fclose($file);

// This should be called when you need to wait for the requests to finish.
// This will automatically run on destruct of the ParallelCurl object, so the next line is optional.
$parallel_curl->finishAllRequests();

// This function gets called back for each request that completes
function on_request_done($content, $url, $ch, $search) {    
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);    
    if ($httpcode !== 200) {
        print "Fetch error $httpcode for '$url'\n";
        return;
    } 
    print_r('[SUCCESS] /home/ndl/Desktop/LOADTEST/'.$search.PHP_EOL);
    // file_put_contents('/home/ndl/Desktop/LOADTEST/'.$search.'.pdf', $content);
    // print_r($content.PHP_EOL);
}


?>
