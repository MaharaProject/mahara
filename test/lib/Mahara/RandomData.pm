#
# Inserts random data into a mahara database, for testing purposes
#
# Author: Martyn Smith <martyn@catalyst.net.nz>
# Re-organised into a module by Nigel McNie <nigel@catalyst.net.nz>
#
package Mahara::RandomData;

use strict;
use warnings;

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
    my $host = $config->get('dbhost');
    $connect_string .= ';host=' . $host if $host and $host =~ /\S/;
    my $port = $config->get('dbport');
    $connect_string .= ';port=' . $port if $port and $port =~ /\S/;
    $self->{dbh} = DBI->connect($connect_string, $config->get('dbuser'), $config->get('dbpass'), { RaiseError => 1, PrintError => 0 });
    $self->{dbh}->{HandleError} = sub { confess(shift) };

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
            print 'INSERT INTO ' . $prefix . 'usr (username,password,institution,firstname,lastname,email) VALUES (';
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

    my $prefix = $self->{config}->get('dbprefix');

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
        $groupname =~ s/[\x80-\xff]//g;
        $groupdescription =~ s/[\x80-\xff]//g;
        if ( $self->{pretend} or $self->{verbose} ) {
            print "Creating group '$groupname'\n";
            print "INSERT INTO ${prefix}usr_group (name,owner,description,ctime,mtime) VALUES (?,?,?,?,?)\n";
            my $members = {};
            foreach (1..(int(rand(20)) + 5)) {
                $members->{((keys %$existing_users)[int(rand(keys %$existing_users))])} = 1;
            }
            print "Members ... " . join(', ', keys %$members) . "\n";;
        }
        unless ( $self->{pretend} ) {
            $self->{dbh}->do(
                'INSERT INTO ' . $prefix . 'usr_group (name,owner,description,ctime,mtime) VALUES (?,?,?,current_timestamp,current_timestamp)',
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
                    'INSERT INTO ' . $prefix . 'usr_group_member (grp,member,ctime) VALUES (currval(\'' . $prefix . 'usr_group_id_seq\'),?,current_timestamp)',
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

    my $prefix = $self->{config}->get('dbprefix');
    
    my $existing_users = $self->{dbh}->selectall_hashref('SELECT id, username FROM ' . $prefix . 'usr', 'username');

    foreach my $user ( keys %{$existing_users} ) {
        $self->insert_random_groups($user, $count);
    }
}

sub insert_random_activity {
    my ($self, $user, $count) = @_;

    my $prefix = $self->{config}->get('dbprefix');
    my $user_id = $self->{dbh}->selectall_arrayref('SELECT id FROM ' . $prefix . 'usr WHERE username = ?', undef, $user)->[0][0];

    unless ( defined $user_id ) {
        croak qq{User '$user' doesn't exist\n};
    }

    print qq{Adding activity for '$user' ($user_id)\n};
    $self->{dbh}->begin_work();

    my $wl = new Data::Random::WordList( wordlist => '/usr/share/dict/words' );

    foreach ( 1 .. $count ) { ### [...  ] (%)
        my $message = join(' ', $wl->get_words(int(rand(3)) + 2));
        $message =~ s/[\x80-\xff]//g;
        $self->{dbh}->do(
            'INSERT INTO ' . $prefix . 'notification_internal_activity (type, usr, ctime, message, url, read) VALUES (?, ?, current_timestamp, ?, ?, ?)',
            undef,
            'maharamessage', $user_id, $message, 'http://mahara.org/', int(rand(2)));
    }

    $self->{dbh}->commit();
}

sub insert_random_activity_all_users {
    my ($self, $count) = @_;

    my $prefix = $self->{config}->get('dbprefix');
    
    my $existing_users = $self->{dbh}->selectall_hashref('SELECT id, username FROM ' . $prefix . 'usr', 'username');

    foreach my $user ( keys %{$existing_users} ) {
        $self->insert_random_activity($user, $count);
    }
}

sub insert_random_communities {
   my ($self, $user, $count) = @_;

    my $prefix = $self->{config}->get('dbprefix');
    my $user_id = $self->{dbh}->selectall_arrayref('SELECT id FROM ' . $prefix . 'usr WHERE username = ?', undef, $user)->[0][0];

    unless ( defined $user_id ) {
        croak qq{User '$user' doesn't exist\n};
    }

    print qq{Adding communities for '$user' ($user_id)\n};
    $self->{dbh}->begin_work();

    my $wl = new Data::Random::WordList( wordlist => '/usr/share/dict/words' );

    foreach ( 1 .. $count ) { ### [...  ] (%)
        my $cname = join(' ', $wl->get_words(int(rand(5)) + 1));
        my $cdesc = join(' ', $wl->get_words(int(rand(50)) + 10));
        $cname =~ s/[\x80-\xff]//g;
        $cdesc =~ s/[\x80-\xff]//g;
        $self->{dbh}->do(
            'INSERT INTO ' . $prefix . 'community (name, description, owner, ctime, mtime) VALUES (?, ?, ?, current_timestamp, current_timestamp)',
            undef,
            $cname, $cdesc, $user_id);
    }

    $self->{dbh}->commit();

}

sub insert_random_communities_all_users {
    my ($self, $count) = @_;

    my $prefix = $self->{config}->get('dbprefix');
    
    my $existing_users = $self->{dbh}->selectall_hashref('SELECT id, username FROM ' . $prefix . 'usr', 'username');

    foreach my $user ( keys %{$existing_users} ) {
        $self->insert_random_communities($user, $count);
    }
}

sub insert_random_artefacts {
    
    my ($self, $user, $count) = @_;

    my $prefix = $self->{config}->get('dbprefix');
    my $user_id = $self->{dbh}->selectall_arrayref('SELECT id FROM ' . $prefix . 'usr WHERE username = ?', undef, $user)->[0][0];

    unless ( defined $user_id ) {
        croak qq{User '$user' doesn't exist\n};
    }

    print qq{Adding artefacts for '$user' ($user_id)\n};
    $self->{dbh}->begin_work();

    my $wl = new Data::Random::WordList( wordlist => '/usr/share/dict/words' );

    my @profilefields = qw(firstname lastname introduction);

    foreach ( 1 .. $count ) { ### [...  ] (%)

        foreach my $field (@profilefields) {
            my $title = join(' ', $wl->get_words(int(rand(5)) + 1));
            $title =~ s/[\x80-\xff]//g;
            $self->{dbh}->do(
            'INSERT INTO ' . $prefix . 'artefact (artefacttype, owner, title, ctime, mtime, atime) VALUES (?, ?, ?, current_timestamp, current_timestamp, current_timestamp)',
            undef,
            $field, $user_id, $title);
        }
    }

    $self->{dbh}->commit();
}


sub insert_random_artefacts_all_users {
    my ($self, $count) = @_;

    my $prefix = $self->{config}->get('dbprefix');
    
    my $existing_users = $self->{dbh}->selectall_hashref('SELECT id, username FROM ' . $prefix . 'usr', 'username');

    foreach my $user ( keys %{$existing_users} ) {
        $self->insert_random_artefacts($user, $count);
    }
}

sub insert_random_views {
    my ($self, $user, $count) = @_;

    my $prefix = $self->{config}->get('dbprefix');

    my $user_id = $self->{dbh}->selectall_arrayref('SELECT id FROM ' . $prefix . 'usr WHERE username = ?', undef, $user)->[0][0];

    unless ( defined $user_id ) {
        croak qq{User '$user' doesn't exist\n};
    }

    print qq{Adding views for '$user' ($user_id)\n};
    $self->{dbh}->begin_work();

    my $wl = new Data::Random::WordList( wordlist => '/usr/share/dict/words' );

    my ($template_id) = $self->{dbh}->selectrow_array('SELECT name FROM ' . $prefix . 'template ORDER BY RANDOM() LIMIT 1');
    
    my $title = join(' ', $wl->get_words(int(rand(5)) + 1));
    my $name = join(' ', $wl->get_words(int(rand(5)) + 1));

    unless ($template_id) {
        $self->{dbh}->do('INSERT INTO ' . $prefix . 'template (name, title, category, owner, ctime, mtime) 
                VALUES(?, ?, ?, ?, current_timestamp, current_timestamp)', undef, $name, $title, 'blog', $user_id);
        $template_id = $name;
    }

    foreach ( 1 .. $count ) { ### [...  ] (%)

        my $title = join(' ', $wl->get_words(int(rand(5)) + 1));
        my $description = join(' ', $wl->get_words(int(rand(5)) + 5));
        $title =~ s/[\x80-\xff]//g;
        $description =~ s/[\x80-\xff]//g;

        $self->{dbh}->do('INSERT INTO ' . $prefix . 'view (title, description, owner, template, startdate, stopdate, ctime, mtime, atime)
             VALUES(?, ?, ?, ?, current_timestamp, current_timestamp, current_timestamp, current_timestamp, current_timestamp)', undef,
             $title, $description, $user_id, $template_id);
        my $view_id = $self->{dbh}->last_insert_id(undef, undef, $prefix . 'view', undef);

        $self->{dbh}->do('INSERT INTO ' . $prefix . 'view_artefact (view, artefact, block, ctime) 
             (SELECT ' . $view_id . ', id, \'foo\', current_timestamp FROM '. $prefix . 'artefact WHERE owner = ? ORDER BY RANDOM() LIMIT 5)', undef, $user_id);
	
    }
    $self->{dbh}->commit();
}

sub insert_random_views_all_users {
    my ($self, $count) = @_;

    my $prefix = $self->{config}->get('dbprefix');
    
    my $existing_users = $self->{dbh}->selectall_hashref('SELECT id, username FROM ' . $prefix . 'usr', 'username');

    foreach my $user ( keys %{$existing_users} ) {
        $self->insert_random_views($user, $count);
    }
}

sub insert_random_watchlist {
    
    my ($self, $user, $count) = @_;

    my $prefix = $self->{config}->get('dbprefix');
    
    my $user_id = $self->{dbh}->selectall_arrayref('SELECT id FROM ' . $prefix . 'usr WHERE username = ?', undef, $user)->[0][0];

    unless ( defined $user_id ) {
        croak qq{User '$user' doesn't exist\n};
    }

    print qq{Adding views for '$user' ($user_id)\n};
    $self->{dbh}->begin_work();
    $self->{dbh}->do('DELETE FROM ' . $prefix . 'usr_watchlist_view WHERE usr = ?', undef, $user_id);
    $self->{dbh}->do('INSERT INTO ' . $prefix . 'usr_watchlist_view (usr, view, ctime) 
             (SELECT ' . $user_id . ', id, current_timestamp FROM ' . $prefix . 'view  
                 ORDER BY RANDOM() LIMIT ' . (int($count/3)+1) . ')' );

    $self->{dbh}->do('DELETE FROM ' . $prefix . 'usr_watchlist_community WHERE usr = ?', undef, $user_id);
    $self->{dbh}->do('INSERT INTO ' . $prefix . 'usr_watchlist_community (usr, community, ctime) 
             (SELECT ' . $user_id . ', id, current_timestamp FROM ' . $prefix . 'community  
                 ORDER BY RANDOM() LIMIT ' . (int($count/3)+1) . ')' );

    $self->{dbh}->do('DELETE FROM ' . $prefix . 'usr_watchlist_artefact WHERE usr = ?', undef, $user_id);
    $self->{dbh}->do('INSERT INTO ' . $prefix . 'usr_watchlist_artefact (usr, artefact, ctime) 
             (SELECT ' . $user_id . ', id, current_timestamp FROM ' . $prefix . 'artefact
                 ORDER BY RANDOM() LIMIT ' . (int($count/3)+1) . ')' );


    $self->{dbh}->commit();
}

sub insert_random_watchlist_all_users {
    my ($self, $count) = @_;

    my $prefix = $self->{config}->get('dbprefix');
    
    my $existing_users = $self->{dbh}->selectall_hashref('SELECT id, username FROM ' . $prefix . 'usr', 'username');

    foreach my $user ( keys %{$existing_users} ) {
        $self->insert_random_watchlist($user, $count);
    }
}

sub insert_random_template {
    
    my ($self, $user, $count) = @_;

    my $prefix = $self->{config}->get('dbprefix');
    
    my $user_id = $self->{dbh}->selectall_arrayref('SELECT id FROM ' . $prefix . 'usr WHERE username = ?', undef, $user)->[0][0];

    unless ( defined $user_id ) {
        croak qq{User '$user' doesn't exist\n};
    }

    my $wl = new Data::Random::WordList( wordlist => '/usr/share/dict/words' );

    my $existing_templates = $self->{dbh}->selectall_hashref('SELECT name FROM ' . $prefix . 'template', 'name');

    $self->{dbh}->begin_work();

    foreach ( 1 .. $count ) { ### [...  ] (%)
        my $name = $wl->get_words(1)->[0];
        my $title = join(' ', $wl->get_words(int(rand(5)) + 1));
        my $description = join(' ', $wl->get_words(int(rand(10)) + 5));

        $name =~ s/[\x80-\xff]//g;
        $title =~ s/[\x80-\xff]//g;
        $description =~ s/[\x80-\xff]//g;

        while ( exists $existing_templates->{$name} ) {
            $name =~ s{ (\d*) \z }{($1 or 0)+1}exms;
        }

        $existing_templates->{$name} = 1;

        $self->{dbh}->do(q{
            INSERT INTO } . $prefix . q{template (name, title, description, category, owner, ctime, mtime)
            VALUES (?, ?, ?, ?, ?, current_timestamp, current_timestamp)},
            undef,
            $name,
            $title,
            $description,
            'blog',
            $user_id
        );

    }

    $self->{dbh}->commit();
}

sub insert_random_template_all_users {
    my ($self, $count) = @_;

    my $prefix = $self->{config}->get('dbprefix');
    
    my $existing_users = $self->{dbh}->selectall_hashref('SELECT id, username FROM ' . $prefix . 'usr', 'username');

    foreach my $user ( keys %{$existing_users} ) {
        $self->insert_random_template($user, $count);
    }
}


1;


1;
