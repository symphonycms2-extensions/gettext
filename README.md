# Static Resources

* Version: 1.0.0
* Author: Remie Bolte <http://github.com/remie>
* Build Date: 2011-09-05
* Requirements: Symphony 2.2.x, Language Redirect

## Description

Ahhh yes... yet another extension in the battle for Multi-Language support!
This one is more developer centric: it allows you to translate those small words 
like "Login", "Username" and other small pieces of text that needs translation
but aren't exactly what you would call articles.

## Requirements

This extension requires Symphony 2.2.x and the Language Redirect extension.

## Installation

1. Download and install the Language Redirect extension
2. Download the Static Resources extension and add it to your extensions folder using `staticresources` as the folder name
3. Add an XML file in the Manifest folder called `staticresources.xml`
4. Add parameters to your XML file (look at the Usage section below for more information)

## Usage

The extension uses a simple XML file in the manifest folder called `staticresources.xml`.
The schema for this file is:

	<resources>
		<language code="">
			<param name="" value="" />
		</language>
	</resources>

For large amounts of text (don't know if this is the right extension for it, but it is supported), 
you can create childNodes for the `@name` and `@value` attributes:

	<resources>
		<language code="">
			<param>
				<name></name>
				<value></value>
			</param>
		</language>
	</resources>

You can combine this, by using the `@name` attribute, and the `value` childnode, etc, etc.
Keep in mind that it will first use the attribute value, and if the attribute does not exist, it will look for the node.
If there is no attribute and no childnode specified, the extension will throw an error.

Each parameter is added to your frontend as... XSLT parameters!
So you can get the value in your template by using `$name` (which happens to be the value of the `@name` 
attribute in your `staticresources.xml` file).

## Caveats

### Language Redirect based

In order to get the parameters for a specific languag code, the extension matches the `@code` attribtue value
with the value returned by the `LanguageCode` property that is returned by the Language Redirect extension.

So if this returns 'en-us', based on the provided URL `http://example.org/en-us/somepage`, the extension will
look for the following xpath in the `staticresources.xml` file: `/resources/language[@code='en-us']/param`.

### Default language

If there is no language specified, the extension will look for the default language code as specified in 
the Language Redirect preferences. If there is no default, no parameters will be resolved.

If the extension does not find parameters based on the language code returned by the Language Redirect extension, 
it will not resolve any parameters.

### Missing Parameters

Keep in mind that missing parameters might cause your Symphony instance to throw XSLT errors!
To avoid this, you can either make sure your `staticresources.xml` file is up-to-date, or add

	<xsl:param name="name" />

to the top of your XSLT pages, to ensure that the parameter is declared.

## Roadmap and Version History

Can be found on the GitHub repository: https://github.com/remie/StaticResources/issues
