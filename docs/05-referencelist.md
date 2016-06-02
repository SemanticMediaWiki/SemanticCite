## #referencelist parser

In general a reference list is auto-maintained (except for the NS_FILE namespace)
and added to the bottom of a page if a citation reference is being detected. In
case a user whishes to place a list differently, `#referencelist` can be used to
mark the position as to where the list is expected to appear.

Citation resources that use the same key are displayed on the reference list and
linked to each other if `$GLOBALS['scigBrowseLinkToCitationResource']` is enabled.
For example, ` ↑ | ↑` is indicating that two resources use the same citation
key with `↑` linking to each of the resources.

### Options

The `#referencelist` parser does accept several options that allows to modify the output
of an individual list.

```
{{#referencelist:
 |listtype=ul
 |browselinks=false // ("true", "1", "on" and "yes") or ( "false", "0", "off", and "no")
 |columns=3
 |header=Notes
}}
```
### Table of contents

To display an entry in the table of contents section (which by default is hidden)
the parameter `|toc=yes` is required.

```
{{#referencelist:
 |listtype=ol
 |browselinks=no
 |columns=1
 |toc=yes
}}
```
### Unbound reference list

To generate an unbound reference list (for notes or additional literature references)
using the `|references=` parameter is required because such list uses the information
provided by this parameter and is not bound to any of the `Citation reference` annotations
made on the source page.

```
{{#referencelist:
 |listtype=ul
 |browselinks=yes
 |columns=1
 |header=Notes
 |references=PMC2483364;Einstein et al. 1935|+sep=;
}}
```

If an unbound list is added an additional `#referencelist` is required to position the
standard list in context of the unbound list.

## Hide the reference list

The magic word `__NOREFERENCELIST__` can be used to suppress a reference list from showing
on an individual page.
