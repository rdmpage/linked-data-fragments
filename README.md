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


## Web 

CouchDB list view can be used to mimic SPARQL DESCRIBE by simply returning source document in JSON-LD. Note that this will FAIL if there is more than one document with information about the same entity (in other words, **it’s going to fail**).
 

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



