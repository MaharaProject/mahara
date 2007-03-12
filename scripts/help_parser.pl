#!/usr/bin/perl

use strict;
use warnings;
use XML::LibXML;
use FindBin;
use File::Find;
use Perl6::Slurp;
use HTML::Tidy;
binmode STDOUT, ':utf8';

my $projectroot = qq{$FindBin::Bin/../};
my $parser = XML::LibXML->new();
my $number = 1;
$parser->recover(1);

my $cleanup_tasks = [
    sub { # Only want contents of the <body> tag
        my $dom = shift;
        if ($dom->findnodes('.//body')) {
            $dom = @{$dom->findnodes('.//body')}[0];
        }
        return $dom;
    },
    sub { # Don't want <strong> inside h3
        my $dom = shift;
        foreach my $node ($dom->findnodes('.//h3//strong')) {
            my $parent = $node->parentNode;
            $node = $node->parentNode->removeChild($node);
            foreach my $child ($node->childNodes) {
                $parent->appendChild($child);
            }
        }
        return $dom;
    },
    sub { # Remove empty tags
        my $dom = shift;
        foreach my $node ($dom->findnodes('.//*[count(node())=1]')) {
            if (
                $node->firstChild->nodeName eq 'text'
                and $node->firstChild->toString() =~ m{ \A \s* \z }xms
            ) {
                $node->parentNode->removeChild($node);
            }
        }
        foreach my $node ($dom->findnodes('.//*[count(node())=0]')) {
            if (
                $node->nodeName eq 'strong'
            ) {
                $node->parentNode->removeChild($node);
            }
        }
        return $dom;
    },
    sub { # Ensure there is no "text" at the top level
        my $dom = shift;
        my $root = shift;
        foreach my $node ($dom->findnodes('./node()')) {
            next unless $node->nodeName eq 'text';
            next unless $node->textContent =~ m{ \S }xms;
            my $p = $root->createElement('p');
            $node->replaceNode($p);
            $p->appendChild($node);
        }
        return $dom;
    },
    sub { # Template
        my $dom = shift;
        return $dom;
    },
];

find( \&process, $projectroot );

sub process {
    my $filename = $_;
    my $directory = $File::Find::dir;

    return unless $directory =~ m{ lang/en\.utf8/help }xms;
    return unless $filename  =~ m{ html \z }xms;

    my $dom = parse_file($directory . '/' . $filename);
    my $root = $dom;
    foreach my $cleanup ( @{$cleanup_tasks} ) {
        $dom = &$cleanup($dom, $root);
        return unless defined $dom;
    }

    unlink $directory . '/' . $filename;

    my $output = '';
    foreach my $child ($dom->childNodes) {
        $output .= $child->toString(0, 'POSIX');
    }
    $output .= "\n";

    $output =~ s{ \A \s* (.*?) \s* \z }{$1\n}xms;
    $output =~ s/\x{A0}//g;

    open OUTFILE, '>', $directory . '/' . $filename;
    print OUTFILE $output;
    close OUTFILE;

    # check that it's okay now ...
    unless ( grep { $_->nodeName ne 'p' and $_->nodeName ne 'h3' } $dom->findnodes('.//*')) {
        return;
    }

    unless (
        grep { $_->nodeName ne 'em' and $_->nodeName ne 'p' and $_->nodeName ne 'h3' } $dom->findnodes('.//*')
        and not grep {
            defined $_->previousSibling()
            and defined $_->nextSibling()
            and $_->previousSibling()->nodeName eq 'text'
            and $_->nextSibling()->nodeName eq 'text'
        } $dom->findnodes('.//em')
    )
    {
        return;
    }

    print $directory, '/', $filename, "\n";

}

sub parse_file {
    my $filename = shift;

    my $filedata = slurp($filename);

    $filedata =~ s{\r\n}{\n}smg;

    # Trap STDERR because the parser is quite verbose and annoying
    my $dom;
    {
        #local *STDERR;
        #open STDERR, '>', '/dev/null';
        $dom = $parser->parse_html_string($filedata);
    }

    return $dom;
}

