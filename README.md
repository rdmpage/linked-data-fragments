# CouchDB linked data fragments server

## Linked data fragments

For background see [Linked data fragments](http://linkeddatafragments.org)

## CouchDB as hexastore

Basic idea. We encode the data (e.g., JSON-LD) in six CouchDB views (spo, sop, pso, pos, osp, ops) creating a hexastore. We then parse LDF query and decide which view should be used to match the query.

The CouchDB views are reduce views, so we can use group_levels to get the triples indexed by the appropriate key. If we commit the keys then we get total number of triples that match the query.

To do: Add pagination

## Data lake

Combine CouchDB with views to support a “data lake” approach where we add URLs to resolve, these get added to a queue, resolved (ideally as JSON-LD), and the hexastore automatically grows. Need to think about whether we restrict things to JSON-LD or support other RDF formats, and/or other data formats.

## Server

Server needs to return triples with mime type “application/n-quads” to be recognised.

## Testing

Can test using [jQuery widget to query Triple Pattern Fragments interfaces](https://github.com/LinkedDataFragments/jQuery-Widget.js). Download Github repo and follow instructions.


## Web 

CouchDB list view can be used to mimic SPARQL DESCRIBE by simply returning source document in JSON-LD. Note that this will FAIL if there is more than one document with information about the same entity (in other words, it’s going to fail).
 
