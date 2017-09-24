---
layout: wiki
title: Editing
excerpt: The core of WorldEditArt - Edit the world with your artistic mind!
keywords: selection,edit,replace,set
---

There are two main modes of editing &mdash; Set and Replace. "Set" is to change **all** blocks in a selection, while
"replace" is to change blocks of **certain types** in the selection.

The blocks can be changed **to** all the same type, or a (weighted) random type in a list, or a repeating sequence of
types.

## Syntax
"Set" can be done with the `//set` command (alias `//s`):

```
//set [r] [h[=<padding>[,<margin>]]] [s <selectionName>] <to blocks [x<weight>]>...
```

"Replace" can be done with the //replace command (alias `//rep`):

```
//replace [r] [h[=<padding>[,<margin>]]] [s <selectionName>] <from blocks ...> with <to blocks [x<weight>] ...>
```

Looks very complicated? Let's look at it part by part:

#### `<to blocks [x<weight>] ...>`
This is a list of [block types](#block-types) separated by spaces.

If you want to apply [weighting](#weighting)/[frequency](#frequency) to a block type, type a space after it, then an
`x`, then the weight/frequency. If no weight/frequency is provided, it is is assumed as `1`.

For example, the following command will have `air` with a weight of `0.8`, `stone` with a weight of `1`, and
`cobblestone` with a weight `0.5`, i.e. 8:10:5 ratio:

```
//set air x0.8 stone cobblestone x0.5
```

#### `<from blocks ...>`
This is a list of [block types](#block-types) separated by spaces. Weighting/frequency is not supported in this list.

#### `with`
If there is more than one block type in `<from blocks>` or in `<to blocks>`, they must be separated by a
`with`/`as`/`to`/`>` to distinguish which ones are "from blocks" and which ones are "to blocks".

#### `[r]`
`r` means [repeating](#repeating-types). If you want repeating types instead of [random types](#random-types):

```
//set r stone x4 glowstone glass x4 jack_o_lantern
```

This will place 4 stones, one glowstone, four glass blocks and one Jack O' lantern. On the other hand, without `r`:

```
//set r stone x4 glowstone glass x4 jack_o_lantern
```

For each block, there will be 40% chance stone, 40% chance glass, 10% chance glowstone and 10% chance Jack O' lantern.

#### `[h[=<padding>[,<margin>]]]`
`h` means [hollow](#hollow-selection).

* If you just write `h`, the padding is one-block thick and there is no margin.
* To specify the padding, write `h=<padding>`, e.g. if padding is 10, write `h=10`.
* To specify both the padding and the margin, write `h=<padding>,<margin>`, e.g. if there is no padding and margin is 2-
  block thick, write `h=0,2`.

If the `=` sign is hard to reach on your keyboard, you can type `h:` instead of `h=`.

#### `[s <selectionName>]`
`s` means selection. You may choose [which selection](Selection#default-selection-vs-multiple-selections) to work with,
e.g.:

```
//set s two stone
```

This will change all blocks in the selection called `two` to stone.

## Block types
A block type can be any of the following:

1. The block ID or the block name .
  * The list of block names is the same as those in `/give` (e.g. `bedrock`, `coal_ore`, etc.). See
    [this file](https://github.com/pmmp/PocketMine-MP/blob/master/src/pocketmine/block/BlockIds.php) for all names.
  * The block damage is assumed `0`
  * If this is in the `<from blocks>` list, only blocks with damage 0 will be replaced
2. `1.` followed by `:`, then the block damage as a number
3. `1.` followed by `:`, then the block variant type, e.g. `stone:diorite`
4. `1.` followed by `:*`/`:any`/`:random`.
  * If this is in the `<to blocks>` list, a random number between 0 and 15 will be used.
  * If this is in the `<from blocks>` list, all blocks matching the block ID will be replaced.
5. ~~`1.` followed by `:in` (only applicable for block types in `<to list>`)~~ (Upcoming feature)
  * If the block is rotatable, it will be rotated to face the center of the selection.
6. ~~`1.` followed by `:out` (only applicable for block types in `<to list>`)~~ (Upcoming feature)
  * If the block is rotatable, it will be rotated to face **away from** the center of the selection.
7. **Preset:** `preset:` followed by the [preset name](Preset).
  * If this is in `<from blocks>`, this will include all blocks in the preset to the `<form blocks>` list.
  * See below for behaviour in `<to blocks>`.

### Random types
If there are multiple types, a random block is selected from the list.

#### Weighting
Weighting means that each block type has a "weight", proportional to their probability of being selected. Blocks with a
higher weight have a higher chance to get selected.

Weights don't have to add up to 100, and weights can be decimal numbers.

### Repeating types
The [`r`](#r) option allows you to use repeating types. This means that the first block type will be used for the first
block changed, vice versa. The first block type will be used again after the last block type in the list.

#### Frequency
If a block type has a frequency, it will repeat by that number of times before the next block type is used. It is only
reasonable to use positive integers as the frequency. What if decimal numbers are used? You guess.

## Hollow selection
A hollow selection means that only the blocks on the surface of the selection will be affected.

To be more precise, "hollow" is defined by two parameters &ndash; the padding and the margin. The padding is the
thickness **beneath** the surface, while the margin is the thickness **above** the surface, counted in blocks.

For nerds, a mathematical representation of the shape `S`'s hollow selection `S.H(padding, margin)` is

```
all blocks whose shortest distance to the surface of S, multiplied by -1 if the block is inside the shape,
is in the closed range [-padding, margin - 1]
```

For example, for `padding = 1, margin = 0`, only the outermost blocks **inside** the selection touching the surface
will be edited. For `padding = 0, margin = 1`, only the blocks **outside but touching** the selection will be edited.
