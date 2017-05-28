The listed examples are provided as starting point which may or may not be appropriate
or complete in one or the other use case. Examples can contain conditionals therefore
it is expected that a user has installed the [ParserFunctions][ext:pf] extension.

- The naming of a template is of no real importance with the only restriction that
  type-template assignments and names should correlate with each other
- Template parameter names correspond to the names used in the `#scite` parser
  function which are independent of property assignments (`MediaWiki:Sci-property-definition`)

## MediaWiki:Sci-template-definition

`MediaWiki:Sci-template-definition` page may contain the following type-template
assignments ([see also](03-template-mapping.md)).

```
 article|Sci-mla-citation-formatter
 journal|Sci-mla-citation-formatter
 journal article|Sci-mla-citation-formatter
 inproceedings|Sci-mla-citation-formatter
 book|Sci-mla-citation-formatter
 personal|Sci-viaf-citation-formatter
 internet|Sci-mla-citation-formatter-web-publication
 research-article|Sci-mla-citation-formatter-web-publication
```

### Examples

- [Template:Sci-mla-citation-formatter](tmpl-sci-mla-citation-formatter.mediawiki)
- [Template:Sci-mla-citation-formatter-book (incomplete)](tmpl-sci-mla-citation-formatter.mediawiki)
- [Template:Sci-mla-citation-formatter-web-publication](tmpl-sci-mla-citation-formatter-web-publication.mediawiki)
- [Template:Sci-apa-citation-formatter (incomplete)](tmpl-sci-apa-citation-formatter.mediawiki)
- [Template:Sci-apa-citation-formatter-journal (incomplete)](tmpl-sci-apa-citation-formatter-journal.mediawiki)
- [Template:Sci-apa-citation-formatter-web-publication (incomplete)](tmpl-sci-apa-citation-formatter-web-publication.mediawiki)
- [Template:Sci-viaf-citation-formatter](tmpl-sci-viaf-citation-formatter.mediawiki)


[ext:pf]: https://www.mediawiki.org/wiki/Extension:ParserFunctions
