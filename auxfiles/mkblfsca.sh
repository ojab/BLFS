#!/bin/bash
# Begin mkblfscas.sh
# Script to populate  OpenSSL's CApath from a bundle of PEM formatted CAs

# Version number is obtained from the version of nss.
if test -z "${1}"
then
    #rev 1.65
    VERSION="3.12.8.0"
else
  VERSION="${1}"
fi

TEMPDIR=`mktemp -d`
CERTDATA="certdata.txt"
TRUSTATTRIBUTES="CKA_TRUST_SERVER_AUTH"
TARBALL="${PWD}/BLFS-ca-bundle-${VERSION}.tar.bz2"
CONVERTSCRIPT="./mkcert.pl"

mkdir "${TEMPDIR}/certs"

# Get a list of staring lines for each cert
CERTBEGINLIST=`grep -n "^# Certificate" "${CERTDATA}" | cut -d ":" -f 1`

# Get a list of ending lines for each cert
CERTENDLIST=`grep -n "^CKA_TRUST_STEP_UP_APPROVED" "${CERTDATA}" | cut -d ":" -f 1`
# Start a loop
for certbegin in ${CERTBEGINLIST}
do
  for certend in ${CERTENDLIST}
  do
    if test "${certend}" -gt "${certbegin}"
    then
      break
    fi
  done
  # Dump to a temp file with the name of the file as the beginning line number
  sed -n "${certbegin},${certend}p" "${CERTDATA}" > "${TEMPDIR}/certs/${certbegin}.tmp"
done
unset CERTBEGINLIST CERTDATA CERTENDLIST certebegin certend

mkdir -p certs

for tempfile in ${TEMPDIR}/certs/*.tmp
do
 # Make sure that the cert is trusted...
  grep "CKA_TRUST_SERVER_AUTH" "${tempfile}" | \
    grep "CKT_NETSCAPE_TRUST_UNKNOWN" > /dev/null
  if test "${?}" = "0"
  then
    # Thow a meaningful error and remove the file
    cp "${tempfile}" tempfile.cer
    "${CONVERTSCRIPT}" > tempfile.crt
    keyhash=`openssl x509 -noout -in tempfile.crt -hash`
    echo "Certificate ${keyhash} is not trusted!  Removing..."
    rm -f tempfile.cer tempfile.crt "${tempfile}"
    continue
  fi
  # If execution made it to here in the loop, the temp cert is trusted
  # Find the cert data and generate a cert file for it

  cp "${tempfile}" tempfile.cer
  "${CONVERTSCRIPT}" > tempfile.crt
  keyhash=`openssl x509 -noout -in tempfile.crt -hash`
  mv tempfile.crt "certs/${keyhash}.pem"
  rm -f tempfile.cer "${tempfile}"
  echo "Created ${keyhash}.pem"
done

# Remove blacklisted files
# MD5 Collision Proof of Concept CA
if test -f certs/8f111d69.pem
then
  echo "Certificate 8f111d69 is not trusted!  Removing..."
  rm -f certs/8f111d69.pem
fi

# Finally, generate the tarball and clean up.
tar -jcf ${TARBALL} certs/
rm -r certs/
rm -r "${TEMPDIR}"

