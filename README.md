# CouchDB linked data fragments server

## Linked data fragments

For background see [Linked data fragments](http://linkeddatafragments.org)

## CouchDB as hexastore

Basic idea. We encode the data (e.g., JSON-LD) in six CouchDB views (spo, sop, pso, pos, osp, ops) creating a hexastore. We then parse LDF query and decide which view should be used to match the query.

The CouchDB views are reduce views, so we can use group_levels to get the triples indexed by the appropriate key. If we omit the keys then we get total number of triples that match the query (a requirement for LDF clients).

**To do: Add pagination**

## Data lake

Combine CouchDB with views to support a “data lake” approach where we add URLs to resolve, these get added to a queue, resolved (ideally as JSON-LD), and the hexastore automatically grows. Need to think about whether we restrict things to JSON-LD or support other RDF formats, and/or other data formats.

## Personalisation

Could use https://hypothes.is as annotation server, and fetch those to enhance records. Could even imagine having a local PouchDB version of data-lake/LDF server so people could have their own, local annotations on data.

## Server

Server needs to return triples with mime type “application/n-quads” to be recognised by LDF clients.

## Testing

Can test using [jQuery widget to query Triple Pattern Fragments interfaces](https://github.com/LinkedDataFragments/jQuery-Widget.js). Download Github repo and follow instructions.

RDFa https://rdfa.info/play/

## Elasticsearch

Use a CouchDB view to generate Elasticsearch schema documents, then upload these to Elasticsearch.

### Get JSONL dump of Elastic documents

```
curl http://127.0.0.1:5984/fragments/_design/export/_list/jsonl/elastic > elastic.jsonl
```

Then we need to chunk it and add metadata:

```
php chunk-elastic.php
```

```
chmod 777 upload-elastic.sh 
```

```
./upload-elastic.sh
```


## Web 

CouchDB list view can be used to mimic SPARQL DESCRIBE by simply returning source document in JSON-LD. Note that this will FAIL if there is more than one document with information about the same entity (in other words, **it’s going to fail**).
 

## Applications

### IPNI authors

The problems with IPNI author teams is that the order of an author is treated as a global property of that author (team member), not specific to a particular team for a name. Perhaps could achieve this using named graphs where each IPNI LSID is in its own named graph, but that won’t work for triples. IPNI really needs the notion of a “role”, or we have to use non-RDF means to help do the author matching.

Updated: this is an bug in the way IPNI stores this info, we can program around this.

Matching IPNI authors to paper creators

```
prefix tn: <http://rs.tdwg.org/ontology/voc/TaxonName#>
prefix tm: <http://rs.tdwg.org/ontology/voc/Team#>
prefix tcom: <http://rs.tdwg.org/ontology/voc/Common#>
prefix tp: <http://rs.tdwg.org/ontology/voc/Person#>
PREFIX wdt: <http://www.wikidata.org/prop/direct/>
PREFIX wd: <http://www.wikidata.org/entity/>	
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX schema: <http://schema.org/>
PREFIX dc: <http://purl.org/dc/elements/1.1/>

SELECT ?ipniAuthor ?person ?roleName ?familyName ?ipniAuthorName ?name
where
{
	# publication of name
	<urn:lsid:ipni.org:names:77177604-1> tcom:publishedInCitation ?publication .
	?publication schema:creator ?role  .
	?role  rdf:type schema:Role . 
	?role schema:roleName ?roleName  . # same as IPNI tm:index
	?role schema:creator ?person  .
	?person rdf:type schema:Person .
	?person schema:name ?name .
	?person schema:familyName ?familyName . # same as IPNI

	#ipni authors in team
	<urn:lsid:ipni.org:names:77177604-1> tn:authorteam ?team .
	?team tm:hasMember ?member .
	?member tm:role ?ipnirole .
	?member tm:index ?roleName . # same as roleName
	?member tm:member ?ipniAuthor .

	?ipniAuthor dc:title ?ipniAuthorName .

	?ipniAuthor tp:alias ?alias .
	?alias tp:surname ?familyName . # same as publication
	
	FILTER (?ipnirole =  "Publishing Author")
}
```


## Related projects

### Citable specimens (CETAF)

https://cetaf.org/cetaf-stable-identifiers

HYAM, R., DRINKWATER, R. E., & HARRIS, D. J. (2012). <p class=“HeadingRunIn”><strong>Stable citations for herbarium specimens on the internet: an illustration from a taxonomic revision of <em>Duboscia </em>(Malvaceae)</strong></p>. Phytotaxa, 73(1), 17. doi:10.11646/phytotaxa.73.1.4

Specimens in Table 1:

http://data.rbge.org.uk/herb/E00435912
http://data.rbge.org.uk/herb/E00435914
http://data.rbge.org.uk/herb/E00435922
http://data.rbge.org.uk/herb/E00435918
http://data.rbge.org.uk/herb/E00435917
http://data.rbge.org.uk/herb/E00435913
http://data.rbge.org.uk/herb/E00435920
http://data.rbge.org.uk/herb/E00435911
http://data.rbge.org.uk/herb/E00435923
http://data.rbge.org.uk/herb/E00435919
http://data.rbge.org.uk/herb/E00435924
http://data.rbge.org.uk/herb/E00435925
http://data.rbge.org.uk/herb/E00435915
http://data.rbge.org.uk/herb/E00435916
http://data.rbge.org.uk/herb/E00435926
http://data.rbge.org.uk/herb/E00435921
http://data.rbge.org.uk/herb/E00435909
http://data.rbge.org.uk/herb/E00435908
http://data.rbge.org.uk/herb/E00421511
http://data.rbge.org.uk/herb/E00421512
http://data.rbge.org.uk/herb/E00421507
http://data.rbge.org.uk/herb/E00421510
http://data.rbge.org.uk/herb/E00421508
http://data.rbge.org.uk/herb/E00421509


### Microsoft Academic Knowledge Graph

http://ma-graph.org:8080/mag-pubby/page/1987717313

http://ma-graph.org/knowledge-graph-exploration/

http://ma-graph.org/entity/1987717313

http://ma-graph.org/sparql

```
select * where { ?s ?p "10.1111/j.1756-1051.2008.00162.x"^^xsd:string }
```

http://ma-graph.org/sparql?default-graph-uri=&query=select+*+where+%7B+%3Fs+%3Fp+%2210.1111%2Fj.1756-1051.2008.00162.x%22%5E%5Exsd%3Astring+%7D&format=text%2Fhtml&timeout=0&debug=on&run=+Run+Query+

### Google Knowledge Graph

```
{
    "@context": {
        "@vocab": "http://schema.org/",
        "goog": "http://schema.googleapis.com/",
        "EntitySearchResult": "goog:EntitySearchResult",
        "detailedDescription": "goog:detailedDescription",
        "resultScore": "goog:resultScore",
        "kg": "http://g.co/kg"
    },
    "@type": "ItemList",
    "itemListElement": [
        {
            "@type": "EntitySearchResult",
            "result": {
                "@id": "kg:/g/11c80r37lv",
                "name": "Sara A. Lourie",
                "@type": [
                    "Thing",
                    "Person"
                ],
                "description": "Canadian ichthyologist"
            },
            "resultScore": 149.065659
        },
        {
            "@type": "EntitySearchResult",
            "result": {
                "@id": "kg:/m/064l2q7",
                "name": "Satomi's pygmy seahorse",
                "@type": [
                    "Thing"
                ],
                "description": "Fish",
                "image": {
                    "contentUrl": "http://t1.gstatic.com/images?q=tbn:ANd9GcTnxVm3-RLfVTVpSiNYj3sL0r3P-0Kz8SvR6EtIVdQUtwfaYUuG",
                    "url": "https://en.wikipedia.org/wiki/Satomi's_pygmy_seahorse"
                },
                "detailedDescription": {
                    "articleBody": "Satomi's pygmy seahorse is the smallest known seahorse in the world with an average length of 13.8 millimetres and an approximate height of 11.5 millimetres.\nThis member of the family Syngnathidae is found at the Derawan Islands off Kalimantan. ",
                    "url": "https://en.wikipedia.org/wiki/Satomi's_pygmy_seahorse",
                    "license": "https://en.wikipedia.org/wiki/Wikipedia:Text_of_Creative_Commons_Attribution-ShareAlike_3.0_Unported_License"
                }
            },
            "resultScore": 17.578156
        },
        {
            "@type": "EntitySearchResult",
            "result": {
                "@id": "kg:/m/02wy1zv",
                "name": "Denise's pygmy seahorse",
                "@type": [
                    "Thing"
                ],
                "description": "Fish",
                "image": {
                    "contentUrl": "http://t0.gstatic.com/images?q=tbn:ANd9GcThshzSDgirh2kM3SpYdTsQjzEhgJsN17ecAFOq-abvqnFtYhfI",
                    "url": "https://en.wikipedia.org/wiki/Denise's_pygmy_seahorse"
                },
                "detailedDescription": {
                    "articleBody": "Hippocampus denise, also known as Denise's pygmy seahorse or the yellow pygmy seahorse, is a seahorse of the family Syngnathidae native to the western Pacific.",
                    "url": "https://en.wikipedia.org/wiki/Denise's_pygmy_seahorse",
                    "license": "https://en.wikipedia.org/wiki/Wikipedia:Text_of_Creative_Commons_Attribution-ShareAlike_3.0_Unported_License"
                }
            },
            "resultScore": 11.528545
        },
        {
            "@type": "EntitySearchResult",
            "result": {
                "@id": "kg:/m/0nybt",
                "name": "Seahorse",
                "@type": [
                    "Thing"
                ],
                "description": "Fish",
                "image": {
                    "contentUrl": "http://t2.gstatic.com/images?q=tbn:ANd9GcTGqElvq6GUKH-PITRnlcf-007r8wPRPl92AMzdTJms5avaTDBy",
                    "url": "https://en.wikipedia.org/wiki/Seahorse"
                },
                "detailedDescription": {
                    "articleBody": "Seahorse is the name given to 45 species of small marine fishes in the genus Hippocampus. \"Hippocampus\" comes from the Ancient Greek hippokampos, itself from hippos meaning \"horse\" and kampos meaning \"sea monster\". ",
                    "url": "https://en.wikipedia.org/wiki/Seahorse",
                    "license": "https://en.wikipedia.org/wiki/Wikipedia:Text_of_Creative_Commons_Attribution-ShareAlike_3.0_Unported_License"
                }
            },
            "resultScore": 8.4383
        }
    ]
}
```

