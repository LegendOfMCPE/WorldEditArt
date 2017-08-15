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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\Session;

class WandTrigger{
	/** @var string */
	public $wandName;
	/** @var int */
	public $itemId;
	/** @var int */
	public $actionType;

	public function __construct(string $wandName, int $itemId, int $actionType){
		$this->wandName = $wandName;
		$this->itemId = $itemId;
		$this->actionType = $actionType;
	}

	public function getClickId() : int{
		return WandTrigger::clickId($this->itemId, $this->actionType);
	}

	public static function clickId(int $itemId, $actionType) : int{
		return ($itemId << 8) | $actionType;
	}
}
