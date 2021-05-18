INTRODUCTION
-------------

Migrate Entities module allows end user to import content from csv. Currently
we have integrated migration only from csv and only for content. This module
is depended on Simple node Importer module. End user needs to upload the 
content in CSV format and map the columns of CSV file to the fields which they
want to associate with that column. Mapping column's functionality is provided
by Simple Node Importer module.

We have integrated migration API. so if you are adding wrong data in CSV then
it will get migrated. Suppose you are migrating email field and you have 
entered wrong email id in CSV like "abc@" then this will get migrated as it is.


SUMMARY
--------

* Module allows end user to import entity (node) using CSV file.
* Module allows end user to map csv columns to fields using Flexible Mapping
UI.(provided by Simple node Importer module).


REQUIREMENTS
-------------

You need to install first Simple Node Importer module.


INSTALLATION
---------------

* Install as usual, see http://drupal.org/node/895232 for further information.


CUSTOMIZATION
---------------

None


TROUBLESHOOTING
-----------------

None.


NOTE
-----

* If author uid is correct in CSV and it exist in database otherwise uid 1
  (If exist in database otherwise anonmyous uid will be set), will be set.


FAQ
----

1) How does Date field import?
Ans. You have to set format d-m-Y OR m/d/Y in CSV otherwise date field will not
be imported.

2) Will wrong informations be imported?
Ans. If you have placed wrong path in uploading widgets like file, image then
node will not be imported but in another widgets like date, link. if you 
have put wrong format in date then node will be imported but date field not be
imported. What informations you have provided in CSV, will be imported.

3) Will this module work for Single and Multivalue fields?
Ans. Yes, This module does support for single and multivalue fields.

4) Does this module support for paragraph module or other contributed modules?
Ans. No, currently this modules does support for only generic default field
widgets like(file, image, textfield, textarea, link, boolean etc) but in future
we will try to provide for paragraph or other contributed modules.
