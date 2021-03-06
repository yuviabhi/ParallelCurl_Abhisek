
<?php

// This code hit parallely upto a limited no of thread which is give as 2nd param 'max_requests'  
// Create chunks(size) and then hit all data

$start = microtime(true);
require_once('parallelcurl_v2.php');
define ('SEARCH_URL_PREFIX', 'http://<URL>/services/v3/extractMetadataFromId/doi');

if (!isset($argv[1])) {
  print_r('Provide filename as 1st parameter');
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
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",    
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30000,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_USERAGENT, 'Parallel Curl by Abhisek',
    CURLOPT_HTTPHEADER => array(
      "content-type: multipart/form-data"
    )
);

$parallel_curl = new ParallelCurl($max_requests, $curl_options);

$file = fopen($argv[1], 'r');
$break_count=1;
while (($line = fgetcsv($file)) !== FALSE) {
  $fielid = $line[0];
  $post_data = array (
      "value" => $fielid
  );
  $parallel_curl->startRequest(SEARCH_URL_PREFIX, 'on_request_done', array(), $post_data);
  print_r($break_count.') '.SEARCH_URL_PREFIX.PHP_EOL);     
  $break_count=$break_count+1;
  if ($break_count >1) // total 10 files will be processed
    break;
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
    print_r($content.PHP_EOL);
}

$time_elapsed_secs = microtime(true) - $start;
print_r($time_elapsed_secs. " seconds.");

?>
