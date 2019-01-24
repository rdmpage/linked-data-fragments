<?php

// bibliographic reference


//--------------------------------------------------------------------------------------------------
function reference_to_rdf($reference)
{
	$triples = array();
	
	$sameAs = array();
	
	$guid = $reference->guid;
	
	// DOI
	if (preg_match('/^10\./', $guid))
	{
		$guid = 'https://doi.org/' . strtolower($guid);
		
		$sameAs[] = $guid;
	}

	// jstor
	if (preg_match('/http:\/\/www.jstor.org/', $guid))
	{
		$guid = str_replace('http', 'https', $guid);
		
		$sameAs[] = $guid;
	}
	
	// handle
	if (preg_match('/^\d+\/[a-z0-9]+$/', $guid))
	{
		$guid = 'https://hdl.handle.net/' . strtolower($guid);
		
		$sameAs[] = $guid;
	}
	
	
	$subject_id = $guid; // fix this

	$s = '<' . $subject_id . '>';
	
	$type = 'ScholarlyArticle';
	
	if ($reference->type == 'book')
	{
		$type = 'Book';
	}
	
	$triples[] = $s . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://schema.org/' . $type . '> .';
	
	
	$have_title = false;
	
	if (isset($reference->multi))
	{
		if (isset($reference->multi->{'_key'}->title))
		{
			$have_title = true;
			
			foreach ($reference->multi->{'_key'}->title as $language => $value)
			{
				$triples[] = $s . ' <http://schema.org/name> ' . '"' . addcslashes($value, '"') . '"@' . $language . ' .';
			}
		}	
	}
	
	if (!$have_title)
	{
		if (isset($reference->title))
		{
			$triples[] = $s . ' <http://schema.org/name> ' . '"' . addcslashes($reference->title, '"') . '" .';		
		}
	}
	
	//-----------------------------------------
	// Abstract
	$have_abstract = false;
	
	if (isset($reference->multi))
	{
		if (isset($reference->multi->{'_key'}->abstract))
		{
			$have_abstract = true;
			
			foreach ($reference->multi->{'_key'}->abstract as $language => $value)
			{
				$triples[] = $s . ' <http://schema.org/description> ' . '"' . addcslashes($value, '"') . '"@' . $language . ' .';
			}
		}	
	}
	
	if (!$have_abstract)
	{
		if (isset($reference->abstract))
		{
			$triples[] = $s . ' <http://schema.org/description> ' . '"' . addcslashes($reference->abstract, '"') . '" .';		
		}
	}

	//-----------------------------------------
	// Authors
	
	if (isset($reference->author))
	{
	
		$n = count($reference->author);
		for ($i = 0; $i < $n; $i++)
		{
			$index = $i + 1;
		
			// Author
			$author_id = '<' . $subject_id . '#creator/' . $index . '>';
			
			if (isset($reference->author[$i]->multi))
			{
				if (isset($reference->author[$i]->multi->{'_key'}->literal))
				{
					foreach ($reference->author[$i]->multi->{'_key'}->literal as $language => $value)
					{
						$triples[] = $author_id . ' <http://schema.org/name> ' . '"' . addcslashes($value, '"') . '"@' . $language . ' .';
					}
				}	
			}
			else
			{
				$triples[] = $author_id . ' <http://schema.org/name> ' . '"' . addcslashes($reference->author[$i]->name, '"') . '" .';					
			}
			
			// assume is a person, need to handle cases where this is not true
			$triples[] = $author_id . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> ' . ' <http://schema.org/Person>' . ' .';			
		
			$use_role = true;
							
			if ($use_role)
			{
				// Role to hold author position
				$role_id = '<' . $subject_id . '#role/' . $index . '>';
				
				$triples[] = $role_id . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> ' . ' <http://schema.org/Role>' . ' .';			
				$triples[] = $role_id . ' <http://schema.org/roleName> "' . $index . '" .';			
			
				$triples[] = $s . ' <http://schema.org/creator> ' .  $role_id . ' .';
				$triples[] = $role_id . ' <http://schema.org/creator> ' .  $author_id . ' .';
			}
			else
			{
				// Author is creator
				$triples[] = $s . ' <http://schema.org/creator> ' .  $author_id . ' .';						
			}
			
		}
	}	


	//------------------------------------------------------------------------------------
	if (isset($reference->journal))
	{
		$journal_id = $subject_id . '#container';
		
		$sici = array();
		
		
		$issns = array();
		if (isset($reference->journal->identifier))
		{
			foreach ($reference->journal->identifier as $identifier)
			{
				switch ($identifier->type)
				{
					case 'issn':
						$issns[] = $identifier->id;
						break;
						
					default:
						break;
				}
			}
		}
		
		if (count($issns) > 0)
		{
			$journal_id = 'http://worldcat.org/issn/' . $issns[0];
			
			$sici[] = $issns[0];

			if (isset($reference->year))
			{
				$sici[] = '(' . $reference->year . ')';
			}
		}
				
		$triples[] = $s . ' <http://schema.org/isPartOf> ' . '<' . $journal_id . '> .';
		
		foreach ($issns as $issn)
		{
			$triples[] = '<' . $journal_id . '> <http://schema.org/issn> ' . '"' . $issn. '"' . ' .';		
		}
		
		switch ($reference->type)
		{
			case 'article':
				$triples[] = '<' . $journal_id . '> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://schema.org/Periodical> .';	
				break;
		
			default:
				break;
		}
		
				
		if (isset($reference->journal->multi))
		{
			if (isset($reference->journal->multi->{'_key'}->name))
			{
				foreach ($reference->journal->multi->{'_key'}->name as $language => $value)
				{
					$triples[] = '<' . $journal_id . '> <http://schema.org/name> ' . '"' . addcslashes($value, '"') . '"@' . $language . ' .';
				}
			}		
		}
		else
		{
			if (isset($reference->journal->name))
			{
				$triples[] = '<' . $journal_id . '> <http://schema.org/name> ' . '"' . addcslashes($reference->journal->name, '"') . '"' . ' .';		
			}
		}
	
		if (isset($reference->journal->volume))
		{
			$triples[] = $s . ' <http://schema.org/volumeNumber> ' . '"' . addcslashes($reference->journal->volume, '"') . '" .';
			
			$sici[] = $reference->journal->volume;
		}
		if (isset($reference->journal->issue))
		{
			$triples[] = $s . ' <http://schema.org/issueNumber> ' . '"' . addcslashes($reference->journal->issue, '"') . '" .';
		}
		if (isset($reference->journal->pages))
		{
			$triples[] = $s . ' <http://schema.org/pagination> ' . '"' . addcslashes(str_replace('--', '-', $reference->journal->pages), '"') . '" .';

			if (preg_match('/(?<spage>[a-z]?\d+)/', $reference->journal->pages, $m))
			{
				$sici[] = '<' . $m['spage'] . '>';
			}
		}
		
		
		// sici to help reference linking
		if (count($sici) == 4)
		{
			$identifier_id = '<' . $subject_id . '#sici' . '>';

			$triples[] = $s . ' <http://schema.org/identifier> ' . $identifier_id . '.';			
			$triples[] = $identifier_id . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://schema.org/PropertyValue> .';
			$triples[] = $identifier_id . ' <http://schema.org/propertyID> ' . '"sici"' . '.';
			$triples[] = $identifier_id . ' <http://schema.org/value> ' . '"' . addcslashes(join('', $sici), '"') . '"' . '.';		
		}
		
		
		
	}
	
	//------------------------------------------------------------------------------------
	if (isset($reference->link))
	{
		foreach ($reference->link as $link)
		{
			switch ($link->anchor)
			{
				case 'LINK':
					$triples[] = $s . ' <http://schema.org/url> ' . '"' . $link->url . '" .';				
					$sameAs[] = $link->url;
					break;

				// eventually handle this difefrently, cf Ozymandias
				case 'PDF':
					$sameAs[] = $link->url;
					break;
			
				default:
					break;
			}
		}
	}
	
	//------------------------------------------------------------------------------------
	// Identifiers
	
	
	if (isset($reference->identifier))
	{
		foreach ($reference->identifier as $identifier)
		{
			$identifier_id = '';
		
			switch ($identifier->type)
			{
			
				case 'cinii':
					$identifier_id = '<' . $subject_id . '#cinii' . '>';

					$triples[] = $identifier_id . ' <http://schema.org/propertyID> ' . '"cinii"' . '.';
					$triples[] = $identifier_id . ' <http://schema.org/value> ' . '"' . $identifier->id . '"' . '.';
				
					// Consistent with CiNii RDF
					$sameAs[]  = 'https://ci.nii.ac.jp/naid/' . $identifier->id . '#article';
					break;
			
			
				case 'doi':
					$identifier_id = '<' . $subject_id . '#doi' . '>';

					$triples[] = $identifier_id . ' <http://schema.org/propertyID> ' . '"doi"' . '.';
					$triples[] = $identifier_id . ' <http://schema.org/value> ' . '"' . $identifier->id . '"' . '.';
				
					$sameAs[]  = 'https://doi.org/' . $identifier->id;
					break;
					
				case 'handle':
					$identifier_id = '<' . $subject_id . '#handle' . '>';

					$triples[] = $identifier_id . ' <http://schema.org/propertyID> ' . '"handle"' . '.';
					$triples[] = $identifier_id . ' <http://schema.org/value> ' . '"' . $identifier->id . '"' . '.';
				
					$sameAs[]  = 'https://hdl.handle.net/' . $identifier->id;
					break;

				case 'jstor':
					$identifier_id = '<' . $subject_id . '#jstor' . '>';

					$triples[] = $identifier_id . ' <http://schema.org/propertyID> ' . '"jstor"' . '.';
					$triples[] = $identifier_id . ' <http://schema.org/value> ' . '"' . $identifier->id . '"' . '.';
				
					$sameAs[]  = 'https://www.jstor.org/stable/' . $identifier->id;
					break;
			
			
				default:
					break;
			}
			
			if ($identifier_id != '')
			{
				$triples[] = $s . ' <http://schema.org/identifier> ' . $identifier_id . '.';			
				$triples[] = $identifier_id . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://schema.org/PropertyValue> .';			
			}
		
		}
	
	}	
		
	
	//------------------------------------------------------------------------------------
	// Links to other versions/instances/representations
	$sameAs = array_unique($sameAs);
	foreach ($sameAs as $link)
	{
		$triples[] = $s . ' <http://schema.org/sameAs> ' . '"' . addcslashes($link, '"') . '" .';		
	}
	
	//------------------------------------------------------------------------------------
	if (isset($reference->date))
	{
		$triples[] = $s . ' <http://schema.org/datePublished> ' . '"' . addcslashes($reference->date, '"') . '" .';			
	}
	else
	{
		if (isset($reference->year))
		{
			$triples[] = $s . ' <http://schema.org/datePublished> ' . '"' . addcslashes($reference->year, '"') . '" .';					
		}
	}
	
	//------------------------------------------------------------------------------------
	if (isset($reference->citation))
	{
		foreach ($reference->citation as $citation)
		{
			$citation_id = '';
			
			if (isset($citation->doi))
			{
				$citation->doi = str_replace('<', '%3C', $citation->doi);
				$citation->doi = str_replace('>', '%3E', $citation->doi);
				$citation->doi = trim($citation->doi);
				$cite_id = 'https://doi.org/' . $citation->doi;
			}
			else
			{
				$cite_id = $subject_id . '/reference#' . $citation->key;
			}
			
			$citation_id = '<' . $cite_id . '>';
			
			$triples[] = $s . ' <http://schema.org/citation> ' . $citation_id  . ' .';					
			$triples[] = $citation_id . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://schema.org/CreativeWork> .';				
			
			if (isset($citation->{'article-title'}))
			{
				$triples[] = $citation_id . ' <http://schema.org/name> "' . addcslashes($citation->{'article-title'}, '"')  . '" . ';				
			}

			if (isset($citation->volume))
			{
				$triples[] = $citation_id . ' <http://schema.org/volumeNumber> "' . addcslashes($citation->volume, '"')  . '" . ';				
			}
			if (isset($citation->issue))
			{
				$triples[] = $citation_id . ' <http://schema.org/issueNumber> "' . addcslashes($citation->issue, '"')  . '" . ';				
			}
			
			if (isset($citation->{'first-page'}))
			{
				$triples[] = $citation_id . ' <http://schema.org/pageStart> "' . addcslashes($citation->{'first-page'}, '"')  . '" . ';				
			}
			if (isset($citation->{'last-page'}))
			{
				$triples[] = $citation_id . ' <http://schema.org/pageEnd> "' . addcslashes($citation->{'last-page'}, '"')  . '" . ';				
			}
			
			if (isset($citation->year))
			{
				$triples[] = $citation_id . ' <http://schema.org/datePublished> "' . addcslashes($citation->year, '"')  . '" . ';				
			}

			if (isset($citation->unstructured) && !isset($citation->{'article-title'}))
			{
				$triples[] = $citation_id . ' <http://schema.org/name> "' . addcslashes($citation->unstructured, '"')  . '" . ';				
			}
			
			// Container
			if (isset($citation->{'journal-title'}))
			{
				$container_id = $cite_id . '_container';
				
				if (isset($citation->ISSN))
				{
					$container_id = 'http://worldcat.org/issn/' . $citation->ISSN;
				}
				$container_id  = '<' . $container_id  . '>';
				
				$triples[] = $citation_id . ' <http://schema.org/isPartOf> ' . $container_id . ' . ';
				$triples[] = $container_id . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://schema.org/Periodical> .';							
				$triples[] = $container_id . ' <http://schema.org/name> "' . addcslashes($citation->{'journal-title'}, '"')  . '" . ';				
			}
			
			
			
		}
	}
	

	
	$nt = join("\n", $triples);	
	return $nt;
}

?>