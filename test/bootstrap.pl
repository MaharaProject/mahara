#!/usr/bin/perl
#
# Bootstraps the mahara system, filling out all the relevant forms
#
use warnings;
use strict;
use WWW::Mechanize;

# Read in configuration
my $configfile = shift || 'config';
my $CFG = do $configfile or die('could not read configuration file');
die('no URL set by your configuration file') unless ($CFG->{url});
$CFG->{url} .= '/' unless $CFG->{url} =~ m{/$};

# Go!
my $m = WWW::Mechanize->new( autocheck => 1 );

# Check for a mahara install page, and run it if we find it
$m->get($CFG->{url});
die('This doesn\'t look like a mahara install page!') unless $m->content =~ m{admin/upgrade\.php">Agree</a>};

# Agree to license
debug("Agreeing to license...");
$m->follow_link( text_regex => qr/Agree/ );

# At this point, need to parse page to get scripts to hit to install stuff
$_ = $m->content;
my $components = /var todo = \[(".*",?)+\]/s;
my @things = split(/","/, substr($1, 1, -1));
for my $thing (@things) {
    debug("Installing $thing...");
    # @todo check for errors
    $m->get($CFG->{url} . 'admin/upgrade.json.php?name=' . $thing);
}

# Request the core data page
debug("Installing core data...");
$m->get($CFG->{url} . 'admin/upgrade.json.php?install=1');

# Install done, move on to log in
debug("Going to login page...");
$m->get ($CFG->{url} . 'admin/');

# Log in
debug("Logging in...");
$m->submit_form(
    form_name => 'login',
    fields => {
        login_username => 'root',
        login_password => 'mahara',
    }
);

# Change password
debug("Changing root password...");
$m->submit_form(
    form_name => 'change_password',
    fields => {
        password1 => $CFG->{password},
        password2 => $CFG->{password}
    }
);

if ($m->content =~ /Your new password has been saved/) {
    print "Done!\n";
}
else {
    warn "Err... I didn't detect that the password has been saved, maybe something go boom?\n";
}

sub debug {
    print shift() . "\n" if $CFG->{debug};
}
