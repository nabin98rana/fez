#!/bin/bash
pushd $1/build
export DISPLAY=:99 
ant selenium
popd
