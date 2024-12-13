#!/bin/bash

if [ -z "$DATABASE_URL" ]; then
   echo "env var 'DATABASE_URL' is not set. Exiting script"
   exit 1
fi

TMP_SCRIPT=qrcode_gen.bash

db_query="SELECT student_id,fname,lname FROM common.student"

## use "," as separator
psql --tuples-only -AF$',' $DATABASE_URL -c "$db_query" | \
   awk -F',' '{printf("qrencode -s 5 -o ../imgs/%d.png \"%s %s\"\n", $1, $2, $3)}' > $TMP_SCRIPT

echo "See $TMP_SCRIPT"
