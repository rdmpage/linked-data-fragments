<?php

// Delete ALL! records from CouchDB to start afresh

require_once (dirname(__FILE__) . '/config.inc.php');
require_once (dirname(__FILE__) . '/couchsimple.php');

// Get list of documents
// http://127.0.0.1:5984/crowdsource/_design/housekeeping/_view/ids

$response_obj = null;
	
$url = '_design/housekeeping/_view/ids';

echo $url . "\n";

$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/" . $url);

if ($resp)
{
	$response_obj = json_decode($resp);
	
	foreach ($response_obj->rows as $row)
	{
		//echo $row->value . "\n";		
		$couch->add_update_or_delete_document(null, $row->value, 'delete');
	}
}	


?>