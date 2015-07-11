## Settings

- `$GLOBALS['scigCitationReferenceCaptionFormat']` specifies the display format for a citation
  reference to be displayed either as a number (`SCI_CITEREF_NUM`) or by its key (`SCI_CITEREF_KEY`)
- `$GLOBALS['scigShowTooltipForCitationReference']` maintains an array for which format the tooltip
   can be shown (`false` or an empty array will disable the tooltip)
- `$GLOBALS['scigTooltipRequestCacheTTLInSeconds']` to allow to store tooltip query results from
   the backend to the local browser cache in order to avoid repeated requests for already queried
   references. Setting this parameter to false will disable the cache. Items that are cached will show
   a `[+]` as indicator.
- `$GLOBALS['scigReferenceListCacheType']` to disable caching for the reference list, use setting
  [`CACHE_NONE`][mw-cachetype] otherwise the cache is being renewed an each new revision or when
  the page is purged
- `$GLOBALS['scigStrictParserValidationEnabled']` whether a strict validation of input data for
  the `{{#scite:}}` parser should be carried out or not

### Reference list

- `$GLOBALS['scigNumberOfReferenceListColumns']` specifies the number of columns to be shown
  on the reference list
- `$GLOBALS['scigReferenceListType']` either formatted using `ul` or `ol`
- `$GLOBALS['scigBrowseLinkToCitationResource']` whether to show a browse link to the citation
  resource or not

## Configuration

### Property mapping

Not all resource identifiers in `{{#scite:}}` (e.g `|pages=...` etc. ) are relevant for semantic recognition
therefore `MediaWiki:Sci-property-definition` describes the mapping between identifiers
and properties specific to a wiki (no mapping, means no annotations).

<pre>
type|Has reference type
author|Has author
publisher|Has publisher
</pre>

```
{{#scite:Foo
 ...
 |type=Faz
 |author=Bar
 |publisher=Foobar
 |pages=123
 |volume=10
 ...
}}
```
For example, with the mapping above the following annotations `[[Has reference type::Faz]]`, `[[Has author::Bar]]`, and
`[[Has publisher::Foobar]]` will be made available. The `pages` and `volume` identifier are not mapped to a
property therefore are not available as semantic annotation.

### Template mapping

The generation of a citation text is handled by different rules resolved through
the following process.

`MediaWiki:Sci-template-definition` describes and maps citation resource types to specific
templates. A template will then describe the rules of how indentifiers are expected to be formatted
in order to generate a citation text.

<pre>
 online|SciteOnlineResourceFormatter
 journal|SciteAPAJournalResourceFormatter
 book|SciteMPABookResourceFormatter
 someothertype|SciteFormatterForAnotherType
</pre>

A citation resource can override the template type assignment by invoking
the `|template=...` parameter.

```
{{#scite:Segon & Booth 2011
 |type=online
 |author=Segon, M;Booth, C|+sep=;
 |year=2011
 |title=Bribery: what do Australian managers know and what do they do?
 |journal=Journal of Business Systems, Governance and Ethics
 |volumn=vol. 6, no. 3
 |pages=15-29
 |url=http://www.jbsge.vu.edu.au/issues/vol06no3/Segon_&_Booth.pdf
 |available=20 October 2014
 |template=SciteUseDifferentOnlineResourceFormatter
}}
```

Using the `|citation text=...` parameter allows to circumvent
any template rendering by storing the input text directly.

```
{{#scite:
 |type=book
 |reference=Barone and Gianfranco, 1982
 |citation text=Barone, Antonio, and Gianfranco Paterno. Physics and applications of the Josephson effect. Vol. 1. New York: Wiley, 1982.
}}
```

The mapping is made flexible enough to support citation styles or types that
do not fit the standard guidelines or use citations for more than just
literature references.

### #scite usage

`{{#scite:}}` parser can only be used on [namespaces][smw-ns] that are enabled
for Semantic MediaWiki. Parameters (or identifiers) are free from any restrictions
besides those listed below:

- `type` is a reserved parameter and is required for when `$GLOBALS['scigStrictParserValidationEnabled']` is set `true`
- `doi` is a reserved parameter and is linked to the `DOI` property
- `pmcid` is a reserved parameter and is linked to the `PMCID` property
- `reference` is a reserved parameter and is linked to the `Citation key` property
- `citation text` is a reserved parameter and is linked to the `Citation text` property
- `sortkey` is a reserved parameter and is linked to the `_SKEY` property and can be
   set to find a resource more easily during querying as the resource is by default set
   to the internal resource id.

For example, below represents the same `Segon & Booth 2011` entity reference using the
short and the explicit form to create a property/value assignment equal to
`[[Citation key::Segon & Booth 2011]]`.

```
{{#scite:Segon & Booth 2011
  ...
}}

is the same as

{{#scite:
  |reference=Segon & Booth 2011
  ...
}}
```
### bibtex import

To easy the reuse of existing bibtex records, `#scite` provides the `|bibtex=` parameter to
import a bibtex by simply adding its text and have `#scite` transform it into a structured form
defined by the property and template rules.

```
{{#scite:
 |bibtex=@ARTICLE{Meyer2000,
AUTHOR="Bernd Meyer",
TITLE="A constraint-based framework for diagrammatic reasoning",
JOURNAL="Applied Artificial Intelligence",
VOLUME= "14",
ISSUE = "4",
PAGES= "327--344",
YEAR=2000
}
}}

{{#scite:Einstein, Podolsky, and Nathan 1935
 |bibtex=@article{einstein1935can,
  title={Can quantum-mechanical description of physical reality be considered complete?},
  author={Einstein, Albert and Podolsky, Boris and Rosen, Nathan},
  journal={Physical review},
  volume={47},
  number={10},
  pages={777},
  year={1935},
  publisher={APS}
}
 |authors=Albert Einstein, Boris Podolsky, Nathan Rosen|+sep=,
}}
```


### #referencelist usage

Normally a reference list is self-maintained and added to the bottom a page if
a citation reference is present but in case the list should positioned
differently, `{{#referencelist:}}` can be used to mark the position on where the
list is expected to appear.

`{{#referencelist:}}` parser does accept options that can modify the output of an individual
reference list.

```
{{#referencelist:
 |listtype=ul
 |browselinks=false // ("true", "1", "on" and "yes") or ( "false", "0", "off", and "no")
 |columns=3
 |header=Notes
}}
```

To display a table of contents section for the reference list (by default the auto-added
list is hidden) the parameter `|toc=yes` should be added to:

```
{{#referencelist:
 |listtype=ol
 |browselinks=no
 |columns=1
 |toc=yes
}}
```

To generate a nonbound reference list (for notes or additional literature references)
using the `|reference=` parameter is required because such list uses the information
provided by `|reference=` and is not bound to any of the `Citation reference`
annotations made to a particular page or subject.

```
{{#referencelist:
 |listtype=ul
 |browselinks=yes
 |columns=1
 |header=Notes
 |references=PMC2483364;Einstein et al. 1935|+sep=;
}}
```

### References and citation keys

Citation keys are available wiki-wide therefore selecting an
appropriate key is paramount to safeguard against unnecessary changes.

- If it becomes necessary to rename a citation key (because a resource with key
  `Foo 2007` no longer represents a unique resource due to adding another resource
  with the same key) then the existing usage of that resource needs to be queried and
  changed before applying the new citation key (e.g. `Foo 2007a`).
- Citation resources that use the same key are displayed on the reference list
  and linked to each other in case `$GLOBALS['scigBrowseLinkToCitationResource']` is set
  true. For example, ` ↑ | ↑` is indicating that two resources use the same citation
  key with `↑` each to link to its resource.
- The parentheses style of a citation reference can be modified using the `scite.styles.css`
  style sheet.
- If a page number reference is required then one can use the longform syntax
  `[[CiteRef::Foo and Bar, 1970|Foo and Bar, 1970:42]]` for `SCI_CITEREF_KEY` to highlight
  the page 42 while the shortform `[[CiteRef::Foo and Bar, 1970|:42]]` is suggested for
  `SCI_CITEREF_NUM` in order to appear as `...`<sup>`[1]:42`</sup>
- To avoid cluttering a source text with citation resources it is suggested to divide
  text and resource definitions by storing `{{#scite:}}` resources on a related a subpage
  and use the `[[CiteRef:: ...]]` annotation on the source page for inclusion.

## Questions

### How to handle different authors

There are various ways of making different authors available to the semantic search
and as ordered output.

For example using a parameter `authors` that is matched to a property `Has author`
(to contain all authors in clear form) while an `author` parameter (not matched to any
property) is used as identifier so that the template formatter can generate the expected
ordered output from `{{{author|}}}`  without having to apply a complex parsing process.

```
{{#scite:Watson and Crick, 1953
 ...
 |author=James D. Watson;Francis HC Crick|+sep=;
 |authors=Watson, James D., and Francis HC Crick
 ...
}}
```

### How to create a simple citation resource

Sometimes a simple resource is all that is required and a very convenient way is
to use the `|citation text=...` parameter and assign the text without further processing.

```
{{#scite:
 |type=book
 |reference=Barone and Gianfranco, 1982
 |citation text=Barone, Antonio, and Gianfranco Paterno. Physics and applications of the Josephson effect. Vol. 1. New York: Wiley, 1982.
}}
```

[mw-cachetype]: http://www.mediawiki.org/wiki/Manual:$wgMainCacheType
[smw-ns]: https://semantic-mediawiki.org/wiki/Help:$smwgNamespacesWithSemanticLinks
