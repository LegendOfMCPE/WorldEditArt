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

use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;

class PresetManager{
	/** @var string */
	private $path;
	/** @var Preset[] */
	private $presets;
	/** @var bool */
	private $changed = false;

	public function __construct(WorldEditArt $plugin){
		$plugin->saveResource("presets.yml");

		foreach((array) yaml_parse_file($this->path = $plugin->getDataFolder() . "presets.yml") as $name => $preset){
			/** @noinspection UnnecessaryCastingInspection */
			$shapes = array_merge((array) ($preset["recommended_shapes"] ?? []), (array) ($preset["recommended_shape"] ?? []));
			if(count($shapes) === 0){
				$shapes = null;
			}
			/** @noinspection UnnecessaryCastingInspection */
			try{
				$this->presets[mb_strtolower($name)] = new Preset($this, $name, $preset["description"] ?? "", (bool) ($preset["repeating"] ?? false), $shapes, $preset["value"]);
			}catch(\InvalidArgumentException $e){
				$plugin->getLogger()->error("Error loading a preset called $name: {$e->getMessage()}");
			}
		}
	}

	/**
	 * @return Preset[]
	 */
	public function getPresets() : array{
		return $this->presets;
	}

	public function getPreset(string $name) : ?Preset{
		return $this->presets[mb_strtolower($name)] ?? null;
	}

	public function addPreset(Preset $preset) : void{
		$this->presets[mb_strtolower($preset->getName())] = $preset;
		$this->changed = true;
	}

	public function save() : void{
		if(!$this->changed){
			return;
		}
		$data = [];
		foreach($this->presets as $preset){
			$datum = ["description" => $preset->getDescription()];
			if($preset->isRepeating()){
				$datum["repeating"] = true;
			}
			if(count($preset->getRecommendedShapes()) > 1){
				$datum["recommended_shapes"] = $preset->getRecommendedShapes();
			}elseif(count($preset->getRecommendedShapes()) === 1){
				$datum["recommended_shape"] = $preset->getRecommendedShapes()[0];
			}
			$datum["value"] = $preset->getStoreValue();
			$data[$preset->getName()] = $datum;
		}
		file_put_contents($this->path, yaml_emit($data));
	}
}
