# Static Resources

* Version: 0.9.0
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
you can create childNodes for the @name and @value attributes:

	<resources>
		<language code="">
			<param>
				<name></name>
				<value></value>
			</param>
		</language>
	</resources>

You can combine this, by using the @name attribute, and the <value> childnode, etc, etc.

Each parameter is added to your frontend as... XSLT parameters!
So you can get the value in your template by using $name (which happens to be the value of the @name 
attribute in your `staticresources.xml` file).

## Roadmap and Version History

Can be found on the GitHub repository: https://github.com/remie/StaticResources/issues
