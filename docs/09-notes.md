# Technical notes

## Properties

- Property `Citation key` is a string value that identifies a citation resource
- Property `Citation reference` (or its alias `CiteRef`) is representing the citation key when used as in-text
  annotation and contains special rules for display and text placement
- Property `Citation resource` describes an individual entity that is uniquely identified
  by a key. It represents the sum of all information collectively stored as subobject.
- Property `Citation text` contains the citation output generated from a template or free text assignment
- Pre-deployed properties are scheduled to create separate property tables that can be found under `smw_ftp_sci*`
- Properties for external representation include `DOI`, `PMCID`, `PMID`, `VIAF`,
  `OCLC`, and `OLID` as a `UidValue` (a string value that is transformed into an
  appropriate output URL representation when displayed)

## Citation reference

The `CitationReferenceValue` object together with the `CitationReferencePositionJournal` are building
the basis in counting and identifying the position of each `[[CiteRef::]]` annotation in a text source.

## #scite parser

`#scite` parser is self-sustained and does not make use of any of the `Cite` provided functionality.

## #referencelist parser

The `CachedReferenceListOutputRenderer` is responsible for caching the generated list that
is retrieved from `ReferenceListOutputRenderer` together with an appropriate text position (which
if the `{{#referencelist:}}` parser is used can be different from the default bottom position).

`ReferenceListOutputRenderer` uses the position information from `CitationReferencePositionJournal`
that was collected from each `Citation reference` annotation within a page and generatesa list of
references matched with information from the `CitationResourceMatchFinder` (citation text etc.).

`{{#referencelist:}}` adds a simple placeholder (this is required as all parser functions run before
the `OutputPage` hooks) to mark the position of the list where it is expected to appear. This
placeholder is later replaced by `CachedReferenceListOutputRenderer::addReferenceListToCorrectTextPosition`.

## Metadata search / provider

To register a new metadata provider (e.g. NCBI gene or protein DB), `HttpRequestLookupFactory`
is expected to provide the object invocation by having:

- Access to a `HttpRequestContentFetcher` (that actual makes the REST request) and
- A `HttpResponseContentParser` which depending on the format (json, xml, text etc.)
  parses the response and returns a `FilteredMetadataRecord` with an ordered/reduced
  set of values from the source database.