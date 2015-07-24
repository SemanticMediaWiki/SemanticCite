## #scite parser

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

[smw-ns]: https://semantic-mediawiki.org/wiki/Help:$smwgNamespacesWithSemanticLinks
