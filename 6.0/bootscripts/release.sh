#!/bin/bash
# Creates a dated (or released tarball)

if [ ! $UID = "0" ]
then
	echo "This script must be run as root!!!"
	exit 1
else

if [ -n "$1" ]
then
	RELEASE=$1
else
	RELEASE=`date +%Y%m%d`
fi

ORPWD=$PWD
RTPWD=$ORPWD/../blfs-bootscripts-$RELEASE

cp -a $ORPWD $RTPWD &&
rm -f $RTPWD/{README.release,release.sh} &&
rm -rf $RTPWD/.svn &&
rm -rf $RTPWD/blfs/.svn &&
rm -rf $RTPWD/blfs/init.d/.svn &&
rm -rf $RTPWD/blfs/sysconfig/.svn &&
rm -rf $RTPWD/blfs/sysconfig/network-devices/.svn &&
rm -rf $RTPWD/blfs/sysconfig/network-devices/services/.svn &&
chown root:root -R $RTPWD &&
chmod 644 -R $RTPWD &&
cd .. &&
chmod 766 blfs-bootscripts-$RELEASE &&
chmod 755 blfs-bootscripts-$RELEASE/{,blfs{,/init.d,/sysconfig{,/network-devices{,/services}}}} &&
tar -cf $ORPWD/blfs-bootscripts-$RELEASE.tar blfs-bootscripts-$RELEASE/ &&
cd $ORPWD &&
bzip2 -9 $ORPWD/blfs-bootscripts-$RELEASE.tar &&
echo "blfs-bootscripts-$RELEASE.tar.bz2 created successfully"

fi
# End
