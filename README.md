# Semantic Cite

[![Build Status](https://secure.travis-ci.org/SemanticMediaWiki/SemanticCite.svg?branch=master)](http://travis-ci.org/SemanticMediaWiki/SemanticCite)
[![Code Coverage](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticCite/badges/coverage.png?s=f3501ede0bcc98824aa51501eb3647ecf71218c0)](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticCite/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticCite/badges/quality-score.png?s=d9aac7e68e6554f95b0a89608cbc36985429d819)](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticCite/)
[![Latest Stable Version](https://poser.pugx.org/mediawiki/semantic-cite/version.png)](https://packagist.org/packages/mediawiki/semantic-cite)
[![Packagist download count](https://poser.pugx.org/mediawiki/semantic-cite/d/total.png)](https://packagist.org/packages/mediawiki/semantic-cite)
[![Dependency Status](https://www.versioneye.com/php/mediawiki:semantic-cite/badge.png)](https://www.versioneye.com/php/mediawiki:semantic-cite)

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

- PHP 5.3.2 or later
- MediaWiki 1.24 or later
- [Semantic MediaWiki][smw] 2.3 or later

Semantic Cite **does not require** nor uses any part of [`Cite`][mw-cite] (or `<ref>`)
as a means to declare a citation resource.

## Installation

The recommended way to install Semantic Cite is by using [Composer][composer] with an entry in MediaWiki's `composer.json`.

```json
{
	"require": {
		"mediawiki/semantic-cite": "~1.1"
	}
}
```
1. From your MediaWiki installation directory, execute
   `composer require mediawiki/semantic-cite:~1.1`
2. Run the **maintenance [`update.php`][mw-update] script** to ensure that property tables
   are properly initialized
3. Navigate to _Special:Version_ on your wiki and verify that the package
   have been successfully installed.

## Usage

![scite-sneak](https://cloud.githubusercontent.com/assets/1245473/8370671/7d8bfeac-1bcb-11e5-9007-79a3d39f70ce.png)

A citation resource collects all structured data of a citation under one unique key that
can be accessed through out the wiki and is created and managed by the `#scite` parser.

Citation resources (those created by `#scite`) can be added to a source page
or any other wiki page each being identifiable by a citation key.

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

Above shows an example for a citation resource to be created by the `#scite` parser. More
information about `#scite` can be found [here][docs-scite].

### In-text citation

A resource can be cited using the `Citation reference` (or its alias `CiteRef`)
property for an in-text annotation in form of `Lorem ipsum [[CiteRef::Byrne 2008]] ...` to appear as
`Lorem ipsum`<sup>`[1]`</sup>` ...`.

A reference list is automatically added to the content as soon as a `Citation reference`
annotation is added to a page. The magic word `__NOREFERENCELIST__` can be used to suppress
a reference list from showing on an individual page while `#referencelist` can be used to position
the list differently.

More information about in-text citations and references can be found [here][docs-intext] together
with a description about the usage of the [`#referencelist`][docs-referencelist] parser.

For questions about Semantic Cite and [`Cite`][mw-cite], see the comments [section][docs-faq].

### Metadata search

`Special:FindCitableMetadata` is provided as user interface to search, find, and map metadata with the
`#scite` parser to conveniently integrate authority data from sources like PubMed or CrossRef.

For more information, please read the search [section][docs-search].

## Configuration

To change default settings, or add property mapping add text formatting rules, please read the
[configuration][config] document.

## Contribution and support

If you want to contribute work to the project please subscribe to the developers mailing list and
have a look at the contribution guideline.

* [File an issue](https://github.com/SemanticMediaWiki/SemanticCite/issues)
* [Submit a pull request](https://github.com/SemanticMediaWiki/SemanticCite/pulls)
* Ask a question on [the mailing list](https://semantic-mediawiki.org/wiki/Mailing_list)
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
[config]: https://github.com/SemanticMediaWiki/SemanticCite/blob/master/docs/00-configurations.md
[docs-faq]: https://github.com/SemanticMediaWiki/SemanticCite/blob/master/docs/08-faq.md
[docs-search]: https://github.com/SemanticMediaWiki/SemanticCite/blob/master/docs/07-metadata-search.md
[docs-scite]: https://github.com/SemanticMediaWiki/SemanticCite/blob/master/docs/04-scite.md
[docs-intext]: https://github.com/SemanticMediaWiki/SemanticCite/blob/master/docs/06-references.md
[docs-referencelist]: https://github.com/SemanticMediaWiki/SemanticCite/blob/master/docs/05-referencelist.md
[remi]: https://github.com/onoi/remi