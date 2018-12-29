<?php

// export views  CouchDB

//----------------------------------------------------------------------------------------
function get($url)
{
	$data = null;
	
	$opts = array(
	  CURLOPT_URL =>$url,
	  //CURLOPT_FOLLOWLOCATION => TRUE,
	  CURLOPT_RETURNTRANSFER => TRUE
	);
	
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data = curl_exec($ch);
	$info = curl_getinfo($ch); 
	
	$http_code = $info['http_code'];
	
	curl_close($ch);
	
	return $data;
}

$views = array(
	'fragments' => array('export', 'queue', 'triples')
);


foreach ($views as $database => $views)
{
	foreach ($views as $view)
	{
		$url = 'http://127.0.0.1:5984/' . $database . '/_design/' . $view;
		$resp = get($url);
		
		$obj = json_decode($resp);
		
	
		file_put_contents('couchdb/' . $view . '.js', json_encode($obj, JSON_PRETTY_PRINT));
	}
}
		


?>