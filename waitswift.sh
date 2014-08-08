#!/bin/bash

webroot="/home/davidk/tryswift"
maxwait=60
counter=0

while [ ! -f "swift.pid" ] && [ $counter -lt $maxwait ] 
do
   counter=$( expr $counter + 1)
   sleep 1
done

if [ $counter -ge $maxwait ]; then
   exit 1
fi

swiftpid=$( cat swift.pid )
while ps -p $swiftpid > /dev/null 
do
   sleep 1
done

exit 0
