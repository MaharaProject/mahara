package Mahara::Config;
use Perl6::Slurp;
use Carp;

sub new {
    my ($class,$config_file) = @_;

    croak 'Need to spefify config file' unless defined $config_file;

    my $self = {};

    $self->{config_file} = $config_file;

    my $data = slurp($self->{config_file});

    while ( $data =~ m{ \$cfg-> ( [^=\s]+ ) \s* = \s* ([^;]+) }gxms ) {
        my ($key, $value) = ($1, $2);
        $value =~ s{ \A ' ( .*? ) ' \z }{$1}xms;
        $self->{config}{$key} = $value;
    }

    bless $self, $class;
    return $self;
}

sub get {
    my ($self, $key) = @_;

    return $self->{config}{$key};
}


1;
