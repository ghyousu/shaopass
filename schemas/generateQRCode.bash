#!/bin/bash

if [ -z "$DATABASE_URL" ]; then
   echo "env var 'DATABASE_URL' is not set. Exiting script"
   exit 1
fi

if [ $# -ne 1 ]; then
   echo "Usage: $0 [901-904]"
   exit 2
fi

class=$1

TMP_SCRIPT=qrcode_gen_${class}.bash

db_query="SELECT student_id,fname,lname FROM common.student WHERE class='$class'"

## use "," as separator
psql --tuples-only -AF$',' $DATABASE_URL -c "$db_query" | \
   awk -F',' '{printf("qrencode -o %d.png \"%s %s\"\n", $1, $2, $3)}' > $TMP_SCRIPT

echo "See $TMP_SCRIPT"
