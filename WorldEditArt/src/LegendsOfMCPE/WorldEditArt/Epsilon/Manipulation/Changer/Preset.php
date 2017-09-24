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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\Manipulation\Changer;

use LegendsOfMCPE\WorldEditArt\Epsilon\Manipulation\Changer\Picker\RandomLinearBlockPicker;
use LegendsOfMCPE\WorldEditArt\Epsilon\Manipulation\Changer\Picker\RandomWeightedBlockPicker;
use LegendsOfMCPE\WorldEditArt\Epsilon\Manipulation\Changer\Picker\RepeatingLinearBlockPicker;
use LegendsOfMCPE\WorldEditArt\Epsilon\Manipulation\Changer\Picker\RepeatingWeightedBlockPicker;
use LegendsOfMCPE\WorldEditArt\Epsilon\Manipulation\Changer\Picker\SingleBlockPicker;
use LegendsOfMCPE\WorldEditArt\Epsilon\Utils\WEAUtils;

class Preset{
	/** @var string */
	private $name;
	/** @var string */
	private $description;
	/** @var bool */
	private $repeating;
	/** @var string[]|null */
	private $recommendedShapes;
	/** @var BlockPicker */
	private $value;
	/** @var mixed */
	private $storeValue;

	/**
	 * Preset constructor.
	 *
	 * @param PresetManager $presets
	 * @param string        $name
	 * @param string        $description
	 * @param bool          $repeating
	 * @param string[]|null $recommendedShapes
	 * @param mixed         $value
	 */
	public function __construct(PresetManager $presets, string $name, string $description, bool $repeating, ?array $recommendedShapes, $value){
		$this->name = $name;
		$this->description = $description;
		$this->repeating = $repeating;
		$this->recommendedShapes = $recommendedShapes;
		$this->storeValue = $value;
		if(is_array($value)){
			if(WEAUtils::isLinearArray($value)){
				$types = array_map(function($entry) use ($presets){
					$type = BlockType::parse($presets, (string) $entry, $error);
					if($type === null){
						throw new \InvalidArgumentException($error);
					}
					return $type;
				}, $value);
				$this->value = $repeating ? new RepeatingLinearBlockPicker($types) : new RandomLinearBlockPicker($types);
			}else{
				$types = [];
				foreach($value as $typeName => $weight){
					$type = BlockType::parse($presets, $typeName, $error, true);
					if($type === null){
						throw new \InvalidArgumentException($error);
					}
					$types[] = $type;
				}
				$this->value = $repeating ? new RepeatingWeightedBlockPicker($types) : new RandomWeightedBlockPicker($types);
			}
		}else{
			$type = BlockType::parse($presets, (string) $value, $error);
			if($type === null){
				throw new \InvalidArgumentException($error);
			}
			$this->value = new SingleBlockPicker($type);
		}
	}

	public function getName() : string{
		return $this->name;
	}

	public function getDescription() : string{
		return $this->description;
	}

	public function isRepeating() : bool{
		return $this->repeating;
	}

	public function getRecommendedShapes() : ?array{
		return $this->recommendedShapes;
	}

	public function getValue() : BlockPicker{
		return clone $this->value;
	}

	public function getStoreValue(){
		return $this->storeValue;
	}
}
