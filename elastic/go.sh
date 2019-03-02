#!/bin/sh

curl http://127.0.0.1:5984/fragments/_design/export/_list/jsonl/elastic > elastic.jsonl
php chunk-elastic.php
./upload-elastic.sh
