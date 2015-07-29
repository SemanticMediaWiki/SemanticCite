## In-text citation

A resource can easily be cited using the `Citation reference` (or its alias `CiteRef`)
property for an in-text annotation such as `Lorem ipsum [[CiteRef::Byrne 2008]] ...` to appear as
`Lorem ipsum`<sup>`[1]`</sup>` ...`.

### References and citation keys

Citation keys are available wiki-wide therefore selecting an
appropriate key is paramount to safeguard against unnecessary changes.

- If it becomes necessary to rename a citation key (because a resource with key
  `Foo 2007` no longer represents a unique resource due to adding another resource
  with the same key) then the existing usage of that resource needs to be queried and
  changed before applying the new citation key (e.g. `Foo 2007a`).
- Citation resources that use the same key are displayed on the reference list
  and linked to each other in case `$GLOBALS['scigBrowseLinkToCitationResource']` is set
  true. For example, ` ↑ | ↑` is indicating that two resources use the same citation
  key with `↑` linking to each resource.
- The parentheses style of a citation reference can be modified using the `scite.styles.css`
  style sheet.
- If a page number reference is required then one can use the longform syntax
  `[[CiteRef::Foo and Bar, 1970|Foo and Bar, 1970:42]]` for `SCI_CITEREF_KEY` to highlight
  the page 42 while the shortform `[[CiteRef::Foo and Bar, 1970|:42]]` is suggested for
  `SCI_CITEREF_NUM` in order to appear as `...`<sup>`[1]:42`</sup>
- To avoid cluttering a source text with citation resources it is suggested to divide
  text and resource definitions by storing `{{#scite:}}` resources on a related a subpage
  and use the `[[CiteRef:: ...]]` annotation on the source page for inclusion.
