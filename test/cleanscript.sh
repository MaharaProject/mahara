#!/bin/sh

BASEDIR=$(dirname $0)

if test -z "$1"; then
    # Test that PWD is above BASEDIR/..
    set -- "${PWD}"
fi



CODEFILES="php\|js\|css"
IMAGEFILES="png\|gif\|jpe?g"

for DIR in $*; do

    find "${DIR}" -type f -regex ".*\.\(${IMAGEFILES}\)" | xargs -r chmod ugo-x

    # Replace indented TAB's with four (4) spaces
    find "${DIR}" -type f -regex ".*\.\(${CODEFILES}\)" | xargs -r sed -i -e "s/\t/    /g"

    # Remove trailing whitespace
    find "${DIR}" -type f -regex ".*\.\(${CODEFILES}\)" | xargs -r sed -i -e "s/\s*$//"

    # Remove windows/mac file endings
    find "${DIR}" -type f -regex ".*\.\(${CODEFILES}\)" | xargs -r dos2unix

    # Move opening braces at start of line to line previous
    find "${DIR}" -type f -regex ".*\.\(${CODEFILES}\)" | xargs -r sed -i -f "${BASEDIR}/gnuopenbraces.sed"

    # Move statements on the same line as an opening brace to the next line
    # Won't alter empty braces (ie { })
    # Will ignore SQL code with {name}
    find "${DIR}" -type f -regex ".*\.\(${CODEFILES}\)" | xargs -r sed -i -f "${BASEDIR}/inlineopenbraces.sed"

    # Make sure closing braces are on a line by themselves (with an optional comment)
    # Won't alter empty braces (ie { })
    # Will also deal with cuddled elses
    # Will ignore SQL code with {name}
    find "${DIR}" -type f -regex ".*\.\(${CODEFILES}\)" | xargs -r sed -i -f "${BASEDIR}/closebraces.sed"

    # Change elseif to else if
    find "${DIR}" -type f -regex ".*\.\(${CODEFILES}\)" | xargs -r sed -i -e "s/elseif/else if/"

    # Make sure there is a space between condition and looping statements and the bracket
    # Make sure there is only one...
    find "${DIR}" -type f -regex ".*\.\(${CODEFILES}\)" | xargs -r sed -i -e "s/\(if\|while\|for\) *(/\1 (/"

    # Make sure there is a opening brace after a conditional or looping condition
    # Have support for multiline conditions
    # Would need to have an awk script to match the brackets of the condition
    #find "${DIR}" -type f -regex ".*\.\(${CODEFILES}\)" | xargs -r awk ...
done
