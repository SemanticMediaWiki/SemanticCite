## General settings

- `$GLOBALS['scigCitationReferenceCaptionFormat']` specifies the display format for a citation
  reference to be displayed either as a number (`SCI_CITEREF_NUM`) or by its key (`SCI_CITEREF_KEY`)
- `$GLOBALS['scigShowTooltipForCitationReference']` maintains an array for which format the tooltip
   can be shown (`false` or an empty array will disable the tooltip)
- `$GLOBALS['scigTooltipRequestCacheTTLInSeconds']` to allow to store tooltip query results from
   the backend to the local browser cache in order to avoid repeated requests for already queried
   references. Setting this parameter to false will disable the cache. Items that are cached will show
   a `[+]` as indicator.
- `$GLOBALS['scigReferenceListCacheType']` to disable caching for the reference list, use setting
  [`CACHE_NONE`][mw-cachetype] otherwise the cache is being renewed an each new revision or when
  the page is purged
- `$GLOBALS['scigStrictParserValidationEnabled']` whether a strict validation of input data for
  the `{{#scite:}}` parser should be carried out or not
- `$GLOBALS['scigEnabledCitationTextChangeUpdateJob']` whether an update job should be dispatched
  for changed citation text entities or not

### Reference list

- `$GLOBALS['scigNumberOfReferenceListColumns']` specifies the number of columns to be shown
  on the reference list
- `$GLOBALS['scigReferenceListType']` either formatted using `ul` or `ol`
- `$GLOBALS['scigBrowseLinkToCitationResource']` whether to show a browse link to the citation
  resource or not

### Metadata search

- `$GLOBALS['scigMetadataResponseCacheType']` specifies the type of the cache expected to be used
- `$GLOBALS['scigMetadataResponseCacheLifetime']` specifies the time duration responses are cached from a
   metadata service provider
- `$GLOBALS['wgGroupPermissions']['user']['sci-metasearch'] = true;` to restricted access to users

## Configuration

- [Property mapping](https://github.com/SemanticMediaWiki/SemanticCite/blob/master/docs/02-property-mapping.md)
- [Template mapping](https://github.com/SemanticMediaWiki/SemanticCite/blob/master/docs/03-template-mapping.md)

[mw-cachetype]: http://www.mediawiki.org/wiki/Manual:$wgMainCacheType
