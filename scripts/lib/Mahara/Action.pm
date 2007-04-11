#
# Handles available actions for maharactl
#
# Author: Nigel McNie <nigel@catalyst.net.nz>
#
package Mahara::Action;

use strict;
use warnings;

use Carp;
use DBI;
use Data::RandomPerson;
use Data::Random::WordList;
#use Smart::Comments;
use Data::Dumper;
use IO::Prompt;

sub new {
    my ($class,$config) = @_;

    croak 'Need to specify config object' unless defined $config;

    my $self = {};
    $self->{config} = $config;

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


sub add_user {
    my ($self) = @_;
    my ($username, $password, $institution, $firstname, $lastname, $studentid, $preferredname, $email);
    my $prefix = $self->{config}->get('dbprefix');
    print "Creating a new user\n";

    # IOprompt's interface is poos...
    $username      = prompt("Username: ", -while => qr{^[a-zA-Z0-9]+$}) until $username;
    $password      = prompt("Password: ", -echo => '*');
    $institution   = prompt("Institution: ", -while => qr{^[a-z]+$}, -default => 'mahara') until $institution;
    $email         = prompt("E-mail: ") until $email;
    $firstname     = prompt("First Name: ") until $firstname;
    $preferredname = prompt("Preferred Name: ", -default => $firstname);
    $lastname      = prompt("Last Name: ") until $lastname;
    $studentid     = prompt("Student ID: ");

    print "Creating user '$username'\n";
    $self->{dbh}->begin_work;

    $self->{dbh}->do('INSERT INTO ' . $prefix . 'usr
        (username,password,institution,passwordchange,firstname,lastname,studentid,preferredname,email,quota)
        VALUES (?,?,?,?,?,?,?,?,?,
            (SELECT value::bigint FROM ' . $prefix . 'artefact_config WHERE plugin = \'file\' AND field = \'defaultquota\')
        )',
        undef,
        $username,
        $password,
        $institution,
        1,
        $firstname,
        $lastname,
        $studentid,
        $preferredname,
        $email
    );

    # First name, last name and e-mail address are all required
    $self->{dbh}->do('INSERT INTO ' . $prefix . 'artefact
        (artefacttype,owner,ctime,mtime,atime,title)
        VALUES (?,currval(\'' . $prefix . 'usr_id_seq\'),current_timestamp,current_timestamp,current_timestamp,?)', undef,
        'firstname',
        $firstname
    );


    $self->{dbh}->do('INSERT INTO ' . $prefix . 'artefact
        (artefacttype,owner,ctime,mtime,atime,title)
        VALUES (?,currval(\'' . $prefix . 'usr_id_seq\'),current_timestamp,current_timestamp,current_timestamp,?)', undef,
        'lastname',
        $lastname
    );

    $self->{dbh}->do('INSERT INTO ' . $prefix . 'artefact
        (artefacttype,owner,ctime,mtime,atime,title)
        VALUES (?,currval(\'' . $prefix . 'usr_id_seq\'),current_timestamp,current_timestamp,current_timestamp,?)', undef,
        'email',
        $email
    );

    # Insert record for primary e-mail address
    $self->{dbh}->do('INSERT INTO ' . $prefix . 'artefact_internal_profile_email
         (owner, email, verified, principal, artefact)
         VALUES (currval(\'' . $prefix . 'usr_id_seq\'), ?, 1, 1, currval(\'' . $prefix . 'artefact_id_seq\'))', undef,
         $email,
    );

    # These fields are optional
    $self->{dbh}->do('INSERT INTO ' . $prefix . 'artefact
        (artefacttype,owner,ctime,mtime,atime,title)
        VALUES (?,currval(\'' . $prefix . 'usr_id_seq\'),current_timestamp,current_timestamp,current_timestamp,?)', undef,
        'studentid',
        $studentid
    ) if $studentid;

    $self->{dbh}->do('INSERT INTO ' . $prefix . 'artefact
        (artefacttype,owner,ctime,mtime,atime,title)
        VALUES (?,currval(\'' . $prefix . 'usr_id_seq\'),current_timestamp,current_timestamp,current_timestamp,?)', undef,
        'preferredname',
        $preferredname
    ) if $preferredname;


    $self->{dbh}->commit;

}


1;
