#!/usr/bin/perl

use strict;
use warnings;
use Carp;
use FindBin;
use Data::Dumper;
use DBI;
use Data::RandomPerson;
use Data::Random::WordList;
use Getopt::Declare;
use Perl6::Slurp;
#use Smart::Comments;

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
my $config = read_config($args->{-c});

my $connect_string = qq{dbi:Pg:dbname=$config->{dbname}};
$connect_string .= qq{host=$config->{host}} if ( defined $config->{host} and $config->{host} =~ /\S/ );
$connect_string .= qq{port=$config->{port}} if ( defined $config->{port} and $config->{port} =~ /\S/ );
my $dbh = DBI->connect($connect_string, $config->{dbuser}, $config->{dbpass}, { RaiseError => 1 });

$config->{dbprefix} = '' unless defined $config->{dbprefix};

if ( $args->{-t} eq 'user' ) {
    # get a list of existing usernames
    my $existing_users = $dbh->selectall_hashref('SELECT id, username FROM ' . $config->{dbprefix} . 'usr', 'username');

    $dbh->begin_work();

    foreach ( 1 .. $args->{-n} ) { ### [...  ] (%)
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

if ( $args->{-t} eq 'group' ) {
    unless ( defined $args->{-u} ) {
        croak 'Need to specify a user with -u';
    }

    my $existing_users = $dbh->selectall_hashref('SELECT id, username FROM ' . $config->{dbprefix} . 'usr', 'username');

    unless ( exists $existing_users->{$args->{-u}} ) {
        croak qq{User '$args->{-u}' doesn't exist\n};
    }

    my $user_id = $existing_users->{$args->{-u}}{id};

    print qq{Adding groups for '$args->{-u}' ($user_id)\n};

    my $wl = new Data::Random::WordList( wordlist => '/usr/share/dict/words' );

    $dbh->begin_work();

    foreach ( 1 .. $args->{-n} ) { ### [...  ] (%)
        my $groupname = join(' ', $wl->get_words(int(rand(5)) + 1));
        my $groupdescription = join(' ', $wl->get_words(int(rand(50)) + 10));
        if ( $args->{-p} or $args->{-v} ) {
            print "Creating group '$groupname'\n";
            print "INSERT INTO usr_group (name,owner,description,ctime,mtime) VALUES (?,?,?,?,?)\n";
            my $members = {};
            foreach (1..(int(rand(20)) + 5)) {
                $members->{((keys %$existing_users)[int(rand(keys %$existing_users))])} = 1;
            }
            print "Members ... " . join(', ', keys %$members) . "\n";;
        }
        unless ( $args->{-p} ) {
            $dbh->do(
                'INSERT INTO usr_group (name,owner,description,ctime,mtime) VALUES (?,?,?,current_timestamp,current_timestamp)',
                undef,
                $groupname,
                $user_id,
                $groupdescription,
            );
            my $members = {};
            foreach (1..(int(rand(20)) + 5)) {
                $members->{$existing_users->{((keys %$existing_users)[int(rand(keys %$existing_users))])}{id}} = 1;
            }
            foreach my $id (keys %$members) {
                $dbh->do(
                    q{INSERT INTO usr_group_member (grp,member,ctime) VALUES (currval('usr_group_id_seq'),?,current_timestamp)},
                    undef,
                    $id,
                );
            }
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
