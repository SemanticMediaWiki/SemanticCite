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
- `template` is reserved to define a preferred template for output processing
- Other reserved parameters include `doi`, `pmcid`, `pmid`, `olid`, `oclc`, and `viaf` linking
  to its representing property

### Citation key

A citation resource is expected to be identifiable by a unique key and to be available
wiki-wide therefore selecting an appropriate key is paramount to safeguard against
unnecessary changes.

The reference parameter is the descriptor for that key. For example, to describe
a `Segon & Booth 2011` entity the short or the explicit reference parameter form can be used.

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

If it becomes necessary to rename a citation key (because a resource with key `Foo 2007`
no longer represents a unique resource due to adding another resource with the same key)
then the existing usage of that resource needs to be queried and changed before applying
the new citation key (e.g. `Foo 2007a`).

### Type assignment

A type assignment is expected for each citation resource unless `$GLOBALS['scigStrictParserValidationEnabled']`
is set `false`.

If something like `|type=bgn:Thesis;schema:Book|+sep=;` has been generated or specified then
the last entry (e.g. `schema:Book`) will be selected as valid descriptor.

### Bibtex record import

To ease the reuse of bibtex records, `#scite` provides the `|bibtex=` parameter to
import a bibtex record as text which `#scite` will transform into a structured form
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

#### Authors

Authors (e.g. `Einstein, Albert and Podolsky, Boris and Rosen, Nathan`) will be split
into an author list of natural representations (`Albert Einstein` etc.) while the original
annotation text is still available using the hidden `bibtex-author` parameter.

```
{{#scite:
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
}}
```

#### Formatting

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

In the example above, `@article` is parsed as type `article` which can be assigned to a
[template](03-template-mapping.md) that contains the rules of how text elements are to be
positioned but please be aware that for the given example no automatic clean-up are done
for `{...}` or new lines as in `in \n SUSY`.

### Citation text

Content that is directly added to parameter `|citation text=` is stored **as-is** text value with property
`Citation text` and circumventing any additional processing. The property `Citation text` contains the
formatted output of a citation resource used for the [referencelist](05-referencelist.md).

```
{{#scite:Einstein 1956
 |type=book
 |citation text=Einstein, Albert. Investigations on the Theory of the Brownian Movement. Courier Corporation, 1956.
}}
```

If parameter `|citation text=` is not used then `#scite` is trying to determine an output processor
by first looking at the `|template=` parameter and if not declared using the [template](03-template-mapping.md)
assigned to the type in order to process the input and return a formatted text value.

[smw-ns]: https://semantic-mediawiki.org/wiki/Help:$smwgNamespacesWithSemanticLinks
