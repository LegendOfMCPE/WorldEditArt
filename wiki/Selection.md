Selection
=========

A selection is like selecting text in a document editor. In a document editor, you can do different things on a text
selection, e.g. changing text color, deleting the text, searching words inside the text, copying/moving the text to
somewhere else, etc.

In WorldEditArt, _blocks_ are selected instead. After you make a selection, you can do many cool things with it, like:

- viewing block statistics inside
- changing all/some blocks inside
- creating [construction zones](Construction_zone)

## Default Selection vs Multiple Selections
> Warning: This section is slightly complicated. If you don't understand it, you can safely skip it and ignore all
  `[selectionName]` arguments you see in WorldEditArt commands; just remember that whenever WorldEditArt says
  `your "default" selection` it is referring to your very only selection.

WorldEditArt allows you to manage multiple selections at the same time by giving them different names.

Most commands that use your selection will have a `[selectionName]` argument. You may put the name (case-insensitive) of
the selection you wish to be used. If this argument is skipped, your _default selection name_ will be used.
[Wands](Wand), a special form of commands, will also use the default selection name.

Normally, your default selection name is `default`. You may change it to other names using the `//use` command, e.g.
`//use castle` will change your default selection name to `castle`.

> Note: The default selection is only a **name** &mdash; even if you select something else with the same selection name,
  or even deselect the selection, the default selection will still not be affected.

<!-- TODO: Implement sending selection to another builder -->

## Managing selections
### Creating selections

### Displaying selections
You may see what selections are available using the

