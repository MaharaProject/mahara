#!/usr/bin/perl
# This is slightly modified from Andrew Morton's Perfect Patch.
# Lines you introduce should not have trailing whitespace.
# Also check for an indentation that has SP before a TAB.
my $found_bad = 0;
my $filename;
my $reported_filename = "";
my $lineno;
my $stack;
my $stackage;
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
    if (s/^\+//) {
        $lineno++;
        chomp;
        if (/^([<>])\1{6} |^={7}$/) {
            bad_line("unresolved merge conflict", $_);
        }
        if ($filename !~ /\.(php|js)$/) {
            next;
        }
        if (/\s$/) {
            bad_line("trailing whitespace", $_);
        }
        if (/\t+/) {
            bad_line("TABs should be replaced by 4 spaces.", $_);
        }
        if (/\}\s*(else|catch)/) {
            bad_line("cuddled elses/catches are against Mahara coding guidelines", $_);
        }
        if (/\belseif\b/) {
            bad_line("a single space is requred between an else and an if on the same line", $_);
        }
        if (/\b(if|while|for)\(/ || /\b(if|while|for)\s\s+\(/) {
            bad_line("conditional and looping statements should have a space between keywords ".
                     "and the condition brackets", $_);
        }
        if (/\b(if|while|for) \(.+\)(\s{2,})?{$/) {
            bad_line("if/while/for constructs should have a space between the closing parenthesis ".
                     "of the expression and the opening brace of the following block", $_);
        }
        if (/\)$/) {#Note no trailing semicolon
            $stack = $_;
        }
        if (/^\s*{/ && $stack ne "") {
            bad_line("opening curly braces do not need their own line.", "$stack\n$_");
            $stack = "";
        }
        if (/require_once\s+\(?/) {
            bad_line("a require_once statement should look like a function call, ".
                     "without a space between the keyword and the bracket.", $_);
        }
    }

    #Implement aging of the stack to prevent
    #erroneous matches of seccond condition
    if ($stackage > 0) {
        $stackage = 0;
        $stack = "";
    }
    if ($stack ne "") {
        $stackage++;
    }
}

exit($found_bad);
