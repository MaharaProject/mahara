#!/usr/bin/perl

use strict;
use warnings;
use FindBin;
use File::Find;
use Perl6::Slurp;
use Text::Diff;

my $EXCLUDE_FILES = [
    qr{ \A examples/                        }xms,
    qr{ \A htdocs/tests                     }xms,
    qr{ \A htdocs/lib/adodb                 }xms,
    qr{ \A htdocs/lib/phpmailer             }xms,
    qr{ \A htdocs/lib/xmldb                 }xms,
    qr{ \A htdocs/lib/smarty                }xms,
    qr{ \A htdocs/lib/ddl.php               }xms,
    qr{ \A htdocs/lib/dml.php               }xms,
    qr{ \A htdocs/lib/xmlize.php            }xms,
    qr{ \A htdocs/lib/kses.php              }xms,
    qr{ \A htdocs/lib/validateurlsyntax.php }xms,
];

my $FILE_HEADER = <<EOF;
<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * \@package    mahara
EOF

my $projectroot = qq{$FindBin::Bin/../};
my $language_strings = {};

find( \&readlang, $projectroot . 'htdocs/lang/en.utf8' );
find( \&process, $projectroot );

# loads language strings
sub readlang {
    my $filename = $_;
    my $directory = $File::Find::dir;

    return unless $filename =~ m{ \A (.*)\.php \z }xms;

    my $section = $1;

    my $file_data = slurp $directory . '/' . $filename;

    while ( $file_data =~ m{ \$string\['(.*?)'\] \s+ = \s+ }xmsg ) {
        $language_strings->{$section}{$1} = 1;
    }
}

sub process {
    my $filename = $_;
    my $directory = $File::Find::dir;
    $directory =~ s{ \A $projectroot }{}xms;
    $directory =~ s{ ([^/])$ }{$1/}xms;

    return unless $filename =~ m{ \.php \z }xms;

    foreach my $exclude_file ( @{$EXCLUDE_FILES} ) {
        return if ( ( $directory . $filename ) =~ $exclude_file );
    }

    my $file_data = slurp $projectroot . $directory . $filename;

    # check header
    if ( $FILE_HEADER ne substr ($file_data, 0, length $FILE_HEADER) ) {
        my $header = substr ($file_data, 0, length $FILE_HEADER);
        print $directory, $filename, " failed header check\n";
        print diff \$header, \$FILE_HEADER;
    }

    # check footer
    if ( $file_data !~ m{ \? > \n \z }xms ) {
        print $directory, $filename, " failed footer check\n";
    }

    # check subpackage
    if ( $file_data =~ m{ \@subpackage (.*?) $ }xms ) {
        my $subpackage_data = $1;
        unless (
            $subpackage_data =~ m{ \A \s* ( core | lang | tests | admin | ( auth | form | artefact | notification | search )(?:-.+)? ) \s* \z }xms
        ) {
            print $directory, $filename, " invalid \@subpackage '$subpackage_data'\n";
        }
    }
    else {
        print $directory, $filename, " missing \@author\n";
    }

    # check author
    if ( $file_data =~ m{ \@author (.*?) $ }xms ) {
        my $author_data = $1;
        unless (
            $author_data =~ m{ \s* Martyn \s Smith \s <martyn\@catalyst\.net\.nz> \s* }xms
            or $author_data =~ m{ \s* Penny \s Leach \s <penny\@catalyst\.net\.nz> \s* }xms
            or $author_data =~ m{ \s* Nigel \s McNie \s <nigel\@catalyst\.net\.nz> \s* }xms
            or $author_data =~ m{ \s* Richard \s Mansfield \s <richard\.mansfield\@catalyst\.net\.nz> \s* }xms
        ) {
            print $directory, $filename, " invalid \@author '$author_data'\n";
        }
    }
    else {
        print $directory, $filename, " missing \@author\n";
    }

    # check copyright
    if ( $file_data !~ m{\@copyright  \(C\) 2006,2007 Catalyst IT Ltd http://catalyst\.net\.nz} ) {
        print $directory, $filename, " missing \@copyright (or invalid)\n";
    }

    # check language strings
    while ( $file_data =~ m{ get_string\( (?: ['"](.*?)['"] \s* , )? \s* ['"](.*?)['"] \) }xmg ) {
        my ( $section, $tag ) = ( $1, $2 );

        $section ||= 'mahara';

        unless ( exists $language_strings->{$section}{$tag} ) {
            print $directory, $filename, " has call to get_string that doesn't exist: get_string('$section', '$tag')\n";
        }
    }
}

print "\n";

