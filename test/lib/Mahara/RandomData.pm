#
# Inserts random data into a mahara database, for testing purposes
#
# Author: Martyn Smith <martyn@catalyst.net.nz>
# Re-organised into a module by Nigel McNie <nigel@catalyst.net.nz>
#
package Mahara::RandomData;
use Carp;
use DBI;
use Data::RandomPerson;
use Data::Random::WordList;
#use Smart::Comments;

sub new {
    my ($class,$config) = @_;

    croak 'Need to spefify config object' unless defined $config;

    my $self = {};
    $self->{config} = $config;
    $self->{verbose} = 0;
    $self->{pretend} = 0;

    my $connect_string = 'dbi:Pg:dbname=' . $config->get('dbname');
    my $host = $config->get('host');
    $connect_string .= 'host=' . $host if $host and $host =~ /\S/;
    my $port = $config->get('port');
    $connect_string .= 'port=' . $port if $port and $port =~ /\S/;
    $self->{dbh} = DBI->connect($connect_string, $config->get('dbuser'), $config->get('dbpass'), { RaiseError => 1 });

    bless $self, $class;
    return $self;
}

sub verbose {
    my ($self, $value) = @_;

    $self->{verbose} = $value;
}

sub pretend {
    my ($self, $value) = @_;

    $self->{pretend} = $value;
}

sub insert_random_users {
    my ($self, $count) = @_;

    my $prefix = $self->{config}->get('dbprefix');

    # get a list of existing usernames
    my $existing_users = $self->{dbh}->selectall_hashref('SELECT id, username FROM ' . $prefix . 'usr', 'username');

    $self->{dbh}->begin_work();

    foreach ( 1 .. $count ) { ### [...  ] (%)
        my $userinfo = Data::RandomPerson->new()->create();
        $userinfo->{username} = lc $userinfo->{firstname};
        $userinfo->{email} = lc $userinfo->{firstname} . '.' . lc $userinfo->{lastname} . '@dollyfish.net.nz';

        while ( exists $existing_users->{$userinfo->{username}} ) {
            $userinfo->{username} =~ s{ (\d*) \z }{($1 or 0)+1}exms;
        }

        $existing_users->{$userinfo->{username}} = 1;

        if ( $self->{verbose} or $self->{pretend} ) {
            print 'INSERT INTO usr (username,password,institution,firstname,lastname,email) VALUES (';
            print join(',',$userinfo->{username}, 'password', 'mahara', @{$userinfo}{qw(firstname lastname email)}), ")\n";
        }
        unless ( $self->{pretend} ) {
            $self->{dbh}->do('INSERT INTO ' . $prefix . 'usr (username,password,institution,firstname,lastname,email) VALUES (?,?,?,?,?,?)', undef,
                $userinfo->{username},
                'password',
                'mahara',
                @{$userinfo}{qw(firstname lastname email)},
            );
        }
    }

    $self->{dbh}->commit();
}

sub insert_random_groups {
    my ($self, $user, $count) = @_;

    my $prefix = $self->{config}{dbprefix};

    my $existing_users = $self->{dbh}->selectall_hashref('SELECT id, username FROM ' . $prefix . 'usr', 'username');

    unless ( exists $existing_users->{$user} ) {
        croak qq{User '$user' doesn't exist\n};
    }

    my $user_id = $existing_users->{$user}{id};

    print qq{Adding groups for '$user' ($user_id)\n};

    my $wl = new Data::Random::WordList( wordlist => '/usr/share/dict/words' );

    $self->{dbh}->begin_work();

    foreach ( 1 .. $count ) { ### [...  ] (%)
        my $groupname = join(' ', $wl->get_words(int(rand(5)) + 1));
        my $groupdescription = join(' ', $wl->get_words(int(rand(50)) + 10));
        if ( $self->{pretend} or $self->{verbose} ) {
            print "Creating group '$groupname'\n";
            print "INSERT INTO usr_group (name,owner,description,ctime,mtime) VALUES (?,?,?,?,?)\n";
            my $members = {};
            foreach (1..(int(rand(20)) + 5)) {
                $members->{((keys %$existing_users)[int(rand(keys %$existing_users))])} = 1;
            }
            print "Members ... " . join(', ', keys %$members) . "\n";;
        }
        unless ( $self->{pretend} ) {
            $self->{dbh}->do(
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
                $self->{dbh}->do(
                    'INSERT INTO ' . $prefix . 'usr_group_member (grp,member,ctime) VALUES (currval(\'usr_group_id_seq\'),?,current_timestamp)',
                    undef,
                    $id,
                );
            }
        }
    }

    $self->{dbh}->commit();

}


sub insert_random_groups_all_users {
    my ($self, $count) = @_;
    
    my $existing_users = $self->{dbh}->selectall_hashref('SELECT id, username FROM ' . $prefix . 'usr', 'username');

    foreach my $user ( keys %{$existing_users} ) {
        $self->insert_random_groups($user, $count);
    }
}

sub insert_random_activity {
    my ($self, $user, $count) = @_;

    my $prefix = $self->{config}{dbprefix};
    my $user_id = $self->{dbh}->selectall_arrayref('SELECT id FROM ' . $prefix . 'usr WHERE username = ?', undef, $user)->[0][0];

    unless ( defined $user_id ) {
        croak qq{User '$user' doesn't exist\n};
    }

    print qq{Adding activity for '$user' ($user_id)\n};
    $self->{dbh}->begin_work();

    my $wl = new Data::Random::WordList( wordlist => '/usr/share/dict/words' );

    foreach ( 1 .. $count ) { ### [...  ] (%)
        my $message = join(' ', $wl->get_words(int(rand(3)) + 2));
        $self->{dbh}->do(
            'INSERT INTO ' . $prefix . 'notification_internal_activity (type, usr, ctime, message, url, read) VALUES (?, ?, current_timestamp, ?, ?, ?)',
            undef,
            'maharamessage', $user_id, $message, 'http://mahara.org/', int(rand(2)));
    }

    $self->{dbh}->commit();
}

sub insert_random_activity_all_users {
    my ($self, $count) = @_;
    
    my $existing_users = $self->{dbh}->selectall_hashref('SELECT id, username FROM ' . $prefix . 'usr', 'username');

    foreach my $user ( keys %{$existing_users} ) {
        $self->insert_random_activity($user, $count);
    }
}

1;
