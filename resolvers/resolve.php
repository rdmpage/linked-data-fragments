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
function rdf_to_triples($xml)
{	
	// Parse RDF into triples
	$parser = ARC2::getRDFParser();		
	$base = 'http://example.com/';
	$parser->parse($base, $xml);	
	
	$triples = $parser->getTriples();
	
	//print_r($triples);
	
	// clean up
	
	$cleaned_triples = array();
	foreach ($triples as $triple)
	{
		$add = true;

		if ($triple['s'] == 'http://example.com/')
		{
			$add = false;
		}
		
		if ($add)
		{
			$cleaned_triples[] = $triple;
		}
	}
	
	print_r($cleaned_triples);
	
	return $parser->toNTriples($cleaned_triples);
}

//----------------------------------------------------------------------------------------
// IPNI LSID
function ipni_lsid($lsid, $cache_dir = '')
{
	$data = null;
	
	$id = str_replace('urn:lsid:ipni.org:names:', '', $lsid);
	$id = preg_replace('/-\d+$/', '', $id);

	// Either use an existing cache (e.g., on external hard drive)
	// or cache locally
	if ($cache_dir != '')
	{
	}
	else
	{
		$cache_dir = dirname(__FILE__) . "/cache";
		if (!file_exists($cache_dir))
		{
			$oldumask = umask(0); 
			mkdir($cache_dir, 0777);
			umask($oldumask);
		}
	
		$cache_dir .= '/ipni';
	
		if (!file_exists($cache_dir))
		{
			$oldumask = umask(0); 
			mkdir($cache_dir, 0777);
			umask($oldumask);
		}
	}
		
	$dir = $cache_dir . '/' . floor($id / 1000);
	if (!file_exists($dir))
	{
		$oldumask = umask(0); 
		mkdir($dir, 0777);
		umask($oldumask);
	}
	
	$filename = $dir . '/' . $id . '.xml';

	if (!file_exists($filename))
	{
		$url = 'http://ipni.org/' . $lsid;
		$xml = get($url);
		
		file_put_contents($filename, $xml);	
	}
	
	$xml = file_get_contents($filename);
	
	if (($xml != '') && preg_match('/<\?xml/', $xml))
	{
		// fix
		
		// convert
		$nt = rdf_to_triples($xml);
		$doc = jsonld_from_rdf($nt, array('format' => 'application/nquads'));

		// Context to set vocab to schema
		$context = new stdclass;

		$context->{'@vocab'} = "http://rs.tdwg.org/ontology/voc/TaxonName#";

		$context->tcom = "http://rs.tdwg.org/ontology/voc/Common#";
		$context->tm = "http://rs.tdwg.org/ontology/voc/Team#";
		$context->tp = "http://rs.tdwg.org/ontology/voc/Person#";

		$context->owl = "http://www.w3.org/2002/07/owl#";
		$context->dcterms = "http://purl.org/dc/terms/";
		$context->dc = "http://purl.org/dc/elements/1.1/";

		// hasMember is always an array
		$hasMember = new stdclass;
		$hasMember->{'@id'} = "http://rs.tdwg.org/ontology/voc/Team#hasMember";
		$hasMember->{'@container'} = "@set";

		$typifiedBy= new stdclass;
		$typifiedBy->{'@id'} = "http://rs.tdwg.org/ontology/voc/TaxonName#typifiedBy";
		$typifiedBy->{'@container'} = "@set";

		$context->{'tm:hasMember'} = $hasMember;
		$context->{'typifiedBy'} = $typifiedBy;

		$frame = (object)array(
			'@context' => $context,

			// Root on article
			'@type' => 'http://rs.tdwg.org/ontology/voc/TaxonName#TaxonName',
		);	


		$data = jsonld_frame($doc, $frame);

	}
	
	return $data;	
}

//----------------------------------------------------------------------------------------
// CiNII RDF
function cinii_rdf($url, $cache_dir = '')
{
	$data = null;
	
	$id = preg_replace('/https?:\/\/ci.nii.ac.jp\/naid\//', '', $url);
	$id = preg_replace('/#article/', '', $id);

	// Either use an existing cache (e.g., on external hard drive)
	// or cache locally
	if ($cache_dir != '')
	{
	}
	else
	{
		$cache_dir = dirname(__FILE__) . "/cache";
		if (!file_exists($cache_dir))
		{
			$oldumask = umask(0); 
			mkdir($cache_dir, 0777);
			umask($oldumask);
		}
	
		$cache_dir .= '/cinii';
	
		if (!file_exists($cache_dir))
		{
			$oldumask = umask(0); 
			mkdir($cache_dir, 0777);
			umask($oldumask);
		}
	}
		
	$dir = $cache_dir . '/' . floor($id / 1000);
	if (!file_exists($dir))
	{
		$oldumask = umask(0); 
		mkdir($dir, 0777);
		umask($oldumask);
	}
	
	$filename = $dir . '/' . $id . '.xml';

	if (!file_exists($filename))
	{
		$url = $url . '.rdf';
		$xml = get($url);
		
		file_put_contents($filename, $xml);	
	}
	
	$xml = file_get_contents($filename);
	
	if (($xml != '') && preg_match('/<\?xml/', $xml))
	{
		// fix
		
		// convert
		$nt = rdf_to_triples($xml);
		$doc = jsonld_from_rdf($nt, array('format' => 'application/nquads'));

		// Context 
		$context = new stdclass;


		$context->rdf 		= "http://www.w3.org/1999/02/22-rdf-syntax-ns#";
		$context->rdfs 		= "http://www.w3.org/2000/01/rdf-schema#";

		$context->dc 		= "http://purl.org/dc/elements/1.1/";
		$context->dcterms 	= "http://purl.org/dc/terms/";

		$context->foaf 		= "http://xmlns.com/foaf/0.1/";
		$context->prism 	= "http://prismstandard.org/namespaces/basic/2.0/";
		$context->con 		= "http://www.w3.org/2000/10/swap/pim/contact#";
		$context->cinii 	= "https://ci.nii.ac.jp/ns/1.0/";
		$context->bibo 		= "http://purl.org/ontology/bibo/";

		$frame = (object)array(
			'@context' => $context,
			
			// Root on article
			'@type' => 'http://purl.org/ontology/bibo/Article',
			
		);	

		$data = jsonld_frame($doc, $frame);

	}
	
	return $data;	
}


//----------------------------------------------------------------------------------------
function microcitation_reference ($guid)
{
	$data = null;
	
	$url = 'http://localhost/~rpage/microcitation/www/rdf.php?guid=' . $guid;
	
	//echo $url . "\n";
	
	$nt = get($url);

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
	
	$done = false;
	
	
	// IPNI
	if (!$done)
	{
		if (preg_match('/urn:lsid:ipni.org:names:/', $url))
		{
			$data = ipni_lsid($url);

			if ($data)
			{
				$doc = new stdclass;
				$doc->{'message-source'} = 'http://ipni.org/' . $url;
				$doc->{'message-format'} = 'application/ld+json';
				$doc->message = $data;
			}
			
			$done = true;
		}
	}
	
	// CiNii
	if (!$done)
	{
		if (preg_match('/https?:\/\/ci.nii.ac.jp\/naid\/\d+#article/', $url))
		{
			$data = cinii_rdf($url);

			if ($data)
			{
				$doc = new stdclass;
				$doc->{'message-source'} = $url . '.rdf';
				$doc->{'message-format'} = 'application/ld+json';
				$doc->message = $data;
			}
			
			$done = true;
		}
	}
	
	
	// DBPedia
	if (!$done)
	{
		if (preg_match('/dbpedia.org/', $url))
		{
			$q = 'http://dbpedia.org/sparql?default-graph-uri=http://dbpedia.org'
			. '&query=' . urlencode('DESCRIBE <' . $url . '>') . '&format=application/json-ld';
			
			$json = get($q);

			if ($json != '')
			{
				$data = json_decode($json);

				$doc = new stdclass;
				$doc->{'message-source'} = $q;
				$doc->{'message-format'} = 'application/ld+json';
				$doc->message = $data;
			}
			
			$done = true;
		}
	}
		
	// Microcitation
	if (!$done)
	{	
		$guid = '';
	
		// keep things simple 
		if (preg_match('/https?:\/\/(dx\.)?doi.org\/(?<guid>.*)/', $url, $m))
		{
			$guid = $m['guid'];
		}
	
		// fall back
		if ($guid == '')
		{
			$guid = $url;
		}
	
	
		if ($guid != '')
		{	
			$data = microcitation_reference($guid);
			
			// make nice
			
				
			if ($data)
			{	
				$doc = new stdclass;
				$doc->{'message-source'} = 'http://localhost/~rpage/microcitation/www/rdf.php?guid=' . $guid;
				$doc->{'message-format'} = 'application/ld+json';
				$doc->message = $data;
			}
	
			$done = true;
		}
		
		
	}
	
	return $doc;
}

// test
if (0)
{
	$url = 'https://doi.org/10.3969/j.issn.1000-3142.2007.06.001';
	
	$url = 'http://dbpedia.org/resource/Distichochlamys';
	
	$url = 'urn:lsid:ipni.org:names:981552-1';
	$url = 'urn:lsid:ipni.org:names:77122780-1';
	
	$url = 'https://doi.org/10.1017/S096042860000192X';
	
	$url = 'https://ci.nii.ac.jp/naid/110003758629#article';
	
	$doc = resolve_url($url);
	print_r($doc);
	
	echo json_encode($doc->message, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	echo "\n";


}

?>