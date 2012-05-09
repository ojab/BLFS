ETCDIR=/etc
LIBDIR=${DESTDIR}/lib/services
EXTDIR=${DESTDIR}${ETCDIR}
MODE=754
DIRMODE=755
CONFMODE=644

all:
	@grep "^install" Makefile | cut -d ":" -f 1
	@echo "Select an appropriate install target from the above list" 

create-dirs:
	install -d -m ${DIRMODE} ${EXTDIR}/rc.d/rc{0,1,2,3,4,5,6,S}.d
	install -d -m ${DIRMODE} ${EXTDIR}/rc.d/init.d
	install -d -m ${DIRMODE} ${EXTDIR}/sysconfig

create-service-dir:
	install -d -m ${DIRMODE} ${EXTDIR}/sysconfig/network-devices/services
	install -d -m ${DIRMODE} ${LIBDIR}

install-service-dhclient: create-service-dir
	install -m ${MODE} blfs/services/dhclient ${LIBDIR}

install-service-dhcpcd: create-service-dir
	install -m ${MODE} blfs/services/dhcpcd  ${LIBDIR}

install-service-bridge: create-service-dir
	install -m ${MODE} blfs/services/bridge  ${LIBDIR}

install-service-ipx: create-service-dir
	install -m ${MODE} blfs/sysconfig/network-devices/services/ipx      ${EXTDIR}/sysconfig/network-devices/services

install-service-pppoe: create-service-dir
	install -m ${MODE} blfs/sysconfig/network-devices/services/pppoe    ${EXTDIR}/sysconfig/network-devices/services
	install -d -m ${DIRMODE} ${EXTDIR}/ppp/peers
	install -m ${CONFMODE} blfs/ppp/pppoe ${EXTDIR}/ppp/peers

install-alsa: create-dirs
	install -m ${MODE} blfs/init.d/alsa       ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/alsa ${EXTDIR}/rc.d/rc0.d/K35alsa
	ln -sf  ../init.d/alsa ${EXTDIR}/rc.d/rc1.d/K35alsa
	ln -sf  ../init.d/alsa ${EXTDIR}/rc.d/rc6.d/K35alsa

install-httpd: create-dirs
	install -m ${MODE} blfs/init.d/httpd     ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/httpd ${EXTDIR}/rc.d/rc0.d/K28httpd
	ln -sf  ../init.d/httpd ${EXTDIR}/rc.d/rc1.d/K28httpd
	ln -sf  ../init.d/httpd ${EXTDIR}/rc.d/rc2.d/K28httpd
	ln -sf  ../init.d/httpd ${EXTDIR}/rc.d/rc3.d/S32httpd
	ln -sf  ../init.d/httpd ${EXTDIR}/rc.d/rc4.d/S32httpd
	ln -sf  ../init.d/httpd ${EXTDIR}/rc.d/rc5.d/S32httpd
	ln -sf  ../init.d/httpd ${EXTDIR}/rc.d/rc6.d/K28httpd

install-autofs: create-dirs
	install -m $(MODE) blfs/init.d/autofs    $(EXTDIR)/rc.d/init.d/
	install -m $(CONFMODE) blfs/sysconfig/autofs.conf $(EXTDIR)/sysconfig/
	ln -sf  ../init.d/autofs $(EXTDIR)/rc.d/rcS.d/S52autofs

install-atd: create-dirs
	install -m ${MODE} blfs/init.d/atd       ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/atd ${EXTDIR}/rc.d/rc0.d/K29atd
	ln -sf  ../init.d/atd ${EXTDIR}/rc.d/rc1.d/K29atd
	ln -sf  ../init.d/atd ${EXTDIR}/rc.d/rc2.d/S68atd
	ln -sf  ../init.d/atd ${EXTDIR}/rc.d/rc3.d/S68atd
	ln -sf  ../init.d/atd ${EXTDIR}/rc.d/rc4.d/S68atd
	ln -sf  ../init.d/atd ${EXTDIR}/rc.d/rc5.d/S68atd
	ln -sf  ../init.d/atd ${EXTDIR}/rc.d/rc6.d/K29atd

install-avahi: create-dirs
	install -m ${MODE} blfs/init.d/avahi     ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/avahi ${EXTDIR}/rc.d/rc0.d/K28avahi
	ln -sf  ../init.d/avahi ${EXTDIR}/rc.d/rc1.d/K28avahi
	ln -sf  ../init.d/avahi ${EXTDIR}/rc.d/rc2.d/S34avahi
	ln -sf  ../init.d/avahi ${EXTDIR}/rc.d/rc3.d/S34avahi
	ln -sf  ../init.d/avahi ${EXTDIR}/rc.d/rc4.d/S34avahi
	ln -sf  ../init.d/avahi ${EXTDIR}/rc.d/rc5.d/S34avahi
	ln -sf  ../init.d/avahi ${EXTDIR}/rc.d/rc6.d/K28avahi

install-bind: create-dirs
	install -m ${MODE} blfs/init.d/bind       ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/bind ${EXTDIR}/rc.d/rc0.d/K49bind
	ln -sf  ../init.d/bind ${EXTDIR}/rc.d/rc1.d/K49bind
	ln -sf  ../init.d/bind ${EXTDIR}/rc.d/rc2.d/K49bind
	ln -sf  ../init.d/bind ${EXTDIR}/rc.d/rc3.d/S22bind
	ln -sf  ../init.d/bind ${EXTDIR}/rc.d/rc4.d/S22bind
	ln -sf  ../init.d/bind ${EXTDIR}/rc.d/rc5.d/S22bind
	ln -sf  ../init.d/bind ${EXTDIR}/rc.d/rc6.d/K49bind

install-cups: create-dirs
	install -m ${MODE} blfs/init.d/cups       ${EXTDIR}/rc.d/init.d/
	rm -f ${EXTDIR}/rc.d/rc0.d/K36cups
	rm -f ${EXTDIR}/rc.d/rc2.d/K36cups
	rm -f ${EXTDIR}/rc.d/rc3.d/K36cups
	rm -f ${EXTDIR}/rc.d/rc5.d/K36cups
	rm -f ${EXTDIR}/rc.d/rc2.d/S81cups
	rm -f ${EXTDIR}/rc.d/rc3.d/S81cups
	rm -f ${EXTDIR}/rc.d/rc5.d/S81cups
	ln -sf  ../init.d/cups ${EXTDIR}/rc.d/rc0.d/K00cups
	ln -sf  ../init.d/cups ${EXTDIR}/rc.d/rc1.d/K00cups
	ln -sf  ../init.d/cups ${EXTDIR}/rc.d/rc2.d/S25cups
	ln -sf  ../init.d/cups ${EXTDIR}/rc.d/rc3.d/S25cups
	ln -sf  ../init.d/cups ${EXTDIR}/rc.d/rc4.d/S25cups
	ln -sf  ../init.d/cups ${EXTDIR}/rc.d/rc5.d/S25cups
	ln -sf  ../init.d/cups ${EXTDIR}/rc.d/rc6.d/K00cups

install-cyrus-sasl: create-dirs
	install -m ${MODE} blfs/init.d/cyrus-sasl ${EXTDIR}/rc.d/init.d/
	install -m ${CONFMODE} blfs/sysconfig/cyrus-sasl ${EXTDIR}/sysconfig/
	ln -sf  ../init.d/cyrus-sasl ${EXTDIR}/rc.d/rc0.d/K49cyrus-sasl
	ln -sf  ../init.d/cyrus-sasl ${EXTDIR}/rc.d/rc1.d/K49cyrus-sasl
	ln -sf  ../init.d/cyrus-sasl ${EXTDIR}/rc.d/rc2.d/S24cyrus-sasl
	ln -sf  ../init.d/cyrus-sasl ${EXTDIR}/rc.d/rc3.d/S24cyrus-sasl
	ln -sf  ../init.d/cyrus-sasl ${EXTDIR}/rc.d/rc4.d/S24cyrus-sasl
	ln -sf  ../init.d/cyrus-sasl ${EXTDIR}/rc.d/rc5.d/S24cyrus-sasl
	ln -sf  ../init.d/cyrus-sasl ${EXTDIR}/rc.d/rc6.d/K49cyrus-sasl

install-dbus: create-dirs
	install -m ${MODE} blfs/init.d/dbus ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/dbus ${EXTDIR}/rc.d/rc0.d/K30dbus
	ln -sf  ../init.d/dbus ${EXTDIR}/rc.d/rc1.d/K30dbus
	ln -sf  ../init.d/dbus ${EXTDIR}/rc.d/rc2.d/S29dbus
	ln -sf  ../init.d/dbus ${EXTDIR}/rc.d/rc3.d/S29dbus
	ln -sf  ../init.d/dbus ${EXTDIR}/rc.d/rc4.d/S29dbus
	ln -sf  ../init.d/dbus ${EXTDIR}/rc.d/rc5.d/S29dbus
	ln -sf  ../init.d/dbus ${EXTDIR}/rc.d/rc6.d/K30dbus

install-wicd: create-dirs
	install -m ${MODE} blfs/init.d/wicd ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/wicd ${EXTDIR}/rc.d/rc0.d/K20wicd
	ln -sf  ../init.d/wicd ${EXTDIR}/rc.d/rc1.d/K20wicd
	ln -sf  ../init.d/wicd ${EXTDIR}/rc.d/rc2.d/S30wicd
	ln -sf  ../init.d/wicd ${EXTDIR}/rc.d/rc3.d/S30wicd
	ln -sf  ../init.d/wicd ${EXTDIR}/rc.d/rc4.d/S30wicd
	ln -sf  ../init.d/wicd ${EXTDIR}/rc.d/rc5.d/S30wicd
	ln -sf  ../init.d/wicd ${EXTDIR}/rc.d/rc6.d/K20wicd
		
install-dhcp: create-dirs
	install -m ${MODE} blfs/init.d/dhcp       ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/dhcp ${EXTDIR}/rc.d/rc0.d/K30dhcp
	ln -sf  ../init.d/dhcp ${EXTDIR}/rc.d/rc1.d/K30dhcp
	ln -sf  ../init.d/dhcp ${EXTDIR}/rc.d/rc2.d/K30dhcp
	ln -sf  ../init.d/dhcp ${EXTDIR}/rc.d/rc3.d/S30dhcp
	ln -sf  ../init.d/dhcp ${EXTDIR}/rc.d/rc4.d/S30dhcp
	ln -sf  ../init.d/dhcp ${EXTDIR}/rc.d/rc5.d/S30dhcp
	ln -sf  ../init.d/dhcp ${EXTDIR}/rc.d/rc6.d/K30dhcp

install-exim: create-dirs
	install -m ${MODE} blfs/init.d/exim       ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/exim ${EXTDIR}/rc.d/rc0.d/K25exim
	ln -sf  ../init.d/exim ${EXTDIR}/rc.d/rc1.d/K25exim
	ln -sf  ../init.d/exim ${EXTDIR}/rc.d/rc2.d/K25exim
	ln -sf  ../init.d/exim ${EXTDIR}/rc.d/rc3.d/S35exim
	ln -sf  ../init.d/exim ${EXTDIR}/rc.d/rc4.d/S35exim
	ln -sf  ../init.d/exim ${EXTDIR}/rc.d/rc5.d/S35exim
	ln -sf  ../init.d/exim ${EXTDIR}/rc.d/rc6.d/K25exim

install-fcron: create-dirs
	install -m ${MODE} blfs/init.d/fcron      ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/fcron ${EXTDIR}/rc.d/rc0.d/K08fcron
	ln -sf  ../init.d/fcron ${EXTDIR}/rc.d/rc1.d/K08fcron
	ln -sf  ../init.d/fcron ${EXTDIR}/rc.d/rc2.d/S40fcron
	ln -sf  ../init.d/fcron ${EXTDIR}/rc.d/rc3.d/S40fcron
	ln -sf  ../init.d/fcron ${EXTDIR}/rc.d/rc4.d/S40fcron
	ln -sf  ../init.d/fcron ${EXTDIR}/rc.d/rc5.d/S40fcron
	ln -sf  ../init.d/fcron ${EXTDIR}/rc.d/rc6.d/K08fcron

install-gdm: create-dirs
	install -m ${MODE} blfs/init.d/gdm        ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/gdm ${EXTDIR}/rc.d/rc0.d/K05gdm
	ln -sf  ../init.d/gdm ${EXTDIR}/rc.d/rc1.d/K05gdm
	ln -sf  ../init.d/gdm ${EXTDIR}/rc.d/rc2.d/K05gdm
	ln -sf  ../init.d/gdm ${EXTDIR}/rc.d/rc3.d/K05gdm
	ln -sf  ../init.d/gdm ${EXTDIR}/rc.d/rc4.d/K05gdm
	ln -sf  ../init.d/gdm ${EXTDIR}/rc.d/rc5.d/S95gdm
	ln -sf  ../init.d/gdm ${EXTDIR}/rc.d/rc6.d/K05gdm

install-gpm: create-dirs
	install -m ${MODE} blfs/init.d/gpm        ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/gpm ${EXTDIR}/rc.d/rc0.d/K10gpm
	ln -sf  ../init.d/gpm ${EXTDIR}/rc.d/rc1.d/K10gpm
	ln -sf  ../init.d/gpm ${EXTDIR}/rc.d/rc2.d/S70gpm
	ln -sf  ../init.d/gpm ${EXTDIR}/rc.d/rc3.d/S70gpm
	ln -sf  ../init.d/gpm ${EXTDIR}/rc.d/rc4.d/S70gpm
	ln -sf  ../init.d/gpm ${EXTDIR}/rc.d/rc5.d/S70gpm
	ln -sf  ../init.d/gpm ${EXTDIR}/rc.d/rc6.d/K10gpm

install-heimdal: create-dirs
	install -m ${MODE} blfs/init.d/heimdal        ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/heimdal ${EXTDIR}/rc.d/rc0.d/K42heimdal
	ln -sf  ../init.d/heimdal ${EXTDIR}/rc.d/rc1.d/K42heimdal
	ln -sf  ../init.d/heimdal ${EXTDIR}/rc.d/rc2.d/K42heimdal
	ln -sf  ../init.d/heimdal ${EXTDIR}/rc.d/rc3.d/S28heimdal
	ln -sf  ../init.d/heimdal ${EXTDIR}/rc.d/rc4.d/S28heimdal
	ln -sf  ../init.d/heimdal ${EXTDIR}/rc.d/rc5.d/S28heimdal
	ln -sf  ../init.d/heimdal ${EXTDIR}/rc.d/rc6.d/K42heimdal

install-iptables: create-dirs
	install -m ${MODE} blfs/init.d/iptables        ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/iptables ${EXTDIR}/rc.d/rc3.d/S19iptables
	ln -sf  ../init.d/iptables ${EXTDIR}/rc.d/rc4.d/S19iptables
	ln -sf  ../init.d/iptables ${EXTDIR}/rc.d/rc5.d/S19iptables

install-kerberos: create-dirs
	install -m ${MODE} blfs/init.d/kerberos ${EXTDIR}/rc.d/init.d/
	ln -sf ../init.d/kerberos ${EXTDIR}/rc.d/rc0.d/K42kerberos
	ln -sf ../init.d/kerberos ${EXTDIR}/rc.d/rc1.d/K42kerberos
	ln -sf ../init.d/kerberos ${EXTDIR}/rc.d/rc2.d/K42kerberos
	ln -sf ../init.d/kerberos ${EXTDIR}/rc.d/rc3.d/S28kerberos
	ln -sf ../init.d/kerberos ${EXTDIR}/rc.d/rc4.d/S28kerberos
	ln -sf ../init.d/kerberos ${EXTDIR}/rc.d/rc5.d/S28kerberos
	ln -sf ../init.d/kerberos ${EXTDIR}/rc.d/rc6.d/K42kerberos

install-lprng: create-dirs
	install -m ${MODE} blfs/init.d/lprng      ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/lprng ${EXTDIR}/rc.d/rc0.d/K00lprng
	ln -sf  ../init.d/lprng ${EXTDIR}/rc.d/rc1.d/K00lprng
	ln -sf  ../init.d/lprng ${EXTDIR}/rc.d/rc2.d/S99lprng
	ln -sf  ../init.d/lprng ${EXTDIR}/rc.d/rc3.d/S99lprng
	ln -sf  ../init.d/lprng ${EXTDIR}/rc.d/rc4.d/S99lprng
	ln -sf  ../init.d/lprng ${EXTDIR}/rc.d/rc5.d/S99lprng
	ln -sf  ../init.d/lprng ${EXTDIR}/rc.d/rc6.d/K00lprng

install-mysql: create-dirs
	install -m ${MODE} blfs/init.d/mysql      ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/mysql ${EXTDIR}/rc.d/rc0.d/K26mysql
	ln -sf  ../init.d/mysql ${EXTDIR}/rc.d/rc1.d/K26mysql
	ln -sf  ../init.d/mysql ${EXTDIR}/rc.d/rc2.d/K26mysql
	ln -sf  ../init.d/mysql ${EXTDIR}/rc.d/rc3.d/S34mysql
	ln -sf  ../init.d/mysql ${EXTDIR}/rc.d/rc4.d/S34mysql
	ln -sf  ../init.d/mysql ${EXTDIR}/rc.d/rc5.d/S34mysql
	ln -sf  ../init.d/mysql ${EXTDIR}/rc.d/rc6.d/K26mysql

install-netfs: create-dirs
	install -m ${MODE} blfs/init.d/netfs      ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/netfs ${EXTDIR}/rc.d/rc0.d/K47netfs
	ln -sf  ../init.d/netfs ${EXTDIR}/rc.d/rc1.d/K47netfs
	ln -sf  ../init.d/netfs ${EXTDIR}/rc.d/rc2.d/K47netfs
	ln -sf  ../init.d/netfs ${EXTDIR}/rc.d/rc3.d/S28netfs
	ln -sf  ../init.d/netfs ${EXTDIR}/rc.d/rc4.d/S28netfs
	ln -sf  ../init.d/netfs ${EXTDIR}/rc.d/rc5.d/S28netfs
	ln -sf  ../init.d/netfs ${EXTDIR}/rc.d/rc6.d/K47netfs

install-networkmanager: create-dirs
	install -m ${MODE} blfs/init.d/networkmanager ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/networkmanager ${EXTDIR}/rc.d/rc0.d/K28networkmanager
	ln -sf  ../init.d/networkmanager ${EXTDIR}/rc.d/rc1.d/K28networkmanager
	ln -sf  ../init.d/networkmanager ${EXTDIR}/rc.d/rc2.d/S33networkmanager
	ln -sf  ../init.d/networkmanager ${EXTDIR}/rc.d/rc3.d/S33networkmanager
	ln -sf  ../init.d/networkmanager ${EXTDIR}/rc.d/rc4.d/S33networkmanager
	ln -sf  ../init.d/networkmanager ${EXTDIR}/rc.d/rc5.d/S33networkmanager
	ln -sf  ../init.d/networkmanager ${EXTDIR}/rc.d/rc6.d/K28networkmanager

install-nfs-client: create-dirs
	install -m ${MODE} blfs/init.d/nfs-client ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/nfs-client ${EXTDIR}/rc.d/rc0.d/K48nfs-client
	ln -sf  ../init.d/nfs-client ${EXTDIR}/rc.d/rc1.d/K48nfs-client
	ln -sf  ../init.d/nfs-client ${EXTDIR}/rc.d/rc2.d/K48nfs-client
	ln -sf  ../init.d/nfs-client ${EXTDIR}/rc.d/rc3.d/S24nfs-client
	ln -sf  ../init.d/nfs-client ${EXTDIR}/rc.d/rc4.d/S24nfs-client
	ln -sf  ../init.d/nfs-client ${EXTDIR}/rc.d/rc5.d/S24nfs-client
	ln -sf  ../init.d/nfs-client ${EXTDIR}/rc.d/rc6.d/K48nfs-client

install-nfs-server: create-dirs
	install -m ${MODE} blfs/init.d/nfs-server ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/nfs-server ${EXTDIR}/rc.d/rc0.d/K48nfs-server
	ln -sf  ../init.d/nfs-server ${EXTDIR}/rc.d/rc1.d/K48nfs-server
	ln -sf  ../init.d/nfs-server ${EXTDIR}/rc.d/rc2.d/K48nfs-server
	ln -sf  ../init.d/nfs-server ${EXTDIR}/rc.d/rc3.d/S24nfs-server
	ln -sf  ../init.d/nfs-server ${EXTDIR}/rc.d/rc4.d/S24nfs-server
	ln -sf  ../init.d/nfs-server ${EXTDIR}/rc.d/rc5.d/S24nfs-server
	ln -sf  ../init.d/nfs-server ${EXTDIR}/rc.d/rc6.d/K48nfs-server

install-ntpd: create-dirs
	install -m ${MODE} blfs/init.d/ntpd       ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/ntpd ${EXTDIR}/rc.d/rc0.d/K46ntpd
	ln -sf  ../init.d/ntpd ${EXTDIR}/rc.d/rc1.d/K46ntpd
	ln -sf  ../init.d/ntpd ${EXTDIR}/rc.d/rc2.d/K46ntpd
	ln -sf  ../init.d/ntpd ${EXTDIR}/rc.d/rc3.d/S26ntpd
	ln -sf  ../init.d/ntpd ${EXTDIR}/rc.d/rc4.d/S26ntpd
	ln -sf  ../init.d/ntpd ${EXTDIR}/rc.d/rc5.d/S26ntpd
	ln -sf  ../init.d/ntpd ${EXTDIR}/rc.d/rc6.d/K46ntpd

install-openldap: create-dirs
	install -m ${MODE} blfs/init.d/openldap ${EXTDIR}/rc.d/init.d/openldap
	install -m ${CONFMODE} blfs/sysconfig/openldap ${EXTDIR}/sysconfig/
	ln -sf  ../init.d/openldap ${EXTDIR}/rc.d/rc0.d/K46openldap
	ln -sf  ../init.d/openldap ${EXTDIR}/rc.d/rc1.d/K46openldap
	ln -sf  ../init.d/openldap ${EXTDIR}/rc.d/rc2.d/K46openldap
	ln -sf  ../init.d/openldap ${EXTDIR}/rc.d/rc3.d/S25openldap
	ln -sf  ../init.d/openldap ${EXTDIR}/rc.d/rc4.d/S25openldap
	ln -sf  ../init.d/openldap ${EXTDIR}/rc.d/rc5.d/S25openldap
	ln -sf  ../init.d/openldap ${EXTDIR}/rc.d/rc6.d/K46openldap

install-postfix: create-dirs
	install -m ${MODE} blfs/init.d/postfix    ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/postfix ${EXTDIR}/rc.d/rc0.d/K25postfix
	ln -sf  ../init.d/postfix ${EXTDIR}/rc.d/rc1.d/K25postfix
	ln -sf  ../init.d/postfix ${EXTDIR}/rc.d/rc2.d/K25postfix
	ln -sf  ../init.d/postfix ${EXTDIR}/rc.d/rc3.d/S35postfix
	ln -sf  ../init.d/postfix ${EXTDIR}/rc.d/rc4.d/S35postfix
	ln -sf  ../init.d/postfix ${EXTDIR}/rc.d/rc5.d/S35postfix
	ln -sf  ../init.d/postfix ${EXTDIR}/rc.d/rc6.d/K25postfix

install-postgresql: create-dirs
	install -m ${MODE} blfs/init.d/postgresql ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/postgresql ${EXTDIR}/rc.d/rc0.d/K26postgresql
	ln -sf  ../init.d/postgresql ${EXTDIR}/rc.d/rc1.d/K26postgresql
	ln -sf  ../init.d/postgresql ${EXTDIR}/rc.d/rc2.d/K26postgresql
	ln -sf  ../init.d/postgresql ${EXTDIR}/rc.d/rc3.d/S34postgresql
	ln -sf  ../init.d/postgresql ${EXTDIR}/rc.d/rc4.d/S34postgresql
	ln -sf  ../init.d/postgresql ${EXTDIR}/rc.d/rc5.d/S34postgresql
	ln -sf  ../init.d/postgresql ${EXTDIR}/rc.d/rc6.d/K26postgresql

install-proftpd: create-dirs
	install -m ${MODE} blfs/init.d/proftpd    ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/proftpd ${EXTDIR}/rc.d/rc0.d/K28proftpd
	ln -sf  ../init.d/proftpd ${EXTDIR}/rc.d/rc1.d/K28proftpd
	ln -sf  ../init.d/proftpd ${EXTDIR}/rc.d/rc2.d/K28proftpd
	ln -sf  ../init.d/proftpd ${EXTDIR}/rc.d/rc3.d/S32proftpd
	ln -sf  ../init.d/proftpd ${EXTDIR}/rc.d/rc4.d/S32proftpd
	ln -sf  ../init.d/proftpd ${EXTDIR}/rc.d/rc5.d/S32proftpd
	ln -sf  ../init.d/proftpd ${EXTDIR}/rc.d/rc6.d/K28proftpd

install-random: create-dirs
	install -m ${MODE} blfs/init.d/random     ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/random ${EXTDIR}/rc.d/rc0.d/K45random
	ln -sf  ../init.d/random ${EXTDIR}/rc.d/rc1.d/S25random
	ln -sf  ../init.d/random ${EXTDIR}/rc.d/rc2.d/S25random
	ln -sf  ../init.d/random ${EXTDIR}/rc.d/rc3.d/S25random
	ln -sf  ../init.d/random ${EXTDIR}/rc.d/rc4.d/S25random
	ln -sf  ../init.d/random ${EXTDIR}/rc.d/rc5.d/S25random
	ln -sf  ../init.d/random ${EXTDIR}/rc.d/rc6.d/K45random

install-rpcbind: create-dirs
	install -m ${MODE} blfs/init.d/rpcbind    ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/rpcbind ${EXTDIR}/rc.d/rc0.d/K49rpcbind
	ln -sf  ../init.d/rpcbind ${EXTDIR}/rc.d/rc1.d/K49rpcbind
	ln -sf  ../init.d/rpcbind ${EXTDIR}/rc.d/rc2.d/K49rpcbind
	ln -sf  ../init.d/rpcbind ${EXTDIR}/rc.d/rc3.d/S22rpcbind
	ln -sf  ../init.d/rpcbind ${EXTDIR}/rc.d/rc4.d/S22rpcbind
	ln -sf  ../init.d/rpcbind ${EXTDIR}/rc.d/rc5.d/S22rpcbind
	ln -sf  ../init.d/rpcbind ${EXTDIR}/rc.d/rc6.d/K49rpcbind

install-rsyncd: create-dirs
	install -m ${MODE} blfs/init.d/rsyncd     ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/rsyncd ${EXTDIR}/rc.d/rc0.d/K30rsyncd
	ln -sf  ../init.d/rsyncd ${EXTDIR}/rc.d/rc1.d/K30rsyncd
	ln -sf  ../init.d/rsyncd ${EXTDIR}/rc.d/rc2.d/K30rsyncd
	ln -sf  ../init.d/rsyncd ${EXTDIR}/rc.d/rc3.d/S30rsyncd
	ln -sf  ../init.d/rsyncd ${EXTDIR}/rc.d/rc4.d/S30rsyncd
	ln -sf  ../init.d/rsyncd ${EXTDIR}/rc.d/rc5.d/S30rsyncd
	ln -sf  ../init.d/rsyncd ${EXTDIR}/rc.d/rc6.d/K30rsyncd

install-samba: create-dirs
	install -m ${MODE} blfs/init.d/samba      ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/samba ${EXTDIR}/rc.d/rc0.d/K48samba
	ln -sf  ../init.d/samba ${EXTDIR}/rc.d/rc1.d/K48samba
	ln -sf  ../init.d/samba ${EXTDIR}/rc.d/rc2.d/K48samba
	ln -sf  ../init.d/samba ${EXTDIR}/rc.d/rc3.d/S45samba
	ln -sf  ../init.d/samba ${EXTDIR}/rc.d/rc4.d/S45samba
	ln -sf  ../init.d/samba ${EXTDIR}/rc.d/rc5.d/S45samba
	ln -sf  ../init.d/samba ${EXTDIR}/rc.d/rc6.d/K48samba

install-swat: create-dirs
	install -m ${MODE} blfs/init.d/swat      ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/swat ${EXTDIR}/rc.d/rc0.d/K47swat
	ln -sf  ../init.d/swat ${EXTDIR}/rc.d/rc1.d/K47swat
	ln -sf  ../init.d/swat ${EXTDIR}/rc.d/rc2.d/K47swat
	ln -sf  ../init.d/swat ${EXTDIR}/rc.d/rc3.d/S46swat
	ln -sf  ../init.d/swat ${EXTDIR}/rc.d/rc4.d/S46swat
	ln -sf  ../init.d/swat ${EXTDIR}/rc.d/rc5.d/S46swat
	ln -sf  ../init.d/swat ${EXTDIR}/rc.d/rc6.d/K47swat

install-sendmail: create-dirs
	install -m ${MODE} blfs/init.d/sendmail   ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/sendmail ${EXTDIR}/rc.d/rc0.d/K25sendmail
	ln -sf  ../init.d/sendmail ${EXTDIR}/rc.d/rc1.d/K25sendmail
	ln -sf  ../init.d/sendmail ${EXTDIR}/rc.d/rc2.d/K25sendmail
	ln -sf  ../init.d/sendmail ${EXTDIR}/rc.d/rc3.d/S35sendmail
	ln -sf  ../init.d/sendmail ${EXTDIR}/rc.d/rc4.d/S35sendmail
	ln -sf  ../init.d/sendmail ${EXTDIR}/rc.d/rc5.d/S35sendmail
	ln -sf  ../init.d/sendmail ${EXTDIR}/rc.d/rc6.d/K25sendmail

install-soprano: create-dirs
	install -m ${MODE} blfs/init.d/soprano    ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/soprano ${EXTDIR}/rc.d/rc0.d/K35soprano
	ln -sf  ../init.d/soprano ${EXTDIR}/rc.d/rc1.d/K35soprano
	ln -sf  ../init.d/soprano ${EXTDIR}/rc.d/rc2.d/K35soprano
	ln -sf  ../init.d/soprano ${EXTDIR}/rc.d/rc3.d/S48soprano
	ln -sf  ../init.d/soprano ${EXTDIR}/rc.d/rc4.d/S48soprano
	ln -sf  ../init.d/soprano ${EXTDIR}/rc.d/rc5.d/S48soprano
	ln -sf  ../init.d/soprano ${EXTDIR}/rc.d/rc6.d/K35soprano

install-qpopper: create-dirs
	install -m ${MODE} blfs/init.d/qpopper   ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/qpopper ${EXTDIR}/rc.d/rc0.d/K23qpopper
	ln -sf  ../init.d/qpopper ${EXTDIR}/rc.d/rc1.d/K23qpopper
	ln -sf  ../init.d/qpopper ${EXTDIR}/rc.d/rc2.d/K23qpopper
	ln -sf  ../init.d/qpopper ${EXTDIR}/rc.d/rc3.d/S37qpopper
	ln -sf  ../init.d/qpopper ${EXTDIR}/rc.d/rc4.d/S37qpopper
	ln -sf  ../init.d/qpopper ${EXTDIR}/rc.d/rc5.d/S37qpopper
	ln -sf  ../init.d/qpopper ${EXTDIR}/rc.d/rc6.d/K23qpopper

install-sshd: create-dirs
	install -m ${MODE} blfs/init.d/sshd       ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/sshd ${EXTDIR}/rc.d/rc0.d/K30sshd
	ln -sf  ../init.d/sshd ${EXTDIR}/rc.d/rc1.d/K30sshd
	ln -sf  ../init.d/sshd ${EXTDIR}/rc.d/rc2.d/K30sshd
	ln -sf  ../init.d/sshd ${EXTDIR}/rc.d/rc3.d/S30sshd
	ln -sf  ../init.d/sshd ${EXTDIR}/rc.d/rc4.d/S30sshd
	ln -sf  ../init.d/sshd ${EXTDIR}/rc.d/rc5.d/S30sshd
	ln -sf  ../init.d/sshd ${EXTDIR}/rc.d/rc6.d/K30sshd

install-stunnel: create-dirs
	install -m ${MODE} blfs/init.d/stunnel    ${EXTDIR}/rc.d/init.d/
	ln -sf  ../init.d/stunnel ${EXTDIR}/rc.d/rc0.d/K47stunnel
	ln -sf  ../init.d/stunnel ${EXTDIR}/rc.d/rc1.d/K47stunnel
	ln -sf  ../init.d/stunnel ${EXTDIR}/rc.d/rc2.d/K47stunnel
	ln -sf  ../init.d/stunnel ${EXTDIR}/rc.d/rc3.d/S55stunnel
	ln -sf  ../init.d/stunnel ${EXTDIR}/rc.d/rc4.d/S55stunnel
	ln -sf  ../init.d/stunnel ${EXTDIR}/rc.d/rc5.d/S55stunnel
	ln -sf  ../init.d/stunnel ${EXTDIR}/rc.d/rc6.d/K47stunnel

install-svn: create-dirs
	install -m ${MODE} blfs/init.d/svn        ${EXTDIR}/rc.d/init.d/
	ln -sf ../init.d/svn ${EXTDIR}/rc.d/rc0.d/K27svn
	ln -sf ../init.d/svn ${EXTDIR}/rc.d/rc1.d/K27svn
	ln -sf ../init.d/svn ${EXTDIR}/rc.d/rc2.d/K27svn
	ln -sf ../init.d/svn ${EXTDIR}/rc.d/rc3.d/S33svn
	ln -sf ../init.d/svn ${EXTDIR}/rc.d/rc4.d/S33svn
	ln -sf ../init.d/svn ${EXTDIR}/rc.d/rc5.d/S33svn
	ln -sf ../init.d/svn ${EXTDIR}/rc.d/rc6.d/K27svn

install-sysstat: create-dirs
	install -m ${MODE} blfs/init.d/sysstat    ${EXTDIR}/rc.d/init.d/
	ln -sf ../init.d/sysstat ${EXTDIR}/rc.d/rcS.d/S85sysstat

install-virtuoso: create-dirs
	install -m ${MODE} blfs/init.d/virtuoso   ${EXTDIR}/rc.d/init.d/
	ln -sf ../init.d/virtuoso ${EXTDIR}/rc.d/rc0.d/K40virtuoso
	ln -sf ../init.d/virtuoso ${EXTDIR}/rc.d/rc1.d/K40virtuoso
	ln -sf ../init.d/virtuoso ${EXTDIR}/rc.d/rc2.d/K40virtuoso
	ln -sf ../init.d/virtuoso ${EXTDIR}/rc.d/rc3.d/S47virtuoso
	ln -sf ../init.d/virtuoso ${EXTDIR}/rc.d/rc4.d/S47virtuoso
	ln -sf ../init.d/virtuoso ${EXTDIR}/rc.d/rc5.d/S47virtuoso
	ln -sf ../init.d/virtuoso ${EXTDIR}/rc.d/rc6.d/K40virtuoso

install-vsftpd: create-dirs
	install -m ${MODE} blfs/init.d/vsftpd     ${EXTDIR}/rc.d/init.d/
	ln -sf ../init.d/vsftpd ${EXTDIR}/rc.d/rc0.d/K28vsftpd
	ln -sf ../init.d/vsftpd ${EXTDIR}/rc.d/rc1.d/K28vsftpd
	ln -sf ../init.d/vsftpd ${EXTDIR}/rc.d/rc2.d/K28vsftpd
	ln -sf ../init.d/vsftpd ${EXTDIR}/rc.d/rc3.d/S32vsftpd
	ln -sf ../init.d/vsftpd ${EXTDIR}/rc.d/rc4.d/S32vsftpd
	ln -sf ../init.d/vsftpd ${EXTDIR}/rc.d/rc5.d/S32vsftpd
	ln -sf ../init.d/vsftpd ${EXTDIR}/rc.d/rc6.d/K28vsftpd

uninstall-atd: 
	rm -f ${EXTDIR}/rc.d/init.d/atd
	rm -f ${EXTDIR}/rc.d/rc0.d/K29atd
	rm -f ${EXTDIR}/rc.d/rc1.d/K29atd
	rm -f ${EXTDIR}/rc.d/rc2.d/S68atd
	rm -f ${EXTDIR}/rc.d/rc3.d/S68atd
	rm -f ${EXTDIR}/rc.d/rc4.d/S68atd
	rm -f ${EXTDIR}/rc.d/rc5.d/S68atd
	rm -f ${EXTDIR}/rc.d/rc6.d/K29atd

uninstall-alsa:
	rm -f ${EXTDIR}/rc.d/init.d/alsa
	rm -f ${EXTDIR}/rc.d/rc0.d/K35alsa
	rm -f ${EXTDIR}/rc.d/rc1.d/K35alsa
	rm -f ${EXTDIR}/rc.d/rc6.d/K35alsa

uninstall-httpd:
	rm -f ${EXTDIR}/rc.d/init.d/httpd
	rm -f ${EXTDIR}/rc.d/rc0.d/K28httpd
	rm -f ${EXTDIR}/rc.d/rc1.d/K28httpd
	rm -f ${EXTDIR}/rc.d/rc2.d/K28httpd
	rm -f ${EXTDIR}/rc.d/rc3.d/S32httpd
	rm -f ${EXTDIR}/rc.d/rc4.d/S32httpd
	rm -f ${EXTDIR}/rc.d/rc5.d/S32httpd
	rm -f ${EXTDIR}/rc.d/rc6.d/K28httpd

uninstall-autofs:
	rm -f $(EXTDIR)/rc.d/init.d/autofs
	rm -f $(EXTDIR)/rc.d/rcS.d/S52autofs

uninstall-avahi:
	rm -f ${EXTDIR}/rc.d/init.d/avahi
	rm -f ${EXTDIR}/rc.d/rc0.d/K28avahi
	rm -f ${EXTDIR}/rc.d/rc1.d/K28avahi
	rm -f ${EXTDIR}/rc.d/rc2.d/S34avahi
	rm -f ${EXTDIR}/rc.d/rc3.d/S34avahi
	rm -f ${EXTDIR}/rc.d/rc4.d/S34avahi
	rm -f ${EXTDIR}/rc.d/rc5.d/S34avahi
	rm -f ${EXTDIR}/rc.d/rc6.d/K28avahi

uninstall-bind:
	rm -f ${EXTDIR}/rc.d/init.d/bind
	rm -f ${EXTDIR}/rc.d/rc0.d/K49bind
	rm -f ${EXTDIR}/rc.d/rc1.d/K49bind
	rm -f ${EXTDIR}/rc.d/rc2.d/K49bind
	rm -f ${EXTDIR}/rc.d/rc3.d/S22bind
	rm -f ${EXTDIR}/rc.d/rc4.d/S22bind
	rm -f ${EXTDIR}/rc.d/rc5.d/S22bind
	rm -f ${EXTDIR}/rc.d/rc6.d/K49bind

uninstall-cups:
	rm -f ${EXTDIR}/rc.d/init.d/cups
	rm -f ${EXTDIR}/rc.d/rc0.d/K00cups
	rm -f ${EXTDIR}/rc.d/rc1.d/K00cups
	rm -f ${EXTDIR}/rc.d/rc2.d/S25cups
	rm -f ${EXTDIR}/rc.d/rc3.d/S25cups
	rm -f ${EXTDIR}/rc.d/rc4.d/S25cups
	rm -f ${EXTDIR}/rc.d/rc5.d/S25cups
	rm -f ${EXTDIR}/rc.d/rc6.d/K00cups

uninstall-cyrus-sasl:
	rm -f ${EXTDIR}/rc.d/init.d/cyrus-sasl
	rm -f ${EXTDIR}/rc.d/rc0.d/K49cyrus-sasl
	rm -f ${EXTDIR}/rc.d/rc1.d/K49cyrus-sasl
	rm -f ${EXTDIR}/rc.d/rc2.d/S24cyrus-sasl
	rm -f ${EXTDIR}/rc.d/rc3.d/S24cyrus-sasl
	rm -f ${EXTDIR}/rc.d/rc4.d/S24cyrus-sasl
	rm -f ${EXTDIR}/rc.d/rc5.d/S24cyrus-sasl
	rm -f ${EXTDIR}/rc.d/rc6.d/K49cyrus-sasl

uninstall-dhcp:
	rm -f ${EXTDIR}/rc.d/init.d/dhcp
	rm -f ${EXTDIR}/rc.d/rc0.d/K30dhcp
	rm -f ${EXTDIR}/rc.d/rc1.d/K30dhcp
	rm -f ${EXTDIR}/rc.d/rc2.d/K30dhcp
	rm -f ${EXTDIR}/rc.d/rc3.d/S30dhcp
	rm -f ${EXTDIR}/rc.d/rc4.d/S30dhcp
	rm -f ${EXTDIR}/rc.d/rc5.d/S30dhcp
	rm -f ${EXTDIR}/rc.d/rc6.d/K30dhcp

uninstall-exim:
	rm -f ${EXTDIR}/rc.d/init.d/exim
	rm -f ${EXTDIR}/rc.d/rc0.d/K25exim
	rm -f ${EXTDIR}/rc.d/rc1.d/K25exim
	rm -f ${EXTDIR}/rc.d/rc2.d/K25exim
	rm -f ${EXTDIR}/rc.d/rc3.d/S35exim
	rm -f ${EXTDIR}/rc.d/rc4.d/S35exim
	rm -f ${EXTDIR}/rc.d/rc5.d/S35exim
	rm -f ${EXTDIR}/rc.d/rc6.d/K25exim

uninstall-fcron:
	rm -f ${EXTDIR}/rc.d/init.d/fcron
	rm -f ${EXTDIR}/rc.d/rc0.d/K08fcron
	rm -f ${EXTDIR}/rc.d/rc1.d/K08fcron
	rm -f ${EXTDIR}/rc.d/rc2.d/S40fcron
	rm -f ${EXTDIR}/rc.d/rc3.d/S40fcron
	rm -f ${EXTDIR}/rc.d/rc4.d/S40fcron
	rm -f ${EXTDIR}/rc.d/rc5.d/S40fcron
	rm -f ${EXTDIR}/rc.d/rc6.d/K08fcron

uninstall-gdm:
	rm -f ${EXTDIR}/rc.d/init.d/gdm
	rm -f ${EXTDIR}/rc.d/rc0.d/K05gdm
	rm -f ${EXTDIR}/rc.d/rc1.d/K05gdm
	rm -f ${EXTDIR}/rc.d/rc2.d/K05gdm
	rm -f ${EXTDIR}/rc.d/rc3.d/K05gdm
	rm -f ${EXTDIR}/rc.d/rc4.d/K05gdm
	rm -f ${EXTDIR}/rc.d/rc5.d/S95gdm
	rm -f ${EXTDIR}/rc.d/rc6.d/K05gdm

uninstall-gpm:
	rm -f ${EXTDIR}/rc.d/init.d/gpm
	rm -f ${EXTDIR}/rc.d/rc0.d/K10gpm
	rm -f ${EXTDIR}/rc.d/rc1.d/K10gpm
	rm -f ${EXTDIR}/rc.d/rc2.d/S70gpm
	rm -f ${EXTDIR}/rc.d/rc3.d/S70gpm
	rm -f ${EXTDIR}/rc.d/rc4.d/S70gpm
	rm -f ${EXTDIR}/rc.d/rc5.d/S70gpm
	rm -f ${EXTDIR}/rc.d/rc6.d/K10gpm

uninstall-heimdal:
	rm -f ${EXTDIR}/rc.d/init.d/heimdal
	rm -f ${EXTDIR}/rc.d/rc0.d/K42heimdal
	rm -f ${EXTDIR}/rc.d/rc1.d/K42heimdal
	rm -f ${EXTDIR}/rc.d/rc2.d/K42heimdal
	rm -f ${EXTDIR}/rc.d/rc3.d/S28heimdal
	rm -f ${EXTDIR}/rc.d/rc4.d/S28heimdal
	rm -f ${EXTDIR}/rc.d/rc5.d/S28heimdal
	rm -f ${EXTDIR}/rc.d/rc6.d/K42heimdal

uninstall-iptables:
	rm -f ${EXTDIR}/rc.d/init.d/iptables
	rm -f ${EXTDIR}/rc.d/rc3.d/S19iptables
	rm -f ${EXTDIR}/rc.d/rc4.d/S19iptables
	rm -f ${EXTDIR}/rc.d/rc5.d/S19iptables

uninstall-kerberos:
	rm -f ${EXTDIR}/rc.d/init.d/kerberos
	rm -f ${EXTDIR}/rc.d/rc0.d/K42kerberos
	rm -f ${EXTDIR}/rc.d/rc1.d/K42kerberos
	rm -f ${EXTDIR}/rc.d/rc2.d/K42kerberos
	rm -f ${EXTDIR}/rc.d/rc3.d/S28kerberos
	rm -f ${EXTDIR}/rc.d/rc4.d/S28kerberos
	rm -f ${EXTDIR}/rc.d/rc5.d/S28kerberos
	rm -f ${EXTDIR}/rc.d/rc6.d/K42kerberos

uninstall-lprng:
	rm -f ${EXTDIR}/rc.d/init.d/lprng
	rm -f ${EXTDIR}/rc.d/rc0.d/K00lprng
	rm -f ${EXTDIR}/rc.d/rc1.d/K00lprng
	rm -f ${EXTDIR}/rc.d/rc2.d/S99lprng
	rm -f ${EXTDIR}/rc.d/rc3.d/S99lprng
	rm -f ${EXTDIR}/rc.d/rc4.d/S99lprng
	rm -f ${EXTDIR}/rc.d/rc5.d/S99lprng
	rm -f ${EXTDIR}/rc.d/rc6.d/K00lprng

uninstall-mysql:
	rm -f ${EXTDIR}/rc.d/init.d/mysql
	rm -f ${EXTDIR}/rc.d/rc0.d/K26mysql
	rm -f ${EXTDIR}/rc.d/rc1.d/K26mysql
	rm -f ${EXTDIR}/rc.d/rc2.d/K26mysql
	rm -f ${EXTDIR}/rc.d/rc3.d/S34mysql
	rm -f ${EXTDIR}/rc.d/rc4.d/S34mysql
	rm -f ${EXTDIR}/rc.d/rc5.d/S34mysql
	rm -f ${EXTDIR}/rc.d/rc6.d/K26mysql

uninstall-netfs:
	rm -f ${EXTDIR}/rc.d/init.d/netfs
	rm -f ${EXTDIR}/rc.d/rc0.d/K47netfs
	rm -f ${EXTDIR}/rc.d/rc1.d/K47netfs
	rm -f ${EXTDIR}/rc.d/rc2.d/K47netfs
	rm -f ${EXTDIR}/rc.d/rc3.d/S28netfs
	rm -f ${EXTDIR}/rc.d/rc4.d/S28netfs
	rm -f ${EXTDIR}/rc.d/rc5.d/S28netfs
	rm -f ${EXTDIR}/rc.d/rc6.d/K47netfs

uninstall-networkmanager:
	rm -f ${EXTDIR}/rc.d/init.d/networkmanager
	rm -f ${EXTDIR}/rc.d/rc0.d/K28networkmanager
	rm -f ${EXTDIR}/rc.d/rc1.d/K28networkmanager
	rm -f ${EXTDIR}/rc.d/rc2.d/S33networkmanager
	rm -f ${EXTDIR}/rc.d/rc3.d/S33networkmanager
	rm -f ${EXTDIR}/rc.d/rc4.d/S33networkmanager
	rm -f ${EXTDIR}/rc.d/rc5.d/S33networkmanager
	rm -f ${EXTDIR}/rc.d/rc6.d/K28networkmanager

uninstall-nfs-client:
	rm -f ${EXTDIR}/rc.d/init.d/nfs-client
	rm -f ${EXTDIR}/rc.d/rc0.d/K48nfs-client
	rm -f ${EXTDIR}/rc.d/rc1.d/K48nfs-client
	rm -f ${EXTDIR}/rc.d/rc2.d/K48nfs-client
	rm -f ${EXTDIR}/rc.d/rc3.d/S24nfs-client
	rm -f ${EXTDIR}/rc.d/rc4.d/S24nfs-client
	rm -f ${EXTDIR}/rc.d/rc5.d/S24nfs-client
	rm -f ${EXTDIR}/rc.d/rc6.d/K48nfs-client

uninstall-nfs-server:
	rm -f ${EXTDIR}/rc.d/init.d/nfs-server
	rm -f ${EXTDIR}/rc.d/rc0.d/K48nfs-server
	rm -f ${EXTDIR}/rc.d/rc1.d/K48nfs-server
	rm -f ${EXTDIR}/rc.d/rc2.d/K48nfs-server
	rm -f ${EXTDIR}/rc.d/rc3.d/S24nfs-server
	rm -f ${EXTDIR}/rc.d/rc4.d/S24nfs-server
	rm -f ${EXTDIR}/rc.d/rc5.d/S24nfs-server
	rm -f ${EXTDIR}/rc.d/rc6.d/K48nfs-server

uninstall-ntp:
	rm -f ${EXTDIR}/rc.d/init.d/ntp
	rm -f ${EXTDIR}/rc.d/rc0.d/K46ntp
	rm -f ${EXTDIR}/rc.d/rc1.d/K46ntp
	rm -f ${EXTDIR}/rc.d/rc2.d/K46ntp
	rm -f ${EXTDIR}/rc.d/rc3.d/S26ntp
	rm -f ${EXTDIR}/rc.d/rc4.d/S26ntp
	rm -f ${EXTDIR}/rc.d/rc5.d/S26ntp
	rm -f ${EXTDIR}/rc.d/rc6.d/K46ntp

uninstall-openldap1:
	rm -f ${EXTDIR}/rc.d/init.d/openldap
	rm -f ${EXTDIR}/rc.d/rc0.d/K46openldap
	rm -f ${EXTDIR}/rc.d/rc1.d/K46openldap
	rm -f ${EXTDIR}/rc.d/rc2.d/K46openldap
	rm -f ${EXTDIR}/rc.d/rc3.d/S25openldap
	rm -f ${EXTDIR}/rc.d/rc4.d/S25openldap
	rm -f ${EXTDIR}/rc.d/rc5.d/S25openldap
	rm -f ${EXTDIR}/rc.d/rc6.d/K46openldap

uninstall-postfix:
	rm -f ${EXTDIR}/rc.d/init.d/postfix
	rm -f ${EXTDIR}/rc.d/rc0.d/K25postfix
	rm -f ${EXTDIR}/rc.d/rc1.d/K25postfix
	rm -f ${EXTDIR}/rc.d/rc2.d/K25postfix
	rm -f ${EXTDIR}/rc.d/rc3.d/S35postfix
	rm -f ${EXTDIR}/rc.d/rc4.d/S35postfix
	rm -f ${EXTDIR}/rc.d/rc5.d/S35postfix
	rm -f ${EXTDIR}/rc.d/rc6.d/K25postfix

uninstall-postgresql:
	rm -f ${EXTDIR}/rc.d/init.d/postgresql
	rm -f ${EXTDIR}/rc.d/rc0.d/K26postgresql
	rm -f ${EXTDIR}/rc.d/rc1.d/K26postgresql
	rm -f ${EXTDIR}/rc.d/rc2.d/K26postgresql
	rm -f ${EXTDIR}/rc.d/rc3.d/S34postgresql
	rm -f ${EXTDIR}/rc.d/rc4.d/S34postgresql
	rm -f ${EXTDIR}/rc.d/rc5.d/S34postgresql
	rm -f ${EXTDIR}/rc.d/rc6.d/K26postgresql

uninstall-proftpd:
	rm -f ${EXTDIR}/rc.d/init.d/proftpd
	rm -f ${EXTDIR}/rc.d/rc0.d/K28proftpd
	rm -f ${EXTDIR}/rc.d/rc1.d/K28proftpd
	rm -f ${EXTDIR}/rc.d/rc2.d/K28proftpd
	rm -f ${EXTDIR}/rc.d/rc3.d/S32proftpd
	rm -f ${EXTDIR}/rc.d/rc4.d/S32proftpd
	rm -f ${EXTDIR}/rc.d/rc5.d/S32proftpd
	rm -f ${EXTDIR}/rc.d/rc6.d/K28proftpd

uninstall-random:
	rm -f ${EXTDIR}/rc.d/init.d/random
	rm -f ${EXTDIR}/rc.d/rc0.d/K45random
	rm -f ${EXTDIR}/rc.d/rc1.d/S25random
	rm -f ${EXTDIR}/rc.d/rc2.d/S25random
	rm -f ${EXTDIR}/rc.d/rc3.d/S25random
	rm -f ${EXTDIR}/rc.d/rc4.d/S25random
	rm -f ${EXTDIR}/rc.d/rc5.d/S25random
	rm -f ${EXTDIR}/rc.d/rc6.d/K45random

uninstall-rsyncd:
	rm -f ${EXTDIR}/rc.d/init.d/rsyncd
	rm -f ${EXTDIR}/rc.d/rc0.d/K30rsyncd
	rm -f ${EXTDIR}/rc.d/rc1.d/K30rsyncd
	rm -f ${EXTDIR}/rc.d/rc2.d/K30rsyncd
	rm -f ${EXTDIR}/rc.d/rc3.d/S30rsyncd
	rm -f ${EXTDIR}/rc.d/rc4.d/S30rsyncd
	rm -f ${EXTDIR}/rc.d/rc5.d/S30rsyncd
	rm -f ${EXTDIR}/rc.d/rc6.d/K30rsyncd

uninstall-samba:
	rm -f ${EXTDIR}/rc.d/init.d/samba
	rm -f ${EXTDIR}/rc.d/rc0.d/K48samba
	rm -f ${EXTDIR}/rc.d/rc1.d/K48samba
	rm -f ${EXTDIR}/rc.d/rc2.d/K48samba
	rm -f ${EXTDIR}/rc.d/rc3.d/S45samba
	rm -f ${EXTDIR}/rc.d/rc4.d/S45samba
	rm -f ${EXTDIR}/rc.d/rc5.d/S45samba
	rm -f ${EXTDIR}/rc.d/rc6.d/K48samba

uninstall-sendmail:
	rm -f ${EXTDIR}/rc.d/init.d/sendmail
	rm -f ${EXTDIR}/rc.d/rc0.d/K25sendmail
	rm -f ${EXTDIR}/rc.d/rc1.d/K25sendmail
	rm -f ${EXTDIR}/rc.d/rc2.d/K25sendmail
	rm -f ${EXTDIR}/rc.d/rc3.d/S35sendmail
	rm -f ${EXTDIR}/rc.d/rc4.d/S35sendmail
	rm -f ${EXTDIR}/rc.d/rc5.d/S35sendmail
	rm -f ${EXTDIR}/rc.d/rc6.d/K25sendmail

uninstall-sshd:
	rm -f ${EXTDIR}/rc.d/init.d/sshd
	rm -f ${EXTDIR}/rc.d/rc0.d/K30sshd
	rm -f ${EXTDIR}/rc.d/rc1.d/K30sshd
	rm -f ${EXTDIR}/rc.d/rc2.d/K30sshd
	rm -f ${EXTDIR}/rc.d/rc3.d/S30sshd
	rm -f ${EXTDIR}/rc.d/rc4.d/S30sshd
	rm -f ${EXTDIR}/rc.d/rc5.d/S30sshd
	rm -f ${EXTDIR}/rc.d/rc6.d/K30sshd

uninstall-stunnel:
	rm -f ${EXTDIR}/rc.d/init.d/stunnel
	rm -f ${EXTDIR}/rc.d/rc0.d/K47stunnel
	rm -f ${EXTDIR}/rc.d/rc1.d/K47stunnel
	rm -f ${EXTDIR}/rc.d/rc2.d/K47stunnel
	rm -f ${EXTDIR}/rc.d/rc3.d/S55stunnel
	rm -f ${EXTDIR}/rc.d/rc4.d/S55stunnel
	rm -f ${EXTDIR}/rc.d/rc5.d/S55stunnel
	rm -f ${EXTDIR}/rc.d/rc6.d/K47stunnel

uninstall-svn:
	rm -f ${EXTDIR}/rc.d/init.d/svn
	rm -f ${EXTDIR}/rc.d/rc0.d/K27svn
	rm -f ${EXTDIR}/rc.d/rc1.d/K27svn
	rm -f ${EXTDIR}/rc.d/rc2.d/K27svn
	rm -f ${EXTDIR}/rc.d/rc3.d/S33svn
	rm -f ${EXTDIR}/rc.d/rc4.d/S33svn
	rm -f ${EXTDIR}/rc.d/rc5.d/S33svn
	rm -f ${EXTDIR}/rc.d/rc6.d/K27svn

uninstall-sysstat:
	rm -f ${EXTDIR}/rc.d/init.d/sysstat
	rm -f ${EXTDIR}/rc.d/rcS.d/S85sysstat

uninstall-virtuoso:
	rm -f ${EXTDIR}/rc.d/init.d/virtuoso
	rm -f ${EXTDIR}/rc.d/rc0.d/K40virtuoso
	rm -f ${EXTDIR}/rc.d/rc1.d/K40virtuoso
	rm -f ${EXTDIR}/rc.d/rc2.d/K40virtuoso
	rm -f ${EXTDIR}/rc.d/rc3.d/S47virtuoso
	rm -f ${EXTDIR}/rc.d/rc4.d/S47virtuoso
	rm -f ${EXTDIR}/rc.d/rc5.d/S47virtuoso
	rm -f ${EXTDIR}/rc.d/rc6.d/K40virtuoso

uninstall-vsftpd:
	rm -f ${EXTDIR}/rc.d/init.d/vsftpd
	rm -f ${EXTDIR}/rc.d/rc0.d/K28vsftpd
	rm -f ${EXTDIR}/rc.d/rc1.d/K28vsftpd
	rm -f ${EXTDIR}/rc.d/rc2.d/K28vsftpd
	rm -f ${EXTDIR}/rc.d/rc3.d/S32vsftpd
	rm -f ${EXTDIR}/rc.d/rc4.d/S32vsftpd
	rm -f ${EXTDIR}/rc.d/rc5.d/S32vsftpd
	rm -f ${EXTDIR}/rc.d/rc6.d/K28vsftpd

.PHONY: all create-dirs create-service-dir \
	install-service-dhclient \
	install-service-dhcpcd \
	install-service-ipx \
	install-service-pppoe \
	install-atd \
	install-alsa \
	install-avahi \
	install-httpd \
	install-bind \
	install-cups \
	install-cyrus-sasl \
	install-dhcp \
	install-exim \
	install-fcron \
	install-gdm \
	install-gpm \
	install-heimdal \
	install-iptables \
	install-kerberos \
	install-lprng \
	install-mysql \
	install-netfs \
	install-nfs-client \
	install-nfs-server \
	install-ntp \
	install-openldap1 \
	install-postfix \
	install-postgresql \
	install-proftpd \
	install-random \
	install-rsync \
	install-samba \
	install-sendmail \
	install-soprano \
	install-sshd \
	install-stunnel \
	install-svn \
	install-sysstat \
	install-vsftpd \
	install-virtuoso \
	uninstall-atd \
	uninstall-alsa \
	uninstall-avahi \
	uninstall-httpd \
	uninstall-bind \
	uninstall-cups \
	uninstall-cyrus-sasl \
	uninstall-dhcp \
	uninstall-exim \
	uninstall-fcron \
	uninstall-gdm \
	uninstall-gpm \
	uninstall-heimdal \
	uninstall-iptables \
	uninstall-kerberos \
	uninstall-lprng \
	uninstall-mysql \
	uninstall-netfs \
	uninstall-nfs-client \
	uninstall-nfs-server \
	uninstall-ntp \
	uninstall-openldap1 \
	uninstall-postfix \
	uninstall-postgresql \
	uninstall-proftpd \
	uninstall-random \
	uninstall-rsync \
	uninstall-samba \
	uninstall-sendmail \
	uninstall-soprano \
	uninstall-sshd \
	uninstall-stunnel \
	uninstall-svn \
	uninstall-sysstat \
	uninstall-virtuoso \
	uninstall-vsftpd \
