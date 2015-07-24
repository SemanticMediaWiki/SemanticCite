## Template mapping

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


