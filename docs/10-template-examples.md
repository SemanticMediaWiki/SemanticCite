## Examples

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

## Templates

### Template:Sci-mla-citation-formatter

```
<noinclude>MLA (Modern Language Association) style is most commonly used to cite sources within the liberal arts and humanities.
* [https://owl.english.purdue.edu/owl/resource/747/01/ MLA Formatting and Style Guide]
* [http://www.citationmachine.net/mla/cite-a-book Citation Machine: MLA format citation generator for book] [[Category:Semantic Cite]] [[Category:Citation formatter]]</noinclude><includeonly>{{#if: {{{authors|}}} |{{{authors}}}. | {{{author}}}. }} "{{{title}}}". {{#if: {{{journal|}}} | {{{journal}}} | {{{publisher}}} }} {{#if: {{{volume|}}} | {{{volume}}}.{{{number|}}} | }} ({{{year}}}){{#if: {{{pages|}}} |<nowiki>:</nowiki> {{{pages}}}. |. }} {{#if: {{{doi|}}} |doi: [http://dx.doi.org/{{{doi}}} {{{doi}}}] | }} {{#if: {{{pmcid|}}} |PMCID: [https://www.ncbi.nlm.nih.gov/pmc/{{{pmcid}}} {{{pmcid}}}] | }}</includeonly>
```

### Template:Sci-mla-citation-formatter-book (imcomplete)

```
<noinclude>[[Category:Semantic Cite]] [[Category:Citation formatter]]</noinclude><includeonly>{{#if: {{{bibtex-author|}}} |{{{bibtex-author}}}. | {{{author}}}. }} ''{{{title}}}''. {{#if: {{{publisher|}}} |{{{publisher}}}. | }}{{#if: {{{edition|}}} |{{{edition}}} ed. | }}, {{{year|pubdate}}}.</includeonly>
```

### Template:Sci-mla-citation-formatter-web-publication

```
<noinclude>[https://owl.english.purdue.edu/owl/resource/747/08/ MLA Works Cited: Electronic Sources] (Web Publications) states that:
* Publisher information, including the publisher name and publishing date.
* Take note of any page numbers (if available).
* Medium of publication.
* Date you accessed the material
* URL (if required, or for your own personal reference; MLA does not require a URL). [[Category:Semantic Cite]] [[Category:Citation formatter]]</noinclude><includeonly>{{#if: {{{authors|}}} |{{{authors}}}. | }} "{{{title}}}". {{#if: {{{publisher|}}} | {{{publisher}}} | }} {{#if: {{{volume|}}} | {{{volume}}}.{{{number|}}} | }} {{#if: {{{year|}}} | ({{{year}}}) | }} {{#if: {{{pages|}}} |<nowiki>:</nowiki> {{{pages}}}. | }} {{#if: {{{url|}}} |<[{{{url}}} {{{url}}}]> | }} Accessed: {{{accessed}}}</includeonly>
```

### Template:Sci-apa-citation-formatter (imcomplete)

```
<noinclude>[[Category:Semantic Cite]] [[Category:Citation formatter]]</noinclude><includeonly>{{{authors}}} ({{{year}}}). {{{title}}}. {{{publisher}}}{{#if: {{{volume|}}} |, {{{volume}}} | }}{{#if: {{{pages|}}} |, {{{pages}}}. |. }}</includeonly>

```

### Template:Sci-apa-citation-formatter-journal (imcomplete)

```
<noinclude>[[Category:Semantic Cite]] [[Category:Citation formatter]]</noinclude><includeonly>{{{authors}}}. ({{{year}}}) "{{{title}}}". ''{{{journal}}}'' {{#if: {{{volume|}}} | {{{volume}}}({{{number|}}}) | }} {{#if: {{{pages|}}} |<nowiki>:</nowiki> {{{pages}}}. |. }} {{#if: {{{doi|}}} |doi: [http://dx.doi.org/{{{doi}}} {{{doi}}}] | }}</includeonly>

```

### Template:Sci-apa-citation-formatter-web-publication (imcomplete)

```
<noinclude>[[Category:Semantic Cite]] [[Category:Citation formatter]]</noinclude><includeonly>{{{authors}}}. ({{{year}}}) "{{{title}}}". ''{{{journal}}}'' {{#if: {{{volume|}}} | {{{volume}}}({{{number|}}}) | }} {{#if: {{{pages|}}} |<nowiki>:</nowiki> {{{pages}}}. |. }} {{#if: {{{url|}}} |Retrieved from {{{url}}}. |. }}</includeonly>

```

### Template:Sci-viaf-citation-formatter

```
<noinclude>Virtual International Authority File (VIAF)[[Category:Semantic Cite]] [[Category:Citation formatter]]</noinclude><includeonly>{{{name}}} ({{{viaf}}})</includeonly>
```

[ext:pf]: https://www.mediawiki.org/wiki/Extension:ParserFunctions
