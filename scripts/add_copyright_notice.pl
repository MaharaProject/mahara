#!/usr/bin/perl -w
#
# add_copyright_notice.pl
# Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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

use File::Copy;
use File::Find;
use File::Temp;

# Directories which contain files to be "licensified"
my @SRC_DIRS = ('htdocs', 'examples', 'test');

# When changing the copyright notice, one must also change the appropriate
# strings in:
#    - COPYING
#    - debian/copyright
my $copyright_text = <<END_OF_COPYRIGHT_TEXT;
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
END_OF_COPYRIGHT_TEXT

sub process_file
{
    my $filename = shift;
    print "Processing $filename... ";
    open INPUT, "$filename" or die "Cannot open $filename as input.";

    my $in_copyright = 0; # 0 = before the header
    my $copyright_holder = 'unknown';
    my $product = 'unknown';
    my @lines;
    my @after_license_lines;
    while (<INPUT>) {
        if (0 == $in_copyright and m|^/\*\*|) {
            $in_copyright = 1; # 1 = inside the header
        }
        elsif (1 == $in_copyright and m|^ \* This program is part of (.*)$|) {
            $product = $1;
        }
        elsif (2 == $in_copyright and 
               m|^ \* \@copyright.*\([cC]\)\w*(.*)Catalyst IT Ltd http://catalyst.net.nz|) {
            $copyright_holder = 'Catalyst IT';
            push @after_license_lines, $_;
        }
        elsif (1 == $in_copyright and 
               m|^ \*  Foundation, Inc\., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA|) {
            $in_copyright = 2; # 2 = after the license text
        }
        elsif (m|\*/| and (1 == $in_copyright or 2 == $in_copyright)) {
            $in_copyright = 3; # 3 = after the header
            push @lines, $copyright_text;
            push @lines, @after_license_lines;
            push @lines, " */\n";
        } 
        elsif (0 == $in_copyright or 3 == $in_copyright) {
            push @lines, $_;
        }
        elsif (2 == $in_copyright) {
            push @after_license_lines, $_;
        }
    }
    close INPUT;

    if ($in_copyright == 0) {
        # No header was found, insert copyright text at the front?
        #unshift @lines, $copyright_text;
    }

    die "$filename: Bad header." if $in_copyright == 1;

    if ($copyright_holder eq 'Catalyst IT') {
        if ($product eq 'Mahara') {
            open OUTPUT, ">$filename" or die "Cannot open $filename as output.";
            print OUTPUT foreach @lines;
            close OUTPUT;
            print "done.\n";
        } else {
            print "skipped (product is $product).\n";
        }
    } else {
        print "skipped (copyright holder is $copyright_holder).\n";
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
