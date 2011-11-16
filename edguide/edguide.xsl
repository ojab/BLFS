<?xml version='1.0' encoding='ISO-8859-1'?>

<!-- Version 0.9 - Manuel Canales Esparcia <macana@lfs-es.org>
Based on the original lfs-chunked.xsl created by Matthew Burgess -->

<!-- $LastChangedBy$ -->
<!-- $Date$ -->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns="http://www.w3.org/1999/xhtml"
                version="1.0">

  	<!-- We use XHTML -->
  <xsl:import href="http://docbook.sourceforge.net/release/xsl/current/xhtml/chunk.xsl"/>
  <xsl:param name="chunker.output.encoding" select="'ISO-8859-1'"/>
  <xsl:param name="chunk.section.depth" select="0"/>

  	<!-- The CSS Stylesheet -->
  <xsl:param name="html.stylesheet" select="'edguide.css'"/>

  	<!-- Dropping some unwanted style attributes -->
  <xsl:param name="ulink.target" select="''"></xsl:param>
  <xsl:param name="css.decoration" select="0"></xsl:param>

    <!-- No XML declaration -->
  <xsl:param name="chunker.output.omit-xml-declaration" select="'yes'"/>

    <!-- Insert a stylesheet for printing -->
  <xsl:template name='user.head.content'>
     <link rel='stylesheet' href="edguide-print.css" type="text/css" media='print'/>
  </xsl:template>

  <xsl:template match="userinput">
    <xsl:call-template name="inline.monoseq"/>
  </xsl:template>

    <!-- Handle name and date in info section as a footnote -->

  <xsl:template name="process.footnotes">
    <xsl:variable name="footnotes" select=".//footnote"/>
    <xsl:variable name="fcount">
      <xsl:call-template name="count.footnotes.in.this.chunk">
        <xsl:with-param name="node" select="."/>
        <xsl:with-param name="footnotes" select="$footnotes"/>
      </xsl:call-template>
    </xsl:variable>

    <!-- Only bother to do this if there's at least one non-table footnote -->
    <xsl:if test="$fcount &gt; 0">
      <div class="footnotes">
        <br/>
        <hr width="100" align="left"/>
        <xsl:call-template name="process.footnotes.in.this.chunk">
          <xsl:with-param name="node" select="."/>
          <xsl:with-param name="footnotes" select="$footnotes"/>
        </xsl:call-template>
      </div>
    </xsl:if>

    <!-- Add this to the footnotes -->
    <xsl:apply-templates select='prefaceinfo|chapterinfo' mode='attribution'/>
  </xsl:template>

  <xsl:template match='prefaceinfo|chapterinfo' mode='attribution'>
    <p class='updated'> Last updated by 
      <xsl:apply-templates select="othername" mode='attribution'/>
      on
      <xsl:apply-templates select="date" mode='attribution'/>
    </p>
  </xsl:template>

  <xsl:template match='othername' mode='attribution'>
     <xsl:variable name='author'>
          <xsl:value-of select='.'/>
     </xsl:variable>
     <xsl:variable name='nameonly'>
          <xsl:value-of select='substring($author,10)'/>
     </xsl:variable>
     <xsl:value-of select="substring-before($nameonly,'$')" />
  </xsl:template>

  <xsl:template match='date' mode='attribution'>
      <xsl:variable name='date'>
         <xsl:value-of select='.'/>
      </xsl:variable>
      <xsl:value-of select="substring($date,7,26)" />
  </xsl:template>

</xsl:stylesheet>
