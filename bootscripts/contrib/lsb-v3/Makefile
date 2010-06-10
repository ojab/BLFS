ETCDIR=/etc
EXTDIR=${DESTDIR}${ETCDIR}
MODE=754
DIRMODE=755
CONFMODE=644

# The grep can probably be improved upon.
all:
	@grep "^install" Makefile | cut -d ":" -f 1
	@echo "Select an appropriate install target from the above list" ; exit 1

create-dirs:
	install -d -m ${DIRMODE} ${EXTDIR}/rc{0,1,2,3,4,5,6,S}.d
	install -d -m ${DIRMODE} ${EXTDIR}/init.d
	install -d -m ${DIRMODE} ${EXTDIR}/sysconfig

create-service-dir:
	install -d -m ${DIRMODE} ${EXTDIR}/sysconfig/network-devices/services

install-service-dhcpcd: create-service-dir
	install -m ${MODE} blfs/sysconfig/network-devices/services/dhcpcd   ${EXTDIR}/sysconfig/network-devices/services

install-alsa: create-dirs
	install -m ${MODE} blfs/init.d/alsa       ${EXTDIR}/init.d/
	/usr/lib/lsb/install_initd ${EXTDIR}/init.d/alsa

install-cups: create-dirs
	install -m ${MODE} blfs/init.d/cups       ${EXTDIR}/init.d/
	rm -f ${EXTDIR}/rc0.d/K36cups
	rm -f ${EXTDIR}/rc2.d/K36cups
	rm -f ${EXTDIR}/rc3.d/K36cups
	rm -f ${EXTDIR}/rc5.d/K36cups
	rm -f ${EXTDIR}/rc2.d/S81cups
	rm -f ${EXTDIR}/rc3.d/S81cups
	rm -f ${EXTDIR}/rc5.d/S81cups
	/usr/lib/lsb/install_initd ${EXTDIR}/init.d/cups

install-cyrus-sasl: create-dirs
	install -m ${MODE} blfs/init.d/cyrus-sasl ${EXTDIR}/init.d/
	/usr/lib/lsb/install_initd ${EXTDIR}/init.d/cyrus-sasl

install-dbus: create-dirs
	install -m ${MODE} blfs/init.d/dbus ${EXTDIR}/init.d/
	/usr/lib/lsb/install_initd ${EXTDIR}/init.d/dbus

install-gdm: create-dirs
	install -m ${MODE} blfs/init.d/gdm        ${EXTDIR}/init.d/
	/usr/lib/lsb/install_initd ${EXTDIR}/init.d/gdm

install-gpm: create-dirs
	install -m ${MODE} blfs/init.d/gpm        ${EXTDIR}/init.d/
	/usr/lib/lsb/install_initd ${EXTDIR}/init.d/gpm

install-haldaemon: create-dirs
	install -m ${MODE} blfs/init.d/haldaemon      ${EXTDIR}/init.d/
	/usr/lib/lsb/install_initd ${EXTDIR}/init.d/haldaemon

install-httpd: create-dirs
	install -m ${MODE} blfs/init.d/httpd     ${EXTDIR}/init.d/
	/usr/lib/lsb/install_initd ${EXTDIR}/init.d/httpd

install-named: create-dirs
	install -m ${MODE} blfs/init.d/named       ${EXTDIR}/init.d/
	/usr/lib/lsb/install_initd ${EXTDIR}/init.d/named

install-nas: create-dirs
	install -m ${MODE} blfs/init.d/nas        ${EXTDIR}/init.d/
	/usr/lib/lsb/install_initd ${EXTDIR}/init.d/nas

install-netfs: create-dirs
	install -m ${MODE} blfs/init.d/netfs      ${EXTDIR}/init.d/
	/usr/lib/lsb/install_initd ${EXTDIR}/init.d/netfs

install-ntpd: create-dirs
	install -m ${MODE} blfs/init.d/ntpd       ${EXTDIR}/init.d/
	/usr/lib/lsb/install_initd ${EXTDIR}/init.d/ntpd

install-slapd: create-dirs
	install -m ${MODE} blfs/init.d/slapd  ${EXTDIR}/init.d/slapd
	/usr/lib/lsb/install_initd ${EXTDIR}/init.d/slapd

install-postfix: create-dirs
	install -m ${MODE} blfs/init.d/postfix    ${EXTDIR}/init.d/
	/usr/lib/lsb/install_initd ${EXTDIR}/init.d/postfix

install-random: create-dirs
	install -m ${MODE} blfs/init.d/random     ${EXTDIR}/init.d/
	/usr/lib/lsb/install_initd ${EXTDIR}/init.d/random

install-samba: create-dirs
	install -m ${MODE} blfs/init.d/samba      ${EXTDIR}/init.d/
	/usr/lib/lsb/install_initd ${EXTDIR}/init.d/samba

install-sshd: create-dirs
	install -m ${MODE} blfs/init.d/sshd       ${EXTDIR}/init.d/
	/usr/lib/lsb/install_initd ${EXTDIR}/init.d/sshd


.PHONY: all create-dirs create-service-dir \
	install-service-dhclient \
	install-service-dhcpcd \
	install-alsa \
	install-cups \
	install-cyrus-sasl \
	install-gdm \
	install-gpm \
	install-httpd \
	install-named \
	install-nas \
	install-ntpd \
	install-postfix \
	install-samba \
	install-slapd \
	install-sshd 
