<?php

// Resolve one object

require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';

require_once(dirname(dirname(__FILE__)) . '/documentstore/couchsimple.php');

//----------------------------------------------------------------------------------------
function get($url, $user_agent='', $content_type = '')
{	
	$data = null;

	$opts = array(
	  CURLOPT_URL =>$url,
	  CURLOPT_FOLLOWLOCATION => TRUE,
	  CURLOPT_RETURNTRANSFER => TRUE
	);

	if ($content_type != '')
	{
		$opts[CURLOPT_HTTPHEADER] = array("Accept: " . $content_type);
	}
	
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data = curl_exec($ch);
	$info = curl_getinfo($ch); 
	curl_close($ch);
	
	return $data;
}


//----------------------------------------------------------------------------------------
function microcitation_reference ($guid)
{
	$data = null;
	
	$nt = get('http://localhost/~rpage/microcitation/www/rdf.php?guid=' . $guid);

	$doc = jsonld_from_rdf($nt, array('format' => 'application/nquads'));

	// Context to set vocab to schema
	$context = new stdclass;

	$context->{'@vocab'} = "http://schema.org/";

	// sameAs is always an array
	$sameAs = new stdclass;
	$sameAs->{'@id'} = "http://schema.org/sameAs";
	$sameAs->{'@container'} = "@set";

	// issn is always an array
	$issn = new stdclass;
	$issn->{'@id'} = "http://schema.org/issn";
	$issn->{'@container'} = "@set";


	$context->sameAs = $sameAs;
	$context->issn = $issn;


	$frame = (object)array(
		'@context' => $context,

		// Root on article
		'@type' => 'http://schema.org/ScholarlyArticle',
	);	

	$data = jsonld_frame($doc, $frame);
	
	return $data;
}

	
//----------------------------------------------------------------------------------------
function resolve_url($url)
{
	$doc = null;
	
	$guid = '';
	
	// keep things simple 
	if (preg_match('/https?:\/\/(dx\.)?doi.org\/(?<guid>.*)/', $url, $m))
	{
		$guid = $m['guid'];
	}
	
	if ($guid != '')
	{	
		$data = microcitation_reference($guid);
		
		if ($data)
		{	
			$doc = new stdclass;
			$doc->{'message-source'} = 'http://localhost/~rpage/microcitation/www/rdf.php?guid=' . $guid;
			$doc->{'message-format'} = 'application/ld+json';
			$doc->message = $data;
		}
	
		
	}

	return $doc;
}

// test
if (0)
{
	$url = 'https://doi.org/10.3969/j.issn.1000-3142.2007.06.001';
	
	$doc = resolve_url($url);
	print_r($doc);

}

?>