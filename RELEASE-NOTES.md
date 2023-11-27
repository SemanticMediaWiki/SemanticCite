This file contains the RELEASE-NOTES of the **Semantic Cite** (a.k.a. SCI) extension.

## 2.2.0
Released on November 28, 2023.

* Minimum requirement for
  * PHP changed to version 7.4 and later
  * MediaWiki changed to version 1.35 and later
* #116 Fixed support for MediaWiki 1.39 and Semantic MediaWiki 4.0
* Several internal code changes
* Localisation updates from https://translatewiki.net

## 2.1.1

Released on May 16, 2020.

* #71 Fixed support for MediaWiki 1.31.x
* Localization updates from https://translatewiki.net

## 2.1.0

Released on August 18, 2019.

* Minimum requirement for
  * PHP changed to version 7.0 and later
  * MediaWiki changed to version 1.31 and later
  * Semantic MediaWiki changed to version 3.0 and later
* Added support for Semantic MediaWiki 3.1 and later
* Minor bug fixes and code improvements
* Localization updates from https://translatewiki.net

### 2.0.1

Released on October 9, 2018.

* Added support for translatable extension name
* Localization updates from https://translatewiki.net

### 2.0.0

Released on October 6, 2018.

* Minimum requirement for PHP changed to version 5.6 and later
* Minimum requirement for MediaWiki changed to version 1.27 and later
* #62 Added support for extension registration via "extension.json"  
  → Now you have to use `wfLoadExtension( 'SemanticCite' );` in the "LocalSettings.php" file to invoke the extension
* #60 Made citation targets to be highlighted (only works with Semantic MediaWiki 3.0.0 and later)
* #60 Made the link to resource on special page "Browse" (`Special:Browse`) adaptable via class  
  `.scite-citation-resourcelink a:before`
* Several internal code changes
* Localization updates from https://translatewiki.net


### 1.4.0

Released on May 28, 2017.

* Replace `edit` with `csrf` in `api.postWithToken` (see https://www.mediawiki.org/wiki/API:Tokens)
* Added `PreTextFormatter` to format `#scite` output with parameter `@show`
* Check `wfReadOnly` before making an API request to avoid a `badtoken`
* Generated pages via `Special:FindCitableMetadata` no longer copy the `#scite`
  as `pre` formatted text and instead use `@show` to output a human readable content
* Localization updates from https://translatewiki.net

### 1.3.0

Released on April 29, 2017.

* Minimum requirement for PHP changed to version 5.5 and later
* Minimum requirement for Semantic MediaWiki changed to version 2.5 and later
* #42 Fixed isse with `Special:FindCitableMetadata`
* Internal code changes
* Localization updates from https://translatewiki.net

### 1.2.0

Released on November 5, 2016.

* #35 Introduced a different CSS class schema to accommodate observations from #32 and #33
* #36 Introduced a configuration setting to control the selection of the appropriated number of columns based on the screen width
* Localization updates from https://translatewiki.net

### 1.1.0

Released on July 9, 2016.

* #27 Disabled the auto-referencelist for the `NS_FILE` namespace
* #25 Fixed method visibility in connection with SMW 2.4
* Added `onoi/shared-resources` as dependency
* Localization updates from https://translatewiki.net

### 1.0.0

Released on November 19, 2015.

* Initial release
* Added `#scite` parser function to record and create citation resources
* Added `#referencelist` parser function to modify the reference list display behaviour
* Added `Special:FindCitableMetadata` to download and create citeable metadata from external data provider
