# Metadata search

![image](https://cloud.githubusercontent.com/assets/1245473/10266229/1c8b9bca-6a55-11e5-9ccb-cb44bfb400f0.png)

`Special:FindCitableMetadata` provides access to selected metadata provider to map
external data to the internal `#scite` format. Supported providers are:

- PubMed (PMID, PMCID)
- CrossRef (DOI)
- OpenLibrary
- OCLC (WorldCat)
- VIAF

This [video](https://www.youtube.com/watch?v=d2dNFTUUUjs) shows the usage variety
of the metadata search.

## Direct search

A link `Special:FindCitableMetadata` + `/doi/10.1126/science.1152662` or `/pubmed/18487186`
with a type and ID parameter allows to execute a search immediately.

## Search

After a successful search, a generated `#scite` form is provided which can either
be copied manually (see the `Search text` button) or with the help of the auto creation
feature (use the `Create` button) can add an individual article directly from the
special page.

The data received from a service provider are compacted to fit the `#scite` format but the
parameter `&format=raw` can be used to display the unprocessed data retrieved from a provider.

If a citation resource already exists for one of the selected identifiers then a link
to this resource is provided.

### Citation resource auto creation

`Special:FindCitableMetadata` allows to create a dedicated article containing the mapped
`#scite` with content from the search and a page title that is generated from:

- `CR:` (is the fixed identifier indicating a citation resource article)
- UID prefix ( `PMC`, `PMID`, `OCLC` etc.) and
- UID itself

For example, a search for `18487186` on the PubMed database is expected to create the article
`CR:PMID:18487186` containing the filtered `#scite` content matched during the
search if the user was to engage in a `Create` action.

### CR: namespace

It is possible to customize `CR:` and be recognized as [separate namespace][mw-cns] so
that it is distinguishable from other content.

```php
// Define custom CR namespace
define( "NS_CR", 3000 );
define( "NS_CR_TALK", 3001 );
$GLOBALS['wgExtraNamespaces'][NS_CR] = "CR";
$GLOBALS['wgExtraNamespaces'][NS_CR_TALK] = "CR_talk";

// Enable annotation for the CR namespace when used as custom NS
$GLOBALS['smwgNamespacesWithSemanticLinks'][NS_CR] = true;
```

If this step is done after resources have already been created `rebuildData.php` needs to be
executed to ensure that annotations are reconnected to the newly assigned namespace number.

## Related settings

To avoid repeated data downloads from a service provider, requests are cached that contain the
same signature with an expiry specified by `$GLOBALS['scigMetadataResponseCacheLifetime']`.

By default, the access to the search is restricted to users with the `sci-metasearch` right.

[mw-cns]: https://www.mediawiki.org/wiki/Manual:Using_custom_namespaces
