# Document store

Use CouchDB to store documents and do conversions to other formats. 

Each document has a **message** part that contains the data.

## Javascript views

Beautify Javascript http://jsbeautifier.org

## Convert native JSON to n-triples

Use mime-type as flag to determine JSON format, output simple n-triples for export to RDF triple store. Need @context variables if we want to convert to JSON-LD for export.

### MIME types

MIME types for various identifiers and data sources.

| Identifier    | MIME                                      | 
| :------------ |:-----------------------------------------:| 
| CrossRef      | application/vnd.crossref-api-message+json |
| ORCID         | application/vnd.orcid+json                |


## Conventions

### identifiers


## Get triples

Triples are available in a list view, e.g. 

http://127.0.0.1:5984/a-data-lake/_design/crossref/_list/n-triples/nt

## Notes on vocbularies

- wherever possible use schema.org

## Notes on document types




