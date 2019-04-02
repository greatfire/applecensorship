#!/bin/bash
set -e
echo "starting"
cat mongo.js | mongo ac
echo "OK"
