#!/bin/bash
set -e
php recreate_status_changes.php
echo "db.status_changes_tmp.renameCollection('status_changes', true)" | mongo ac
echo "done"
