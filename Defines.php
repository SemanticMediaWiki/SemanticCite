<?php

/**
 * Constants for the Semantic Cite extension.
 *
 * Loaded via Composer's `autoload.files` so that the constants are available
 * early, including for use in `LocalSettings.php`. It must remain safe to load
 * outside of MediaWiki (e.g. while Composer runs its scripts), so it does no
 * more than define constants.
 *
 * @codeCoverageIgnore
 */

// In-text citation reference format options
define( 'SCI_CITEREF_NUM', 1 );
define( 'SCI_CITEREF_KEY', 2 );
