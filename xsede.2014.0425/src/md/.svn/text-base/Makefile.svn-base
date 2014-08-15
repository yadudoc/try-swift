bin = md

CC = gcc
INCLUDES := 
OPTZ = -O0 
OPTS = -O3 -ffast-math
CFLAGS = $(OPTZ) -g -fPIC -std=gnu99 $(INCLUDES)

SRC = main.c md.c 
OBJ = $(patsubst %.c,%.o,$(SRC))



all: $(bin)

# Link stand-alone executable: 
$(bin): $(OBJ)
	$(CC) -o $(@) $(OBJ) -lm

# Swift/T integration: 

USE_SWIFT = 1
ifeq ($(USE_SWIFT),1)

TCL_HOME = $(HOME)/sfw/tcl-8.6.0
INCLUDES += -I $(TCL_HOME)/include
TCLSH = $(TCL_HOME)/bin/tclsh8.6

swift-pkg: pkgIndex.tcl

md_wrap.c: md.i md.h
	swig $(<)
	sed -i s/Md_Init/Tclmd_Init/ $(@)

libtclmd.so: md_wrap.o $(OBJ)
	gcc -shared -o $(@) $(^) -L $(TCL_HOME)/lib -l tcl8.6 

pkgIndex.tcl: libtclmd.so
	LEAF_PKG=md LEAF_VERSION=0.0 \
	LEAF_SO=$(<) LEAF_TCL=md.tcl \
	$(TCLSH) ./make-package.tcl > $(@)

clean:: 
	rm -fv pkgIndex.tcl libtclmd.so md_wrap.c

endif # Swift/T

.PHONY: clean
clean::
	rm -fv $(OBJ) $(bin)

.PHONY: install
install:
	cp $(bin) ../../bin/$(bin)

.PHONY: uninstall
uninstall:
	rm -f ../../bin/$(bin)

