#!/bin/bash
log1="log/"$(date +%s)
log2="$log1.ops"
set -e
echo "starting"
date
(time cat mongo.js | mongo ac && echo ok) 2>&1 >$log1 &
while [ $(jobs -r | wc -l) != 0 ]; do
	echo "db.currentOp()" | mongo 2>&1 >>$log2
	sleep 1
done

echo "All done"
date
