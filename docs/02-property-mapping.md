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
- `OCLC` as an identifier for a WorldCat catalog entity
- `PMID` and `PMCID` to represent a PubMed identifier
- `DOI` as Digital Object Identifier
- `Citation resource`, `Citation reference`, `Citation key`, and `Citation text`

### External vocabulary assignment

If for some of the predefined properties an external RDF [vocabulary assignment][smw-import] is required then
simple statements (e.g. `[[Imported from::bibo:doi]]`) can be added to a selected property.

`MediaWiki:Smw_import_bibo` to specify:

```
http://purl.org/ontology/bibo/|[http://bibliontology.com/ Bibliographic Ontology]
 title|Type:Text
 doi|Type:Text
 pmid|Type:Text
```

[smw-import]: https://semantic-mediawiki.org/wiki/Help:Import_vocabulary