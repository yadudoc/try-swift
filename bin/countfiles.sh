#!/bin/bash

find * -type f \
   -not -ipath "tc.data" \
   -not -ipath "sites.xml" \
   -not -ipath "script.swift" \
   -not -ipath "swift.out" \
   -not -ipath "swift.pid" \
   -not -ipath "cf" \
   -not -ipath "*.d" \
   -not -ipath "*.kml" \
   -not -ipath "*.swiftx" \
   -not -ipath "*.log" \
| wc -l
