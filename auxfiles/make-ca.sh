#!/bin/sh
# Begin /usr/sbin/make-ca.sh
#
# Script to create OpenSSL certs directory, GnuTLS certificate bundle, NSS
# shared DB, and Java cacerts from upstream certdata.txt and local sources
#
# The file certdata.txt must exist in the local directory
# Version number is obtained from the version of the data
#
# Authors: DJ Lucas
#          Bruce Dubbs
#
# Version 20161118

# Some data in the certs have UTF-8 characters
export LANG=en_US.utf8

certdata="certdata.txt"
TEMPDIR=$(mktemp -d)
WORKDIR="${TEMPDIR}/work"
SSLDIR="/etc/ssl"
PKIDIR="/etc/pki"
WITH_NSS=1
WITH_JAVA=1

# Convert CKA_TRUST values to trust flags for certutil
function convert_trust(){
  case $1 in
    CKT_NSS_TRUSTED_DELEGATOR)
      echo "C"
    ;;
    CKT_NSS_NOT_TRUSTED)
      echo "p"
    ;;
    CKT_NSS_MUST_VERIFY_TRUST)
      echo ""
    ;;
  esac
}

function convert_trust_arg(){
  case $1 in
    C)
      case $2 in
        sa)
          echo "-addtrust serverAuth"
        ;;
        sm)
          echo "-addtrust emailProtection"
        ;;
        cs)
          echo "-addtrust codeSigning"
        ;;
      esac
    ;;
    p)
      case $2 in
        sa)
          echo "-addreject serverAuth"
        ;;
        sm)
          echo "-addreject emailProtection"
        ;;
        cs)
          echo "-addreject codeSigning"
        ;;
      esac
    ;;
    *)
      echo ""
    ;;
  esac
}
    
if test ! -r $certdata; then
  echo "$certdata must be in the local directory"
  exit 1
fi

test -f /usr/bin/certutil || WITH_NSS=0
test -f /opt/jdk/bin/keytool || WITH_JAVA=0

VERSION=$(grep CVS_ID $certdata | cut -d " " -f 8)

if test "${VERSION}x" == "x"; then
  echo "WARNING! ${certdata} has no 'Revision' in CVS_ID"
  echo "Will run conversion unconditionally."
  sleep 2
  VERSION="$(date -u)"
else
  test -f "${SSLDIR}/ca-bundle.crt" &&
  OLDVERSION=$(grep "^VERSION:" "${SSLDIR}/ca-bundle.crt" | cut -d ":" -f 2)
fi

if test "${OLDVERSION}x" == "${VERSION}x"; then
  echo "No update required!"
  exit 0
fi

mkdir -p "${TEMPDIR}"/{certs,ssl/{certs,java},pki/nssdb,work}
cp "${certdata}" "${WORKDIR}"
pushd "${WORKDIR}" > /dev/null

if test "${WITH_NSS}" == "1"; then
  # Create a blank NSS DB
  /usr/bin/certutil -N --empty-password -d "sql:${TEMPDIR}/pki/nssdb"
fi

# Get a list of starting lines for each cert
CERTBEGINLIST=`grep -n "^# Certificate" "${certdata}" | \
                      cut -d ":" -f1`

# Dump individual certs to temp file
for certbegin in ${CERTBEGINLIST}; do
  awk "NR==$certbegin,/^CKA_TRUST_STEP_UP_APPROVED/" "${certdata}" \
      > "${TEMPDIR}/certs/${certbegin}.tmp" 
done

unset CERTBEGINLIST certbegin

for tempfile in ${TEMPDIR}/certs/*.tmp; do
  # Get a name for the cert
  certname="$(grep "^# Certificate" "${tempfile}" | cut -d '"' -f 2)"

  # Determine certificate trust values for SSL/TLS, S/MIME, and Code Signing
  satrust="$(convert_trust `grep '^CKA_TRUST_SERVER_AUTH' ${tempfile} | \
                  cut -d " " -f 3`)"
  smtrust="$(convert_trust `grep '^CKA_TRUST_EMAIL_PROTECTION' ${tempfile} | \
                  cut -d " " -f 3`)"
  cstrust="$(convert_trust `grep '^CKA_TRUST_CODE_SIGNING' ${tempfile} | \
                  cut -d " " -f 3`)"

  # Get args for OpenSSL trust settings
  saarg="$(convert_trust_arg "${satrust}" sa)"
  smarg="$(convert_trust_arg "${smtrust}" sm)"
  csarg="$(convert_trust_arg "${cstrust}" cs)"

  # Convert to a PEM formated certificate
  printf $(awk '/^CKA_VALUE/{flag=1;next}/^END/{flag=0}flag{printf $0}' \
  "${tempfile}") | /usr/bin/openssl x509 -text -inform DER -fingerprint \
  > tempfile.crt

  # Get a hash for the cert
  keyhash=$(/usr/bin/openssl x509 -noout -in tempfile.crt -hash)

  # Print information about cert
  echo "Certificate:  ${certname}"
  echo "Keyhash:      ${keyhash}"

  # Import certificates trusted for SSL/TLS into the Java keystore and 
  # GnuTLS certificate bundle
  if test "${satrust}x" == "Cx"; then
    # Java keystore
    if test "${WITH_JAVA}" == "1"; then
      /opt/jdk/bin/keytool -import -noprompt -alias "${certname}"   \
                           -keystore "${TEMPDIR}/ssl/java/cacerts"  \
                           -storepass 'changeit' -file tempfile.crt \
      2>&1> /dev/null | \
      sed -e 's@Certificate was a@A@' -e 's@keystore@Java keystore.@'
    fi

    # GnuTLS certificate bundle
    cat tempfile.crt >> "${TEMPDIR}/ssl/ca-bundle.crt.tmp"
    echo "Added to GnuTLS ceritificate bundle."
  fi

  # Import certificate into the temporary certificate directory with
  # trust arguments
  /usr/bin/openssl x509 -in tempfile.crt -text -fingerprint \
      -setalias "${certname}" ${saarg} ${smarg} ${csarg}    \
      > "${TEMPDIR}/ssl/certs/${keyhash}.pem"
  echo "Added to OpenSSL certificate directory with trust '${satrust},${smtrust},${cstrust}'."

  # Import all certificates with trust args to the temporary NSS DB
  if test "${WITH_NSS}" == "1"; then
    /usr/bin/certutil -d "sql:${TEMPDIR}/pki/nssdb" -A \
                      -t "${satrust},${smtrust},${cstrust}" \
                      -n "${certname}" -i tempfile.crt
    echo "Added to NSS shared DB with trust '${satrust},${smtrust},${cstrust}'."
  fi

  # Clean up the directory and environment as we go
  rm -f tempfile.crt
  unset certname satrust smtrust cstrust
  echo -e "\n"
done
unset tempfile

# Sanity check
count=$(ls "${TEMPDIR}"/ssl/certs/*.pem | wc -l)
# Historically there have been between 152 and 165 certs
# A minimum of 140 should be safe for a rudimentry sanity check
if test "${count}" -lt "140" ; then
    echo "Error! Only ${count} certificates were generated!"
    echo "Exiting without update!"
    echo ""
    echo "${TEMPDIR} is the temporary working directory"
    exit 2
fi
unset count

# Generate the bundle
echo "VERSION:${VERSION}" > "${TEMPDIR}/ssl/ca-bundle.crt"
cat "${TEMPDIR}/ssl/ca-bundle.crt.tmp" >> "${TEMPDIR}/ssl/ca-bundle.crt"
unset cert

# Install Java Cacerts
if test "${WITH_JAVA}" == "1"; then
  test -f "${SSLDIR}/java/cacerts" && mv "${SSLDIR}"/java/cacerts{,.old}
  install -dm755 "${SSLDIR}/java"
  install -m644 "${TEMPDIR}/ssl/java/cacerts" "${SSLDIR}/java" &&
  rm -f "${SSLDIR}/java/cacerts.old"
fi

# Install NSS Shared DB
if test "${WITH_NSS}" == "1"; then
  sed -e "s@${TEMPDIR}/pki/nssdb@${PKIDIR}/nssdb@"       \
      -e 's/library=/library=libnsssysinit.so/'          \
      -e 's/Flags=internal/Flags=internal,moduleDBOnly/' \
      -i "${TEMPDIR}/pki/nssdb/pkcs11.txt" 
  test -d "${pkiDIR}/nssdb" && mv "${PKIDIR}"/nssdb{,.old}
  install -dm755 "${PKIDIR}/nssdb"
  install -m644 "${TEMPDIR}"/pki/nssdb/{cert9.db,key4.db,pkcs11.txt} \
                 "${PKIDIR}/nssdb" &&
  rm -rf "${PKIDIR}/nssdb.old"
fi

# Install certificates in $SSLDIR/certs
test -d "${SSLDIR}/certs" && mv "${SSLDIR}"/certs{,.old}
install -dm755 "${SSLDIR}/certs"
install -m644 "${TEMPDIR}"/ssl/certs/*.pem "${SSLDIR}/certs/" &&
rm -rf "${SSLDIR}/certs.old"

# Install the certificate bundle
test -f "${SSLDIR}/ca-bundle.crt" && mv "${SSLDIR}"/ca-bundle.crt{,.old}
install -m644 "${TEMPDIR}"/ssl/ca-bundle.crt "${SSLDIR}" &&
rm -f "${SSLDIR}/ca-bundle.crt.old"

# Import any certs in $SSLDIR/local
# Don't do any checking, just trust the admin
if test -d "${SSLDIR}/local"; then
  for cert in `find "${SSLDIR}/local" -name "*.pem"`; do
    # Get some information about the certificate
    keyhash=$(/usr/bin/openssl x509 -noout -in "${cert}" -hash)
    subject=$(/usr/bin/openssl x509 -noout -in "${cert}" -subject)
    count=1
    while test "${count}" -lt 10; do
      echo "${subject}" | cut -d "/" -f "${count}" | grep "CN=" >/dev/null \
           && break
      let count++
    done
    certname=$(echo "${subject}" | cut -d "/" -f "${count}" | sed 's@CN=@@')

    echo "Certificate:  ${certname}"
    echo "Keyhash:      ${keyhash}"

    # Get trust information
    trustlist=$(/usr/bin/openssl x509 -in "${cert}" -text -trustout | \
                       grep -A1 "Trusted Uses")
    satrust=""
    smtrust=""
    cstrust=""
    satrust=$(echo "${trustlist}" | \
              grep "TLS Web Server" 2>&1> /dev/null && echo "C")
    smtrust=$(echo "${trustlist}" | \
              grep "E-mail Protection" 2>&1 >/dev/null && echo "C")
    cstrust=$(echo "${trustlist}" | \
              grep "Code Signing" 2>&1 >/dev/null && echo "C")

    # Get reject information
    rejectlist=$(/usr/bin/openssl x509 -in "${cert}" -text -trustout | \
                     grep -A1 "Rejected Uses")
    if test "${satrust}" == ""; then satrust=$(echo "${rejectlist}" | \
              grep "TLS Web Server" 2>&1> /dev/null && echo "p"); fi
    if test "${smtrust}" == ""; then smtrust=$(echo "${rejectlist}" | \
              grep "E-mail Protection" 2>&1> /dev/null && echo "p"); fi
    if test "${cstrust}" == ""; then cstrust=$(echo "${rejectlist}" | \
              grep "Code Signing" 2>&1> /dev/null && echo "p"); fi

    # Install in Java keystore
    if test "${WITH_JAVA}" == "1" -a "${satrust}x" == "Cx"; then
      /opt/jdk/bin/keytool -import -noprompt -alias "${certname}" \
              -keystore "${SSLDIR}/java/cacerts"        \
              -storepass 'changeit' -file "${cert}" 2>&1> /dev/null | \
      sed -e 's@Certificate was a@A@' -e 's@keystore@Java keystore.@'
    fi

    # Append to the bundle - source should have trust info, process with
    # openssl x509 to strip
    if test "${satrust}x" == "Cx"; then
      /usr/bin/openssl x509 -in "${cert}" -text -fingerprint \
           >> "${SSLDIR}/ca-bundle.crt"
      echo "Added to GnuTLS certificate bundle."
    fi

    # Install into OpenSSL certificate store
    /usr/bin/openssl x509 -in "${cert}" -text -fingerprint \
                          -setalias "${certname}"          \
                          >> "${SSLDIR}/certs/${keyhash}.pem"
    echo "Added to OpenSSL certificate directory."

    # Add to Shared NSS DB
    if test "${WITH_NSS}" == "1"; then
      openssl x509 -in "${cert}" -text -fingerprint |         \
      /usr/bin/certutil -d "sql:${PKIDIR}/nssdb" -A           \
                        -t "${satrust},${smtrust},${cstrust}" \
                        -n "${certname}"
      echo "Added to NSS shared DB with trust '${satrust},${smtrust},${cstrust}'."
    fi

    unset keyhash subject count certname
    unset trustlist rejectlist satrust smtrust cstrust
    echo ""

  done
  unset cert
fi

# We cannot use $SSLDIR/certs directly as the trust anchor because of
# c_rehash usage for OpenSSL (every entry is duplicated)
# Populate a duplicate anchor directory
install -vdm755 "${PKIDIR}"
rm -rf "${PKIDIR}/anchors"
cp -R "${SSLDIR}/certs" "${PKIDIR}/anchors"

/usr/bin/c_rehash "${SSLDIR}/certs" 2>&1>/dev/null
popd > /dev/null

# Clean up the mess
rm -rf "${TEMPDIR}"

# End /usr/sbin/make-ca.sh
