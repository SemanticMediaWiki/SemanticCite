## In-text citation

A resource can easily be cited using the `Citation reference` (or its alias `CiteRef`)
property. For example, a simple in-text annotation is created by `Lorem ipsum [[CiteRef::Byrne 2008]] ...` to appear as
`Lorem ipsum`<sup>`[1]`</sup>` ...`.

### References and citation keys

- The parentheses style of a citation reference can be modified using the `scite.styles.css`
  style sheet.
- If a page number reference is required then one can use the longform syntax
  `[[CiteRef::Foo and Bar, 1970|Foo and Bar, 1970:42]]` for `SCI_CITEREF_KEY` to highlight
  the page 42 while the shortform `[[CiteRef::Foo and Bar, 1970|:42]]` is suggested for
  `SCI_CITEREF_NUM` in order to appear as `...`<sup>`[1]:42`</sup>
- To avoid cluttering a source text with citation resources it is suggested to divide
  text and resource definitions by storing `{{#scite:}}` resources on a related a subpage
  and use the `[[CiteRef:: ...]]` annotation on the source page for inclusion.

## Backlinks (Where is the resource used?)

A citation resource displayed by `Special:Browse` will not only list all available
structured data but also indicate subjects that have a reference to the key annotated
by the `Citation reference` property.