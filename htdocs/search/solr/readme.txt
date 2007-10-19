To install & activate the Mahara solr plugin using Solr 1.2 from the
solr-tomcat5.5 debian package:

For existing Maharas, make sure $cfg->searchplugin does not appear in
config.php

As admin, set searchplugin to 'Solr' in site options, and in plugin
administration, set the Solr URL.  For tomcat this will usually be
something like http://foo.com:8150/solr/.  For the jetty package, the
default port is 8983.

In the solr installation, edit /etc/solr/conf/solrconfig.xml.
  Comment out
  <requestHandler name="/update" class="solr.XmlUpdateRequestHandler" />

  Change handleSelect to "false" in
  <requestDispatcher handleSelect="true" >

Copy the schema.xml file in this directory into /etc/solr/conf/ on the
solr server.
