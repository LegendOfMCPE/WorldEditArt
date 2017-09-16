---
layout: wiki
title: Testing WorldEditArt
excerpt: Test your changes to WorldEditArt
keywords: developer,debug
---

For versioning purposes, WorldEditArt must be loaded as a virion-infected phar file. See [.poggit.yml][blob/.poggit.yml]
for the list of required virions.

In addition, to suppress the spread of unofficial editions of WorldEditArt, by default it checks whether the build was
created by Poggit with WorldEditArt's project. You may bypass this limit by adding the `--worldeditart.allow-non-poggit`
argument when you start the server (e.g. `./start.sh --worldeditart.allow-non-poggit`). You may also add a line
`worldeditart.allow-non-poggit=on` in server.properties.

  [blob/.poggit.yml]: https://github.com/LegendOfMCPE/WorldEditArt/blob/epsilon/indev/.poggit.yml
