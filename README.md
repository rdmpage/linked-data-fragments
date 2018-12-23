# CouchDB linked data fragments server

Basic idea. We encode the data (e.g., JSON-LD) in six CouchDB views (spo, sop, pso, pos, osp, ops) creating a hexastore. We then parse LDF query and decide which view should be used to match the query.

## Server

Server needs to return triples with mime type “application/n-quads” to be recognised.

## Testing

Can test using [jQuery widget to query Triple Pattern Fragments interfaces](https://github.com/LinkedDataFragments/jQuery-Widget.js). Download Github repo and follow instructions.