<?php

// Load some data

require_once(dirname(dirname(__FILE__)) . '/queue/queue.php');


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


// Name clusters, names, and publications
$urls = array(
'http://bionames.org/ipni/cluster/981551-1'
);

$urls = array(
'http://bionames.org/ipni/cluster/70029259-1',
'http://bionames.org/ipni/cluster/551741-1',
'https://doi.org/10.1139/b75-031',
);

$urls = array(

// names

'urn:lsid:ipni.org:names:25386-1',
'urn:lsid:ipni.org:names:331350-2',
'urn:lsid:ipni.org:names:380-1',
'urn:lsid:ipni.org:names:45332-1',
'urn:lsid:ipni.org:names:45333-1',
'urn:lsid:ipni.org:names:45334-1',
'urn:lsid:ipni.org:names:45335-1',
'urn:lsid:ipni.org:names:45336-1',
'urn:lsid:ipni.org:names:45337-1',
'urn:lsid:ipni.org:names:45338-1',
'urn:lsid:ipni.org:names:45339-1',
'urn:lsid:ipni.org:names:45340-1',
'urn:lsid:ipni.org:names:45341-1',
'urn:lsid:ipni.org:names:45342-1',
'urn:lsid:ipni.org:names:45343-1',
'urn:lsid:ipni.org:names:45344-1',
'urn:lsid:ipni.org:names:45345-1',
'urn:lsid:ipni.org:names:45346-1',
'urn:lsid:ipni.org:names:45347-1',
'urn:lsid:ipni.org:names:45348-1',
'urn:lsid:ipni.org:names:45349-1',
'urn:lsid:ipni.org:names:45350-1',
'urn:lsid:ipni.org:names:552808-1',
'urn:lsid:ipni.org:names:552809-1',
'urn:lsid:ipni.org:names:77086735-1',
'urn:lsid:ipni.org:names:77109775-1',
'urn:lsid:ipni.org:names:77109776-1',
'urn:lsid:ipni.org:names:893742-1',
'urn:lsid:ipni.org:names:893743-1',
'urn:lsid:ipni.org:names:893744-1',
'urn:lsid:ipni.org:names:893745-1',
'urn:lsid:ipni.org:names:893746-1',
'urn:lsid:ipni.org:names:896898-1',
'urn:lsid:ipni.org:names:896899-1',
'urn:lsid:ipni.org:names:896900-1',
'urn:lsid:ipni.org:names:916051-1',
'urn:lsid:ipni.org:names:916052-1',
'urn:lsid:ipni.org:names:916053-1',
'urn:lsid:ipni.org:names:77141128-1',
'urn:lsid:ipni.org:names:77137828-1',
'urn:lsid:ipni.org:names:77147174-1',
'urn:lsid:ipni.org:names:77141129-1',
'urn:lsid:ipni.org:names:77137829-1',
'urn:lsid:ipni.org:names:77137830-1',
'urn:lsid:ipni.org:names:77137831-1',


// DOIs
'https://doi.org/10.1017/S0370164600000195',
'https://doi.org/10.2307/4111783',
'https://doi.org/10.1080/00378941.1907.10833386',
'https://doi.org/10.1080/00378941.1929.10836328',
'https://doi.org/10.1111/j.1756-1051.1981.tb01030.x',
'https://doi.org/10.15553/c2014v691a5',
'https://doi.org/10.3417/2012054',


// clusters
'http://bionames.org/ipni/cluster/25386-1',
'http://bionames.org/ipni/cluster/380-1',
'http://bionames.org/ipni/cluster/45332-1',
'http://bionames.org/ipni/cluster/45333-1',
'http://bionames.org/ipni/cluster/45334-1',
'http://bionames.org/ipni/cluster/45335-1',
'http://bionames.org/ipni/cluster/45336-1',
'http://bionames.org/ipni/cluster/45337-1',
'http://bionames.org/ipni/cluster/45338-1',
'http://bionames.org/ipni/cluster/45339-1',
'http://bionames.org/ipni/cluster/45340-1',
'http://bionames.org/ipni/cluster/45341-1',
'http://bionames.org/ipni/cluster/45342-1',
'http://bionames.org/ipni/cluster/45343-1',
'http://bionames.org/ipni/cluster/45344-1',
'http://bionames.org/ipni/cluster/45345-1',
'http://bionames.org/ipni/cluster/45346-1',
'http://bionames.org/ipni/cluster/45347-1',
'http://bionames.org/ipni/cluster/45348-1',
'http://bionames.org/ipni/cluster/45349-1',
'http://bionames.org/ipni/cluster/45350-1',
'http://bionames.org/ipni/cluster/552808-1',
'http://bionames.org/ipni/cluster/552809-1',
'http://bionames.org/ipni/cluster/77086735-1',
'http://bionames.org/ipni/cluster/77109775-1',
'http://bionames.org/ipni/cluster/77109776-1',
'http://bionames.org/ipni/cluster/893742-1',
'http://bionames.org/ipni/cluster/893743-1',
'http://bionames.org/ipni/cluster/893744-1',
'http://bionames.org/ipni/cluster/893745-1',
'http://bionames.org/ipni/cluster/893746-1',
'http://bionames.org/ipni/cluster/896898-1',
'http://bionames.org/ipni/cluster/896899-1',
'http://bionames.org/ipni/cluster/896900-1',
'http://bionames.org/ipni/cluster/916051-1',
'http://bionames.org/ipni/cluster/916052-1',
'http://bionames.org/ipni/cluster/916053-1',
'http://bionames.org/ipni/cluster/77141128-1',
'http://bionames.org/ipni/cluster/77137828-1',
'http://bionames.org/ipni/cluster/77147174-1',
'http://bionames.org/ipni/cluster/77141129-1',
'http://bionames.org/ipni/cluster/77137829-1',
'http://bionames.org/ipni/cluster/77137830-1',
'http://bionames.org/ipni/cluster/77137831-1',


);

$urls = array(

// names
'urn:lsid:ipni.org:names:476-1',
'urn:lsid:ipni.org:names:46928-1',

// pub
'https://doi.org/10.2307/3994997',

// cluster
'http://bionames.org/ipni/cluster/476-1',
'http://bionames.org/ipni/cluster/46928-1',

);

// IPNI author
$urls = array(

// works
'https://doi.org/10.11646/phytotaxa.313.1.9',
'https://doi.org/10.11646/phytotaxa.343.3.6',

// authors
'urn:lsid:ipni.org:authors:37149-1',
'urn:lsid:ipni.org:authors:20021661-1',
'urn:lsid:ipni.org:authors:37266-1',
'urn:lsid:ipni.org:authors:20013722-1',
'urn:lsid:ipni.org:authors:20031438-1',

// names
'urn:lsid:ipni.org:names:77176278-1',
'urn:lsid:ipni.org:names:77177603-1',

// cluster
'http://bionames.org/ipni/cluster/77176278-1',
'http://bionames.org/ipni/cluster/77177603-1',

);

$urls = array(

'urn:lsid:ipni.org:authors:11392-1',
'urn:lsid:ipni.org:authors:37149-1',

'urn:lsid:ipni.org:names:77088053-1',

// cluster
'http://bionames.org/ipni/cluster/77088053-1',
);

$urls = array(
'http://bionames.org/ipni/cluster/1008144-1'
);

$urls = array(
'http://bionames.org/ipni/cluster/1014341-1',
'http://bionames.org/ipni/cluster/77177604-1',
'http://bionames.org/ipni/cluster/77161332-1',
'http://bionames.org/ipni/cluster/104123-1',
'http://bionames.org/ipni/cluster/77095077-1',
'http://bionames.org/ipni/cluster/77160200-1',
);


$urls = array(
//'http://bionames.org/ipni/cluster/77109775-1',
'http://bionames.org/ipni/cluster/77095602-1',
'http://bionames.org/ipni/cluster/50426040-2',
'http://bionames.org/ipni/cluster/77067653-1',
'http://bionames.org/ipni/cluster/77149130-1',
);



$urls = array(
'http://bionames.org/ipni/cluster/77163088-1'
);

$urls = array(
'https://biostor.org/reference/246525'
);



$urls = array(
'http://bionames.org/ipni/cluster/104737-1',
'http://bionames.org/ipni/cluster/77140003-1',
'http://bionames.org/ipni/cluster/77085615-1',
'http://bionames.org/ipni/cluster/77072229-1',
'http://bionames.org/ipni/cluster/105237-1',
'http://bionames.org/ipni/cluster/1007000-1',
);


$force = false;
$force = true;


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
