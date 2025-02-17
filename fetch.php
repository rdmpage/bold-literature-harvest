<?php

error_reporting(E_ALL);

require_once(dirname(__FILE__) . '/HtmlDomParser.php');

use Sunra\PhpSimple\HtmlDomParser;

//----------------------------------------------------------------------------------------
function get($url, $content_type = '')
{	
	$data = null;

	$opts = array(
	  CURLOPT_URL =>$url,
	  CURLOPT_FOLLOWLOCATION => TRUE,
	  CURLOPT_RETURNTRANSFER => TRUE,
	  
	  CURLOPT_SSL_VERIFYHOST=> FALSE,
	  CURLOPT_SSL_VERIFYPEER=> FALSE,
	  
	);

	if ($content_type != '')
	{
		$opts[CURLOPT_HTTPHEADER] = array(
			"Accept: " . $content_type 
		);		
	}
	
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data = curl_exec($ch);
	$info = curl_getinfo($ch); 
	curl_close($ch);
	
	return $data;
}

$page_size = 500;
$offset = 0;

$count = 1;

$num_pages = 14;

for ($page = 1; $page <= $num_pages; $page++)
{
	$offset = ($page - 1) * $page_size;
	
	$url = 'https://v3.boldsystems.org/index.php/Public_Publication_PublicationSearch/getSearchResultPage';
	
	$url .= '?offset=' . $offset . '&limit=' . $page_size;
	
	$html = get($url);
	
	// extract publication ids
	$dom = HtmlDomParser::str_get_html($html);
		
	if ($dom)
	{	
		foreach ($dom->find('div[class=publicationHead] div[name=title]') as $div)
		{
			$id = $div->id;			
			
			$u = 'https://v3.boldsystems.org/index.php/Public_Publication_Download?pubids=' . $id;
			
			$ris = get($u);
			
			echo $ris;
			
			$filename = 'publications/' . $id . '.ris';
			
			if (!file_exists($filename))
			{			
				file_put_contents($filename, $ris);
		
				// Give server a break every 2 items
				if (($count++ % 2) == 0)
				{
					$rand = rand(1000000, 3000000);
					echo "\n-- ...sleeping for " . round(($rand / 1000000),2) . ' seconds' . "\n\n";
					usleep($rand);
				}
			}
		}
	}
}

?>
