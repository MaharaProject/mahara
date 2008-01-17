#!/usr/bin/perl -w

use strict;

use File::Find;
use File::Slurp qw/slurp/;

my $reverse = $ARGV[0] eq '--reverse';

sub process_file
{
    my $filename = shift;
    my $directory = $File::Find::dir;

    my $oldfiledata = slurp $filename;

    my $filedata = $oldfiledata;
    if ($reverse) {
        # Turn the paths back into absolute ones.
        $filedata =~ s|(<td>open</td>\s+<td>)([^/])|$1/$2|gs;
    } else {
        $filedata =~ s|(<td>open</td>\s+<td>)/|$1|gs;
    }

    if ($filedata ne $oldfiledata) {
        print "editing $directory/$filename\n";
        open OUTPUT, ">$filename" or die "Cannot open $filename as output.";
        print OUTPUT $filedata;
        close OUTPUT;
    }

}

File::Find::find sub { /^.*\.(html)$/s && process_file($_) }, '.';
