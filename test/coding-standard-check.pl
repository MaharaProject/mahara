#!/usr/bin/perl
# This is slightly modified from Andrew Morton's Perfect Patch.
# Lines you introduce should not have trailing whitespace.
# Also check for an indentation that has SP before a TAB.
my $found_bad = 0;
my $filename;
my $reported_filename = "";
my $lineno;
sub bad_line {
    my ($why, $line) = @_;

    if (!$found_bad) {
        print STDERR "*\n";
        print STDERR "* You have some suspicious patch lines:\n";
        print STDERR "*\n";
        $found_bad = 1;
    }
    if ($reported_filename ne $filename) {
        print STDERR "* In $filename\n";
        $reported_filename = $filename;
    }

    print STDERR "* $why (line $lineno)\n";
    print STDERR "$filename:$lineno:$line\n";
}

while (<>) {
    if (m|^diff --git a/(.*) b/\1$|) {
        $filename = $1;
        next;
    }
    if (/^@@ -\S+ \+(\d+)/) {
        $lineno = $1 - 1;
        next;
    }
    if (/^ /) {
        $lineno++;
        next;
    }
    if ($filename =~ /\/version.php$/) {
        if (s/^-//) {
            if (/->version = ([0-9]+);/) {
                $oldversion = $1;
                print STDERR "Old version = $oldversion\n";
            }
        }
    }
    if (s/^\+//) {
        $lineno++;
        chomp;
        if ($filename !~ /\.(php|js)$/) {
            next;
        }
        if ($filename =~ /\/version.php$/) {
            if (/->version = ([0-9]+);/) {
                $newversion = $1;
                print STDERR "New version = $newversion\n";
                if ($newversion > $oldversion + 5) {
                    bad_line("stable version number is increased more than 5", $_);
                }
            }
        }
        if (/\s$/) {
            bad_line("trailing whitespace", $_);
        }
        if (/\t+/) {
            bad_line("TABs should be replaced by 4 spaces.", $_);
        }
        if (/^([<>])\1{6} |^={7}$/) {
            bad_line("unresolved merge conflict", $_);
        }
        if (/\}\s*(else|catch)/) {
            bad_line("cuddled elses/catches are against Mahara coding guidelines", $_);
        }
        if (/elseif/) {
            bad_line("a single space is requred between an else and an if on the same line", $_);
        }
        if (/(if|while|for)\(/ || /(if|while|for)\s\s+\(/) {
            bad_line("conditional and looping statements should have a space between keywords ".
                     "and the condition brackets", $_);
        }
        if (/^\s*{/) {
            bad_line("opening curly braces are do no need their own line.", $_);
        }
        if (/require_once\s+\(?/) {
            bad_line("a require_once statement should look like a function call, ".
                     "without a space between the keyword and the bracket.", $_);
        }
    }
}

exit($found_bad);
