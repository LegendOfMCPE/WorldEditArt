---
layout: wiki_shape
title: Shapes | Cuboid
excerpt: The simple blocky shape, the essence of Minecraft.
keywords: selection,shape,cuboid,pixel,pos1,pos2
---

## Commands


## Wands


### Order of editing
As CuboidShape is a very regular shape, the order of block placement is important if you want to predict how your
[repeating edits](Editing#repeating-types) will look like.

For non-hollow edit, the placement will start at the block with the lowest (X,Y,Z), then go along the Z-axis. After
completing one line, it goes to the next line along the X-axis. After completing the lowest level, it goes up one level
and continues. I call this order "the ZXY order".

For hollow edits, if padding + margin is more than one-block thick, the placement completes the innermost layer of
"case" first, then the layer wrapping the previous layer, vice versa until the outermost layer is completed.

For each layer, it first starts with the bottom "ground" in the ZX order, then the top "ceiling" in the ZX order. Then
it fills the walls normal to the Z axis, starting with the wall with a smaller Z-coordinate, each side in the XY order.
Finally it fills the remaining two walls in the ZY order.
