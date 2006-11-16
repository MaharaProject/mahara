#!/usr/bin/perl

use strict;
use warnings;

use FindBin;
use lib qq{$FindBin::Bin/lib/};

use Carp;
use Data::Dumper;
use Getopt::Declare;
use Mahara::Config;
use Mahara::RandomData;

my $args = Getopt::Declare->new(q(
    [strict]
    -t <type>        	Select type of data (user or group)	[required]
    -u <user>        	User to create data for (required for type 'group')
    -c <configfile>  	What config.php to use (defaults to ../htdocs/config.php)	
    -n <count>       	How many to generate (default 1)	
        { reject $count !~ /^\d+$/; }
    -p                 	Pretend (just print what _would_ happen)
    -v                 	Verbose (like pretend but print _and_ insert)
                        
));

exit unless defined $args;

$args->{-n} ||= 1;
$args->{-c} ||= qq{$FindBin::Bin/../htdocs/config.php};
my $config = Mahara::Config->new($args->{-c});
my $randomdata = Mahara::RandomData->new($config);
$randomdata->verbose($args->{-v});
$randomdata->pretend($args->{-p});

if ( $args->{-t} eq 'user' ) {
    $randomdata->insert_random_users($args->{-n});
}

if ( $args->{-t} eq 'group' ) {
    unless ( defined $args->{-u} ) {
        croak 'Need to specify a user with -u';
    }
    $randomdata->insert_random_groups($args->{-u}, $args->{-n});
}

