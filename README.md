# Semantic Cite

[![Build Status](https://secure.travis-ci.org/SemanticMediaWiki/SemanticCite.svg?branch=master)](http://travis-ci.org/SemanticMediaWiki/SemanticCite)
[![Code Coverage](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticCite/badges/coverage.png?s=f3501ede0bcc98824aa51501eb3647ecf71218c0)](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticCite/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticCite/badges/quality-score.png?s=d9aac7e68e6554f95b0a89608cbc36985429d819)](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticCite/)
[![Latest Stable Version](https://poser.pugx.org/mediawiki/semantic-cite/version.png)](https://packagist.org/packages/mediawiki/semantic-cite)
[![Packagist download count](https://poser.pugx.org/mediawiki/semantic-cite/d/total.png)](https://packagist.org/packages/mediawiki/semantic-cite)

Semantic Cite (a.k.a. SCI) is a [Semantic Mediawiki][smw] extension that provides a simple
way of organizing citation resources with the help of semantic annotations.

It can be used to centralize the management of citation resources and foster the
reuse of references stored within a wiki. Supported features include:

- Self-added and customizable reference list
- Individual property annotation and text formatting rules
- In-text reference tooltip
- Bibtex record import support
- Metadata retrieval from selected providers (PubMed, CrossRef etc.)

Several short [videos](https://www.youtube.com/playlist?list=PLIJ9eX-UsA5eI_YFdn6HeO2Dcta4CrPzX) demonstrate
"How Semantic Cite can be used or is expected to work".

## Requirements

- PHP 5.6 or later
- MediaWiki 1.27 or later
- [Semantic MediaWiki][smw] 2.5 or later

Semantic Cite **does not require** nor uses any part of [`Cite`][mw-cite] (or `<ref>`)
as a means to declare a citation resource.

## Installation

## Installation

The recommended way to install Semantic Cite is using [Composer](http://getcomposer.org) with
[MediaWiki's built-in support for Composer](https://www.mediawiki.org/wiki/Composer).

Note that the required extension Semantic MediaWiki must be installed first according to the installation
instructions provided.

### Step 1

Change to the base directory of your MediaWiki installation. This is where the "LocalSettings.php"
file is located. If you have not yet installed Composer do it now by running the following command
in your shell:

    wget https://getcomposer.org/composer.phar

### Step 2
    
If you do not have a "composer.local.json" file yet, create one and add the following content to it:

```
{
	"require": {
		"mediawiki/semantic-cite": "~2.0"
	}
}
```

If you already have a "composer.local.json" file add the following line to the end of the "require"
section in your file:

    "mediawiki/semantic-cite": "~2.0"

Remember to add a comma to the end of the preceding line in this section.

### Step 3

Run the following command in your shell:

    php composer.phar update --no-dev

Note if you have Git installed on your system add the `--prefer-source` flag to the above command. Also
note that it may be necessary to run this command twice. If unsure do it twice right away.

### Step 4

Add the following line to the end of your "LocalSettings.php" file:

    wfLoadExtension( 'SemanticCite' );
    
### Step 5

Run the **maintenance script ["update.php"][mw-update]** to ensure that property tables are properly
initialized.

### Verify installation success

As final step, you can verify SCI got installed by looking at the "Special:Version" page on your wiki and
check that it is listed in the semantic extensions section.

## Usage

![scite-sneak](https://cloud.githubusercontent.com/assets/1245473/8370671/7d8bfeac-1bcb-11e5-9007-79a3d39f70ce.png)

A citation resource collects all structured data of a citation under one unique key that can be accessed
through out the wiki and is created and managed by the `#scite` parser function.

Citation resources (those created by `#scite`) can be added to a source page or any other wiki page each
being identifiable by a citation key.

```
{{#scite:Byrne 2008
 |type=journal
 |author=Byrne, A
 |year=2008
 |title=Web 2.0 strategies in libraries and information services
 |journal=The Australian Library Journal
 |volume=57
 |number=4
 |pages=365-376
}}
```

Above shows an example for a citation resource to be created by the `#scite` parser. More information about
`#scite` can be found [here][docs-scite].

### In-text citation

A resource can be cited using the `Citation reference` (or its alias `CiteRef`) property for an in-text
annotation in form of `Lorem ipsum [[CiteRef::Byrne 2008]] ...` to appear as `Lorem ipsum`<sup>`[1]`</sup>` ...`.

A reference list is automatically added to the content as soon as a `Citation reference` annotation is added
to a page. The magic word `__NOREFERENCELIST__` can be used to suppress a reference list from showing on an
individual page while `#referencelist` can be used to position the list differently.

More information about in-text citations and references can be found [here][docs-intext] together with a
description about the usage of the [`#referencelist`][docs-referencelist] parser function.

For questions about Semantic Cite and [`Cite`][mw-cite], see the comments [section][docs-faq].

### Metadata search

`Special:FindCitableMetadata` is provided as user interface to search, find, and map metadata with the
`#scite` parser to conveniently integrate authority data from sources like PubMed or CrossRef.

For more information, please read the search [section][docs-search].

## Configuration

To change default settings, or add property mapping add text formatting rules, please read the
[configuration][docs-config] document.

## Contribution and support

If you want to contribute work to the project please subscribe to the developers mailing list and
have a look at the contribution guideline.

* [File an issue](https://github.com/SemanticMediaWiki/SemanticCite/issues)
* [Submit a pull request](https://github.com/SemanticMediaWiki/SemanticCite/pulls)
* Ask a question on [the mailing list](https://www.semantic-mediawiki.org/wiki/Mailing_list)
* Ask a question on the #semantic-mediawiki IRC channel on Freenode.

## Tests

This extension provides unit and integration tests that are run by a [continues integration platform][travis]
but can also be executed using `composer phpunit` from the extension base directory.

## License

[GNU General Public License, version 2 or later][gpl-licence].

[smw]: https://github.com/SemanticMediaWiki/SemanticMediaWiki
[contributors]: https://github.com/SemanticMediaWiki/SemanticCite/graphs/contributors
[travis]: https://travis-ci.org/SemanticMediaWiki/SemanticCite
[gpl-licence]: https://www.gnu.org/copyleft/gpl.html
[composer]: https://getcomposer.org/
[mw-cite]: https://github.com/wikimedia/mediawiki-extensions-Cite
[mw-update]: https://www.mediawiki.org/wiki/Manual:Update.php
[docs-config]: https://github.com/SemanticMediaWiki/SemanticCite/blob/master/docs/00-configuration.md
[docs-faq]: https://github.com/SemanticMediaWiki/SemanticCite/blob/master/docs/08-faq.md
[docs-search]: https://github.com/SemanticMediaWiki/SemanticCite/blob/master/docs/07-metadata-search.md
[docs-scite]: https://github.com/SemanticMediaWiki/SemanticCite/blob/master/docs/04-scite.md
[docs-intext]: https://github.com/SemanticMediaWiki/SemanticCite/blob/master/docs/06-references.md
[docs-referencelist]: https://github.com/SemanticMediaWiki/SemanticCite/blob/master/docs/05-referencelist.md
[remi]: https://github.com/onoi/remi
