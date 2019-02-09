<?php

require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';

require_once dirname(dirname(__FILE__)) . '/documentstore/query.php';

$page = 0;
$s = '';
$p = '';
$o = '';

$pagesize = 100;
$skip = 0;

//----------------------------------------------------------------------------------------

// get parameters

$debug = false;

if (isset($_GET['debug']))
{
	$debug = true;
}

if (isset($_GET['s']))
{
	if ($_GET['s'] != '')
	{
		$s = $_GET['s'];
	}
}

if (isset($_GET['p']))
{
	if ($_GET['p'] != '')
	{
		$p = $_GET['p'];
	}
}

if (isset($_GET['o']))
{
	if ($_GET['o'] != '')
	{
		$o = $_GET['o'];
	}
}

if (isset($_GET['page']))
{
	if ($_GET['page'] != '')
	{
		$page = $_GET['page'];
	}
}


$parameters = array(
	's' => $s,
	'p' => $p,
	'o' => $o
);

if ($parameters['s'] == '')
{
	unset($parameters['s']);
}

if ($parameters['p'] == '')
{
	unset($parameters['p']);
}

if ($parameters['o'] == '')
{
	unset($parameters['o']);
}

// pagination
if ($page == 0)
{
	$parameters['skip'] = 0;
}
else
{
	$parameters['skip'] = ($page - 1) * $pagesize;
}
$parameters['limit'] = $pagesize;


//----------------------------------------------------------------------------------------
// Initialise URIs

$server_uri = (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

$parts = parse_url($server_uri);

// Do we have a query string (e.g., we are asking for  triples that match a pattern)?
$query_string = parse_url($server_uri, PHP_URL_QUERY);
parse_str($query_string, $query_parts);

// Remove page parameter as we will be adding this back later (maybe for different pages)
if (isset($query_parts['page']))
{
	unset($query_parts['page']);
}

$server_uri = $parts['scheme'] . '://' . $parts['host'] . $parts['path'];

// The dataset
$data_uri = $server_uri . '#dataset';

// The fragment (include any triple patterns to match)
$fragment_uri = $server_uri;

// Page delimiter will depend on whether page is the only parameter
$page_delimiter = '?';

if (count($query_parts) > 0)
{
	$fragment_uri .= '?' . http_build_query($query_parts);
	$page_delimiter = '&';
}

// Metadata about the fragment
$meta_uri = $fragment_uri . '#metadata';

// The current page being displayed, ignore page parameter if this is the first page
$page_uri = $fragment_uri;

if ($page > 0)
{
	$page_uri .= $page_delimiter . 'page=' . $page;
}

$triples = array();

//----------------------------------------------------------------------------------------
// Do query
// We need estmate of total number of results
// We need to figure out how to query CouchDB to match LDF query

$total_triples = 0;

$code = array('X', 'X', 'X'); // by default match all triples
if (isset($parameters['s']))
{
	$code[0] = 'S';
}
if (isset($parameters['p']))
{
	$code[1] = 'P';
}
if (isset($parameters['o']))
{
	$code[2] = 'O';
}

$function = 'query' . join('', $code);


$r = $function($parameters);

//print_r($r);

$result = query_result_to_triples($r);

$total_triples = $r->count;

$triples = array_merge($triples, $result);

//----------------------------------------------------------------------------------------
// Describe the service

$triples[] = array('<' . $meta_uri . '>', '<http://xmlns.com/foaf/0.1/primaryTopic>', '<' . $fragment_uri . '>', '<' . $meta_uri . '>');

// data
$triples[] = array('<' . $data_uri . '>', '<http://www.w3.org/ns/hydra/core#member>', '<' . $data_uri . '>', '<' . $meta_uri . '>'); 
$triples[] = array('<' . $data_uri . '>', '<http://rdfs.org/ns/void#subset>', '<' . $page_uri . '>', '<' . $meta_uri . '>'); 
$triples[] = array('<' . $data_uri . '>', '<http://rdfs.org/ns/void#subset>', '<' . $fragment_uri . '>', '<' . $meta_uri . '>'); 
$triples[] = array('<' . $data_uri . '>', '<http://www.w3.org/1999/02/22-rdf-syntax-ns#type>', '<http://www.w3.org/ns/hydra/core#Collection>', '<' . $meta_uri . '>'); 
$triples[] = array('<' . $data_uri . '>', '<http://www.w3.org/ns/hydra/core#search>', '_:triplePattern', '<' . $meta_uri . '>');


// patterns
$triples[] = array('_:subject', '<http://www.w3.org/ns/hydra/core#property>', '<http://www.w3.org/1999/02/22-rdf-syntax-ns#subject>', '<' . $meta_uri . '>');
$triples[] = array('_:subject', '<http://www.w3.org/ns/hydra/core#variable>', '"s"', '<' . $meta_uri . '>');

$triples[] = array('_:predicate', '<http://www.w3.org/ns/hydra/core#property>', '<http://www.w3.org/1999/02/22-rdf-syntax-ns#predicate>', '<' . $meta_uri . '>');
$triples[] = array('_:predicate', '<http://www.w3.org/ns/hydra/core#variable>', '"p"', '<' . $meta_uri . '>');

$triples[] = array('_:object', '<http://www.w3.org/ns/hydra/core#property>', '<http://www.w3.org/1999/02/22-rdf-syntax-ns#object>', '<' . $meta_uri . '>');
$triples[] = array('_:object', '<http://www.w3.org/ns/hydra/core#variable>', '"o"', '<' . $meta_uri . '>');

$triples[] = array('_:triplePattern', '<http://www.w3.org/ns/hydra/core#mapping>', '_:subject', '<' . $meta_uri . '>');
$triples[] = array('_:triplePattern', '<http://www.w3.org/ns/hydra/core#mapping>', '_:predicate', '<' . $meta_uri . '>');
$triples[] = array('_:triplePattern', '<http://www.w3.org/ns/hydra/core#mapping>', '_:object', '<' . $meta_uri . '>');
$triples[] = array('_:triplePattern', '<http://www.w3.org/ns/hydra/core#template>', '"' . $server_uri . '{?s,p,o}"', '<' . $meta_uri . '>');


// hypermedia controls

$triples[] = array('<' . $fragment_uri . '>', '<http://rdfs.org/ns/void#subset>', '<' . $page_uri . '>', '<' . $meta_uri . '>'); 
$triples[] = array('<' . $page_uri . '>', '<http://www.w3.org/1999/02/22-rdf-syntax-ns#type>', '<http://www.w3.org/ns/hydra/core#PartialCollectionView>', '<' . $meta_uri . '>'); 
$triples[] = array('<' . $page_uri . '>', '<http://www.w3.org/ns/hydra/core#totalItems>', '"' . $total_triples . '"^^<http://www.w3.org/2001/XMLSchema#integer>', '<' . $meta_uri . '>');
$triples[] = array('<' . $page_uri . '>', '<http://rdfs.org/ns/void#triples>', '"' . $total_triples . '"^^<http://www.w3.org/2001/XMLSchema#integer>', '<' . $meta_uri . '>');
$triples[] = array('<' . $page_uri . '>', '<http://www.w3.org/ns/hydra/core#itemsPerPage>', '"' . $pagesize . '"^^<http://www.w3.org/2001/XMLSchema#integer>', '<' . $meta_uri . '>');
               
// pagination
$first =  $fragment_uri . $page_delimiter . 'page=1';  

$triples[] = array('<' . $page_uri . '>', '<http://www.w3.org/ns/hydra/core#first>', '<' . $first . '>', '<' . $meta_uri . '>');

if ($page > 1)
{
	$previous =  $fragment_uri . $page_delimiter . 'page=' . ($page - 1);
	$triples[] = array('<' . $page_uri . '>', '<http://www.w3.org/ns/hydra/core#previous>', '<' . $previous . '>', '<' . $meta_uri . '>');
}

if ($total_triples > ($pagesize * $page))
{
	$next =  $fragment_uri . $page_delimiter . 'page=' . ($page + 1);
	$triples[] = array('<' . $page_uri . '>', '<http://www.w3.org/ns/hydra/core#next>', '<' . $next . '>', '<' . $meta_uri . '>');	
}

//print_r($triples);

$nt = '';

foreach ($triples as $triple)
{
	$nt .= join(' ', $triple) . ' .' . "\n";
}

// output
if ($debug)
{
	header("Content-type: text/plain");
}
else
{
	header("Content-type: application/n-quads;charset=utf-8");
}
header("Access-Control-Allow-Origin: *");

echo $nt;

if (0)
{
	$doc = jsonld_from_rdf($nt, array('format' => 'application/nquads'));

	//print_r($doc);

	// Context to set vocab to schema
	$context = new stdclass;
	$context->{'@vocab'} = "http://schema.org/";
	$context->hydra 	= 'http://www.w3.org/ns/hydra/core#';
	$context->rdfs 		= 'http://www.w3.org/2000/01/rdf-schema#';
	$context->rdf		= 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
	$context->void 		= 'http://rdfs.org/ns/void#';

	$compacted = jsonld_compact($doc, $context);

	// Note JSON_UNESCAPED_UNICODE so that, for example, Chinese characters are not escaped
	echo json_encode($compacted, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	echo "\n";
}

?>