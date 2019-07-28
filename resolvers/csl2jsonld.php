<?php

// Convert CSL-JSON to JSON-LD

//----------------------------------------------------------------------------------------
// Parse unstructured citation from CrossRef
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
	
	// to do: other publisher's formats
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
						$author_id = $jsonld->{'@id'} . '#creator/' . ($i + 1);
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
						$role_id = $jsonld->{'@id'} . '#role/' . ($i + 1);
						$role = new stdclass;
						$role->{'@id'} = $role_id;
						$role->{'@type'} = 'Role';
					
						$role->roleName = (string)($i + 1); // cast to string
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
				$identifier = new stdclass;
				$identifier->{'@type'} = 'PropertyValue';
				$identifier->propertyID = 'doi';
				$identifier->value = strtolower($v);
				
				if (!isset($jsonld->identifier))
				{
					$jsonld->identifier = array();
				}
				$jsonld->identifier[] = $identifier;
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
				$jsonld->name = strip_tags($jsonld->name);
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
if (1)
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
	
	
	$json = '{
    "status": "ok",
    "message-type": "work",
    "message-version": "1.0.0",
    "message": {
        "indexed": {
            "date-parts": [
                [
                    2019,
                    2,
                    13
                ]
            ],
            "date-time": "2019-02-13T00:59:41Z",
            "timestamp": 1550019581191
        },
        "reference-count": 14,
        "publisher": "Pensoft Publishers",
        "license": [
            {
                "URL": "http://creativecommons.org/licenses/by/3.0/",
                "start": {
                    "date-parts": [
                        [
                            2013,
                            3,
                            21
                        ]
                    ],
                    "date-time": "2013-03-21T00:00:00Z",
                    "timestamp": 1363824000000
                },
                "delay-in-days": 0,
                "content-version": "vor"
            }
        ],
        "content-domain": {
            "domain": [],
            "crossmark-restriction": false
        },
        "short-container-title": [
            "ZK"
        ],
        "DOI": "10.3897/zookeys.278.4765",
        "type": "journal-article",
        "created": {
            "date-parts": [
                [
                    2013,
                    3,
                    21
                ]
            ],
            "date-time": "2013-03-21T14:17:26Z",
            "timestamp": 1363875446000
        },
        "page": "75-90",
        "source": "Crossref",
        "is-referenced-by-count": 0,
        "title": [
            "Four new species of Unixenus Jones, 1944 (Diplopoda, Penicillata, Polyxenida) from Australia"
        ],
        "prefix": "10.3897",
        "volume": "278",
        "author": [
            {
                "given": "Megan",
                "family": "Short",
                "sequence": "first",
                "affiliation": []
            },
            {
                "given": "Cuong",
                "family": "Huynh",
                "sequence": "additional",
                "affiliation": []
            }
        ],
        "member": "2258",
        "published-online": {
            "date-parts": [
                [
                    2013,
                    3,
                    21
                ]
            ]
        },
        "reference": [
            {
                "key": "3746_B1",
                "author": "Burt",
                "year": "1984",
                "journal-title": "Report on the research of the pincushion millipede, Unixenus mjoebergi (Verhoeff, 1924) at Tom Price in the Pilbara of Western Australia."
            },
            {
                "issue": "4",
                "key": "3746_B2",
                "first-page": "254",
                "article-title": "Dipolopodes Pénicillates de Madagascar et des Mascareignes",
                "volume": "29",
                "author": "Condé",
                "year": "1962",
                "journal-title": "Revue Française d’Entomologie"
            },
            {
                "key": "3746_B3",
                "author": "Condé",
                "year": "1963",
                "journal-title": "Pénicillates de Côte d’Ivoire (récoltes de M. Vuillaume). Bulletin Scientifique de l’Institut Fondamental d’Afrique Noire 25(A): 669–684."
            },
            {
                "key": "3746_B4",
                "doi-asserted-by": "crossref",
                "first-page": "47",
                "DOI": "10.5962/bhl.part.81868",
                "article-title": "Diplopodes Pénicillates de Papouasie et de Bornéo",
                "volume": "91",
                "author": "Condé",
                "year": "1984",
                "journal-title": "Revue Suisse Zoologie"
            },
            {
                "issue": "4",
                "key": "3746_B5",
                "first-page": "291",
                "article-title": "Classification actuelle des Diplopodes Pénicillates (Myriapodes) avec nouvelles définitions des taxa",
                "volume": "133",
                "author": "Condé",
                "year": "2008",
                "journal-title": "Bulletin de la Société zoologique de France"
            },
            {
                "key": "3746_B6",
                "first-page": "138",
                "article-title": "On two new south Indian pselaphognathous diplopods",
                "volume": "119",
                "author": "Jones",
                "year": "1937",
                "journal-title": "Zoologischer Anzeiger"
            },
            {
                "issue": "3",
                "key": "3746_B7",
                "first-page": "94",
                "article-title": "Mechanism of defence in a pselaphognathous diplopod, Unixenus padmanabhii Jones",
                "volume": "31",
                "author": "Jones",
                "year": "1944",
                "journal-title": "Proceedings of the Indian Science Congress"
            },
            {
                "key": "3746_B8",
                "author": "Koch",
                "year": "1985",
                "journal-title": "Pincushion millipedes (Diplopoda: Polyxenida): Their aggregations and identity in Western Australia. The Western Australian Naturalist 16(2/3): 30–32."
            },
            {
                "key": "3746_B9",
                "doi-asserted-by": "crossref",
                "first-page": "153",
                "DOI": "10.1111/j.1096-3642.1957.tb02516.x",
                "article-title": "The Evolution of Arthropodan Locomotory Mechanisms – Part 5: The Structure, Habits and Evolution of the Pselaphognatha (Diplopoda)",
                "volume": "43",
                "author": "Manton",
                "year": "1956",
                "journal-title": "Journal of the Linnean Society London"
            },
            {
                "key": "3746_B10",
                "first-page": "43",
                "article-title": "",
                "volume": "64",
                "author": "Nguyen",
                "year": "1967",
                "journal-title": "Mitteilungen aus dem Hamburgischen Zoologischen Museum und Institut"
            },
            {
                "key": "3746_B11",
                "author": "Nguyen",
                "year": "1982",
                "journal-title": "Lophoproctidés insulaire de l’océan Pacifique (Diplopodes Pénicillates). Bulletin du Muséum d’Histoire Naturelle de Paris, 4e Série, Section A (1-2): 95–118."
            },
            {
                "key": "3746_B12",
                "doi-asserted-by": "crossref",
                "first-page": "105",
                "DOI": "10.3897/zookeys.156.2168",
                "article-title": "The genus Unixenus Jones, 1944(Diplopoda, Penicillata, Polyxenida) in Australia",
                "volume": "156",
                "author": "Short",
                "year": "2011",
                "journal-title": "Zookeys"
            },
            {
                "key": "3746_B13",
                "first-page": "214",
                "article-title": "Tavola sinottica dei generi dei Diplopoda Penicillata",
                "volume": "8",
                "author": "Silvestri",
                "year": "1948",
                "journal-title": "Bollettino del Laboratorio di Entomologia Agraria, Portici"
            },
            {
                "issue": "5",
                "key": "3746_B14",
                "first-page": "1",
                "article-title": "Results of Dr. E. Mjöbergi’s Swedish Scientific Expeditions to Australia 1910–1913. 34. Myriapoda: Diplopoda",
                "volume": "16",
                "author": "Verhoeff",
                "year": "1924",
                "journal-title": "Arkiv för Zoologi"
            }
        ],
        "container-title": [
            "ZooKeys"
        ],
        "original-title": [],
        "link": [
            {
                "URL": "http://zookeys.pensoft.net/article_preview.php?id=3746&skip_redirect=1",
                "content-type": "unspecified",
                "content-version": "vor",
                "intended-application": "similarity-checking"
            }
        ],
        "deposited": {
            "date-parts": [
                [
                    2017,
                    6,
                    21
                ]
            ],
            "date-time": "2017-06-21T09:47:34Z",
            "timestamp": 1498038454000
        },
        "score": 1,
        "subtitle": [],
        "short-title": [],
        "issued": {
            "date-parts": [
                [
                    2013,
                    3,
                    21
                ]
            ]
        },
        "references-count": 14,
        "URL": "http://dx.doi.org/10.3897/zookeys.278.4765",
        "relation": {
            "cites": []
        },
        "ISSN": [
            "1313-2970",
            "1313-2989"
        ],
        "issn-type": [
            {
                "value": "1313-2989",
                "type": "print"
            },
            {
                "value": "1313-2970",
                "type": "electronic"
            }
        ]
    }
}';

	$obj = json_decode($json);
	
	if (isset($obj->message))
	{
		$jsonld = csl_to_json($obj->message);
	}
	else
	{	
		$jsonld = csl_to_json($obj);
	}

	//print_r($jsonld);

	echo json_encode($jsonld, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	echo "\n";
}


?>
