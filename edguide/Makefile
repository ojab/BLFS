
OUTPUTDIR = $(HOME)/public_html/edguide/


edguide:
	@echo "Generating Editor's Guide for the BLFS Book with xsltproc..." 
	install -d  $(OUTPUTDIR) 
	xsltproc --xinclude --nonet -stringparam base.dir $(OUTPUTDIR) \
	       stylesheets/edguide.xsl index.xml
