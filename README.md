<!-- Copy and paste the converted output. -->

<!-----
NEW: Check the "Suppress top comment" option to remove this info from the output.

Conversion time: 0.571 seconds.


Using this Markdown file:

1. Paste this output into your source file.
2. See the notes and action items below regarding this conversion run.
3. Check the rendered output (headings, lists, code blocks, tables) for proper
   formatting and use a linkchecker before you publish this page.

Conversion notes:

* Docs to Markdown version 1.0β29
* Sun Oct 18 2020 10:03:20 GMT-0700 (PDT)
* Source doc: Parallel Curl (PHP Client)
* Tables are currently converted to HTML tables.
----->



# Parallel Curl (PHP Client)


## Applicable Cases



1. 
Get Parameter


2. 
Post Parameter (form-data)


3. 
Post Parameter (raw-data)

## How to Run?


<table>
  <tr>
   <td><strong><em>php &lt;codename>.php &lt;filename>.csv &lt;num_thread_default_10></em></strong>
   </td>
  </tr>
  <tr>
   <td><em>eg. php code.php inputfile.csv 4</em>
   </td>
  </tr>
</table>



## Import Library


```
<?php
require_once('parallelcurl_v2.php');
```



## Define Command Line Parameter


```
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
```



## Set Curl Options


```
$curl_options = array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",    
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30000,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_USERAGENT, 'Parallel Curl by Abhisek',
    // CURLOPT_HTTPHEADER => array("content-type: multipart/form-data") // UnComment it for Case 2
    // CURLOPT_HTTPHEADER => array("content-type: application/json")    // UnComment it for Case 3
);
```



## Initiate Parallel Curl Object


```
$parallel_curl = new ParallelCurl($max_requests, $curl_options);
```



```
Code for Case 1 (Get)
Define URL
define ('URL_PREFIX', 'http://10.72.22.128:85/rest-fs-v1/serveFile/');
Read file & Parallel Call for each data
$file = fopen($argv[1], 'r');
$break_count=1;
while (($line = fgetcsv($file)) !== FALSE) {
  $fileid = $line[0];
  if ($fileid != 'File ID') {
  	$search_url = URL_PREFIX.''.urlencode($fileid);    
    
    // Case 1 (Get)
    $parallel_curl->startRequest($search_url, 'on_request_done', $fileid);
    
    print_r($break_count.') '.URL_PREFIX.''.urlencode($fileid).PHP_EOL);	  	
    $break_count=$break_count+1;
    // if ($break_count >1) // total 1 chunks will be processed
    // 	  break;
  }
}
fclose($file);
Code for Case 2 (Post Form-Data)
Define URL
define ('URL_PREFIX', 'http://www.dataentry.ndl.iitkgp.ac.in/services/v3/extractMetadataFromId/doi');
Read file & Parallel Call for each data
$file = fopen($argv[1], 'r');
$break_count=1;
while (($line = fgetcsv($file)) !== FALSE) {
  $fielid = $line[0];
  $post_data = array (
      "value" => $fielid
  );
  // Case 2 (Post form-data)
  $parallel_curl->startRequest(URL_PREFIX, 'on_request_done', array(), $post_data);

  print_r($break_count.') '.URL_PREFIX.PHP_EOL);     
  $break_count = $break_count+1;
  if ($break_count > 1) // total 10 files will be processed
    break;
}
fclose($file);
Code for Case 3 (Post Raw Data)
Define URL
define ('URL_PREFIX', 'http://www.dataentry.ndl.iitkgp.ac.in/services/v3/checkFieldValues');
Read file which contain values
$file = fopen($argv[1], 'r');
$break_count=1;
$fields = array();
while (($line = fgetcsv($file)) !== FALSE) {
  array_push ($fields, $line[0]);
}
fclose($file);
Parallel Call for each chunk of data
foreach (array_chunk($fields, 2000) as $chunk) {
  $post_data = array ( 
      "schema" => "general", 
      "field" => "dc.contributor.author",
      "values" => $chunk,
      "minify" => true
  );
  
  // Case 3 (Post raw-data)
  $parallel_curl->startRequest(URL_PREFIX, 'on_request_done', array(), json_encode($post_data));

  print_r($break_count.') '.URL_PREFIX.PHP_EOL);
  $break_count=$break_count+1;
  // if ($break_count >1) // total 1 chunks will be processed
  // 	break;
}

```



## Finish all leftover requests (if any)


```
// This should be called when you need to wait for the requests to finish.
// This will automatically run on destruct of the ParallelCurl object, so the next line is optional.
$parallel_curl->finishAllRequests();
```



## Print the response


```
// This function gets called back for each request that completes
function on_request_done($content, $url, $ch, $search) {
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);    
    if ($httpcode !== 200) {
        print "Fetch error $httpcode for '$url'\n";
        return;
    } 
    print_r($content.PHP_EOL);
}
?>
```

