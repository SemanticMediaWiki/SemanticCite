# Getting started

- Define property and text formatting rules
- Create a resource with `#scite`
- Create an in-text citation using `[[Citation reference:...]]` (= `[[CiteRef:...]]`)

## Create a citation resource

A simple citation resource can be created using `#scite` and the
`|citation text=...` parameter without the need for any extra customization (or
property mapping).

```
{{#scite:Einstein 1956
 |type=book
 |citation text=Einstein, Albert. Investigations on the Theory of the Brownian Movement. Courier Corporation, 1956.
}}
```

## Create an in-text reference

To create a in-text reference, a user only is required to add an annotation (e.g. `[[CiteRef::Einstein 1956]]`)
to the selected text position and after the document is saved, a reference link
together with a reference list will appear on the page that includes the
`Citation reference` annotation.

## What about structured data?

For more information about how to map local [properties](02-property-mapping.md) and
how to use other features of the `#scite` parser, have a look at this [help](04-scite.md)
document.

## What about citation styles?

`#scite` allows to support different citation styles making use of the existing
[template](03-template-mapping.md) infrastructure to generate a customized text output.