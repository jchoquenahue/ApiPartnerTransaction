#!/bin/bash

chown -R 1000.1000 app
chown -R 1000.1000 database
chown -R 1000.1000 resources
chown -R 1000.1000 config
chown -R 1000.1000 routes
chown -R 1000.1000 .
chown -R 1000.1000 vendor
chmod -R 0777 storage
