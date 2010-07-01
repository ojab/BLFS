#!/bin/bash
# Begin mkblfscas.sh
# Script to populate  OpenSSL's CApath from a bundle of PEM formatted CAs

# Version number is obtained from the version of nss.
if test -z "${1}"
then
    VERSION="3.12.6"
else
  VERSION="${1}"
fi

TEMPDIR=`mktemp -d`
CAFILE="${TEMPDIR}/ca-bundle.crt"
TARBALL="${PWD}/BLFS-ca-bundle-${VERSION}.tar.bz2"
CASCRIPT="./mkcabundle.pl"

"${CASCRIPT}" > "${CAFILE}"

mkdir "${TEMPDIR}/certs"

# Get a list of staring lines for each cert
CERTLIST=`grep -n "^Certificate:$" "${CAFILE}" | cut -d ":" -f 1`

# Get a list of ending lines for each cert
ENDCERTLIST=`grep -n "^-----END" "${CAFILE}" | cut -d ":" -f 1`

# Start a loop
for certbegin in `echo "${CERTLIST}"`
do
  for certend in `echo "${ENDCERTLIST}"`
  do
    if test "${certend}" -gt "${certbegin}"
    then
      break
    fi
  done
  sed -n "${certbegin},${certend}p" "${CAFILE}" > "${TEMPDIR}/certs/${certbegin}"
  object=`grep -m 1 -o "O=.*, " "${TEMPDIR}/certs/${certbegin}" | sed -e 's@O=@@' -e 's@,.*@@'`
  keyhash=`openssl x509 -noout -in "${TEMPDIR}/certs/${certbegin}" -hash`
  if test -z "$object"
  then
      object="NO OBJECT PROVIDED IN DESCRIPTION"
  fi
  echo "generated PEM file with hash ${keyhash} for ${object}"
  mv "${TEMPDIR}/certs/${certbegin}" "${TEMPDIR}/certs/${keyhash}.pem"
done

cd "${TEMPDIR}"
tar -jcf "${TARBALL}" certs/
cd ..
rm -r "${TEMPDIR}"

# End mkblfscas.sh
