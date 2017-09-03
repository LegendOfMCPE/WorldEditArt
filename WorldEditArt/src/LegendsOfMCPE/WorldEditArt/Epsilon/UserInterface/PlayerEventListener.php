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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface;

use LegendsOfMCPE\WorldEditArt\Epsilon\ConstructionZone;
use LegendsOfMCPE\WorldEditArt\Epsilon\Consts;
use LegendsOfMCPE\WorldEditArt\Epsilon\Session\PlayerBuilderSession;
use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;
use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Cancellable;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;

class PlayerEventListener implements Listener{
	/** @var WorldEditArt */
	private $plugin;

	public function __construct(WorldEditArt $plugin){
		$this->plugin = $plugin;
		$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
	}

	public function onJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		/** @noinspection NotOptimalIfConditionsInspection */
		if($this->plugin->getConfig()->get(Consts::CONFIG_SESSION_IMPLICIT) && $player->hasPermission(Consts::PERM_SESSION_START)){
			$this->plugin->startPlayerSession($player);
		}
	}

	public function onQuit(PlayerQuitEvent $event){
		$this->plugin->closeSessions($event->getPlayer());
	}

	/**
	 * @param PlayerMoveEvent $event
	 *
	 * @priority        HIGH
	 * @ignoreCancelled true
	 */
	public function onMove(PlayerMoveEvent $event){
		if($event->getPlayer()->hasPermission(Consts::PERM_CZONE_BUILDER_ENTRY)){
			return;
		}
		foreach($this->plugin->getConstructionZones() as $zone){
			if($zone->getLockMode() === ConstructionZone::LOCK_MODE_ENTRY){
				if($zone->getShape()->isInside($event->getTo())){
					$event->setCancelled();
					break;
				}
			}
		}
	}

	/**
	 * @param BlockPlaceEvent $event
	 *
	 * @priority        HIGH
	 * @ignoreCancelled true
	 */
	public function onPlace(BlockPlaceEvent $event){
		$this->blockEvent($event->getPlayer(), $event->getBlock(), $event);
	}

	/**
	 * @param BlockBreakEvent $event
	 *
	 * @priority        HIGH
	 * @ignoreCancelled true
	 */
	public function onBreak(BlockBreakEvent $event){
		$this->blockEvent($event->getPlayer(), $event->getBlock(), $event);
	}

	private function blockEvent(Player $player, Block $block, Cancellable $event){
		if($player->hasPermission(Consts::PERM_CZONE_BUILDER_BLOCKS)){
			return;
		}
		foreach($this->plugin->getConstructionZones() as $zone){
			if($zone->getLockMode() >= ConstructionZone::LOCK_MODE_BLOCKS){
				if($zone->getShape()->isInside($block)){
					$event->setCancelled();
					break;
				}
			}
		}
	}

	/**
	 * @param PlayerInteractEvent $event
	 *
	 * @priority LOW
	 */
	public function onInteract(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		$sessions = $this->plugin->getSessionsOf($player);
		if(!isset($sessions[PlayerBuilderSession::SESSION_KEY])){
			return;
		}
		$session = $sessions[PlayerBuilderSession::SESSION_KEY];
		$item = $event->getItem();
		$itemName = trim($item->getCustomName(), " \n\t\r/");

		$prefixes = $this->plugin->getConfig()->get(Consts::CONFIG_WAND_ITEM_PREFIX, null);
		if($itemName && $prefixes){
			foreach((array) $prefixes as $prefix){
				$prefix = trim((string) $prefix, " \n\t\r/");
				if(strpos($itemName, $prefix) === 0){
					$wandName = ltrim(substr($itemName, $prefix), " \n\t\r/");
					break;
				}
			}
			if(isset($wandName)){
				$wand = $this->plugin->getWandManager()->getWand($wandName);
			}
		}
		if(!isset($wand)){
			$trigger = $session->getWandTrigger($item->getId(), $event->getAction());
			if($trigger === null){
				return;
			}
			$wand = $this->plugin->getWandManager()->getWand($trigger->wandName);
		}
		$wand->execute($session, $event->getBlock(), $session->getDefaultSelectionName());
	}
}
