
OUTPUTDIR = $(HOME)/public_html/edguide/


edguide:
	@echo "Generating Editor's Guide for the BLFS Book with xsltproc..." 
	install -d  $(OUTPUTDIR) 
	xsltproc --xinclude --nonet -stringparam base.dir $(OUTPUTDIR) \
	       edguide.xsl index.xml
	cp *.css $(OUTPUTDIR)
#	sh goTidy $(OUTPUTDIR)


validate:
	xmllint --noout --nonet --xinclude --postvalid index.xml 

