# Queue

The queue is implemented in CouchDB. Given the URL for a record we create a document in CouchDB that has that URL as its identifier, and a timestamp set to the current time. The contents of the document (stored in the "message" field) are empty. Identifiers are added to the queue by calling the method **enqueue**.

We have a view that indexes the documents that don't have a ```doc.message``` field by their timestamp. This gives us a list of identifiers that we need to resolve. Calling **dequeue** results in a set of documents in the queue being fetched, each is passed to the resolver to retrieve the data. If resolution is successful the ```doc.message``` field is populated and the document automatically leaves the queue. If resolution fails we create a counter for each failed attempt, if it exceeds a cutoff value (e.g., 3) then we remove that document from the queue. This avoids situations where the queue gets "blocked" because we keep failing to resolve an identifier.
