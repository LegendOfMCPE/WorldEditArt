Project Proposal for a World Edit Plugin
===
This proposal describes a world edit plugin largely based on WorldEditArt's originally-intended features, along with some new ideas.

# Plugin Name
This plugin will inherit the development of the WorldEditArt project and bring it to the _Epsilon_ phase (_WorldEditArt Epsilon_).

# About Social Stuff
## Owner and Developers
This plugin be continued in the LegendOfMCPE/WorldEditArt repository.

## PocketMine-MP version
This plugin is going to be targeted at PocketMine-MP API 3.0.0-ALPHA7 onwards.

## Libraries
This plugin is going to use [_libgeom_](https://github.com/BlockHorizons/libgeom) for geometric calculation.

# Plugin Mechanism
## Builder Sessions
Users of this plugin should be managed in "builder sessions" with individual access information. Builder sessions can be created in three modes, namely:

 * Implicit Mode
   * Players with a certain permission will start a builder session upon joining a game. The session's permission and location will be synchronized with the player.
 * Explicit Mode
   * Players with a certain permission will start a builder session upon typing a command. The session's permission and location will be synchronized with the player, and can be closed with a command.
   * The command may be locked with a private password (similar to the `sudo` Linux command) or global password (similar to the `su` Linux command) for additional protection.
 * Minion Mode
   * Command senders with a certain permission (especially non-in-game senders like console) can create minion builder sessions upon typing a command. The session's permission and location will be controlled by the command sender.

Each builder session has an allocated amount of resources; this allocation may affect the rate of world-editing operations to maximize server performance.

For implicit and explicit modes, the builder session's position and orientation is synchronized with the player. The position uses the block that **the player's feet stand in**.

 * If the player is floating on a lake of liquid, the highest level of the liquid is used.
 * If the player is standing on a full block (or the part of the block with full height, e.g. an upper slab, the upper step of a stair block), the **air block above** the full block is used.
 * If the player is standing on an incomplete block (e.g. a lower slab, a chest block, an open-upwards trapdoor), the block itself is used.

## The Core World Editing Chain
> Selection -&gt; Block Iterator -&gt; Block Changer -&gt; Cassette -&gt; User History -&gt; Execution Synchronizer

### Selection and Block Iterator
A selection refers to a 3D shape. A selection can be created by various methods as described in the [Shape Selection](#shape-selection) section.

By default, all commands only affect the selection named "default". Builder sessions may also create other selections, identified by case-insensitive names. The selections should be discarded upon closing a builder session (e.g. when a player quits).

A selection should provide an iterator providing a unique stream of blocks within the selection, or blocks on the margin of the selection at a defined padding (inside the border) and margin (outside the border).

### Block Changer
A block changer is an interface that accepts a Block argument and returns another Block argument, determining the new block to set.

This plugin should provide four variants of block changer:
 * **Simple block changer**: The blocks are always set to the specified block type.
 * **Repeating block changer**: A list of block types is provided by the user, and the blocks are returned in a loop. For example, if the user specifies `1 glowstone, 2 glass, 3 stone, 1 lantern`, the first block set is a glowstone, the next two are glass, etc. This loop repeats at the 9<sup>th</sup> block. This is useful for generating patterns in a rectangle or a cuboid, but the direction is undefined.
 * **Random list block changer**: A list of block types is provided by the user. Each block is set to one of the block types in the list randomly selected.
 * **Weighted random list block changer**: Same as random list block changer, except that the block types have different probability of being selected.

These four variants are combined with parameters for filtering the original block.

### Clipboard Chain
An alternative chain is `Clipboard -> Cassette -> User History -> Execution Synchronizer`, where the clipboard provides both the block iterator and the block changer. More details will be described in the [Clipboard](#clipboard) section.

### Cassette
A cassette is a section of server memory, or a temporary file on the hard disk (if greater than 10MB), storing an ordered list of blocks changed in an operation. It can be re-executed in forward or backward order to redo/undo the operation.

### User History
A user history manages an "undo stack" and a "redo stack", each holding an ordered list of cassettes. When the user requests to undo or redo, a cassette from one stack will be moved to the other stack, and pass a reference to the cassette to the execution synchronizer.

### Execution Synchronizer
The execution synchronizer manages chunk locking and cassette operation queuing. Before starting an operation, it determines whether the operation should be executed synchronously or asynchronously and stores the chunks (identifiers only) affected by the cassette to manage locking. **Cassette operations owned by the same builder session will be executed one by one, but cassette operations owned by different builder sessions are executed orderlessly.**

If the selection shape supports reporting the maximum number of blocks changed per chunk, it would determine whether to use the synchronous strategy (updating a few blocks every tick) or the asynchronous strategy (lock the chunks, pass all parameters to an AsyncTask, modify them on the other thread and send the whole chunks).

### User Interface
While the chain consists of five components, the user only needs to make two inputs, and only look at the final user history.

A builder session first creates a selection using specific means (e.g. wands, commands, etc.). After confirming the selection, the user can execute a manipulation command, e.g. `//set`, `//replace`, etc. This will trigger the block iterator from the selection and instantiate a block changer from the manipulation command's arguments. A cassette will be inserted into the user history, and the user history shall pull data from block iterator and block changer into the cassette for execution.

If the execution synchronizer contains cassettes used by the user, it should show a progress bar on the user's screen (tips for players, periodic logger messages (at a lower frequency) for console).

## Shape Selection
The geometric logic is implemented in _libgeom_, while the UI logic is implemented in this plugin.

A "wand" refers to a combination of an item and an action; left-clicking with an emerald and right-clicking with an emerald are considered as two different wands.

A wand can be virtually used using a command. For example, the command `//pos1` is equivalent to clicking the `pos1` wand at the block that the builder session currently stands in.

### Cuboids
 * Defined by two points named `pos1` and `pos2`. The smallest cuboid inscribing both points is selected.
 * Creating a cuboid selection:
   * Command: `//cuboid shoot <distance>`
     * Selects the builder session's current position as `pos1` and `<distance>` blocks ahead of the builder session's orientation as `pos2`
   * 2 wands: Wand `pos1`, Wand `pos2`.
     * Selects `pos1` and `pos2` respectively.
 * Manipulating the selection:
   * Command: `//cuboid grow <-x> <+x> <-y> <+y> <-z> <+z>`
     * Expands the cuboid selection along the three axes as defined
     * If no cuboid is selected, expands the selection from the builder session's current position.
   * Command: `//cuboid skybed`
     * Expands the cuboid selection vertically, from bedrock to the build height limit

### Circular frustums
 * A circular frustum is defined by two parallel ellipses. The two ellipses may have different dimensions, but their major and minor radii must be either parallel or perpendicular.
 * A basic circular frustum is a cylinder. To create a cylinder selection:
   * Command: `//cylinder <radius> [xyz] <height>`
     * The cylinder's base ellipse is a circle of `<radius>` blocks radius, centred at the builder session's current position.
     * The cylinder's top ellipse is a circle of `<radius>` blocks radius, centered at +`<height>` blocks to the `[xyz]` (one of X, Y or Z, or Y if skipped) axis.
   * 4 wands: Wand `baseCenter`, Wand `topCenter`, Wand `rightCircum`, Wand `frontCircum`. This creates an elliptic cylinder.
     * Wands `baseCenter` and `topCenter` select the centers of the base and top ellipses respectively.
     * Wand `frontCircum` selects any of the two intersection points between the circumference and the major/minor diameter in the base ellipse.
     * Wand `rightCircum` selects any of the two intersection points between the circumference and the other minor/major diameter in the base ellipse. If `frontCircum` has been defined but the angle `frontCircum-baseCenter-rightCircum` is not perpendicular, the `rightCircum` point is shifted to the closest point that makes the angle perpendicular; in other words, `rightCircum` will be shifted to its projection on the plane containing `baseCenter` and perpendicular to the line `baseCenter-frontCircum`.
 * The selection can be further modified as follows:
   * Command: `//cylinder topsize <ratio>`
     * The top ellipse's radii will be set to `<ratio>` times those of the base ellipse, regardless its original radii.
   * Command: `//cylinder radius [top|base] <left|right|front|back> <length>`
     * Sets the length of the left/right/front/back radius of the top/base ellipse to `<length>` blocks.
     * For the same ellipse, `left` is always equal to `right` and `front` is always equal to `back`; the right radius is automatically changed with the left radius.
     * If `[top|base]` is skipped, the radius in the base ellipse will be changed, but **the parallel radius in the top ellipse will also be changed** to the same ratio.
       * For example, for the circular frustum `top{ right=10, front=20 }, base{ right=30, front=40 }`, the command `//cylinder radius right 45` will change it to `top{ right=15, front=20 }, base{ right=45, front=40 }`.
   * Command: `//cylinder justify [RIGHT|LEFT|front|back]`
     * For each ellipse, the `[RIGHT|LEFT|front|back]` radius is set to the length of the other radius in the ellipse.
     * right/left is the default value for the last argument if skipped.
   * Command: `//cylinder normalize [pl]`
     * For each ellipse, the circumference is shifted to its projection on the plane containing the ellipse center and perpendicular to the principal axis (the line passing through both ellipse centers).
     * This will end up making the circumference
     * The optional `pl` argument will preserve the radii length, i.e. the ellipses are rotated to become perpendicular to the principal axis rather than being projected.
   * Command: `//cylinder cone`
     * Equivalent to `//cylinder topsize 0`
     * Makes the top ellipse a point (radii = 0) such that the whole frustum becomes a cone.

### Ellipsoids
 * An ellipsoid is a sphere that can be stretched along the three axes respectively.
 * Creating an ellipsoid selection:
   * Command: `//sphere <radius>`
     * Selects the sphere at the builder session's current position with radii on the three axes as `<radius>`
   * 2-4 wands: `center`, `circumAbs`, `circumX`, `circumY`, `circumZ`
     * `center` sets the center of the ellipsoid.
     * `circumAbs` will set all radii to the distance between the clicked block and the center.
     * When the wand `circumX`/`circumY`/`circumZ` is clicked on a block, the X/Y/Z component of the block's distance from the center is resolved, and used as the X/Y/Z radius of the block.
     * If `circumX`/`circumY`/`circumZ` is clicked before any other `circum*` (including `circumAbs`) wands are clicked, the X/Y/Z component of the block's distance from the center is resolved and used as **all radii** of the block.
 * Manipulating an ellipsoid selection:
   * Command: `//sphere norm`
     * The three radii will be set to the cubic root of the product of the three radii. The ellipsoid becomes a sphere.
   * Command: `//sphere [x|y|z] <radius>`
     * Sets the `[x|y|z]` radius to `<radius>`

### Polygon frustums
 * A polygon frustum is similar to a circular frustum, except that the two bases are similar polygons rather than ellipses. The top polygon is transformed from the base polygon by (1) non-rotational translation and (2) expansion/diminishment about an anchor point.
 * There is only one way to select a polygon frustum:
   * 2 wands: `basePolygon`, `topAnchor`, **strictly** using the following steps:
     * Select the anchor point for the base polygon, using one of the following:
       * Select it using the `basePolygon` wand
       * Type the `//polymean` command. The anchor point will be selected as the mean of all points of the polygon (center of mass if it is a triangle, but maybe not for higher-degree polygons).
       * Type the `//polycent` command. The anchor point will be selected as the [centroid/center of mass](https://en.wikipedia.org/wiki/Centroid#Centroid_of_a_polygon) of the polygon. May be inaccurate for self-intersecting polygons.
     * Click the vertexes of the base polygon one by one using the `basePolygon` wand. Unlike Microsoft Paint, there is **no** need to double-click the last block or re-click the first block again upon completion.
     * Select the anchor point for the top polygon using the `topAnchor` wand.
 * Manipulating a polygon frustum:
   * Command: `//polyfrus normalize [pl]`
     * See `//cylinder normalize [pl]`
   * Command: `//polyfrus t|b|d <height>`
     * Change the distance between the two anchor points (or increase/decrease if `<height>` is signed with `+`/`-`) by:
       * `t`: adjusting the top anchor point
       * `b`: adjusting the base anchor point
      8 `d`: adjusting the both anchor points
   * Command: `//polyfrus topsize <ratio>`
     * See `//cylinder topsize <ratio>`

### Mould selections
 * Mould selection mode is a non-geometric selection mode only available for builder sessions based on players.
 * When a player enables mould selection mode, **every** block that he places (excluding resultant block updates, e.g. non-source water blocks) and breaks (excluding blocks broken using the `noMould` wand), minus those touched by the `noMould` wand, plus those touched by the `appendMould` wand, will be added to the selection.
 * Mould selections are saved orderlessly. They may involve use of temporary files even without involving cassettes (so they may create two temporary files if cassettes are involved).

## Clipboard
### Copying
 * The `//copy` command iterates the current selection and writes each block (coordinates + block type) to a `.clip` file. Each builder session may own their own directory of multiple clips.
 * The `//cut` command deletes the blocks while copying.
 * The `//move` command only copies the selection without deleting the blocks. The first time it is pasted, the blocks are removed.

Selections are copied along with an anchor at the builder session's current position. When the selection is pasted, the coordinates are resolved relative to the builder session's position.

### Pasting
 * The `//paste` command reads a `.clip` file and sets blocks through the cassette chain.

## Construction Zones
Command senders with a certain permission may declare specific zones in the world as "construction zones". Builder sessions are granted with construction access in certain construction zones, only in which they can execute world-editing operations.

This limit may be bypassed through a specific command, or it can be set in the plugin configuration to mark the whole level/all levels as construction zones.

If a world-editing operation overlaps non-construction zones, the ignored blocks will be saved into a new cassette that can be managed later.

## Sandbox Mode
Builder sessions based on players can use a `//sandbox` prefix before their commands (e.g. `//sandbox set snow`) such that all world-editing operations they execute are only temporarily visible on their client side, but do not update the level and are not visible to other players.

Builder sessions may also create named "shared sandboxes", inviting certain players (not related to builder sessions) to it and making world-editing operations they execute also visible to the invited players' client side.. This feature can be ~~used~~ *ab*used to create automated games like spleef matches.

Since the changes are only temporary, it is not necessary to store the changes to a cassette. As a result, the chain of a sandbox operation only consists of four steps:

```
selection -> block iterator -> block changer -> client-side changes
```

Sandbox operations are also affected by construction zones. Since no cassettes are involved, the unchanged blocks will not be stored, but they will be counted and reported. In addition, the block changer may return different blocks due to randomness (unlike redoing operations).
