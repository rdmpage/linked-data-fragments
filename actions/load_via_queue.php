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


$urls = array(
'http://bionames.org/ipni/cluster/1019548-1',
'http://bionames.org/ipni/cluster/20007423-1',
'http://bionames.org/ipni/cluster/60437281-2',
'http://bionames.org/ipni/cluster/156693-1',
'http://bionames.org/ipni/cluster/268060-1',
);

$urls = array(
'https://ci.nii.ac.jp/naid/110004697577#article',
'https://hdl.handle.net/10125/501',
);


$urls = array(
'http://bionames.org/ipni/cluster/938968-1',
'http://bionames.org/ipni/cluster/77144914-1',
'http://bionames.org/ipni/cluster/77144914-1',
'http://bionames.org/ipni/cluster/77146175-1',
'http://bionames.org/ipni/cluster/104280-1',
'http://bionames.org/ipni/cluster/77160201-1',
);


$urls = array(
'http://bionames.org/ipni/cluster/60437281-2',
'http://bionames.org/ipni/cluster/77141141-1',
'http://bionames.org/ipni/cluster/77109580-1',
'http://bionames.org/ipni/cluster/77109581-1',
'http://bionames.org/ipni/cluster/77109582-1',
'http://bionames.org/ipni/cluster/77109583-1',
'http://bionames.org/ipni/cluster/77109584-1',
'http://bionames.org/ipni/cluster/77109585-1',
'http://bionames.org/ipni/cluster/77109586-1',
'http://bionames.org/ipni/cluster/77109587-1',
);

$urls = array(
'http://bionames.org/ipni/cluster/45334-1',
'http://bionames.org/ipni/cluster/45336-1',
'http://bionames.org/ipni/cluster/45339-1',
'http://bionames.org/ipni/cluster/45337-1',
'http://bionames.org/ipni/cluster/77141128-1',
'http://bionames.org/ipni/cluster/893742-1',
'http://bionames.org/ipni/cluster/77086735-1',
);

$urls = array(
//'https://hdl.handle.net/10125/501',
"http://bionames.org/ipni/cluster/32217-1",
"http://bionames.org/ipni/cluster/50425784-2",
"http://bionames.org/ipni/cluster/60434067-2",
"http://bionames.org/ipni/cluster/688314-1",
"http://bionames.org/ipni/cluster/688315-1",
"http://bionames.org/ipni/cluster/688316-1",
"http://bionames.org/ipni/cluster/688317-1",
"http://bionames.org/ipni/cluster/688318-1",
"http://bionames.org/ipni/cluster/688319-1",
"http://bionames.org/ipni/cluster/688320-1",
"http://bionames.org/ipni/cluster/688321-1",
"http://bionames.org/ipni/cluster/688322-1",
"http://bionames.org/ipni/cluster/688323-1",
"http://bionames.org/ipni/cluster/688324-1",
"http://bionames.org/ipni/cluster/688325-1",
"http://bionames.org/ipni/cluster/688326-1",
"http://bionames.org/ipni/cluster/688327-1",
"http://bionames.org/ipni/cluster/688328-1",
"http://bionames.org/ipni/cluster/688329-1",
"http://bionames.org/ipni/cluster/688330-1",
"http://bionames.org/ipni/cluster/688331-1",
"http://bionames.org/ipni/cluster/688332-1",
"http://bionames.org/ipni/cluster/688333-1",
"http://bionames.org/ipni/cluster/688334-1",
"http://bionames.org/ipni/cluster/688335-1",
"http://bionames.org/ipni/cluster/688336-1",
"http://bionames.org/ipni/cluster/688337-1",
"http://bionames.org/ipni/cluster/688338-1",
"http://bionames.org/ipni/cluster/688339-1",
"http://bionames.org/ipni/cluster/688340-1",
"http://bionames.org/ipni/cluster/77066315-1",
"http://bionames.org/ipni/cluster/77095168-1",
"http://bionames.org/ipni/cluster/77096034-1",
"http://bionames.org/ipni/cluster/894707-1",
"http://bionames.org/ipni/cluster/904464-1",
"http://bionames.org/ipni/cluster/904465-1",
"http://bionames.org/ipni/cluster/904466-1",
"http://bionames.org/ipni/cluster/904467-1",
"http://bionames.org/ipni/cluster/904468-1",
"http://bionames.org/ipni/cluster/904469-1",
"http://bionames.org/ipni/cluster/904470-1",
"http://bionames.org/ipni/cluster/904471-1",
"http://bionames.org/ipni/cluster/904472-1",
"http://bionames.org/ipni/cluster/904473-1",
"http://bionames.org/ipni/cluster/904474-1",
"http://bionames.org/ipni/cluster/904475-1",
"http://bionames.org/ipni/cluster/904476-1",
"http://bionames.org/ipni/cluster/904477-1",
"http://bionames.org/ipni/cluster/904478-1",
"http://bionames.org/ipni/cluster/904479-1",
"http://bionames.org/ipni/cluster/904480-1",
"http://bionames.org/ipni/cluster/907673-1",
"http://bionames.org/ipni/cluster/914127-1",
"http://bionames.org/ipni/cluster/914128-1",
"http://bionames.org/ipni/cluster/77144183-1",
);


// GBIF
$urls = array(
'https://www.gbif.org/occurrence/574819276',
'http://www.gbif.org/occurrence/1318162091',
'http://www.gbif.org/occurrence/1261835122',
'http://www.gbif.org/occurrence/1929275314',
);

// Fungi
$urls = array(
'urn:lsid:indexfungorum.org:names:814659',
'urn:lsid:indexfungorum.org:names:814692'
);

// Article in Zenodo with images
$urls = array(
'https://zenodo.org/record/576067',
'https://zenodo.org/record/918933',
'https://zenodo.org/record/918937',
'https://zenodo.org/record/918939',
'https://zenodo.org/record/918935',
);

$urls = array(
'https://zenodo.org/record/1166373',
'https://zenodo.org/record/1166732',
'https://zenodo.org/record/1166746',
'https://zenodo.org/record/1166740',
'https://zenodo.org/record/1166744',
'https://zenodo.org/record/1166734',
'https://zenodo.org/record/1166742',
'https://zenodo.org/record/1166736',
'https://zenodo.org/record/1166738',
);

$urls = array(
'https://zenodo.org/record/919506',
'https://zenodo.org/record/919508',
'https://zenodo.org/record/919510',
'https://zenodo.org/record/576149',
'https://zenodo.org/record/919512',
);


$urls=array(
'https://doi.org/10.1080/01916122.2007.9989637',
'http://bionames.org/indexfungorum/cluster/568745',
);

$urls=array(
'https://doi.org/10.5248/132.305',
'http://bionames.org/indexfungorum/cluster/814659',
);

$urls=array(
'http://www.repository.naturalis.nl/record/534866'
);

// CrossRef URL to resolve using CrossRef
$urls=array(
'https://doi.org/10.1080/00275514.2018.1515449'
);

$urls=array(
'https://doi.org/10.1139/b05-107'
);

$urls = array(
'10.5735/085.047.0307',
'http://bionames.org/ipni/cluster/77106748-1',
'http://bionames.org/ipni/cluster/77112890-1',
'http://www.ingentaconnect.com/content/iapt/tax/2011/00000060/00000003/art00012',
);

$urls = array(
'https://doi.org/10.6165/tai.2012.57(1).55',
'https://doi.org/10.11646/phytotaxa.208.2.4',
'https://doi.org/10.11646/phytotaxa.177.4.6'

);

$urls = array(
'https://doi.org/10.1600/036364407783390764'
);

$urls = array(
'https://doi.org/10.1111/j.1759-6831.2010.00113.x',
'https://doi.org/10.1111/nph.12726',
'https://doi.org/10.11646/phytotaxa.92.2.2',
);


$urls = array(
//'http://www.whzwxyj.cn/CN/abstract/abstract1762.shtml',
'https://doi.org/10.11646/phytotaxa.177.4.6'
);


$urls = array(
'https://doi.org/10.3969/j.issn.2095-0845.2004.02.006',
'https://doi.org/10.11646/phytotaxa.177.4.6'
);

$urls=array(
'http://bionames.org/ipni/cluster/77064877-1'
);

$urls=array(
'http://bbr.nefu.edu.cn/CN/abstract/abstract1775.shtml'
);

// CETAF
$urls=array(
'http://data.rbge.org.uk/herb/E00435919',
'http://herbarium.bgbm.org/object/B100241392',
'http://coldb.mnhn.fr/catalognumber/mnhn/p/p05036298',
);

$urls=array(
'http://data.rbge.org.uk/herb/E00155121',
'http://data.rbge.org.uk/herb/E00155186',
);

$urls=array(
'https://doi.org/10.11646/phytotaxa.23.1.1',
'https://doi.org/10.11646/phytotaxa.131.1.5',
'http://bionames.org/ipni/cluster/77111843-1', // name Phytotaxa
'http://bionames.org/ipni/cluster/315984-1', // basionym (BHL and BioStor - to do)
'http://bionames.org/ipni/cluster/77133177-1', // homonym
);

$urls=array(
'https://biostor.org/reference/246525'
);

$urls=array(
'http://bionames.org/ipni/cluster/77123889-1'
);

// zenodo
$urls=array(
'https://zenodo.org/record/576152',
'https://zenodo.org/record/919528',
'https://zenodo.org/record/919526',
'https://zenodo.org/record/919536',
'https://zenodo.org/record/919524',
'https://zenodo.org/record/919534',
'https://zenodo.org/record/919532',
'https://zenodo.org/record/919530',
);

$urls=array(
'http://bionames.org/ipni/cluster/77148196-1',
);

$urls=array(
'https://doi.org/10.26492/gbs69(2).2017-09'
);

// replacement name
$urls=array(
'http://bionames.org/ipni/cluster/77140287-1'
);

// Poissonia
$urls=array(
'http://bionames.org/ipni/cluster/20012729-1'
);

$urls=array(
'http://bionames.org/ipni/cluster/487377-1',
);


$urls=array(
'http://bionames.org/ipni/cluster/60441193-2',
'http://bionames.org/ipni/cluster/469833-1',
'http://bionames.org/ipni/cluster/77092629-1',
'http://bionames.org/ipni/cluster/981000-1',
);

// DOI that has figures in Zenodo
$urls=array(
'https://doi.org/10.1002/ajp.22631'
);

$urls=array(
'https://doi.org/10.1007/s11230-017-9700-1'
);

$urls=array(
'https://doi.org/10.11646/zootaxa.3931.1.3'
);

$urls=array(
'https://doi.org/10.3897/phytokeys.73.9737'
);
$urls=array(
'https://doi.org/10.3897/zookeys.643.10543'
);


$urls=array(
'https://zenodo.org/record/273086'
);

$urls=array(
'urn:lsid:ipni.org:names:77177604-1'
);

$urls=array(
'https://doi.org/10.11646/phytotaxa.343.3.10'
);

$urls=array(
'http://bionames.org/ipni/cluster/77146175-1',
'https://doi.org/10.1007/s12225-015-9569-6',
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
