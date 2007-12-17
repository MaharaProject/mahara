#!/bin/bash

NEWEST_FILE="`ls -1 -t htdocs/js/MochiKit/*.js | head -n1`"
PACKED_FILE="htdocs/js/MochiKit/Packed.js"

if [ ! -e ${PACKED_FILE} ] || [ ${PACKED_FILE} -ot ${NEWEST_FILE} ]; then
    echo "Packing MochiKit ..."
    scripts/pack.py Base Async DOM Style Color Signal Iter Logging Position Visual DragAndDrop Format > ${PACKED_FILE}
else
    echo "MochiKit packed version already up to date"
fi
