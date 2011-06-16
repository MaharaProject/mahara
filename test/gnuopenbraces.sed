# Fixes open braces at start of line
# Moves them to end of line above
# original:
# (if|while|...) (...)
# { ...
# fixed:
# (if|while|...) (...) {
#     ...
# Note:
# If there is nothing after the {
# a line will be deleted, ie no
# blank line
# Note:
# This requires that the line before
# ends with a ) followed by an optional
# comment //
# Doesn't work with /* comments
# the example below fails (or stays same)
# if (condition) /* blah */
# {
# Note:
# If the open brace is after a single
# line comment, skip


N
/\n\s*{.*$/{
    /\s.*\/\/.*{/{p;d}

# \1 = if|while|... (...)
# \2 = comment after if|while|... (...) (if any)
# \4 = indent of if|while|... (...)
# \5 = code after brace

    s~\(.*)\)\(\s*\(//.*\)\?\)\n\(\s*\){\s*\(.*\)$~\1 {\2\n\4    \5~
# If there was no statment after the {
# on the next line, then remove it
    s/\n\s*$//
}
P;D
