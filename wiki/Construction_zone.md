Construction zone
=================

Construction zones prevent your constructions from causing accidents, such as:

> _Builder Alice: "I just finished building this pretty house, then Builder Bob pasted a rocket right through my house!"_

> _Player Carol: "I was mining a diamond, and suddenly Builder Dan converted the whole cave into a lava sea!"_

> _Builder Elvin": "I didn't want to destroy your spawn! I got randomly teleported just as I typed the command!"_

Construction zones can limit where builders can use WorldEditArt to bulk-edit blocks and prevent others from interfering
with the construction.

## Limit world-editing with construction zones
Builders can only use WorldEditArt to edit blocks inside construction zones, unless the server has disabled construction
zones (`construction zone check: false` in the config) or the whole world is marked as a construction zone (world folder
name is in the `construction zone worlds` list in the config)

In other words, construction zones will disallow a builder from editing a block with WorldEditArt, if:
- `construction zone check` is enabled in the config, **and**
- `construction zone worlds` does not include the folder name of the world containing the block (case-insensitive),
  **and**
- the block is not in any construction zones

In addition, if the block is in a construction zone but the zone is _locked_, only the builder who locked the zone can
edit it with WorldEditArt. See more details about locking [below](#construction-zone-locks).

## Managing construction zones
To create a construction zone:

1. [Select](Selection.md) the zone you want to mark as construction zone. All shapes are acceptable.
2. Use the command `//cz add <czName> [selName]`.
  - `<czName>` is the name of the construction zone. It can be anything, of language, except "here" (case-insensitive).
  - `[selName]` is your selection name. If omitted, the default selection is used.

To change an existing construction zone's shape, repeat the above except that `add` should be changed to `change`.

To rename an existing construction zone, use `//cz rename <oldName> <newName>`, where
  - `<oldName>` is the original name (case-insensitive).
  - `<newName>` is the new name to use.

To delete a construction zone, use the command `//cz del <czName>`, where `<czName>` is the name of the construction zone to delete (case-insensitive).

## Construction zone locks
A construction zone can be locked by a builder (currently limited to only one builder at a time for each construction
zone). It is automatically unlocked when the builder session is closed (e.g. the player quits, the server restarts).

There are three modes of locking, namely `edit`, `blocks` and `entry`:

- `edit` will only stop other builders from **editing with WorldEditArt** inside the construction zone.
- `blocks` will _additionally_ stop other players from **placing/breaking blocks** inside the construction zone.
- `entry` will _additionally_ stop other players from **entering** the construction zone.

Note the word _"additionally"_, i.e. `blocks` includes restrictions from `edit`, and `entry` includes restrictions from
both `edit` and `blocks`.

### Commands
To lock a zone, use the command `//cz lock [zone] [mode]`:

- `[zone]` should be the name of the construction zone you wish to lock (case-insensitive), or `here` (or just type
  `//cz lock`). `here`/empty will choose the construction zone you are standing in, but you will be asked which one you
  are referring to if you are standing in multiple overlapping construction zones.
- `[mode]` should be one of `edit`, `blocks` or `entry` (as explained above). If it is not given, the default value
  `edit` is used.

To unlock a zone, use the command `//cz unlock [zone]`, where the usage of `[zone]` is same as above.

To view a zone's shape and lock status, use the command `//cz view [zone]`, where the usage of `[zone]` is also same as
above, except that all zones you are standing in will be displayed for empty/`here`.

## Bypassing construction zone constraints
If you wish to build **as if everywhere is in a construction zone**, use the command `//cz bypass zone on`; use
`//cz bypass zone off` to stop bypassing this limit. This does not allow you to bypass construction zone locks.

If you wish to build **as if no construction zones are locked**, construction zone locks, use `//cz bypass lock on`;
use `//cz bypass lock off` to stop ignoring.

## Trivia
- Construction zones can be abused to make admin-only area protection.
