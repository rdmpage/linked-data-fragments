<?php

// Convert CSL-JSON to JSON-LD

//----------------------------------------------------------------------------------------
function parse_unstructured(&$cited)
{
	$matched = false;
	
	if (!$matched)
	{
		// Springer
		if (preg_match('/(?<authorstring>.*)\s+\((?<year>[0-9]{4})[a-z]?\)\.\s+(?<title>.*)[\.|\?]\s+(?<journal>.*)\s+(?<volume>\d+)(\s+\((?<issue>.*)\))?:\s+(?<spage>\d+)(\s*[-|–]\s*(?<epage>\d+))?\b/Uu', $cited->unstructured, $m))
		{
			$matched = true;
			
			$keymap = array(
				'authorstring' 	=> 'author',
				'year' 			=> 'year',
				'title' 		=> 'article-title',
				'journal' 		=> 'journal-title',
				'volume'	 	=> 'volume',
				'issue'			=> 'issue',
				'spage'			=> 'first-page',
				'epage'			=> 'last-page'
			);
		
			foreach ($m as $k => $v)
			{
				if (!is_numeric($k))
				{
					if ($v != '')
					{
						$cited->{$keymap[$k]} = $v;					
					}
				}
			}
		
		
		}
	}	


}

//----------------------------------------------------------------------------------------
function csl_to_json($obj)
{
	$jsonld = new stdclass;

	if (isset($obj->_id))
	{
		$jsonld->{'@id'} = $obj->_id;
	}

	if (isset($obj->DOI))
	{
		$jsonld->{'@id'} = 'https://doi.org/' . strtolower($obj->DOI);
	}

	foreach ($obj as $k => $v)
	{
		switch ($k)
		{
			case 'author':	
				$jsonld->creator = array();
			
				$n = count($v);
			
				for ($i = 0; $i < $n; $i++)
				{
					$author_id = '';
				
					if (isset($v[$i]->ORCID))
					{
						$author_id = $v[$i]->ORCID;
					
						// CrossRef stores ORCID as http URL, ORCID as identifier only
						if (preg_match('/^https?/', $v[$i]->ORCID))
						{
							$author_id = str_replace('http:', 'https:', $author_id);
						}
						else
						{
							$author_id = 'https://orcid.org/' . $author_id;
						}
					}
					else
					{
						$author_id = $jsonld->{'@id'} . '#author_' . ($i + 1);
					}
				
					$creator = new stdclass;				
					$creator->{'@id'} = $author_id;
					$creator->{'@type'} = 'Person';
				
					$name_parts = array();
				
					if (isset($v[$i]->given))
					{
						$creator->givenName = $v[$i]->given;
						$name_parts[] = $creator->givenName;
					}

					if (isset($v[$i]->family))
					{
						$creator->familyName = $v[$i]->family;
						$name_parts[] = $creator->familyName;
					}
				
					if (count($name_parts) > 0)
					{
						$creator->name = join(' ', $name_parts);
					}
				
					if (1)
					{
						$role_id = $jsonld->{'@id'} . '#role_' . ($i + 1);
						$role = new stdclass;
						$role->{'@id'} = $role_id;
						$role->{'@type'} = 'Role';
					
						$role->roleName = ($i + 1);
						$role->creator = $creator;
					
						$jsonld->creator[] = $role;
					}
					else
					{
						$jsonld->creator[] = $creator;
					}				
				}
				break;
	
	
			case 'container-title':
				if (!isset($jsonld->isPartOf))
				{
					$jsonld->isPartOf = new stdclass;
					$jsonld->isPartOf->{'@type'} = 'Periodical';
				}
			
				if (is_array($obj->{$k}))
				{
					$jsonld->isPartOf->name = $v[0];
				}
				else
				{
					$jsonld->isPartOf->name = $v;
				}				
				break;	
	
			case 'DOI':
				break;
			
			case 'ISSN':
				if (!isset($jsonld->isPartOf))
				{
					$jsonld->isPartOf = new stdclass;
					$jsonld->isPartOf->{'@type'} = 'Periodical';				
				}
			
				$jsonld->isPartOf->issn = array();			
				foreach ($v as $issn)
				{
					$jsonld->isPartOf->issn[] = $issn;
				}
			
				$jsonld->isPartOf->{'@id'} = 'http://worldcat.org/issn/' . $v[0];
				break;
						
			case 'issue':
				$jsonld->issueNumber = $v;
				break;
			
			case 'issued':
				$dateparts = $v->{'date-parts'}[0];
				$date = '';
						
				switch (count($dateparts))
				{
					case 1:
						$date = $dateparts[0];
						break;
					
					case 2:
						$date = $dateparts[0] . '-' . str_pad($dateparts[1], 2, '0', STR_PAD_LEFT) . '-00';
						break;

					case 3:
						$date = $dateparts[0] . '-' . str_pad($dateparts[1], 2, '0', STR_PAD_LEFT) . '-' . str_pad($dateparts[2], 2, '0', STR_PAD_LEFT);
						break;

					default:
						break;								
				}
		
				if ($date != '')
				{
					$jsonld->datePublished = $date;
				}
				break;
			
			case 'page':
			case 'pages':
				$jsonld->pagination = $v;
				break;

			case 'page-first':
			case 'first-page':
				$jsonld->pageStart = $v;
				break;

			case 'publisher':
				$jsonld->publisher = $v;
				break;
			
			case 'reference':
				$jsonld->citation = array();
				foreach ($v as $cited)
				{
					$citation = new stdclass;
					$citation->{'@type'} = 'CreativeWork';
				
					if (isset($cited->DOI))
					{
						$doi = strtolower($cited->DOI);
						$doi = preg_replace('/sc.1?/', '', $doi);
						$doi = preg_replace('/\s/', '', $doi);
					
						// DOI cleaning (sigh)
						/* Sometimes publishes make a mess of this, e.g. 
						http://dx.doi.org/10.1111/j.1096-0031.2007.00176.x
						{
						  key: "b129_103",
						  DOI: "10.1636/H05-14 SC.1",
						  doi-asserted-by: "publisher"
						}
						*/
					
						$citation->{'@id'} =  'https://doi.org/' . $doi;
					}
					else
					{
						if (isset($cited->key))
						{
							$key = $cited->key;
						
							$key = str_replace("\t", '', $key);
							$key = str_replace("\n", '', $key);

							$key = str_replace("|", '-', $key);
							$key = str_replace("/", '-', $key);
							$key = str_replace(".", '-', $key);
							$key = str_replace(" ", '-', $key);
						
							$citation->{'@id'} = $jsonld->{'@id'} . '/reference' . '#' . $key;
						}
					}	
				
					if (isset($cited->unstructured))
					{
						parse_unstructured($cited);
					}							
				
					foreach ($cited as $ck => $cv)
					{
						switch ($ck)
						{
							case 'article-title':
								$citation->name = $cv;
								break;

							case 'author':
								$citation->creator = $cv;
								break;
							
							case 'first-page':
								$citation->pageStart = $cv;
								break;
							
							case 'ISSN':
								if (!isset($citation->isPartOf))
								{
									$citation->isPartOf = new stdclass;
									$citation->isPartOf->{'@type'} = 'Periodical';
									$citation->{'@type'} = 'ScholarlyArticle';
								}
							
								$issn = $cv;
								$issn = str_replace('http://id.crossref.org/issn/', '', $issn);
							
								$citation->{'@id'} = 'http://worldcat.org/issn/' . $issn;
								$citation->issn[] = $issn;
	
								break;	
																				
							case 'issue':
								$citation->issueNumber = $cv;
								break;		
							
							case 'journal-title':
								if (!isset($citation->isPartOf))
								{
									$citation->isPartOf = new stdclass;
									$citation->isPartOf->{'@type'} = 'Periodical';
									$citation->{'@type'} = 'ScholarlyArticle';
								}
								$citation->isPartOf->name = $cv;
								break;							
														
							case 'last-page':
								$citation->pageEnd = $cv;
								break;
							
							case 'unstructured':
								$citation->name = $cv;
								break;							
																										
							case 'volume':
								$citation->volumeNumber = $cv;
								break;		
							
							case 'volume-title':
								if (!isset($citation->isPartOf))
								{
									$citation->isPartOf = new stdclass;
									$citation->isPartOf->{'@type'} = 'CreativeWork';
								}
								$citation->isPartOf->name = $cv;
								break;					

							case 'year':
								$citation->datePublished = $cv;
								break;					
					
							default:
								break;
						}
				
					}
							
					//$jsonld->citation[] = $citation;							
				}
				break;
			
			case 'subject':
				$jsonld->keywords = array();
				foreach ($v as $subject)
				{
					$jsonld->keywords[] = $subject;
				}
				break;
			
			case 'title':
				if (is_array($obj->{$k}))
				{
					$jsonld->name = $v[0];
				}
				else
				{
					$jsonld->name = $v;
				}
				break;			
			
			case 'type':
				switch ($v)
				{
					case 'journal-article':
					case 'article-journal':
						$jsonld->{'@type'} = 'ScholarlyArticle';
						break;
							
					default:
						$jsonld->{'@type'} = 'CreativeWork';
						break;
				}
				break;
			
			case 'URL':
				$jsonld->url = $v;
				break;			
			
			case 'volume':
				$jsonld->volumeNumber = $v;
				break;
									
		
			default:
				break;
		}	



	}

	return $jsonld;
}


// test
if (0)
{


	$json = '{
		"id": "Hua_2001",
		"type": "article-journal",
		"author": [
			{
				"family": "Hua",
				"given": "Peng",
				"literal": "Peng Hua"
			},
			{
				"family": "Yunfei",
				"given": "Deng",
				"literal": "Deng Yunfei",
				"ORCID": "0000-0002-0876-3286"
			}
		],
		"event-date": {
			"date-parts": [
				[
					2001
				]
			]
		},
		"issued": {
			"date-parts": [
				[
					2001
				]
			]
		},
		"collection-title": "Novon",
		"container-title": "Novon",
		"DOI": "10.2307/3393157",
		"issue": "4",
		"number": "4",
		"number-of-pages": "1",
		"page": "440",
		"page-first": "440",
		"publisher": "JSTOR",
		"title": "A New Species of Pittosporum (Pittosporaceae) from China",
		"URL": "http://dx.doi.org/10.2307/3393157",
		"volume": "11",
		"_id": "https://pub.orcid.org/v2.1/0000-0002-0876-3286/work/16657720"
	}';

	$obj = json_decode($json);
	
	$jsonld = csl_to_json($obj);

	//print_r($jsonld);

	//echo json_encode($jsonld, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	//echo "\n";
}


?>
