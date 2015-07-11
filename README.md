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
reuse of references stored within a wiki with support for:

- Self-added and a customizable reference list
- Individual property and text formatting rules
- In-text reference tooltip
- Bibtex record import support

Semantic Cite does not require nor uses any part of [`Cite`][mw-cite] (or `<ref>`)
as a means to declare a citation resource.

A short [video](https://vimeo.com/126189455) will demonstrate "How Semantic Cite can be used".

## Requirements

- PHP 5.3.2 or later
- MediaWiki 1.24 or later
- [Semantic MediaWiki][smw] 2.3 or later

## Installation

The recommended way to install Semantic Cite is by using [Composer][composer] with an entry in MediaWiki's `composer.json`.

```json
{
	"require": {
		"mediawiki/semantic-cite": "~1.0"
	}
}
```
1. From your MediaWiki installation directory, execute
   `composer require mediawiki/semantic-cite:~1.0`
2. Run the maintenance [`update.php`][mw-update] script to ensure that property tables
   are properly initialized
3. Navigate to _Special:Version_ on your wiki and verify that the package
   have been successfully installed.

## Usage

The easiest way to create a citation resource is by using the `#scite` parser in form of:

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

Citation resources (entities created by `#scite`) can be added to a single page
or spread throughout the wiki each being identifiable by a unique citation key
(in order to distinguish it from any other citation resource declared in a wiki).

Semantic Cite provides six predefined properties `PMCID`, `DOI`, `Citation resource`, `Citation reference`,
`Citation key`, and `Citation text`.

![scite-sneak](https://cloud.githubusercontent.com/assets/1245473/8370671/7d8bfeac-1bcb-11e5-9007-79a3d39f70ce.png)

### In-text reference

A resource can easily be cited using the `Citation reference` (or its alias `CiteRef`)
property for an in-text annotation such as `Lorem ipsum [[CiteRef::Byrne 2008]] ...` to appear as
`Lorem ipsum`<sup>`[1]`</sup>` ...`.

A reference list is automatically added to the content as soon as a `Citation reference`
annotation is added to a page. The magic word `__NOREFERENCELIST__` can be used to suppress
a reference list from showing on an individual page.

> Can I store a `Citation resource` on a page different from where the actual reference is made?

Yes. A `Citation resource` can be stored on any page and is accessible from any page
through the `Citation key` declared by the resource.

> Can I add the same reference multiple times to a (or different) text source?

Yes. Using the same `[[CiteRef:: ...]]` reference at the position that needs citing
is all that is required.

> Is it possible to use a resource defined by `<ref>` (Cite) with #scite?

No. Due to a different technical approach resources declared by `<ref>` can not be used
by (or in) Semantic Cite.

### Getting started

- Define property and text formatting rules
- Create a resource with `#scite`
- Create an in-text citation using `[[Citation reference:...]]` (= `[[CiteRef:...]]`)

## Configuration

For settings, property mapping, and text formatting rules have a look at the [configuration](https://github.com/SemanticMediaWiki/SemanticCite/blob/master/CONFIGURATION.md) document.

## Contribution and support

If you want to contribute work to the project please subscribe to the developers mailing list and
have a look at the contribution guideline.

* [File an issue](https://github.com/SemanticMediaWiki/SemanticCite/issues)
* [Submit a pull request](https://github.com/SemanticMediaWiki/SemanticCite/pulls)
* Ask a question on [the mailing list](https://semantic-mediawiki.org/wiki/Mailing_list)
* Ask a question on the #semantic-mediawiki IRC channel on Freenode.

### Tests

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
