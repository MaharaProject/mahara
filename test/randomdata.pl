#!/usr/bin/perl

use strict;
use warnings;
use FindBin;
use Data::Dumper;
use DBI;
use Data::RandomPerson;
use Getopt::Declare;
use Perl6::Slurp;

my $args = Getopt::Declare->new(q(
    [strict]
    -t <type>        	Select type of data (currently only 'user')	[required]
    -c <configfile>  	What config.php to use (defaults to ../htdocs/config.php)	
    -n <count>       	How many to generate (default 1)	
        { reject $count !~ /^\d+$/; }
    -p                 	Pretend (just print what _would_ happen)
    -v                 	Verbose (like pretend but print _and_ insert)
                        
));

exit unless defined $args;

$args->{-n} ||= 1;
$args->{-c} ||= qq{$FindBin::Bin/../htdocs/config.php};
my $config = read_config($args->{-c});

my $connect_string = qq{dbi:Pg:dbname=$config->{dbname}};
$connect_string .= qq{host=$config->{host}} if ( defined $config->{host} and $config->{host} =~ /\S/ );
$connect_string .= qq{port=$config->{port}} if ( defined $config->{port} and $config->{port} =~ /\S/ );
my $dbh = DBI->connect($connect_string, $config->{dbuser}, $config->{dbpass}, { RaiseError => 1 });

$config->{dbprefix} = '' unless defined $config->{dbprefix};

if ( $args->{-t} eq 'user' ) {
    # get a list of existing usernames
    my $existing_users = $dbh->selectall_hashref('SELECT ' . $config->{dbprefix} . 'username FROM usr', 'username');

    $dbh->begin_work();

    foreach ( 1 .. $args->{-n} ) {
        my $userinfo = Data::RandomPerson->new()->create();
        $userinfo->{username} = lc $userinfo->{firstname};
        $userinfo->{email} = lc $userinfo->{firstname} . '.' . lc $userinfo->{lastname} . '@dollyfish.net.nz';

        while ( exists $existing_users->{$userinfo->{username}} ) {
            $userinfo->{username} =~ s{ (\d*) \z }{($1 or 0)+1}exms;
        }

        $existing_users->{$userinfo->{username}} = 1;

        if ( $args->{-p} or $args->{-v} ) {
            print 'INSERT INTO usr (username,password,institution,firstname,lastname,email) VALUES (';
            print join(',',$userinfo->{username}, 'password', 'mahara', @{$userinfo}{qw(firstname lastname email)}), ")\n";
        }
        unless ( $args->{-p} ) {
            $dbh->do('INSERT INTO ' . $config->{dbprefix} . 'usr (username,password,institution,firstname,lastname,email) VALUES (?,?,?,?,?,?)', undef,
                $userinfo->{username},
                'password',
                'mahara',
                @{$userinfo}{qw(firstname lastname email)},
            );
        }
    }

    $dbh->commit();
}


sub read_config {
    my ($file) = @_;
    my $config = {};

    my $data = slurp($file);

    while ( $data =~ m{ \$cfg-> ( [^=\s]+ ) \s* = \s* ([^;]+) }gxms ) {
        my ($key, $value) = ($1, $2);
        $value =~ s{ \A ' ( .*? ) ' \z }{$1}xms;
        $config->{$key} = $value;
    }

    return $config;
}
