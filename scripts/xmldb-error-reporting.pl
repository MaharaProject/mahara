#!/usr/bin/perl
#
# Takes an XMLDB directory and adds a hook for error reporting wherever error
# messages are defined.
#
# This will change your XMLDB directory and will not make a backup, so make
# one yourself before running this
#
# Author: Nigel McNie <nigel@catalyst.net.nz>
#
use warnings;
use strict;

use Getopt::Long qw(:config permute);

sub show_help; sub show_version;

my ($help, $version);

GetOptions(
    'help'    => \$help,
    'version' => \$version
);

show_help if $help;
show_version if $version;

my $directory = $ARGV[0];

die($directory . ' is not a directory') unless -d $directory;
die($directory . ' does not look like an XMLDB directory (no xmldb.dtd)') unless -e "$directory/xmldb.dtd";


# Add xmldb_dbg lines to each file that needs them
my @files = ('XMLDBField.class.php', 'XMLDBFile.class.php', 'XMLDBIndex.class.php', 'XMLDBKey.class.php',
    'XMLDBObject.class.php', 'XMLDBStatement.class.php', 'XMLDBStructure.class.php', 'XMLDBTable.class.php');

for my $file (@files) {
    print "processing $file...\n";
    open(FH, "$directory/classes/$file");
    my @lines = <FH>;
    my @newlines = ();

    for my $line (@lines) {
        if ($line =~ m/(.*)\$this->errormsg = '/) {
            push @newlines, $line;
            push @newlines, "$1xmldb_dbg(\$this->errormsg);\n";
        }
        else {
            push @newlines, $line;
        }
    }

    close(FH);
    open(FH, ">$directory/classes/$file");
    for my $line (@newlines) {
        print FH $line;
    }
    close(FH);
}


print <<EOF;
Done!
Now you must define the xmldb_dbg function somewhere such that it will be
available whenever an error occurs. A simple version might be:

function xmldb_dbg(\$message) {
    error_log(\$message);
}
EOF


#
# Subroutines
#

sub show_help {
    print <<EOF;
Takes an XMLDB directory and adds a hook for error reporting wherever error
messages are defined.

Usage: $0 /path/to/XMLDB/directory
EOF
    exit 0;
}

sub show_version {
    print "0.1.0\n";
    exit 0;
}

