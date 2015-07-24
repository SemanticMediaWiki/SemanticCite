## Questions

> Can I store a `Citation resource` on a page different from where the actual reference is made?

Yes. A `Citation resource` can be stored on any page and is accessible from any page
through the `Citation key` declared by the resource.

> Can I add the same reference multiple times to a (or different) text source?

Yes. Using the same `[[CiteRef:: ...]]` reference at the position that needs citing
is all that is required.

> Is it possible to use a resource defined by `<ref>` (Cite) with #scite?

No. Due to a different technical approach resources declared by `<ref>` can not be used
by (or in) Semantic Cite.

## FAQ

### How to handle different authors

There are various ways of making different authors available to the semantic search
and as ordered output.

For example using a parameter `authors` that is matched to a property `Has author`
(to contain all authors in clear form) while an `author` parameter (not matched to any
property) is used as identifier so that the template formatter can generate the expected
ordered output from `{{{author|}}}`  without having to apply a complex parsing process.

```
{{#scite:Watson and Crick, 1953
 ...
 |author=James D. Watson;Francis HC Crick|+sep=;
 |authors=Watson, James D., and Francis HC Crick
 ...
}}
```