<?php

// Take RDF from source, convert to JSON-LD and output as JSONL

require_once(dirname(dirname(__FILE__)) . '/vendor/autoload.php');
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
		$opts[CURLOPT_HTTPHEADER] = array(
			"Accept: " . $content_type, 
			"User-agent: Mozilla/5.0 (iPad; U; CPU OS 3_2_1 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Mobile/7B405" 
		);
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
	
	$nt = $parser->toNTriples($cleaned_triples);
	
	// https://stackoverflow.com/a/2934602/9684
	$nt = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
    	return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
		}, $nt);
	
	return $nt;
}


//----------------------------------------------------------------------------------------
// Index Fungorum LSID, names or authors
function indexfungorum_lsid($lsid, $cache_dir = '')
{
	$data = null;
	
	$mode = 'names';
	$id = 'x';
	
	if (preg_match('/urn:lsid:indexfungorum.org:names:(?<id>\d+)/', $lsid, $m))
	{
		$id = $m['id'];
	}

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
	
		$cache_dir .= '/indexfungorum';
	
		if (!file_exists($cache_dir))
		{
			$oldumask = umask(0); 
			mkdir($cache_dir, 0777);
			umask($oldumask);
		}
		
		$cache_dir .= '/' . $mode;
	
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
		$url = 'http://www.indexfungorum.org/IXFWebService/Fungus.asmx/NameByKeyRDF?NameLsid=' . $lsid;
				
		$xml = get($url);
		
		// only cache XML (if record not found or IPNI overloaded we get HTML)
		if (preg_match('/<\?xml/', $xml))
		{
			file_put_contents($filename, $xml);	
		}
	}
	
	if (file_exists($filename))
	{
	
		$xml = file_get_contents($filename);
	
		if (($xml != '') && preg_match('/<\?xml/', $xml))
		{
			// fix
			// Dublin Core title has wrong case
			$xml = str_replace('ns:Title', 'ns:title', $xml);
		
			//echo $xml;
		
			// convert
			$nt = rdf_to_triples($xml);
			
			$doc = jsonld_from_rdf($nt, array('format' => 'application/nquads'));

			// Context to set vocab to TaxonName
			$context = new stdclass;

			$context->{'@vocab'} = "http://rs.tdwg.org/ontology/voc/TaxonName#";

			$context->tcom = "http://rs.tdwg.org/ontology/voc/Common#";
			$context->tm = "http://rs.tdwg.org/ontology/voc/Team#";
			$context->tp = "http://rs.tdwg.org/ontology/voc/Person#";			
			$context->tpc = "http://rs.tdwg.org/ontology/voc/PublicationCitation#";

			$context->owl = "http://www.w3.org/2002/07/owl#";
			$context->dc = "http://purl.org/dc/elements/1.1/";

			$frame = (object)array(
				'@context' => $context,
				'@type' => 'http://rs.tdwg.org/ontology/voc/TaxonName#TaxonName'
			);
			

			$data = jsonld_frame($doc, $frame);
		}
	}
		
	return $data;	
}

//----------------------------------------------------------------------------------------
// IPNI LSID, names or authors
function ipni_lsid($lsid, $cache_dir = '')
{
	$data = null;
	
	$mode = 'names';
	$id = '';
	
	if (preg_match('/urn:lsid:ipni.org:names:(?<id>\d+-\d+)/', $lsid, $m))
	{
		$mode = 'names';
		$id = $m['id'];
	}
	
	if (preg_match('/urn:lsid:ipni.org:authors:(?<id>\d+-\d+)/', $lsid, $m))
	{
		$mode = 'authors';
		$id = $m['id'];
	}

	// remove version from id so we can compute a directory to cache the files in
	$main_id = preg_replace('/-\d+$/', '', $id);

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
		
		$cache_dir .= '/' . $mode;
	
		if (!file_exists($cache_dir))
		{
			$oldumask = umask(0); 
			mkdir($cache_dir, 0777);
			umask($oldumask);
		}
		
	}
		
	$dir = $cache_dir . '/' . floor($main_id / 1000);
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
		
		// only cache XML (if record not found or IPNI overloaded we get HTML)
		if (preg_match('/<\?xml/', $xml))
		{
			file_put_contents($filename, $xml);	
		}
	}
	
	if (file_exists($filename))
	{
	
		$xml = file_get_contents($filename);
	
		if (($xml != '') && preg_match('/<\?xml/', $xml))
		{
			// fix		
			
			// Person vocab is missing hash #
			$xml = str_replace('xmlns:p="http://rs.tdwg.org/ontology/voc/Person"', 'xmlns:p="http://rs.tdwg.org/ontology/voc/Person#"', $xml);
			
			// tm:hasMember is not represented properly, this hack fixes this
			if (preg_match_all('/<tm:hasMember rdf:resource="(?<lsid>.*)"\s+tm:index="(?<index>\d+)"\s+tm:role="(?<role>.*)"\/>/U', $xml, $m))
			{
				$n = count($m[0]);
		
				for ($i = 0; $i < $n; $i++)
				{
					$member = '<tm:hasMember>';
					$member .= '<rdf:Description>';
					$member .= '<tm:index>' . $m['index'][$i] . '</tm:index>';
					$member .= '<tm:role>' . $m['role'][$i] . '</tm:role>';
					$member .= '<tm:member rdf:resource="' . $m['lsid'][$i] . '"/>';
					$member .= '</rdf:Description>';
					$member .= '</tm:hasMember>';
			
					$xml = str_replace($m[0][$i], $member, $xml);
				}
			}
			//echo $xml;
		
			// convert
			$nt = rdf_to_triples($xml);
			$doc = jsonld_from_rdf($nt, array('format' => 'application/nquads'));

			// Context to set vocab to TaxonName
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
				'@context' => $context
			);	

			switch ($mode)
			{
				case 'authors':
					// Root on person
					$frame->{'@type'} = 'http://rs.tdwg.org/ontology/voc/Person#Person';
					break;
				
				case 'names':
				default:
					// Root on name
					$frame->{'@type'} = 'http://rs.tdwg.org/ontology/voc/TaxonName#TaxonName';
					break;		
			}

			$data = jsonld_frame($doc, $frame);

		}
	}
		
	return $data;	
}

//----------------------------------------------------------------------------------------
// ION LSID
function ion_lsid($lsid, $cache_dir = '')
{
	$data = null;
	
	$id = 0;
	
	if (preg_match('/urn:lsid:organismnames.com:name:(?<id>\d+)/', $lsid, $m))
	{
		$id = $m['id'];
	}

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
	
		$cache_dir .= '/ion';
	
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
		$url = 'http://organismnames.com/lsidmetadata.htm?lsid=' . $id;
				
		$xml = get($url);
		
		// only cache XML (if record not found or IPNI overloaded we get HTML)
		if (preg_match('/<\?xml/', $xml))
		{
			file_put_contents($filename, $xml);	
		}
	}
	
	if (file_exists($filename))
	{
	
		$xml = file_get_contents($filename);
	
		if (($xml != '') && preg_match('/<\?xml/', $xml))
		{
			// fix
		
			//echo $xml;
			
			// Dublin Core title has wrong case
			$xml = str_replace('dc:Title', 'dc:title', $xml);
			
			//	PublishedIn has wrong case		
			$xml = str_replace('tdwg_co:PublishedIn', 'tdwg_co:publishedIn', $xml);
			
			// www.organismnames.com no longer resolves, but organismnames.com does 
			$xml = str_replace('http://www.organismnames.com', 'http://organismnames.com', $xml);
			
			// identifier is integer, not LSID (!)
			$xml = preg_replace('/rdf:about="(\d+)"/', 'rdf:about="urn:lsid:organismnames.com:name:$1"', $xml);
			$xml = preg_replace('/<dc:identifier>(\d+)/', '<dc:identifier>urn:lsid:organismnames.com:name:$1', $xml);
			
					
			// convert
			$nt = rdf_to_triples($xml);
			$doc = jsonld_from_rdf($nt, array('format' => 'application/nquads'));

			// Context to set vocab to TaxonName
			$context = new stdclass;

			$context->{'@vocab'} = "http://rs.tdwg.org/ontology/voc/TaxonName#";

			$context->tcom = "http://rs.tdwg.org/ontology/voc/Common#";
			$context->dc = "http://purl.org/dc/elements/1.1/";
			
			$context->rdfs = "http://www.w3.org/2000/01/rdf-schema#";

			$frame = (object)array(
				'@context' => $context,
				'@type' => 'http://rs.tdwg.org/ontology/voc/TaxonName#TaxonName'
			);

			$data = jsonld_frame($doc, $frame);
		}
	}
		
	return $data;	
}

//----------------------------------------------------------------------------------------
// World Spider Catalog 
function nmbe_lsid($lsid, $cache_dir = '')
{
	$data = null;
	
	$id = 0;
	
	if (preg_match('/urn:lsid:nmbe.ch:spidersp:(?<id>\d+)/', $lsid, $m))
	{
		$id = $m['id'];
		$id = preg_replace('/^0+/', '', $id);
	}

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
	
		$cache_dir .= '/nmbe';
	
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
		$url = 'http://lsid.nmbe.ch:80/authority/metadata/?lsid=' . $lsid;
				
		$xml = get($url);
		
		// only cache XML (if record not found or IPNI overloaded we get HTML)
		if (preg_match('/<\?xml/', $xml))
		{
			file_put_contents($filename, $xml);	
		}
	}
	
	if (file_exists($filename))
	{
	
		$xml = file_get_contents($filename);
	
		if (($xml != '') && preg_match('/<\?xml/', $xml))
		{
			// fix
					
			// convert
			$nt = rdf_to_triples($xml);
			
			$doc = jsonld_from_rdf($nt, array('format' => 'application/nquads'));
			
			//print_r($doc);

			// Context to set vocab to TaxonName
			$context = new stdclass;

			$context->{'@vocab'} = "http://rs.tdwg.org/ontology/voc/TaxonName#";

			$context->tcom = "http://rs.tdwg.org/ontology/voc/Common#";
			$context->tc = "http://rs.tdwg.org/ontology/voc/TaxonConcept#";

			$context->dwc = "http://rs.tdwg.org/dwc/terms/";
			$context->dcterms = "http://purl.org/dc/terms/";
			$context->dc = "http://purl.org/dc/elements/1.1/";
			
			$context->nmbe = "urn:lsid:nmbe.ch:predicates:";

			
			$frame = (object)array(
				'@context' => $context,
				
				// WSC doesn't use @type
				'http://purl.org/dc/elements/1.1/type' => 'Scientific Name'
			);
			

			$data = jsonld_frame($doc, $frame);
		}
	}
		
	return $data;	
}

	
//----------------------------------------------------------------------------------------
function resolve_url($url)
{
	$doc = null;	
	
	$done = false;
	
	$caches = array(
		'indexfungorum' => '',
		'ipni' => '',
		'ion' => '',
		'wsc' => '',
	);
		
	// Samsung external drive
	$caches = array(
		'indexfungorum' => '/Volumes/Samsung_T5/rdf-archive/indexfungorum/rdf',
		'ipni' => '/Volumes/Samsung_T5/rdf-archive/ipni/rdf',
		'ion' => '/Volumes/Samsung_T5/rdf-archive/ion/rdf',
		'wsc' => '/Volumes/Samsung_T5/rdf-archive/nmbe/rdf',
	);	
	
	$caches = array(
		'indexfungorum' => '',
		'ipni' => '',
		'ion' => '',
		'wsc' => '',
	);		
	
	// Index Fungorum  -------------------------------------------------------------------
	// Import RDF XML and convert to JSON-LD
	if (!$done)
	{
		if (preg_match('/urn:lsid:indexfungorum.org:names:/', $url))
		{
			$data = indexfungorum_lsid($url, $caches['indexfungorum']);

			if ($data)
			{
				$doc =  $data;	
			}
			
			$done = true;
		}
	}	

	
	// IPNI  -----------------------------------------------------------------------------
	// Import RDF XML and convert to JSON-LD
	if (!$done)
	{
		if (preg_match('/urn:lsid:ipni.org:/', $url))
		{
			$data = ipni_lsid($url, $caches['ipni']);

			if ($data)
			{
				$doc =  $data;					
			}
			
			$done = true;
		}
	}

	// ION  -----------------------------------------------------------------------------
	// Import RDF XML and convert to JSON-LD
	if (!$done)
	{
		if (preg_match('/urn:lsid:organismnames.com:name:/', $url))
		{
			$data = ion_lsid($url, $caches['ion']);

			if ($data)
			{
				$doc =  $data;					
			}
			
			$done = true;
		}
	}
	
	// WSC
	// Import RDF XML and convert to JSON-LD
	if (!$done)
	{
		if (preg_match('/urn:lsid:nmbe.ch:spidersp:/', $url))
		{
			$data = nmbe_lsid($url, $caches['wsc']);

			if ($data)
			{
				$doc =  $data;					
			}
			
			$done = true;
		}
	}
	
		
	
	return $doc;
}

// test
if (1)
{
	
	// core names, IPNI, IndexFungorum, ION, all using TDWG LSID vocab
	
	//$url = 'urn:lsid:ipni.org:names:981552-1';
	//$url = 'urn:lsid:ipni.org:names:77177604-1';
	//$url = 'urn:lsid:ipni.org:names:77179054-1';
	$url = 'urn:lsid:ipni.org:names:1019484-1';
	$url = 'urn:lsid:ipni.org:names:17003000-1';
	
	//$url = 'urn:lsid:indexfungorum.org:names:814659';
	//$url = 'urn:lsid:indexfungorum.org:names:814692';
	//$url = 'urn:lsid:indexfungorum.org:names:814035';
	//$url = 'urn:lsid:indexfungorum.org:names:489999';
	
	//$url = 'urn:lsid:organismnames.com:name:5429322';
	//$url = 'urn:lsid:organismnames.com:name:5429322';
	//$url = 'urn:lsid:organismnames.com:name:5341517';
	$url = 'urn:lsid:organismnames.com:name:5363011';
	
	
	$url = 'urn:lsid:ipni.org:authors:31201-1';
	
	// other sources, possibly using other vocabularies	
	
	// World Spider Catalog
	//$url = 'urn:lsid:nmbe.ch:spidersp:021946';
	$url = 'urn:lsid:nmbe.ch:spidersp:049015';
	
	//$url = 'urn:lsid:organismnames.com:name:1609635';
	$url = 'urn:lsid:organismnames.com:name:5323066';
	
	$url = 'urn:lsid:ipni.org:names:1019484-1';
	$url = 'urn:lsid:ipni.org:names:77179054-1';
	$url = 'urn:lsid:indexfungorum.org:names:489999';
	$url = 'urn:lsid:indexfungorum.org:names:814035';
	
	$url = 'urn:lsid:ipni.org:names:77122780-1';

	$url = 'urn:lsid:ipni.org:authors:31201-1';
		
	$doc = resolve_url($url);
	
	echo json_encode($doc, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	echo "\n";


}

// bulk fetch IPNI authors
if (0)
{
	$count = 1;
	
	$filename = 'author_ids.txt';
	$filename = 'more_ids.txt';
	$file_handle = fopen($filename, "r");
	while (!feof($file_handle)) 
	{
		$url = trim(fgets($file_handle));	
		
		$url = 'urn:lsid:ipni.org:authors:' . $url;
		
		echo $url . "\n";
		
		$doc = resolve_url($url);
	
		//echo json_encode($doc, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		//echo "\n";
		
		if (($count++ % 20) == 0)
		{
			$rand = rand(1000000, 3000000);
			echo "\n...sleeping for " . round(($rand / 1000000),2) . ' seconds' . "\n\n";
			usleep($rand);
		}			

	}
}
?>