<?php

// Borrows heavily from https://github.com/crubier/Hexastore/blob/master/index.js

//error_reporting(E_ALL);

require_once (dirname(__FILE__) . '/couchsimple.php');

//----------------------------------------------------------------------------------------
// Count number of triples matching this pattern
// Simple reduce query
function matching_count($url)
{
	global $config;
	global $couch;
	
	$count = 0;
	
	$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/" . $url);

	$response_obj = json_decode($resp);
	
	$count = $response_obj->rows[0]->value;
	
	return $count;
}

//----------------------------------------------------------------------------------------
// get triple [S,P,O]
function querySPO($query)
{
	global $config;
	global $couch;
	
	$result = array();
	
	$key = array($query['s'], $query['p'], $query['o']);

	$base_url = '_design/triples/_view/spo?key=' . urlencode(json_encode($key, JSON_UNESCAPED_SLASHES));
	
	$url = $base_url . '&group_level=3';
	
	$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/" . $url);

	$response_obj = json_decode($resp);
	
	foreach ($response_obj->rows as $row)
	{
		$result[] = $row->key;
	}

	$data = new stdclass;
	$data->triples = $result;
	$data->count = matching_count($base_url);
	return $data;
}


//----------------------------------------------------------------------------------------
// get [S,P]
function querySPX($query)
{
	global $config;
	global $couch;
	
	$result = array();
	
	$q = array($query['s'], $query['p']);
	$startkey = $q;

	$q[] = new stdclass;
	$endkey = $q;

	$base_url = '_design/triples/_view/spo?startkey=' . urlencode(json_encode($startkey, JSON_UNESCAPED_SLASHES)) 
	. '&endkey=' . urlencode(json_encode($endkey, JSON_UNESCAPED_SLASHES));
	
	$url = $base_url . '&group_level=3';
	
	$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/" . $url);
	
	$response_obj = json_decode($resp);	
	
	foreach ($response_obj->rows as $row)
	{
		$result[] = $row->key;
	}

	$data = new stdclass;
	$data->triples = $result;
	$data->count = matching_count($base_url);
	return $data;
}

//----------------------------------------------------------------------------------------
// get [S,X,X]
function querySXX($query)
{
	global $config;
	global $couch;
	
	$result = array();
	
	// [S]
	$q = array($query['s']);
	$startkey = $q;

	$q[] = new stdclass;
	$endkey = $q;

	$base_url = '_design/triples/_view/spo?startkey=' . urlencode(json_encode($startkey, JSON_UNESCAPED_SLASHES)) 
	. '&endkey=' . urlencode(json_encode($endkey, JSON_UNESCAPED_SLASHES));
	
	$url = $base_url . '&group_level=3';
	
	$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/" . $url);

	$response_obj = json_decode($resp);	
	
	foreach ($response_obj->rows as $row)
	{
		$result[] = $row->key;
	}

	$data = new stdclass;
	$data->triples = $result;
	$data->count = matching_count($base_url);
	return $data;
}

//----------------------------------------------------------------------------------------
// triples with predicate P
function queryXPX($query)
{
	global $config;
	global $couch;
	
	$result = array();
	
	// key is [P]
	$q = array($query['p']);
	$startkey = $q;

	$q[] = new stdclass;
	$endkey = $q;

	$base_url = '_design/triples/_view/pso?startkey=' . urlencode(json_encode($startkey, JSON_UNESCAPED_SLASHES)) 
	. '&endkey=' . urlencode(json_encode($endkey, JSON_UNESCAPED_SLASHES));
	
	$url = $base_url . '&group_level=3';	
	
	$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/" . $url);

	$response_obj = json_decode($resp);
	
	foreach ($response_obj->rows as $row)
	{
		// row key is [P,S,O], reorder to SPO
		// we need to reorder to match XPO
		$result[] = array($row->key[1], $row->key[0], $row->key[2]);
	}

	$data = new stdclass;
	$data->triples = $result;
	$data->count = matching_count($base_url);
	return $data;
}

//----------------------------------------------------------------------------------------
// [P,O]
function queryXPO($query)
{
	global $config;
	global $couch;
	
	$result = array();
	
	// index 
	// key is [P,O]
	$q = array($query['p'], $query['o']);
	$startkey = $q;

	$q[] = new stdclass;
	$endkey = $q;
	
	$base_url = '_design/triples/_view/pos?startkey=' . urlencode(json_encode($startkey, JSON_UNESCAPED_SLASHES)) 
	. '&endkey=' . urlencode(json_encode($endkey, JSON_UNESCAPED_SLASHES));
	
	$url = $base_url . '&group_level=3';
	
	$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/" . $url);

	$response_obj = json_decode($resp);
	
	foreach ($response_obj->rows as $row)
	{
		//print_r($row);
	
		// row key is [P,O,S], reorder to SPO
		// we need to reorder to match XPO
		$result[] = array($row->key[2], $row->key[0], $row->key[1]);
	}

	$data = new stdclass;
	$data->triples = $result;
	$data->count = matching_count($base_url);
	return $data;
}

//----------------------------------------------------------------------------------------
// [O]
function queryXXO($query)
{
	global $config;
	global $couch;
	
	$result = array();
	
	// index 
	// key is [O]
	$q = array($query['o']);
	$startkey = $q;

	$q[] = new stdclass;
	$endkey = $q;
	
	$base_url = '_design/triples/_view/osp?startkey=' . urlencode(json_encode($startkey, JSON_UNESCAPED_SLASHES)) 
	. '&endkey=' . urlencode(json_encode($endkey, JSON_UNESCAPED_SLASHES));

	$url = $base_url . '&group_level=3';
	
	$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/" . $url);

	$response_obj = json_decode($resp);
	
	foreach ($response_obj->rows as $row)
	{
		// row key is [O,S,P], reorder to SPO
		// we need to reorder to match XXO
		$result[] = array($row->key[1], $row->key[2], $row->key[0]);
	}

	$data = new stdclass;
	$data->triples = $result;
	$data->count = matching_count($base_url);
	return $data;
}

//----------------------------------------------------------------------------------------
// [all triples]
function queryXXX($query)
{
	global $config;
	global $couch;
	
	$result = array();
	
	$base_url = '_design/triples/_view/spo';
	$url = $base_url . '?group_level=3';
	
	$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/" . $url);

	$response_obj = json_decode($resp);
	
	foreach ($response_obj->rows as $row)
	{
		$result[] = $row->key;
	}

	$data = new stdclass;
	$data->triples = $result;
	$data->count = matching_count($base_url);
	return $data;
}

//----------------------------------------------------------------------------------------
function query_result_to_triples ($result)
{
	$data = array();

	foreach ($result->triples as $triple)
	{
		$row = array();
	
		$row[] = '<' . $triple[0] . '>';
		$row[] = '<' . $triple[1] . '>';
	
		if (preg_match('/^(https?|urn|_:)/', $triple[2]))
		{
			$row[] = '<' . $triple[2] . '>';
		}
		else
		{
			$row[] = $triple[2];
		}
		$data[] = $row;
	}

	return $data;
}

//----------------------------------------------------------------------------------------


if (0)
{
	$query = array(
		's' => 'https://www.wikidata.org/wiki/Q54517002',
		'p' => 'http://schema.org/name'
	);

	$query = array(
		'p' => 'http://schema.org/name'
	);


	$result = queryXPX($query);

	print_r($result);

	// convert to triples
	print_r(query_result_to_triples($result));

}

?>
