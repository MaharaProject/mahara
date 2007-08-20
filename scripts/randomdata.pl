#!/usr/bin/perl

use strict;
use warnings;

use FindBin;
use lib qq{$FindBin::Bin/lib/};

use Carp;
$Carp::Verbose = 1;
use Data::Dumper;
use Getopt::Declare;
use Mahara::Config;
use Mahara::RandomData;

my $types = [qw(user activity community artefact view watchlist template file folder image blog blogpost)];

my $args = Getopt::Declare->new(q(
    [strict]
    -t <type>        	Select type of data ) . '(' . join(', ', @{$types}) . ')' . q(  [required]
    -u <user>        	User to create data for (required for type 'activity')
    -ua             	Create data for all users (can be used instead of -u)
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

unless ( grep { $args->{-t} eq $_ } @{$types} ) {
    croak q{Type must be one of } . join(', ', map { qq{'$_'} } @{$types} );
}

if ( $args->{-t} eq 'user' ) {
    $randomdata->insert_random_users($args->{-n});
}

if ( $args->{-t} eq 'activity' ) {
    unless ( defined $args->{-u} or defined $args->{-ua} ) {
        croak 'Need to specify a user with -u or -ua';
    }
    if ( defined $args->{-u} ) {
        $randomdata->insert_random_activity($args->{-u}, $args->{-n});
    }
    else {
        $randomdata->insert_random_activity_all_users($args->{-n});
    }
}

if ( $args->{-t} eq 'community' ) {
    unless ( defined $args->{-u} or defined $args->{-ua} ) {
        croak 'Need to specify a user with -u or -ua';
    }
    if ( defined $args->{-u} ) {
        $randomdata->insert_random_communities($args->{-u}, $args->{-n});
    }
    else {
        $randomdata->insert_random_communities_all_users($args->{-n});
    }
}

if ( $args->{-t} eq 'artefact' ) {
    unless ( defined $args->{-u} or defined $args->{-ua} ) {
        croak 'Need to specify a user with -u or -ua';
    }
    if ( defined $args->{-u} ) {
        $randomdata->insert_random_artefacts($args->{-u}, $args->{-n});
    }
    else {
        $randomdata->insert_random_artefacts_all_users($args->{-n});
    }
}

if ( $args->{-t} eq 'view' ) {
    unless ( defined $args->{-u} or defined $args->{-ua} ) {
        croak 'Need to specify a user with -u or -ua';
    }
    if ( defined $args->{-u} ) {
        $randomdata->insert_random_views($args->{-u}, $args->{-n});
    }
    else {
        $randomdata->insert_random_views_all_users($args->{-n});
    }
}

if ( $args->{-t} eq 'watchlist' ) {
    unless ( defined $args->{-u} or defined $args->{-ua} ) {
        croak 'Need to specify a user with -u or -ua';
    }
    if ( defined $args->{-u} ) {
        $randomdata->insert_random_watchlist($args->{-u}, $args->{-n});
    }
    else {
        $randomdata->insert_random_watchlist_all_users($args->{-n});
    }
}

if ( $args->{-t} eq 'template' ) {
    unless ( defined $args->{-u} or defined $args->{-ua} ) {
        croak 'Need to specify a user with -u or -ua';
    }
    if ( defined $args->{-u} ) {
        $randomdata->insert_random_template($args->{-u}, $args->{-n});
    }
    else {
        $randomdata->insert_random_template_all_users($args->{-n});
    }
}

if ( $args->{-t} eq 'file' or $args->{-t} eq 'folder' or $args->{-t} eq 'image' ) {
    unless ( defined $args->{-u} or defined $args->{-ua} ) {
        croak 'Need to specify a user with -u or -ua';
    }
    if ( defined $args->{-u} ) {
        $randomdata->insert_random_filethings($args->{-u}, $args->{-n}, $args->{-t});
    }
    else {
        $randomdata->insert_random_filethings_all_users($args->{-n}, $args->{-t});
    }
}

if ( $args->{-t} eq 'blog' ) {
    unless ( defined $args->{-u} or defined $args->{-ua} ) {
        croak 'Need to specify a user with -u or -ua';
    }
    if ( defined $args->{-u} ) {
        $randomdata->insert_random_blogs($args->{-u}, $args->{-n}, $args->{-t});
    }
    else {
        $randomdata->insert_random_blogs_all_users($args->{-n}, $args->{-t});
    }
}

if ( $args->{-t} eq 'blogpost' ) {
    unless ( defined $args->{-u} or defined $args->{-ua} ) {
        croak 'Need to specify a user with -u or -ua';
    }
    if ( defined $args->{-u} ) {
        $randomdata->insert_random_blogposts($args->{-u}, $args->{-n}, $args->{-t});
    }
    else {
        $randomdata->insert_random_blogposts_all_users($args->{-n}, $args->{-t});
    }
}
