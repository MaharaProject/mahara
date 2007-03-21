#!/usr/bin/perl

use strict;
use warnings;

use FindBin;
use lib qq{$FindBin::Bin/lib/};

use Mahara::RandomData;
use Mahara::Config;

my $config = Mahara::Config->new(qq{$FindBin::Bin/../htdocs/config.php});


print $config->get('dbhost'), "\n";
