## Getting started

- Define property and text formatting rules
- Create a resource with `#scite`
- Create an in-text citation using `[[Citation reference:...]]` (= `[[CiteRef:...]]`)

## Create a citation resource

To start without any extra customizing (or property mapping), a very convenient yet simple way to faciliate
`#scite` is add the `|citation text=...` parameter directly and assign a text expected
to be displayed as-is without further processing.

```
{{#scite:Einstein 1956
 |type=book
 |citation text=Einstein, Albert. Investigations on the Theory of the Brownian Movement. Courier Corporation, 1956.
}}
```

## Create an in-text reference

The text expected to include a reference only requires to have a simple annotation (e.g. `[[CiteRef::Einstein 1956]]`)
and a reference link together with a reference list will appear on the page that
includes the `Citation reference` annotation.

## What about structured data?

To fully utilize `#scite` in terms of property annotations or individual template output
 formatting, see the [property mapping](02-property-mapping.md) and [template mapping](03-template-mapping.md) section.

## Citation styles

`#scite` allows to support different citation styles (harvard, mla etc.) with a
style being determined by a type and/or [templates](03-template-mapping.md)
assigned.