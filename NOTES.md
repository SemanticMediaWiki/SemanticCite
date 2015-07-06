## Technical notes

- Property `Citation key` is a string value that identifies a citation resource
- Property `Citation reference` (or its alias `CiteRef`) is representing the citation key when used as in-text
  annotation and contains special rules for display and text placement
- Property `Citation resource` describes an individual entity that is uniquely identified
  by a key. It represents the sum of all information collectively stored as subobject.
- Property `Citation text` contains the citation output generated from a template or free text assignment
- Property `DOI` is a string value that is transformed into an appropriate URL link when displayed
- Property `PMCID` is a string value that is transformed into an appropriate URL link when displayed
- Pre-deployed properties are scheduled to create separate property tables that can be found under `smw_ftp_sci*`
- `#scite` parser is self-sustained and does not make use of any of the `Cite`
  provided functionality

## Citation reference

The `CitationReferenceValue` object together with the `CitationReferencePositionJournal` are building
the basis in counting and identifying the position of each `[[CiteRef::]]` annotation in a text source.

## Reference list rendering

The `CachedReferenceListOutputRenderer` is responsible for caching the generated list that
is retrieved from `ReferenceListOutputRenderer` together with an appropriate text position (which
if the `{{referencelist:}}` parser is used can be different from the default bottom position).

`ReferenceListOutputRenderer` uses the position information from `CitationReferencePositionJournal`
that was collected from each `Citation reference` annotation within a page and enables to generate
a list of references matched with information from the `CitationResourceMatchFinder`.

`{{referencelist:}}` adds a placeholder (since all parser function run before the `OutputPage` hooks)
ti the position the list is expected to appear. This placeholder is later replaced
by the outcome of `preg_replace_callback` in `CachedReferenceListOutputRenderer`.