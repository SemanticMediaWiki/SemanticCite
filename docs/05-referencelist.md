## #referencelist parser

Normally a reference list is self-maintained and added to the bottom a page if
a citation reference is present but in case the list should positioned
differently, `{{#referencelist:}}` can be used to mark the position on where the
list is expected to appear.

`{{#referencelist:}}` parser does accept options that can modify the output of an individual
reference list.

```
{{#referencelist:
 |listtype=ul
 |browselinks=false // ("true", "1", "on" and "yes") or ( "false", "0", "off", and "no")
 |columns=3
 |header=Notes
}}
```

To display a table of contents section for the reference list (by default the auto-added
list is hidden) the parameter `|toc=yes` should be added to:

```
{{#referencelist:
 |listtype=ol
 |browselinks=no
 |columns=1
 |toc=yes
}}
```

To generate a nonbound reference list (for notes or additional literature references)
using the `|reference=` parameter is required because such list uses the information
provided by `|reference=` and is not bound to any of the `Citation reference`
annotations made to a particular page or subject.

```
{{#referencelist:
 |listtype=ul
 |browselinks=yes
 |columns=1
 |header=Notes
 |references=PMC2483364;Einstein et al. 1935|+sep=;
}}
```