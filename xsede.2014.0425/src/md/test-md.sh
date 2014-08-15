#!/bin/sh -eu

NPART=4
STEPS=10
MASS=1
SEED=42
OUT=output.txt
TRJ=output.trj

./md 3 ${NPART} ${STEPS} 10 ".0001" ${MASS} "0.1 1.0 0.2 0.05 50.0 0.1" 2.5 2.0 ${SEED} ${OUT} ${TRJ}
