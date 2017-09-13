<?php

/*
 *
 * WorldEditArt-Epsilon
 *
 * Copyright (C) 2017 SOFe
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 */

declare(strict_types=1);

namespace LegendsOfMCPE\WorldEditArt\Epsilon;

class Consts{
	const PLUGIN_NAME = "WorldEditArt";

	const CONFIG_SESSION_IMPLICIT = "implicit builder session";
	const CONFIG_SESSION_GLOBAL_PASSPHRASE = "builder session global passphrase";

	const CONFIG_CONSTRUCTION_ZONE_CHECK = "construction zone check";
	const CONFIG_CONSTRUCTION_ZONE_WORLDS = "construction zone worlds";

	const CONFIG_ASYNC_THRESHOLD = "blocks-per-chunk threshold for asynchronous editing";

	const CONFIG_WAND_ITEM_PREFIX = "wand item prefix";

	const CONFIG_VERSION = "DO NOT EDIT THIS LINE";
	const CONFIG_VERSION_VALUE = 1;

	const PERM_STATUS = "worldeditart.status";
	const PERM_SESSION_START = "worldeditart.builder.session.start";
	const PERM_SESSION_CLOSE = "worldeditart.builder.session.close";
	const PERM_BOOKMARK_ADD = "worldeditart.builder.bookmark.add";
	const PERM_BOOKMARK_REMOVE = "worldeditart.builder.bookmark.remove";
	const PERM_BOOKMARK_LIST = "worldeditart.builder.bookmark.list";
	const PERM_BOOKMARK_TP = "worldeditart.builder.bookmark.tp";
	const PERM_AT_ABSOLUTE = "worldeditart.builder.at.absolute";
	const PERM_AT_RELATIVE = "worldeditart.builder.at.relative";
	const PERM_AT_SPAWN = "worldeditart.builder.at.spawn";
	const PERM_AT_BOOKMARK = "worldeditart.builder.at.bookmark";
	const PERM_AT_WARP = "worldeditart.builder.at.warp";
	const PERM_AT_PLAYER = "worldeditart.builder.at.player";
	const PERM_AT_ANY = [
		Consts::PERM_AT_ABSOLUTE,
		Consts::PERM_AT_RELATIVE,
		Consts::PERM_AT_SPAWN,
		Consts::PERM_AT_PLAYER,
		Consts::PERM_AT_WARP,
	];
	const PERM_CZONE_BUILDER_VIEW = "worldeditart.builder.czone.view";
	const PERM_CZONE_BUILDER_LOCK_EDIT = "worldeditart.builder.czone.lock.edit";
	const PERM_CZONE_BUILDER_LOCK_BLOCKS = "worldeditart.builder.czone.lock.blocks";
	const PERM_CZONE_BUILDER_LOCK_ENTRY = "worldeditart.builder.czone.lock.entry";
	const PERM_CZONE_BUILDER_UNLOCK_SELF = "worldeditart.builder.czone.unlockself";
	const PERM_CZONE_ADMIN_UNLOCK_OTHER = "worldeditart.admin.czone.unlockother";
	const PERM_CZONE_ADMIN_CHANGE = "worldeditart.admin.czone.change";
	const PERM_CZONE_ADMIN_BYPASS = "worldeditart.admin.czone.bypass";
	const PERM_CZONE_ADMIN_LOCK_BYPASS = "worldeditart.admin.czone.lockbypass";
	const PERM_CZONE_COMMAND_ANY = [
		Consts::PERM_CZONE_BUILDER_VIEW,
		Consts::PERM_CZONE_BUILDER_LOCK_EDIT,
		Consts::PERM_CZONE_BUILDER_LOCK_BLOCKS,
		Consts::PERM_CZONE_BUILDER_LOCK_EDIT,
		Consts::PERM_CZONE_BUILDER_UNLOCK_SELF,
		Consts::PERM_CZONE_ADMIN_UNLOCK_OTHER,
		Consts::PERM_CZONE_ADMIN_CHANGE,
		Consts::PERM_CZONE_ADMIN_BYPASS,
		Consts::PERM_CZONE_ADMIN_LOCK_BYPASS,
	];
	const PERM_CZONE_BUILDER_BLOCKS = "worldeditart.builder.czone.blocks";
	const PERM_CZONE_BUILDER_ENTRY = "worldeditart.builder.czone.entry";
	const PERM_SELECT_SHOW = "worldeditart.builder.select.show";
	const PERM_SELECT_DEFAULT = "worldeditart.builder.select.default";
	const PERM_SELECT_DESEL = "worldeditart.builder.select.desel";
	const PERM_SELECT_SET_CUBOID = "worldeditart.builder.select.set.cuboid";
	const PERM_SELECT_SET_CYLINDER = "worldeditart.builder.select.set.cylinder";
	const PERM_SELECT_SET_SPHERE = "worldeditart.builder.select.set.sphere";
	const PERM_WAND_TOGGLE = "worldeditart.builder.wand.toggle";
	const PERM_WAND_CONFIGURE = "worldeditart.builder.wand.configure";
	const PERM_SET = "worldeditart.builder.manipulate.set";
	const PERM_REPLACE = "worldeditart.builder.manipulate.replace";
}
