# gettext

* Version: 2.1.0
* Author: Remie Bolte <http://github.com/remie>
* Build Date: 2012-03-10
* Requirements: Symphony 2.3.x

## Description

Ahhh yes... yet another extension in the battle for Multi-Language support!
This one is more developer centric: it allows you to translate those small words 
like "Login", "Username" and other pieces of text that needs translation
but aren't exactly what you would call articles.

## Requirements

This extension requires Symphony 2.3.x

## Installation

1. Download the `gettext` extension and add it to your extensions folder using `gettext` as the folder name
2. Go to the preference screen and choose your parser (defaut = GNU PO);
3. Add parameters to your resource files (look at the Usage section below for more information)
4. Include the resources in your pages!

## Usage

The `gettext` extension comes with two flavors: GNU PO file parser and JAVA-style i18n resource bundle property file 
parser. You can select the parser from the preferences screen.

### XSLT Parameters or XML Data source

There are two ways of using the resources in your XSLT templates.
The resources can be made available as XSLT parameters, or they can be added as data source to the page.

Please note: it is _not_ recommended to use the XSLT parameters in combination with the
GNU PO parser. This might not work due to the nature of PO resource identifiers (using complete string instead of key).

#### XSLT Parameters

You can access the parameters in your XSLT by using `$[parameterName]`:

	<xsl:value-of select="$MyString" />

Keep in mind that missing parameters might cause your Symphony instance to throw XSLT errors!
To avoid this, you can either make sure your resources files are up-to-date, or add

	<xsl:param name="name" />

to the top of your XSLT pages, to ensure that the parameter is declared.

#### Data Source

You can access the resources by including the `gettext resources` data source to your page from the page
configuration screen in Symphony. A `resources` element will be added to your page output XML.

You can retrieve resources values in your XSLT templates like you would do with other XML nodes, but
you can also choose to use the `key()` function in combination with the `xsl:key` element.

GNU PO:

	<xsl:key name="resources" match="/data/resources/resource[@regionCode=$regionCode]/context[@name=$context]/item" use="msgid" />
	<xsl:value-of select="key('resources','My Resource String')/msgstr" />

Please note that you will need to declare the `$regionCode` and `$context` parameters in your XSLT template.
Please read the sections below and the PO specifications for more information on the parameters values.

i18n:

	<xsl:key name="resources" match="/data/resources/resource[@regionCode=$regionCode]/item" use="@name" />
	<xsl:value-of select="key('resources','MyString')" />

Please note that you will need to declare the `$regionCode` parameter in your XSLT template.
Please read the sections below and the PO specifications for more information on the parameters values.

### GNU PO Parser

This parser is based on the GNU PO format specifications that can be found 
(here)[http://www.gnu.org/software/gettext/manual/gettext.html#PO-Files].
 
All resources files must be located in the folder `[Root] > manifest > resources`.
This folder will be created by the extension during the installation.

#### PO Format

It is important that you read the specifications before using the PO parser.
The most basic implementation of a PO translation block is:

     white-space
     #  translator-comments
     #. extracted-comments
     #: reference...
     #, flag...
     #| msgid previous-untranslated-string
     msgid untranslated-string
     msgstr translated-string

Please note: the `white-space` before the comments is mandatory!
This means you will always need to start your document with a line break.

#### PO files naming convention

It is imperitive that the file name convention is strictly enforced
This is due to the fact that the file name includes information on
language-code, country-code and file encoding that is required for 
parsing the file

Default language file:
	'name.[encoding].po'

	[encoding]: file encoding (default is UTF-8)

Region specific language file:
	'name.[lc]_[cc].[encoding].po'

	[lc]: language code
	[cc]: country code
	[encoding]: file encoding (default is UTF8)

The [lc] and [cc] values will be used to construct the Region Code.
This will also be made available in the gettext data source.

Example:	Language-code is 'nl' and Country-Code is 'NL'.
			The Region Code will be 'nl-NL'
Example:	Language-code is 'de' and Country-Code is empty.
			The Region Code will be 'de-de'
Example:	Language-code is empty and Country-Code is 'uk'.
			The Region Code will be 'uk'


### I18N Property Files

JAVA-Style property files consist of a line-seperated list of key/value pairs.
This parser is based on the JAVA properties format specifications that can be found 
(here)[http://download.oracle.com/javase/6/docs/api/java/util/Properties.html#load(java.io.Reader)].
 
All resources files must be located in the folder `[Root] > manifest > resources`.
This folder will be created by the extension during the installation.

#### JAVA Property file Format

It is important that you read the specifications before using the i18n parser.
The most basic implementation of a JAVA property file:

	MyString=A very interesting story about tax benefits and the development of cross-country skiing in the late 19th century.

Please note: You do not need to use quotes (") around the property value.

#### I18n files naming convention
It is imperitive that the file name convention is strictly enforced
This is due to the fact that the file name includes information on
language-code, country-code and file encoding that is required for
parsing the file

Default language file:
	'name.properties'

Region specific language file:
	'name.[lc]_[cc].properties'

	[lc]: language code
	[cc]: country code

The [lc] and [cc] values will be used to construct the Region Code.
This will also be made available in the gettext data source.

Example:	Language-code is 'nl' and Country-Code is 'NL'.
			The Region Code will be 'nl-NL'
Example:	Language-code is 'de' and Country-Code is empty.
			The Region Code will be 'de-de'
Example:	Language-code is empty and Country-Code is 'uk'.
			The Region Code will be 'uk'

## Caveats

### Language selected on query parameters

In order to get the XSLT parameters for a specific region code, the extension uses the value of the `language` and 
`region` querystring parameters to construct a region code. These values are set by the Language Redirect extension, 
so you can use this to manage your localization effort, but it is not required.

You can also find other ways to fill these query parameters, or use the Data Source instead of the XSLT parameters,
which allows you to manually select the proper translation using XPath.

### Default language

Please use the file naming convention for the default language resource file.
By doing so, the `gettext` extension will always return a value for your translations.

If no default language resource file is available, the XSLT parameters will not be resolved and the Data Source will
remain empty. This might cause your application to throw errors!

## Roadmap and Version History

Can be found on the GitHub repository: https://github.com/remie/gettext/issues
