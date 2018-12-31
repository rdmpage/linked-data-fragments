<?php

// Load some data

require_once(dirname(dirname(__FILE__)) . '/queue/queue.php');

$force = false;
$force = true;

$urls = array();

$urls = array('https://doi.org/10.3969/j.issn.1000-3142.2007.06.001');

$urls = array('https://doi.org/10.6165/tai.1983.28.146');

$urls = array(
'https://doi.org/10.1663/0007-196X(2003)055[0205:DRZANS]2.0.CO;2',
'https://doi.org/10.1017/S096042860000192X',
);

$urls = array(
'http://www.siamese-heritage.org/nhbsspdf/vol041-050/NHBSS_049_1k_Larsen_ANewSpeciesOfDisti.pdf'
);

$urls = array(
'http://www.siamese-heritage.org/nhbsspdf/vol041-050/NHBSS_049_1k_Larsen_ANewSpeciesOfDisti.pdf'
);

$urls = array(
'http://www.jjbotany.com/getpdf.php?aid=10761'
);

$urls = array(
'https://doi.org/10.6165/tai.2008.53(3).248',
'http://old.ssbg.asu.ru/turcz/turcz_14_3_14-27.pdf'
);

// DBPedia
$urls = array(
'http://dbpedia.org/resource/Distichochlamys',
'http://dbpedia.org/resource/Caulokaempferia'
);

// IPNI
$urls = array(
'urn:lsid:ipni.org:names:327798-2',
'urn:lsid:ipni.org:names:981551-1',
'urn:lsid:ipni.org:names:77122780-1',
'urn:lsid:ipni.org:names:981552-1',
'urn:lsid:ipni.org:names:20002425-1',
'urn:lsid:ipni.org:names:70029259-1',
);

$urls = array(
'https://doi.org/10.1017/S096042860000192X',
'https://doi.org/10.1663/0007-196X(2003)055[0205:DRZANS]2.0.CO;2',
'https://doi.org/10.2307/4135607'
);

$urls = array(
'https://ci.nii.ac.jp/naid/110003758629#article'
);

$urls = array(
'http://www.worldcat.org/oclc/1281768'
);


// Add items to the queue
foreach ($urls as $url)
{
	echo $url . "\n";
	enqueue($url, $force);
}
	
// Resolve items
while (!queue_is_empty())
{	
	dequeue(100);
}

?>
