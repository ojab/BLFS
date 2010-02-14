#!/usr/bin/perl -w
#
# Used to regenerate ca-bundle.crt from the Mozilla certdata.txt.
# Run as ./mkcabundle.pl > ca-bundle.crt
#
# This script courtesy of RedHat.  Latest version is avaliable from:
# http://cvs.fedoraproject.org/viewvc/rpms/ca-certificates/devel/mkcabundle.pl
#

# This script modified to use a local copy of certdata.txt obtained from the 
# latest version of the firefox source tree.  The certdata.txt file can be
# found in mozilla-$version/security/nss/lib/ckfw/builtins/certdata.txt

my $certdata = './certdata.txt';

open(IN, "cat $certdata|")
    || die "could not open  certdata.txt";

my $incert = 0;

print<<EOH;
# This is a bundle of X.509 certificates of public Certificate
# Authorities.  It was generated from the Mozilla root CA list.
#
# Source: $certdata
#
EOH

while (<IN>) {
    if (/^CKA_VALUE MULTILINE_OCTAL/) {
        $incert = 1;
        open(OUT, "|openssl x509 -text -inform DER -fingerprint")
            || die "could not pipe to openssl x509";
    } elsif (/^END/ && $incert) {
        close(OUT);
        $incert = 0;
        print "\n\n";
    } elsif ($incert) {
        my @bs = split(/\\/);
        foreach my $b (@bs) {
            chomp $b;
            printf(OUT "%c", oct($b)) unless $b eq '';
        }
    } elsif (/^CVS_ID.*Revision: ([^ ]*).*/) {
        print "# Generated from certdata.txt RCS revision $1\n#\n";
    }
}
