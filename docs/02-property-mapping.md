## Property mapping

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

## Predefined properties

Semantic Cite provides several predefined properties including:

- `OLID` to identify OpenLibrary records
- `VIAF` to describe a Virtual International Authority File
- `OCLC` as an identifier of a WoldCat catalog entity
- `PMID` and `PMCID` to represent PubMed identifier
- `DOI` as Digital Object Identifier
- `Citation resource`, `Citation reference`, `Citation key`, and `Citation text`