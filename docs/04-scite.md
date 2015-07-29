## #scite parser

`{{#scite:}}` parser can only be used in [namespaces][smw-ns] that are enabled
for Semantic MediaWiki.

### Reserved parameters

Parameters (or identifiers) are free from any restrictions
besides those listed below:

- `type` is a reserved parameter and is required for when `$GLOBALS['scigStrictParserValidationEnabled']` is set `true`
- `reference` is a reserved parameter and is linked to the `Citation key` property
- `citation text` is a reserved parameter and is linked to the `Citation text` property
- `sortkey` is a reserved parameter and is linked to the `_SKEY` property and can be
   set to find a resource more easily during querying as the resource is by default set
   to the internal resource id.
- `bibtex` is a reserved parameter and used for the bibtex record import
-`template` is reserved to define a preferred template for output processing
- Other reserved parameters include:
 - `doi` is linked to the `DOI` property
 - `pmcid` is linked to the `PMCID` property
 - `pmid` is linked to the `PMID` property
 - `olid` is linked to the `OLID` property
 - `oclc` is linked to the `OCLC` property
 - `viaf` is linked to the `VIAF` property

### Unique identifier

A citation resource is expected to be identifiable by a unique key. The reference
parameter is the descriptor for that key. For example, to describe a `Segon & Booth 2011`
entity the short or the explicit reference parameter form can be used.

```
{{#scite:Segon & Booth 2011
  ...
}}
```
```
{{#scite:
  |reference=Segon & Booth 2011
  ...
}}
```

### Type assignment

A type assignment is expected for each citation resource unless `$GLOBALS['scigStrictParserValidationEnabled']`
is set `false`.

If something like `|type=bgn:Thesis;schema:Book|+sep=;` has been generated or specified then
the last entry (e.g. `schema:Book`) will be selected as valid descriptor.

### Bibtex import

To ease the reuse of bibtex records, `#scite` provides the `|bibtex=` parameter to
import a record as text which `#scite` will transform into a structured form
according to the [property](02-property-mapping.md) and [template](03-template-mapping.md) mapping.

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
```
```
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

### Citation text

Content directly added to parameter `|citation text=` is stored as text value to property
`Citation text` without further processing. Property `Citation text` is used as output for
the [referencelist](05-referencelist.md) display.

```
{{#scite:Einstein 1956
 |type=book
 |citation text=Einstein, Albert. Investigations on the Theory of the Brownian Movement. Courier Corporation, 1956.
}}
```

If parameter `|citation text=` is not used then a [template](03-template-mapping.md)
assigned to a type is expected to parse the input and return a text value to be assigned to
to property `Citation text`.

```
{{#scite:
 |bibtex=@article{Marshakov:2010si,
      author         = "Marshakov, A.",
      title          = "{Period Integrals, Quantum Numbers and Confinement in
                        SUSY QCD}",
      journal        = "Theor. Math. Phys.",
      volume         = "165",
      year           = "2010",
      pages          = "1650-1661",
      doi            = "10.1007/s11232-010-0135-y",
      note           = "[Teor. Mat. Fiz.165,488(2010)]",
      eprint         = "1003.2089",
      archivePrefix  = "arXiv",
      primaryClass   = "hep-th",
      reportNumber   = "FIAN-TD-02-10, ITEP-TH-05-10",
      SLACcitation   = "%%CITATION = ARXIV:1003.2089;%%"
}
}}
```

In the example above, `@article` is parsed as type `article` which is assigned to a template
that contains the rules of how text elements are to be positioned but please be aware that
for the given example no automatic clean-up are done for `{...}` or new lines as in `in \n SUSY`.

[smw-ns]: https://semantic-mediawiki.org/wiki/Help:$smwgNamespacesWithSemanticLinks
