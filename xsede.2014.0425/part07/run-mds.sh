#!/bin/sh -eu

stc run-mds.swift

MD_PKG=$( cd ${PWD}/../src/md ; /bin/pwd )
export TURBINE_USER_LIB=${MD_PKG}
export TURBINE_LOG=0 ADLB_DEBUG=0
turbine -n 2 run-mds.tcl --simulations=4 --steps=10
