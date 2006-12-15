#!/usr/bin/perl
#
# Bootstraps the mahara system, filling out all the relevant forms
#
# Author: Nigel McNie <nigel@catalyst.net.nz>
#
# TODO: Remove -u, just have the groups created for the randomly inserted users
#
use strict;
use warnings;

use FindBin;
use lib qq{$FindBin::Bin/lib/};

use Carp;
use Data::Dumper;
use Getopt::Declare;
use Mahara::Config;
use Mahara::RandomData;
use JSON;
use WWW::Mechanize;

my $args = Getopt::Declare->new(q(
    [strict]
    -c <config>     	The config file to use for the installation (defaults to 'config')
    -nu <ucount>    	The number of random users to create (default 0)	
        { reject $ucount !~ /^\d+$/; }
    -ng <gcount>     	The number of random groups to create	
        { reject $gcount !~ /^\d+$/; }
    -u <user>        	User to create data for (required for type 'group')
    -mc <configfile>  	What mahara config.php to use (defaults to ../htdocs/config.php)	
    -v                 	Verbose (say what's going on at each step)
                        
));

exit unless defined $args;

$args->{-nu} ||= 0;
$args->{-ng} ||= 0;
croak 'You must insert some users so groups can have members (specify with -nu)' if (!$args->{-nu} and $args->{-ng});
croak 'You must specify a user to create the groups for with -u' if (!defined $args->{-u} and $args->{-ng} > 0);

my $randomdata;
my $json_response;

if ($args->{-nu} or $args->{-ng}) {
    $args->{-mc} ||= qq{$FindBin::Bin/../htdocs/config.php};
    my $config = Mahara::Config->new($args->{-mc});
    $randomdata = Mahara::RandomData->new($config);
    $randomdata->verbose($args->{-v});
}

# Read in configuration
my $configfile = $args->{-c} || 'config';
my $CFG = do $configfile or croak 'Could not read configuration file';
croak 'no URL set by your configuration file' unless ($CFG->{url});
$CFG->{url} .= '/' unless $CFG->{url} =~ m{/$};

# Go!
my $m = WWW::Mechanize->new( autocheck => 1 );

# Check for a mahara install page, and run it if we find it
$m->get($CFG->{url});
croak 'This doesn\'t look like a mahara install page!' unless $m->content =~ m{admin/upgrade\.php">Agree</a>};

# Agree to license
debug("Agreeing to license...");
$m->follow_link( text_regex => qr/Agree/ );

# At this point, need to parse page to get scripts to hit to install stuff
$_ = $m->content;
my $components = /var todo = \[(".*",?)+\]/s;
my @things = split(/","/, substr($1, 1, -1));
for my $thing (@things) {
    debug("Installing $thing...");
    $m->get($CFG->{url} . 'admin/upgrade.json.php?name=' . $thing);
    $json_response = my_jsonToObj($m->content());
    if ( $json_response->{error} ) {
        croak qq{Failed to install $thing} . Dumper($json_response);
    }
    if ( defined $json_response->{message} ) {
        print 'MESSAGE:', $json_response->{message};
    }
}

# Request the core data page
debug("Installing core data...");
$m->get($CFG->{url} . 'admin/upgrade.json.php?install=1');
$json_response = my_jsonToObj($m->content());
if ( $json_response->{error} ) {
    croak qq{Failed to install core data:} . Dumper($json_response);
}
if ( defined $json_response->{message} ) {
    print 'MESSAGE:', $json_response->{message};
}

# Install done, now Log in
debug("Logging in...");
$m->get($CFG->{url} . 'admin/');
$m->post($CFG->{url} . 'admin/',
    { login_username => 'admin', login_password => 'mahara', 'pieform_login' => ''  }
);

# Change password
debug("Changing admin password...");
$m->submit_form(
    form_name => 'change_password',
    fields => { password1 => $CFG->{password}, password2 => $CFG->{password} }
);

if ($m->content =~ /Your new password has been saved/) {
    if ($args->{-nu}) {
        debug('Inserting ' . $args->{-nu} . ' random users...');
        $randomdata->insert_random_users($args->{-nu});
    }
    if ($args->{-ng}) {
        debug('Inserting ' . $args->{-ng} . ' random groups...');
        $randomdata->insert_random_groups($args->{-u}, $args->{-ng});
    }
    print "Done!\n";
}
else {
    warn "Err... I didn't detect that the password has been saved, maybe something go boom?\n";
}

sub debug {
    print shift() . "\n" if $CFG->{debug};
}

sub my_jsonToObj {
    my $data = shift;

    my $obj = eval { jsonToObj($data); };

    unless ( defined $obj ) {
        $data =~ s{ < [^>]* > }{}xmgs;
        $data =~ s{ \r?\n\s*\r?\n\s*\r?\n }{\n\n}xmgs;
        $data =~ s{ &quot; }{"}xmgs;
        croak q{Failed to parse JSON data: } . $data;
    }

    return $obj;
}
