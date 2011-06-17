# Moves statements after a opening brace
# to next line
# original
# if (...) { stmt...
# fixed
# if (...) {
#     stmt...
# Note:
# If the braces are empty (ie. { })
# then ignore
# Note:
# If the pattern is found after a
# single line comment //, then skip
# Note:
# If the brace only contains [0-9]*
# then it is most likely part of a
# regular expression, skip

/)\s*{\([^}].*\)$/{
    /\/\/.*)\s*{\([^}].*\)/{p;d}
    /)\s*{[0-9]*}/{p;d}

# \1 = index of original line
# \2 = statement up to and including '{'
# \4 = comment (incl. whitespace) or '\s*}' directly after '{'
# \6 = code after '{'

    s~^\(\s*\)\([^{]*{\)\(\(\s*\(//\|}\).*\)\|\s*\)\(.*\)$~\1\2\4\n\1    \6~
# If there was no stmt, only a comment
# then remove the extra blank line
    s/\n\s*$//
}
