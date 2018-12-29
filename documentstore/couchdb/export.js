{
    "_id": "_design\/export",
    "_rev": "2-96b369a9736ca0c10bd5c2c7bddd87ef",
    "lists": {
        "jsonld": "function(head,req) { var row; start({ 'headers': { 'Content-Type': 'text\/plain' } }); while(row = getRow()) { send(JSON.stringify(row.value) + '\\n'); } }"
    },
    "views": {
        "jsonld": {
            "map": "function (doc) {\n  if (doc.message) {\n    if (doc['message-format'] == 'application\/ld+json') {\n      var id = '';\n      if (doc.message['@graph']) {\n        id = doc.message['@graph'][0]['@id'];\n      } else {\n        id = doc.message['@id'];\n      }\n      emit(id, doc.message);\n    }\n  }\n}"
        }
    },
    "language": "javascript"
}