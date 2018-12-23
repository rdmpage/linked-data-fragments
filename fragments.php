<?php

require_once 'vendor/autoload.php';

require_once (dirname(__FILE__) . '/documentstore/query.php');

$page = 1;
$s = '';
$p = '';
$o = '';

$itemsPerPage = 1000;

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


//----------------------------------------------------------------------------------------
// Initialise URIs

$server_uri = (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

$fragment_uri = $server_uri;// . '?' . http_build_query($parameters);

$data_uri = $server_uri . '#data';

$meta_uri = $server_uri . '#meta';

$triples = array();

//----------------------------------------------------------------------------------------
// Do query
// We need estmate of total number of results
// We need to figure out how to query CouchDB to match LDF query

$total_triples = 0;


$code = array('X', 'X', 'X');
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


//if ($function != 'queryXXX')
{
	$r = $function($parameters);
	
	//print_r($r);

	$result = query_result_to_triples($r);
	
	$total_triples = $r->count;
	
	$triples = array_merge($triples, $result);
	
	$triples[] = array('<' . $fragment_uri . '>', '<http://www.w3.org/ns/hydra/core#totalItems>', '"' . $total_triples . '"', '<' . $meta_uri . '>');
	
}

//----------------------------------------------------------------------------------------
// Describe the service



// meta


// hypermedia controls

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

// data


$triples[] = array('<' . $data_uri . '>', '<http://rdfs.org/ns/void#subset>', '<' . $fragment_uri . '>', '<' . $meta_uri . '>'); 
$triples[] = array('<' . $data_uri . '>', '<http://www.w3.org/ns/hydra/core#search>', '_:triplePattern', '<' . $meta_uri . '>');
                    
                    
// pagination
$first =  $fragment_uri . '&page=' . $page;  
                    
$triples[] = array('<' . $fragment_uri . '>', '<http://www.w3.org/ns/hydra/core#totalItems>', '"' . $total_triples . '"', '<' . $meta_uri . '>');

$triples[] = array('<' . $fragment_uri . '>', '<http://www.w3.org/ns/hydra/core#first>', '<' . $first . '>', '<' . $meta_uri . '>');

if ($page > 1)
{
	$previous =  $fragment_uri . '&page=' . ($page - 1);
	$triples[] = array('<' . $fragment_uri . '>', '<http://www.w3.org/ns/hydra/core#previous>', '<' . $previous . '>', '<' . $meta_uri . '>');
}

if ($total_triples > ($itemsPerPage * $page))
{
	$next =  $fragment_uri . '&page=' . ($page + 1);
	$triples[] = array('<' . $fragment_uri . '>', '<http://www.w3.org/ns/hydra/core#next>', '<' . $next . '>', '<' . $meta_uri . '>');	
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
	header("Content-type: application/n-quads");
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