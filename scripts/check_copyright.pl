#!/usr/bin/perl -w
#
# check_copyright.pl
# Copyright (C) 2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# This file incorporates work covered by the following copyright and
# permission notice:
#  
#   Copyright (C) 2006 Serious Hack Inc. (http://www.serioushack.com)
#
#   This library is free software; you can redistribute it and/or
#   modify it under the terms of the GNU Lesser General Public
#   License as published by the Free Software Foundation; either
#   version 2.1 of the License, or (at your option) any later version.
#
#   This library is distributed in the hope that it will be useful,
#   but WITHOUT ANY WARRANTY; without even the implied warranty of
#   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
#   Lesser General Public License for more details.
#
#   You should have received a copy of the GNU Lesser General Public
#   License along with this library; if not, write to the Free
#   Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
#   Boston, MA 02110-1301, USA
#

use strict;

use Cwd;
use File::Copy;
use File::Find;
use File::Temp;

# Directories which contain licensed files
my @SRC_DIRS = ('htdocs', 'examples', 'test');

my %detected;

sub process_file
{
    my $filename = shift;
    open INPUT, "$filename" or die "Cannot open $filename as input.";

    my $in_copyright = 0; # 0 = before the header
    my @copyright_holders;
    my $license = '';
    my $license_version = 0;
    my $license_upgradable = 0;
    my $check_carefully = 0; # Could indicate proprietary code
    while (<INPUT>) {
        s/\r//g;
        if ('' eq $license) {
            # We only look at the first license statement
            if (m/under (the terms of )?the GNU (General Public|GPL)/) {
                $license = 'GPL';
            }
            elsif (m/under (the terms of )?the GNU (Lesser General|LGPL|Lesser GPL)/) {
                $license = 'LGPL';
            }
            elsif (m|subject to version ([\d.]+) of the PHP license|) {
                $license = 'PHP';
                $license_version = $1;
            }
            elsif (m|\@license\s+(http://[^\s]+)?(.*?)$|) {
                if ($1 and !$2) {
                    $license = $1;
                } else {
                    $license = $2;
                }
            }
            elsif (m|see\s+<?([^\s]+?)>?\s+.*for.*license|i) {
                $license = $1;
            }
            elsif (m/released under (the|a)?(.+?) Licen[cs]e/i) {
                my $s = $2;
                $s =~ s/both //gi;
                $s =~ s/license and/and/gi;
                $s =~ s/GNU //gi;
                $s =~ s/Lesser GPL library/LGPL/gi;

                # Trim the string
                $s =~ s/^\s+//g;
                $s =~ s/\s+$//g;

                $license = $s;
            }
            elsif (m/Licen[cs]e:\s+(GPL|LGPL|BSD)/i) {
                $license = uc($1);
            }
        }

        if ($license eq 'GPL' or $license eq 'LGPL') {
            if (0 == $license_version and m|version ([\d.]+) of the License|) {
                $license_version = $1;
            }
            if ($license_version and m|any later version|) {
                $license_upgradable = 1;
            }
        }
        
        my $potential_holder = '';
        if (m/(copyright(\s+\(c\))?|\(c\))\s+([\d\-,; ]+\s+)?(.*?)$/i) {
            $potential_holder = $4;
        }
        elsif (m|authors?:?\s+([^,;]+)|i) {
            $potential_holder = $1;
        }

        if ($potential_holder) {
            my $s = $potential_holder;

            # Ignore contributors
            if ($s =~ m/Contributions from/i or $s =~ m/credits? to/i) {
                $s = '';
            }

            # Ignore specific sentences
            $s = '' if $s =~ m/(notice|legislation|statement|field for)/i;
            $s = '' if $s =~ m/based on work by/i;

            # That's a bad sign... Should double-check these ones
            if ($s =~ m/All rights reserved/i) {
                $check_carefully = 1;
            }
            $s =~ s/All rights reserved//gi;
            $s =~ s/All rights//gi;

            # Useless words
            $s =~ s/the authors//gi;
            $s =~ s/and others//gi;
            $s =~ s/author://gi;
            $s =~ s/copyright//gi;
            $s =~ s/\xA9//gi;
            $s =~ s/&copy;//gi;
            $s =~ s/((199\d|20\d\d)[,\-]?)+//gi;
            $s =~ s/onwards//gi;
            $s =~ s/portions from//gi;
            $s =~ s/additional modifications//gi;
            $s =~ s/(and )?released (under)?.*$//gi;
            $s =~ s/don't translate.*$//g;
            $s =~ s/USER->get//g;
            $s =~ s/userid//g;
            $s =~ s/names in.*$//g;
            $s =~ s/data (for|at).*$//g;
            
            # Remove emails and URLs
            $s =~ s/<[^>]+>//gi;
            $s =~ s/\([^)]+\)//gi;
            $s =~ s|http://[^\s]+||gi;

            # Remove useless whitespace and punctuation
            $s =~ s|[\(\),:;=\|\$+"]||g;
            $s =~ s|\\n||g;
            $s =~ s|//$||g;
            $s =~ s|[{}]||g;
            $s =~ s|\s{2,}| |g;
            $s =~ s|\s[.'/]||gi;
            $s =~ s|^[/']||gi;
            $s =~ s|['/]\s||gi;
            $s =~ s|['/]$||gi;
            $s =~ s|\s-\s||gi;
            $s =~ s|\s{2,}| |g;
            $s =~ s|^\s+||g;
            $s =~ s|\s+$||g;

            if ($s) {
                push @copyright_holders, $s;
            }
        }
    }
    close INPUT;

    # Fill-in which license these files/URL refer to
    $license = 'MIT or Academic Free License 2.1' if $license eq 'http://mochikit.com/';
    $license = 'MIT' if $license eq 'scriptaculous.js';
    $license = 'BSD' if $license eq 'http://www.opensource.org/licenses/bsd-license.php';

    $license .= $license_version if $license_version != 0;
    $license .= '+' if $license_upgradable;

    if ($check_carefully and !$license) {
        # These ones need to be considered carefully since the default
        # is full copyright, not public domain
        $license = 'proprietary?';
    }

    my %saw;
    my @unique_copyright_holders = grep(!$saw{$_}++, @copyright_holders);

    if (@unique_copyright_holders > 0) {
        if ($license) {
            print &Cwd::cwd()."/$filename: $license, ".join(', ', @unique_copyright_holders)."\n";
        } else {
            # This is fine, the overall project license applies.
            #print STDERR &Cwd::cwd()."/$filename: copyright holders, but no license information\n";
        }
    } elsif ($license) {
        print &Cwd::cwd()."/$filename: $license, unknown author(s)\n";
    } else {
        # This is not a problem in itself, the overall project license applies.
        #print STDERR &Cwd::cwd()."/$filename\n";#": no license information or copyright holders\n";
    }
}

sub process_standard_files
{
    # Find all php and javascript files in the standard directories
    File::Find::find sub { /^.*\.(php|js)$/s && process_file($_) }, @SRC_DIRS;
}

sub main
{
    if (@ARGV > 0) {
        while (@ARGV > 0) {
            process_file(shift @ARGV);
        }
    } else {
        &process_standard_files;
    }
}

&main;
