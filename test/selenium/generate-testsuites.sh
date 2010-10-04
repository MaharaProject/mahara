#!/bin/bash

SHARED='shared'
INDEX=index.html
PROCEDURE='test_suite'
MAINSUITE="TestSuite.html"
FRONT_MATTER='<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
        <title>Mahara Test Suite</title>
    </head>
    <body>
        <table cellpadding="1" cellspacing="1" border="1">
            <tbody>
                <tr><td><strong>Mahara Test Suite</strong></td></tr>'
BACK_MATTER='            </tbody>
        </table>
    </body>
</html>'
START='<tr><td><<a href="'

IGNOREDIRS="./shared ./server ./basic-install"

function generate_index
{
  root="$1"

  # Only process folders with a PROCEDURE file
  if [ ! -f "$root"/"$PROCEDURE" ]
  then
    echo  "Directory [$root] contains no test procedure file [$PROCEDURE]"
    return
  fi

  thisindex="$root"/"$INDEX"
  rm -f "$thisindex"
  touch "$thisindex"

  # Add the headers
  echo "$FRONT_MATTER" > "$thisindex"

  # Process the PROCEDURE line by line
  OLDIFS=$IFS
  IFS=$'\n'
  for line in `cat "$root"/"$PROCEDURE"`
  do
    # Ignore comments
    if [[ "$line" =~ ^#.* ]]
    then
      continue
    fi

    # Try and find files specified in the PROCEDURE
    files=`find "$root" -type f -name "$line.html" | head -1`
    if [ ! -z "$files" ]
    then
      printf "                <tr><td><a href='../%s'>%s</a></td></tr>\n" "$files" "$line" >> "$thisindex"
      printf "                <tr><td><a href='%s'>%s</a></td></tr>\n" "$files" "$line" >> "$MAINSUITE"
      continue
    fi

    # If we reach here, then we haven't found the test in the root. Try
    # shared
    files=`find "$SHARED" -type f -name "$line.html" | head -1`
    if [ ! -z "$files" ]
    then
      printf "                <tr><td><a href='../%s'>%s</a></td></tr>\n" "$files" "$line" >> "$thisindex"
      printf "                <tr><td><a href='./%s'>%s</a></td></tr>\n" "$files" "$line" >> "$MAINSUITE"
      continue
    fi

    # This test doesn't exist
    echo  "Nonexistant test [$line]"
  done
  IFS=$OLDIFS
  echo "$BACK_MATTER" >> "$thisindex"
}

echo "$FRONT_MATTER" > "$MAINSUITE"
# Generate the basic-install first
generate_index ./basic-install

# Generate the rest of the folder's test suites
for directory in `find . -mindepth 1 -maxdepth 1 -type d`
do
  if [[ "$IGNOREDIRS" =~ .*$directory.* ]]
  then
    continue
  fi
  generate_index "$directory"
done
echo "$BACK_MATTER" >> "$MAINSUITE"
