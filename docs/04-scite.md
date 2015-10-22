# #scite parser

`{{#scite:}}` parser can only be used in [namespaces][smw-ns] that are enabled
for Semantic MediaWiki.

```
{{#scite:Byrne 2008
 |type=journal
 |author=Byrne, A
 |year=2008
 |title=Web 2.0 strategies in libraries and information services
 |journal=The Australian Library Journal
 |volume=57
 |number=4
 |pages=365-376
}}
```

## Reserved parameters

Parameters (or identifiers) are free from any restrictions
besides those listed below:

- `type` is a reserved parameter
- `reference` is a reserved parameter and is linked to the `Citation key` property
- `citation text` is a reserved parameter and is linked to the `Citation text` property
- `sortkey` is a reserved parameter and is linked to the `_SKEY` property and can be
  set to find a resource more easily during querying as the resource is by default set
  to the internal resource id.
- `bibtex` is a reserved parameter and used for the bibtex record import
- `template` is reserved to define a preferred template for output processing
- Other reserved parameters include `doi`, `pmcid`, `pmid`, `olid`, `oclc`, and `viaf` linking
  to its representing property

## Citation key

A citation resource is expected to be identifiable by a unique key and to be available
wiki-wide therefore selecting an appropriate key is paramount to safeguard against
unnecessary changes.

The reference parameter is the descriptor for that key. For example, to describe
a `Byrne 2008` resource the short or the explicit reference parameter form can be used.

```
{{#scite:Byrne 2008
  ...
}}
```
```
{{#scite:
  |reference=Byrne 2008
  ...
}}
```

If it becomes necessary to rename a citation key (because a resource with key `Foo 2007`
no longer represents a unique resource due to adding another resource with the same key)
then the existing usage of that resource needs to be queried and changed before applying
the new citation key (e.g. `Foo 2007a`).

## Citation text

The property `Citation text` contains the formatted output of a citation resource and is
used when the [referencelist](05-referencelist.md) is generated. The text is formatted using
assinged template or can be added directly (without further processing) in its final form
to the `|citation text=` parameter.

```
{{#scite:Einstein 1956
 |type=book
 |citation text=Einstein, Albert. Investigations on the Theory of the Brownian Movement. Courier Corporation, 1956.
}}
```

In case the parameter `|citation text=` is not declared then `#scite` is going to try to determine
an a template by first looking at the `|template=` parameter and if such parameter is not assigned
then the [template](03-template-mapping.md) assigned to the type of the resource
is used for processing to return a formatted text value.

```
{{#scite:Einstein 1956
 |type=book
 |authoru=Albert Einstein
 |title=Investigations on the Theory of the Brownian Movement
 |publisher=Courier Corporation
 |year=1956
 |template=FormatThisEntityAccordingToHarvardStyle
}}
```

If `$GLOBALS['scigEnabledCitationTextChangeUpdateJob']` is set true then a change to
a citation text will initiate an update job for those pages that make reference to the
related citation resource.

## Type assignment

A type assignment is expected for each citation resource unless `$GLOBALS['scigStrictParserValidationEnabled']`
is set `false`.

If multiple types are assigned (e.g.`|type=bgn:Thesis;schema:Book|+sep=;`) then
the last entry (e.g. `schema:Book`) will be selected as valid type descriptor.

## Bibtex record import

To ease the reuse of bibtex records, `#scite` provides the `|bibtex=` parameter to
import a bibtex formatted text to create annotatable record following
the assignments declared in the `MediaWiki:` [property](02-property-mapping.md) and
[template](03-template-mapping.md) page.

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

### Author list

Authors (e.g. `Einstein, Albert and Podolsky, Boris and Rosen, Nathan`) will be split
into an author list of natural representations (`Albert Einstein` etc.) while the original
annotation string is still available using the hidden `bibtex-author` parameter.

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

### Content formatting

`@article` is parsed as type `article` that can be assigned to a specific [template](03-template-mapping.md)
containing the rules of how text elements are to be formatted. Please be aware
that no automatic clean-up is done on elements like containing `{`/`}` or new lines as in
`in \n SUSY`. Furthermore, complex expressions (those involve macros etc.) are
not parsed or resolved.

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

[smw-ns]: https://semantic-mediawiki.org/wiki/Help:$smwgNamespacesWithSemanticLinks
