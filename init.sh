#!/bin/bash

echo "127.0.0.1  backend.antavo.io frontend.antavo.io" >> /etc/hosts

docker build -t antavo .