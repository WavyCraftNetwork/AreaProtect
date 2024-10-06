<?php

declare(strict_types=1);

namespace wavycraft\areaprotect\utils;

use pocketmine\player\Player;

use pocketmine\math\Vector3;

use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

use wavycraft\areaprotect\Loader;

use jojoe77777\FormAPI\SimpleForm;

class AreaManager {
    use SingletonTrait;

    private $areas = [];
    private $positionBuffer = [];
    private $config;

    public function __construct() {
        $this->config = new Config(Loader::getInstance()->getDataFolder() . "areas.json", Config::JSON);
    }

    public function loadAreas() {
        $this->areas = $this->config->getAll();
    }

    public function saveAreas() {
        $this->config->setAll($this->areas);
        $this->config->save();
    }

    public function initiateAreaCreation(Player $player, string $tag) {
        $this->positionBuffer[$player->getName()] = ['tag' => $tag, 'pos1' => null, 'pos2' => null];
        $player->sendMessage("Left-click a block to set Position 1 for area '$tag'.");
    }

    public function setPosition(Player $player, Vector3 $position) {
        if (isset($this->positionBuffer[$player->getName()])) {
            $buffer = &$this->positionBuffer[$player->getName()];
            if ($buffer['pos1'] === null) {
                $buffer['pos1'] = $position;
                $player->sendMessage("Position 1 set! Now left-click another block for Position 2.");
            } elseif ($buffer['pos2'] === null) {
                $buffer['pos2'] = $position;
                $this->saveArea($buffer['tag'], $buffer['pos1'], $buffer['pos2']);
                $player->sendMessage("Position 2 set! Area '{$buffer['tag']}' created.");
                unset($this->positionBuffer[$player->getName()]);
            }
        }
    }

    public function saveArea(string $tag, Vector3 $pos1, Vector3 $pos2) {
        $this->areas[$tag] = [
            'pos1' => [
                'x' => $pos1->getX(),
                'y' => $pos1->getY(),
                'z' => $pos1->getZ()
            ],
            'pos2' => [
                'x' => $pos2->getX(),
                'y' => $pos2->getY(),
                'z' => $pos2->getZ()
            ],
            'flags' => [
                'pvp' => true,
                'block_place' => true,
                'block_break' => true,
                'item_drop' => true,
                'item_interaction' => true
            ]
        ];
        $this->saveAreas();
    }

    public function toggleFlag(string $tag, string $flag, Player $player) {
        if (isset($this->areas[$tag]['flags'][$flag])) {
            $this->areas[$tag]['flags'][$flag] = !$this->areas[$tag]['flags'][$flag];
            $status = $this->areas[$tag]['flags'][$flag] ? "enabled" : "disabled";
            $player->sendMessage("Flag '$flag' for area '$tag' is now $status.");
            $this->saveAreas();
        }
    }

    public function isSettingPosition(Player $player) : bool{
        return isset($this->positionBuffer[$player->getName()]);
    }

    public function getAreaAtPosition(Vector3 $pos) : ?array{
        foreach ($this->areas as $tag => $area) {
            if ($this->isPositionInsideArea($pos, $area['pos1'], $area['pos2'])) {
                return $area;
            }
        }
        return null;
    }

    private function isPositionInsideArea(Vector3 $pos, array $pos1, array $pos2) : bool{
        return (
            $pos->getX() >= min($pos1['x'], $pos2['x']) &&
            $pos->getX() <= max($pos1['x'], $pos2['x']) &&
            $pos->getY() >= min($pos1['y'], $pos2['y']) &&
            $pos->getY() <= max($pos1['y'], $pos2['y']) &&
            $pos->getZ() >= min($pos1['z'], $pos2['z']) &&
            $pos->getZ() <= max($pos1['z'], $pos2['z'])
        );
    }

    public function openAreaEditForm(Player $player, string $tag) {
        if (!isset($this->areas[$tag])) {
            $player->sendMessage("Area '$tag' does not exist.");
            return;
        }

        $form = new SimpleForm(function (Player $player, $data) use ($tag) {
            if ($data === null) return;

            switch ($data) {
                case 0:
                    $this->toggleFlag($tag, 'pvp', $player);
                    break;
                case 1:
                    $this->toggleFlag($tag, 'block_place', $player);
                    break;
                case 2:
                    $this->toggleFlag($tag, 'block_break', $player);
                    break;
                case 3:
                    $this->toggleFlag($tag, 'item_drop', $player);
                    break;
                case 4:
                    $this->toggleFlag($tag, 'item_interaction', $player);
                    break;
            }
        });

        $form->setTitle("Edit Area '$tag' Flags");
        $form->addButton("Enable/Disable PvP");
        $form->addButton("Enable/Disable Block Place");
        $form->addButton("Enable/Disable Block Break");
        $form->addButton("Enable/Disable Item Drop");
        $form->addButton("Enable/Disable Item Interaction");

        $player->sendForm($form);
    }
}