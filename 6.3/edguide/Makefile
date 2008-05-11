#
# $LastChangedBy$
# $Date$
#
#

OUTPUTDIR = $(HOME)/public_html/edguide/


edguide:
	@echo "Generating Editor's Guide for the BLFS Book with xsltproc..." 
	install -d  $(OUTPUTDIR) 
	xsltproc --xinclude --nonet -stringparam base.dir $(OUTPUTDIR) \
	       edguide.xsl index.xml
	cp *.css $(OUTPUTDIR)

	for filename in `find $(OUTPUTDIR) -name "*.html"`; do \
	  tidy -config tidy.conf $$filename; \
	  true; \
	  sh obfuscate.sh $$filename; \
	  sed -i -e "s@text/html@application/xhtml+xml@g" $$filename; \
	done;


validate:
	xmllint --noout --nonet --xinclude --postvalid index.xml 

