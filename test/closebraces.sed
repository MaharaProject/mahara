# Fixes closing brace at end of line
# Moves it to the following line by itself
# original:
#     stmt } ...
# fixed:
#     stmt
# }
# ...
# Note:
# If there is nothing after the }
# there will be no extra line after
# the new }, ie. no blank line
# Note:
# This assumes there is proper
# indenting of four (4) spaces to
# be able to get proper align
# Note:
# If ... is comment, then it is
# kept on the same line as the }
# Note:
# stmt is optional, if not present,
# the alignment is still correct
# Note:
# If the input contains {...} that is
# NOT preceeded by a ) then skip
# Note:
# If the braces are empty (ie. { })
# then ignore
# Note:
# If the } preceeds any of }; }); }, or }),
# then keep that on same line as }
# Note:
# If the } is after a single line comment //
# then skip
# NOte:
# If the braces only contains [0-9]*
# then it is most likely just a
# regular expression, skip

/}/{
    /[^)]\s*{[^}]*}/{p;d}
    /{\s*}/{p;d}
    /\/\/.*}/{p;d}
    /{[0-9]}/{p;d}

# \1 = indent for closing block
# \2 = extra indent + code for 1st line (4 spaces)
# \3 = allowed token (incl. whitespace) after '}' if one exists
# \3 = allowed token is any of }; }); }, }),
# \4 = comment (incl. whitespace) after '}\s*;?' if one exists
# \6 = code after '}\s*;?' (stripped of whitespace)

    s~\(\s*\)\(    \S.*\S\)\?\s*}\(\s*)\?\s*[;,]\)\?\(\s*\(//.*\)\)\?\s*\(.*\)~\1\2\n\1}\3\4\n\1\6~
# If there was no statment before/after the }
# on the same line, then remove it the
# extra line(s)
    s/^\s*\n//
    s/\n\s*$//
}
