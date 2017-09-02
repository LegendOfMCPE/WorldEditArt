<?php

/*
 *
 * WorldEditArt
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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands\Session;

use LegendsOfMCPE\WorldEditArt\Epsilon\BuilderSession;
use LegendsOfMCPE\WorldEditArt\Epsilon\Consts;
use LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\UserFormat;
use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class BookmarkCommand extends SessionCommand{
	public function __construct(WorldEditArt $plugin){
		parent::__construct($plugin, "/bm", "Manage bookmarks", "//bm list|add|remove <name>", [], implode(";", [
			Consts::PERM_BOOKMARK_LIST,
			Consts::PERM_BOOKMARK_ADD,
			Consts::PERM_BOOKMARK_REMOVE,
			Consts::PERM_BOOKMARK_TP,
		]), [
			"edit" => [
				[
					"name" => "action",
					"type" => "stringenum",
					"enum_values" => ["add", "remove", "tp"],
				],
				[
					"name" => "bookmarkName",
					"type" => "string",
				],
			],
			"list" => [],
		]);
	}

	public function run(BuilderSession $session, array $args) : void{
		if(isset($args[0])){
			$action = mb_strtolower($args[0]);
			if(!isset($args[1])){
				$this->sendUsage($session);
				return;
			}
			if($action === "tp"){
				$owner = $session->getOwner();
				if(!($owner instanceof Player)){
					$session->msg("Please execute this command in-game.");
					return;
				}
				if(!$session->hasPermission(Consts::PERM_BOOKMARK_TP)){
					$session->msg("You don't have permission to ues //bm tp", BuilderSession::MSG_CLASS_ERROR);
					return;
				}
				if(!$session->hasBookmark($args[1])){
					$session->msg("No bookmark named $args[1]", BuilderSession::MSG_CLASS_ERROR);
					return;
				}
				$bm = $session->getBookmark($args[1]);
				$owner->teleport($bm);
				$session->msg("Teleported to bookmark $args[1]: " . UserFormat::formatLocation($bm, TextFormat::GREEN), BuilderSession::MSG_CLASS_SUCCESS);
				return;
			}
			if($action !== "add" && $action !== "remove"){
				$this->sendUsage($session);
				return;
			}
			$name = $args[1];
			$add = $action === "add";
			if($add){
				if($session->hasBookmark($name)){
					$session->msg("You already have a bookmark called \"$name\"!", BuilderSession::MSG_CLASS_ERROR);
					return;
				}
				$session->setBookmark($name, $session->getLocation());
				$session->msg("Added bookmark \"$name\" at " . UserFormat::formatLocation($session->getLocation(), BuilderSession::MSG_CLASS_COLOR_MAP[BuilderSession::MSG_CLASS_SUCCESS]), BuilderSession::MSG_CLASS_SUCCESS);
			}else{
				if(!$session->hasBookmark($name)){
					$session->msg("You do not have a bookmark called \"$name\"!", BuilderSession::MSG_CLASS_ERROR);
					return;
				}
				$session->removeBookmark($name);
				$session->msg("Removed bookmark \"$name\"", BuilderSession::MSG_CLASS_SUCCESS);
			}
		}else{
			$str = "";
			foreach($session->getBookmarks() as $name => $location){
				$str .= sprintf("%s%s%s: %s",
					TextFormat::BLUE, $name, TextFormat::WHITE,
					UserFormat::formatLocation($location, TextFormat::WHITE));
			}
			$session->msg($str, BuilderSession::MSG_CLASS_INFO, "Your Bookmarks");
		}
	}
}
