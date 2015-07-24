# Metadata search

`Special:FindMetadataById` provides access to selected metadata provider that can
map the etxernal format to the internal `#scite` format. Supported providers are:

- PubMed (PMID, PMCID)
- CrossRef (DOI)
- OpenLibrary
- OCLC (WorldCat)
- VIAF

After a successful search, the generated `#scite` form can either be copied manually to a page
or the auto creation feature is used to create an individual article to host the data.

The data received from a service provider are compacted to fit the `#scite` format but the
parameter `&format=raw` can be used to display the unprocessed data retrieved from a provider.

If a citation resource already exists for one of the selected identifiers then a link
to this resource is provided.

## Citation resource auto creation

`Special:FindMetadataById` allows to create a dedicated article contaning the mapped
`#scite` with content being copied to a page where the title is generated from:

- `CR:` (as fixed identifier indicating a citation resource article)
- UID prefix ( `PMC`, `PMID`, `OCLC` etc.) and
- UID itself

For example, a search for `18487186` on the PubMed database will allow to create article
`CR:PMID:18487186` containing the filtered `#scite` content that was matched during the search.

### CR: namespace

It is possible to customize the prefix `CR:` and be recognized as [separate namespace][mw-cns] to
select resources on a namespace bases or be distinguishable from other content.

```php
// Define custom CR namespace
define( "NS_CR", 3000 );
define( "NS_CR_TALK", 3001 );
$GLOBALS['wgExtraNamespaces'][NS_CR] = "CR";
$GLOBALS['wgExtraNamespaces'][NS_CR_TALK] = "CR_talk";

// Enable annotation for the CR namespace when used as custom NS
$GLOBALS['smwgNamespacesWithSemanticLinks'][NS_CR] = true;
```

## Related settings

To avoid repeated downloads from a service provider for the same search request, positive responses
are cached using the specified `$GLOBALS['scigMetadataRequestCacheTTLInSeconds']` lifetime.

By default, the access to the search is restricted to users with the `sci-metasearch` right.

## Example

![image](https://cloud.githubusercontent.com/assets/1245473/8856154/490ac43e-3169-11e5-8b79-52ff1adf05ad.png)

[mw-cns]: https://www.mediawiki.org/wiki/Manual:Using_custom_namespaces
