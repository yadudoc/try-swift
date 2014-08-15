# awk "\$1 == ${1:-1} { print \$0 }"

awk "\$1 == ${1:-1} { for (i=2; i<NF; i++) printf \$i \" \"; print $NF}"
