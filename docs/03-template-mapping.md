## Template mapping and citation text rendering

The generation of a citation text is determined by rules and include:

### Type assignment

`MediaWiki:Sci-template-definition` describes and maps citation resource types to specific
templates with a template containing the rules of how identifiers are expected to be formatted
and positioned to generate a citation text.

<pre>
 online|SciteOnlineResourceFormatter
 journal|SciteAPAJournalResourceFormatter
 book|SciteMPABookResourceFormatter
 someothertype|SciteFormatterForAnotherType
</pre>

### Template parameter

A citation resource can override the template type assignment by invoking
the `|template=...` parameter directly.

```
{{#scite:Segon & Booth 2011
 |type=online
 ...
 |journal=Journal of Business Systems, Governance and Ethics
 |available=20 October 2014
 |template=SciteUseDifferentOnlineResourceFormatter
}}
```
### Citation text parameter

Using the `|citation text=...` parameter allows to circumvent
any template rendering by storing the input text "as-is".

```
{{#scite:
 |type=book
 |reference=Barone and Gianfranco, 1982
 |citation text=Barone, Antonio, and Gianfranco Paterno. Physics and applications of the Josephson effect. Vol. 1. New York: Wiley, 1982.
}}
```

The mapping is made flexible enough to support citation styles or types that
do not fit the standard guidelines or use citations for more than just
bibliographic references.
