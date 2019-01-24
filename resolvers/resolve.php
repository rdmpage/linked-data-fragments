<?php

// Resolve one object

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
	
	//print_r($cleaned_triples);
	
	return $parser->toNTriples($cleaned_triples);
}

//----------------------------------------------------------------------------------------
// Call Zenodo API to get links for image and thumbnail
function fetch_zenodo_json($id, &$jsonld)
{	
	$url = "https://zenodo.org/api/records/" . $id;

	$opts = array(
	  CURLOPT_URL =>$url,
	  CURLOPT_FOLLOWLOCATION => TRUE,
	  CURLOPT_RETURNTRANSFER => TRUE
	);
	
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data = curl_exec($ch);
	$info = curl_getinfo($ch); 
	curl_close($ch);
	
	if ($data != '')
	{
		$obj = json_decode($data);
		
		//print_r($obj);
		
		// image URL
		if (isset($obj->files[0]->links->self))
		{
			$jsonld->contentUrl = $obj->files[0]->links->self;
		}
		
		// image thumbnail
		if (isset($obj->links->thumb250))
		{
			$jsonld->thumbnailUrl = $obj->links->thumb250;
		}
		
	}
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
		
			//echo $xml;
		
			// convert
			$nt = rdf_to_triples($xml);
			$doc = jsonld_from_rdf($nt, array('format' => 'application/nquads'));

			// Context to set vocab to schema
			$context = new stdclass;

			$context->{'@vocab'} = "http://rs.tdwg.org/ontology/voc/TaxonName#";

			$context->tcom = "http://rs.tdwg.org/ontology/voc/Common#";
			$context->tm = "http://rs.tdwg.org/ontology/voc/Team#";
			$context->tp = "http://rs.tdwg.org/ontology/voc/Person#";			
			$context->tpc = "http://rs.tdwg.org/ontology/voc/PublicationCitation#";

			$context->owl = "http://www.w3.org/2002/07/owl#";
			$context->dcterms = "http://purl.org/dc/terms/";
			$context->dc = "http://purl.org/dc/elements/1.1/";

			/*
			// hasMember is always an array
			$hasMember = new stdclass;
			$hasMember->{'@id'} = "http://rs.tdwg.org/ontology/voc/Team#hasMember";
			$hasMember->{'@container'} = "@set";
			

			$typifiedBy= new stdclass;
			$typifiedBy->{'@id'} = "http://rs.tdwg.org/ontology/voc/TaxonName#typifiedBy";
			$typifiedBy->{'@container'} = "@set";

			$context->{'tm:hasMember'} = $hasMember;
			$context->{'typifiedBy'} = $typifiedBy;
			*/

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
// CETAF RDF
// 
function cetaf_rdf($url, $cache_dir = '')
{
	$data = null;
	
	$parts = parse_url($url);
	
	$id = 'cetaf';
	
	if (preg_match('/\/(?<id>[^\/]+)$/', $parts['path'], $m))
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
	
		$cache_dir .= '/cetaf';
	
		if (!file_exists($cache_dir))
		{
			$oldumask = umask(0); 
			mkdir($cache_dir, 0777);
			umask($oldumask);
		}
		
		$cache_dir .= '/' . $parts['host'];
	
		if (!file_exists($cache_dir))
		{
			$oldumask = umask(0); 
			mkdir($cache_dir, 0777);
			umask($oldumask);
		}

	}
		
	$dir = $cache_dir;
	
	$filename = $dir . '/' . $id . '.xml';

	if (!file_exists($filename))
	{
		$xml = get($url, '', 'application/rdf+xml');
		
		file_put_contents($filename, $xml);	
	}
	
	$xml = file_get_contents($filename);
	
	if (($xml != '') && preg_match('/<\?xml/', $xml))
	{
		// clean coldb.mnhn.fr crap
		$xml = preg_replace('/<!DOCTYPE rdf:RDF \[([^\]]+)\]>/', '', $xml);	
	
		// convert
		$nt = rdf_to_triples($xml);
				
		$doc = jsonld_from_rdf($nt, array('format' => 'application/nquads'));

		// Context 
		$context = new stdclass;

		$context->{'@vocab'} 	= "http://rs.tdwg.org/dwc/terms/";
		$context->dcterms 		= "http://purl.org/dc/terms/";
		
		$context->rdfs			= "http://www.w3.org/TR/2014/REC-rdf-schema-20140225/";
		$context->foaf			= "http://xmlns.com/foaf/spec/";
		$context->ma			= "https://www.w3.org/ns/ma-ont#";
		$context->geo			= "http://www.w3.org/2003/01/geo/wgs84_pos#";
		
		$data = jsonld_compact($doc, $context);

	}
	
	return $data;	
}

//----------------------------------------------------------------------------------------
function gbif_to_jsonld($obj)
{
	$doc = new stdclass;
	
	$doc->{'@id'} = 'https://www.gbif.org/occurrence/' . $obj->key;
	
	
	foreach ($obj as $k => $v)
	{
		$go = true;
		
		if ($v == '')
		{
			$go = false;
		}
		
		if (!$v)
		{
			$go = false;
		}
		
		if ($go)
		{
	
			switch ($k)
			{
				// record
				// Dublin Core
				case 'type':
				case 'modified':
				case 'language':
				case 'license':
				case 'rightsHolder':
				case 'accessRights':
				case 'bibliographicCitation':
				case 'references':
					$doc->{'dcterms:' . $k} = (string)$v;
					break;								
				
				// DarwinCore
				case 'institutionID':
				case 'collectionID':
				case 'datasetID':
					if (preg_match('/^(https?|urn)/', $v))
					{
						$id = new stdclass;
						$id->{'@id'} = $v;
						$doc->{$k} = $id;
					}
					else
					{
						$doc->{$k} = (string)$v;
					}
					break;				
				
				case 'institutionCode':
				case 'collectionCode':
				case 'datasetName':
				case 'ownerInstitutionCode':
				case 'basisOfRecord':
				case 'informationWithheld':
				case 'dataGeneralizations':
				case 'dynamicProperties':				
					$doc->{$k} = (string)$v;
					break;				
				
			
				// occurrence
				case 'occurrenceID':
					if (preg_match('/^(https?|urn)/', $v))
					{
						$id = new stdclass;
						$id->{'@id'} = $v;
						$doc->{$k} = $id;
					}
					else
					{
						$doc->{$k} = (string)$v;
					}
					break;				
				
				case 'catalogNumber':
				case 'recordNumber':
				case 'recordedBy':
				case 'individualCount':
				case 'organismQuantity':
				case 'organismQuantityType':
				case 'sex':
				case 'lifeStage':
				case 'reproductiveCondition':
				case 'behavior':
				case 'establishmentMeans':
				case 'occurrenceStatus':
				case 'preparations':
				case 'disposition':
				case 'associatedMedia':
				case 'associatedReferences':
				case 'associatedSequences':
				case 'associatedTaxa':
				case 'otherCatalogNumbers':
				case 'occurrenceRemarks':
					$doc->{$k} = (string)$v;
					break;			
					
				// organism
				case 'organismID':
					if (preg_match('/^(https?|urn)/', $v))
					{
						$id = new stdclass;
						$id->{'@id'} = $v;
						$doc->{$k} = $id;
					}
					else
					{
						$doc->{$k} = (string)$v;
					}
					break;				
				
				case 'organismName':
				case 'organismScope':
				case 'associatedOccurrences':
				case 'associatedOrganisms':
				case 'previousIdentifications':
				case 'organismRemarks':					
					$doc->{$k} = (string)$v;
					break;			
			
				// specimen
				case 'institutionCode':
				case 'collectionCode':
				case 'catalogNumber':
					$doc->{$k} = (string)$v;
					break;
					
				// materialSample
				case 'materialSampleID':
					if (preg_match('/^(https?|urn)/', $v))
					{
						$id = new stdclass;
						$id->{'@id'} = $v;
						$doc->{$k} = $id;
					}
					else
					{
						$doc->{$k} = (string)$v;
					}
					break;				
					break;					
					
				// event
				case 'eventID':
				case 'parentEventID':
					if (preg_match('/^(https?|urn)/', $v))
					{
						$id = new stdclass;
						$id->{'@id'} = $v;
						$doc->{$k} = $id;
					}
					else
					{
						$doc->{$k} = (string)$v;
					}
					break;				
				
				case 'fieldNumber':
				case 'eventDate':
				case 'eventTime':
				case 'startDayOfYear':
				case 'endDayOfYear':
				case 'year':
				case 'month':
				case 'day':
				case 'verbatimEventDate':
				case 'habitat':
				case 'samplingProtocol':
				case 'sampleSizeValue':
				case 'sampleSizeUnit':
				case 'samplingEffort':
				case 'fieldNotes':
				case 'eventRemarks':					
					$doc->{$k} = (string)$v;
					break;				
	
				// location
				case 'locationID':
				case 'higherGeographyID':
					if (preg_match('/^(https?|urn)/', $v))
					{
						$id = new stdclass;
						$id->{'@id'} = $v;
						$doc->{$k} = $id;
					}
					else
					{
						$doc->{$k} = (string)$v;
					}
					break;				
				
				case 'higherGeography':
				case 'continent':
				case 'waterBody':
				case 'islandGroup':
				case 'island':
				case 'country':
				case 'countryCode':
				case 'stateProvince':
				case 'county':
				case 'municipality':
				case 'locality':
				case 'verbatimLocality':
				case 'minimumElevationInMeters':
				case 'maximumElevationInMeters':
				case 'verbatimElevation':
				case 'minimumDepthInMeters':
				case 'maximumDepthInMeters':
				case 'verbatimDepth':
				case 'minimumDistanceAboveSurfaceInMeters':
				case 'maximumDistanceAboveSurfaceInMeters':
				case 'locationAccordingTo':
				case 'locationRemarks':
				case 'decimalLatitude':
				case 'decimalLongitude':
				case 'geodeticDatum':
				case 'coordinateUncertaintyInMeters':
				case 'coordinatePrecision':
				case 'pointRadiusSpatialFit':
				case 'verbatimCoordinates':
				case 'verbatimLatitude':
				case 'verbatimLongitude':
				case 'verbatimCoordinateSystem':
				case 'verbatimSRS':
				case 'footprintWKT':
				case 'footprintSRS':
				case 'footprintSpatialFit':
				case 'georeferencedBy':
				case 'georeferencedDate':
				case 'georeferenceProtocol':
				case 'georeferenceSources':
				case 'georeferenceVerificationStatus':
				case 'georeferenceRemarks':
					$doc->{$k} = (string)$v;
					break;
					
				// GeologicalContext
		
				// identification
				case "kingdom":
				case "phylum":
				case "order":
				case "class":
				case "family":
				case "genus":
				case "species":
				case 'scientificName': // convenience

				// identification
				case 'identificationID':
					if (preg_match('/^(https?|urn)/', $v))
					{
						$id = new stdclass;
						$id->{'@id'} = $v;
						$doc->{$k} = $id;
					}
					else
					{
						$doc->{$k} = (string)$v;
					}
					break;				
				
				case 'identificationQualifier':
				case 'typeStatus':
				case 'identifiedBy':
				case 'dateIdentified':
				case 'identificationReferences':
				case 'identificationVerificationStatus':
				case 'identificationRemarks':
					$doc->{$k} = (string)$v;
					break;
				
				// taxon
				case 'taxonID':
				case 'scientificNameID':
				case 'acceptedNameUsageID':
				case 'parentNameUsageID':
				case 'originalNameUsageID':
				case 'nameAccordingToID':
				case 'namePublishedInID':
				case 'taxonConceptID':
					if (preg_match('/^(https?|urn)/', $v))
					{
						$id = new stdclass;
						$id->{'@id'} = $v;
						$doc->{$k} = $id;
					}
					else
					{
						$doc->{$k} = (string)$v;
					}
					break;				
				
				case 'scientificName':
				case 'acceptedNameUsage':
				case 'parentNameUsage':
				case 'originalNameUsage':
				case 'nameAccordingTo':
				case 'namePublishedIn':
				case 'namePublishedInYear':
				case 'higherClassification':
				case 'kingdom':
				case 'phylum':
				case 'class':
				case 'order':
				case 'family':
				case 'genus':
				case 'subgenus':
				case 'specificEpithet':
				case 'infraspecificEpithet':
				case 'taxonRank':
				case 'verbatimTaxonRank':
				case 'scientificNameAuthorship':
				case 'vernacularName':
				case 'nomenclaturalCode':
				case 'taxonomicStatus':
				case 'nomenclaturalStatus':
				case 'taxonRemarks':				
					$doc->{$k} = (string)$v;
					break;
			
				default:
					break;
			}
		}	
	
	}
	
	
	if (isset($obj->media))
	{
		if (count($obj->media) > 0)
		{
			$doc->associatedMedia = array();
			
			$count = 1;
	
			foreach ($obj->media as $media)
			{
				$media_obj = new stdclass;
				
				// default id
				$media_obj->{'@id'} = $doc->{'@id'} . '#media_' . $count++;
						
				// Darwin and Dublin Core (to do: add schema.org)
				foreach ($media as $k => $v)
				{
					switch ($k)
					{
						case 'type':
						case 'format':
						case 'title':
						case 'description':
						case 'created':
						case 'creator':
						case 'contributor':
						case 'publisher':
						case 'audience':
						case 'source':
						case 'license':
						case 'rightsHolder':
							$media_obj->{'dcterms:' . $k} = (string)$v;
							break;
						
						case 'identifier':
							if (preg_match('/^(https?|urn)/', $v))
							{
								$id = new stdclass;
								$id->{'@id'} = $v;
								$media_obj->{'dcterms:' . $k} = $id;
								
								// use this as identifier for image
								$media_obj->{'@id'} = $v;
								
							}
							else
							{
								$media_obj->{'dcterms:' . $k} = (string)$v;
							}
							break;				
						
						case 'references':
							if (preg_match('/^(https?|urn)/', $v))
							{
								$id = new stdclass;
								$id->{'@id'} = $v;
								$media_obj->{'dcterms:' . $k} = $id;
							}
							else
							{
								$media_obj->{'dcterms:' . $k} = (string)$v;
							}
							break;				
						
						case 'datasetID':
							break;
							
						default:
							break;
			
					}
				}
				
				$doc->associatedMedia[] = $media_obj;		
			}
		}
	}


	// context
	// Context to set vocab to schema
	$context = new stdclass;

	$context->{'@vocab'} = "http://rs.tdwg.org/dwc/terms/";
	$context->{'dcterms'} = "http://purl.org/dc/terms/";
	
	$doc->{'@context'} = $context;
	
	return $doc;

}

//----------------------------------------------------------------------------------------
// IPNI LSID, names or authors
function ipni_lsid($lsid, $cache_dir = '')
{
	$data = null;
	
	$mode = 'names';
	$id = '';
	
	if (preg_match('/urn:lsid:ipni.org:names:(?<id>\d+)/', $lsid, $m))
	{
		$mode = 'names';
		$id = $m['id'];
	}
	
	if (preg_match('/urn:lsid:ipni.org:authors:(?<id>\d+)/', $lsid, $m))
	{
		$mode = 'authors';
		$id = $m['id'];
	}

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
		
			$xml = str_replace('xmlns:p="http://rs.tdwg.org/ontology/voc/Person"', 'xmlns:p="http://rs.tdwg.org/ontology/voc/Person#"', $xml);
		
			//echo $xml;
		
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
		$url = preg_replace('/#article/', '', $url);
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
// Raw JSON-LD (or JSON)
function fetch_jsonld($url, $cache_name = 'jsonid', $id = 0, $content_type = '')
{
	$data = null;
	
	
	$cache_dir = dirname(__FILE__) . "/cache/" . $cache_name;
	if (!file_exists($cache_dir))
	{
		$oldumask = umask(0); 
		mkdir($cache_dir, 0777);
		umask($oldumask);
	}
			
	$dir = $cache_dir . '/' . floor($id / 1000);
	if (!file_exists($dir))
	{
		$oldumask = umask(0); 
		mkdir($dir, 0777);
		umask($oldumask);
	}
	
	$filename = $dir . '/' . $id . '.json';

	if (!file_exists($filename))
	{
		$json = get($url, '', $content_type);
		
		file_put_contents($filename, $json);	
	}
	
	$json = file_get_contents($filename);
	
	if ($json != '')
	{
		$data = json_decode($json);
	}
	
	return $data;	
}


//----------------------------------------------------------------------------------------
function microcitation_reference ($guid)
{
	$data = null;
	
	$url = 'http://localhost/~rpage/microcitation/www/rdf.php?guid=' . $guid;
	$url = 'http://localhost/~rpage/linked-data-fragments/services/rdf.php?guid=' . $guid;
	
	//echo $url . "\n";
	
	$nt = get($url);
	
	if ($nt != '')
	{

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
	}
	
	return $data;
}

	
//----------------------------------------------------------------------------------------
function resolve_url($url)
{
	$doc = null;	
	
	$done = false;
	
	// Zenodo JSON-LD ------------------------------------------------------------------
	if (!$done)
	{
		if (preg_match('/https?:\/\/zenodo.org\/record\/(?<id>\d+)/', $url, $m))
		{
			
			$id = $m['id'];
			$jsonld_url = "https://zenodo.org/api/records/" . $id;
			
			$data = fetch_jsonld($jsonld_url, 'zenodo', $id, 'application/ld+json');

			if ($data)
			{
				// force use of http for schema (Zenodo uses https FFS)
				$data->{'@context'} = 'http://schema.org/';
			
				// enhance with links to image and thumbnail (if present)
				fetch_zenodo_json($id, $data);

				$doc = new stdclass;
				$doc->{'message-source'} = $jsonld_url;
				$doc->{'message-format'} = 'application/ld+json';
				$doc->message = $data;
			}
			
			$done = true;
		}
	}
	
	
	
	// Index Fungorum  -------------------------------------------------------------------
	// Import RDF XML and convert to JSON-LD
	if (!$done)
	{
		if (preg_match('/urn:lsid:indexfungorum.org:names:/', $url))
		{
			$data = indexfungorum_lsid($url);

			if ($data)
			{
				$doc = new stdclass;				
				$doc->{'message-source'} = 'http://www.indexfungorum.org/IXFWebService/Fungus.asmx/NameByKeyRDF?NameLsid=' . $url;				
				$doc->{'message-format'} = 'application/ld+json';
				$doc->message = $data;				
				
				// process possible links
				$doc->links = array();
				
				// IndexFungorum may have multiple graphs
				foreach ($data->{'@graph'} as $graph)
				{
					if (isset($graph->{'hasBasionym'}))
					{
						$doc->links[] = $graph->{'hasBasionym'}->{'@id'};
					}								
				}
												
				if (count($doc->links) == 0)
				{
					unset($doc->links);
				}
				else
				{
					$doc->links = array_unique($doc->links);
				}
				
			}
			
			$done = true;
		}
	}	
	
	// CETAF specimen---------------------------------------------------------------------
	// http://data.rbge.org.uk/herb/E00435919
	if (!$done)
	{
		if (preg_match('/
			https?:\/\/
			(
			data.rbge.org.uk
			|herbarium.bgbm.org
			|coldb.mnhn.fr
			)
			/x', $url))
		{
			$data = cetaf_rdf($url);

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
	
	// GBIF occurrence -------------------------------------------------------------------
	if (!$done)
	{
		if (preg_match('/https?:\/\/(www.)?gbif.org\/occurrence\/(?<id>\d+)/', $url, $m))
		{
			
			$id = $m['id'];
			$json_url = 'https://api.gbif.org/v1/occurrence/' . $id;
			
			// JSON
			$data =  fetch_jsonld($json_url, 'gbif', $id);
			
			// Convert to JSON-LD
			$data = gbif_to_jsonld($data);
			

			if ($data)
			{
				$doc = new stdclass;
				$doc->{'message-source'} = $json_url;
				$doc->{'message-format'} = 'application/ld+json';
				$doc->message = $data;
			}
			
			$done = true;
		}
	}	
	
	// BioStor JSON-LD -------------------------------------------------------------------
	if (!$done)
	{
		if (preg_match('/https?:\/\/biostor.org\/reference\/(?<id>\d+)/', $url, $m))
		{
			
			$id = $m['id'];
			$jsonld_url = 'https://biostor.org/api.php?id=biostor/' . $id . '&format=jsonld';
			
			$data =  fetch_jsonld($jsonld_url, 'biostor', $id);

			if ($data)
			{
				$doc = new stdclass;
				$doc->{'message-source'} = $jsonld_url;
				$doc->{'message-format'} = 'application/ld+json';
				$doc->message = $data;
			}
			
			$done = true;
		}
	}	
	
	// IPNI name clusters ----------------------------------------------------------------
	if (!$done)
	{
		if (preg_match('/https?:\/\/bionames.org\/ipni\/cluster\/(?<id>\d+-\d+)/', $url, $m))
		{
			$id = $m['id'];
			
			$url = 'http://localhost/~rpage/ipni-names/jsonld-clusters.php?id=' . $id;
			$json = get($url);
			
			if ($json != '')
			{
				$data = json_decode($json);
				if ($data)
				{				
					$doc = new stdclass;
					$doc->{'message-source'} = $url;
					$doc->{'message-format'} = 'application/ld+json';
					$doc->message = $data;
					
					// process possible links
					$doc->links = array();
					if (isset($data->dataFeedElement))
					{
						foreach ($data->dataFeedElement as $dataFeedElement)
						{
							$doc->links[] = $dataFeedElement->{'@id'};
							
							if (isset($dataFeedElement->{'tcom:publishedInCitation'}))
							{
								$doc->links[] = $dataFeedElement->{'tcom:publishedInCitation'}->{'@id'};
							}
						}
					}
					
					if (count($doc->links) == 0)
					{
						unset($doc->links);
					}
					else
					{
						$doc->links = array_unique($doc->links);
					}
										
				}
			}
						
			$done = true;
		}
	}	
	
	// IndexFungorum name clusters -------------------------------------------------------
	if (!$done)
	{
		if (preg_match('/https?:\/\/bionames.org\/indexfungorum\/cluster\/(?<id>\d+)/', $url, $m))
		{
			$id = $m['id'];
			
			$url = 'http://localhost/~rpage/indexfungorum-publications/jsonld-clusters.php?id=' . $id;
			$json = get($url);
			
			if ($json != '')
			{
				$data = json_decode($json);
				if ($data)
				{				
					$doc = new stdclass;
					$doc->{'message-source'} = $url;
					$doc->{'message-format'} = 'application/ld+json';
					$doc->message = $data;
					
					// process possible links
					$doc->links = array();
					if (isset($data->dataFeedElement))
					{
						foreach ($data->dataFeedElement as $dataFeedElement)
						{
							$doc->links[] = $dataFeedElement->{'@id'};
							
							if (isset($dataFeedElement->{'tcom:publishedInCitation'}))
							{
								$doc->links[] = $dataFeedElement->{'tcom:publishedInCitation'}->{'@id'};
							}
						}
					}
					
					if (count($doc->links) == 0)
					{
						unset($doc->links);
					}
					else
					{
						$doc->links = array_unique($doc->links);
					}
										
				}
			}
						
			$done = true;
		}
	}		
	
	// WorldCat JSON-LD ------------------------------------------------------------------
	if (!$done)
	{
		if (preg_match('/https?:\/\/www.worldcat.org\/oclc\/(?<id>\d+)/', $url, $m))
		{
			// <http://www.worldcat.org/oclc/1281768>
			// http://experiment.worldcat.org/oclc/1281768.jsonld
			
			$id = $m['id'];
			$jsonld_url = 'http://experiment.worldcat.org/oclc/' . $id . '.jsonld';
			
			$data = fetch_jsonld($jsonld_url, 'worldcat', $id);

			if ($data)
			{
				$doc = new stdclass;
				$doc->{'message-source'} = $jsonld_url;
				$doc->{'message-format'} = 'application/ld+json';
				$doc->message = $data;
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
			$data = ipni_lsid($url);

			if ($data)
			{
				$doc = new stdclass;
				$doc->{'message-source'} = 'http://ipni.org/' . $url;
				$doc->{'message-format'} = 'application/ld+json';
				$doc->message = $data;
				
				
				// process possible links
				$doc->links = array();
				if (isset($data->{'@graph'}[0]->authorteam))
				{
					foreach ($data->{'@graph'}[0]->authorteam->{'tm:hasMember'} as $hasMember)
					{
						$doc->links[] = $hasMember->{'@id'};
					}
				}
				
				if (isset($data->{'@graph'}[0]->{'hasBasionym'}))
				{
					$doc->links[] = $data->{'@graph'}[0]->{'hasBasionym'}->{'@id'};
				}								
				
				if (count($doc->links) == 0)
				{
					unset($doc->links);
				}
				else
				{
					$doc->links = array_unique($doc->links);
				}
				
			}
			
			$done = true;
		}
	}
	
	// CiNii -----------------------------------------------------------------------------
	// Import RDF XML and convert to JSON-LD
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
	
	
	// DBPedia ---------------------------------------------------------------------------
	// JSON-LD (not compacted)
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
	
	// Microcitation ---------------------------------------------------------------------
	// My native JSON-LD for bibliographic data
	if (!$done)
	{	
		$guid = '';
	
		// keep things simple 
		if (preg_match('/https?:\/\/(dx\.)?doi.org\/(?<guid>.*)/', $url, $m))
		{
			$guid = $m['guid'];
		}

		// keep things simple 
		if (preg_match('/https?:\/\/hdl.handle.net\/(?<guid>.*)/', $url, $m))
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
				
				$done = true;
			}
	
			
		}
		
		
	}
	
	// CrossRef ---------------------------------------------------------------------
	// CSL+JSON, transform to JSON-LD in CouchDB
	if (!$done)
	{	
	
		$guid = '';
	
		// keep things simple 
		if (preg_match('/https?:\/\/(dx\.)?doi.org\/(?<guid>.*)/', $url, $m))
		{
			$guid = $m['guid'];
		}
		
		if ($guid != '')
		{
			$url = 'https://api.crossref.org/v1/works/http://dx.doi.org/' . $guid;
			
			$json = get($url);
			
			if ($json != '')
			{
				$doc = json_decode($json);
				
				// CrossRef API returns a message natively, we tweak it slightly
				if ($doc)
				{
					$doc->{'message-source'} = $url;
					$doc->{'message-format'} = 'application/vnd.crossref-api-message+json';
				
					$done = true;
				}
			}
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
	//$url = 'urn:lsid:ipni.org:names:77122780-1';
	
	//$url = 'https://doi.org/10.1017/S096042860000192X';
	
	//$url = 'https://ci.nii.ac.jp/naid/110003758629#article';
	
	//$url = 'http://www.worldcat.org/oclc/1281768';
	
	//$url = 'urn:lsid:ipni.org:authors:37149-1';
	//$url = 'urn:lsid:ipni.org:names:77122780-1';
	
	$url = 'http://bionames.org/ipni/cluster/1008144-1';
	
	$url = 'urn:lsid:ipni.org:names:77109775-1';
	
	$url = 'https://biostor.org/reference/146685';
	
	$url = 'http://biostor.org/reference/246525';
	
	$url = 'https://www.jstor.org/stable/42596874';
	
	$url = 'https://www.gbif.org/occurrence/574819276';
	
	$url = 'http://data.rbge.org.uk/herb/E00435919';
	$url = 'http://herbarium.bgbm.org/object/B100241392';
	$url = 'http://coldb.mnhn.fr/catalognumber/mnhn/p/p05036298';
	
	// kew is buggered
	//$url = http://specimens.kew.org/herbarium/K000697728, see http://herbal.rbge.info/index.php
	
	
	$url = 'urn:lsid:indexfungorum.org:names:814659';
	$url = 'urn:lsid:indexfungorum.org:names:814692';
	
	$url = 'https://zenodo.org/record/576067';
	/*https://zenodo.org/record/918933
	https://zenodo.org/record/918937
	https://zenodo.org/record/918939
	https://zenodo.org/record/918935*/
	
	$url = 'https://zenodo.org/record/918935';
	
	$url = 'http://bionames.org/indexfungorum/cluster/568745';
	
	$url = 'https://doi.org/10.1080/00275514.2018.1515449';
	
	$url = 'https://doi.org/10.6165/tai.2014.59.4.326';
	
	$url = 'https://doi.org/10.11646/phytotaxa.208.2.4';
	$url = 'https://doi.org/10.6165/tai.2012.57(1).55';
	
	
	$doc = resolve_url($url);
	print_r($doc);
	
	echo json_encode($doc->message, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	echo "\n";


}

?>