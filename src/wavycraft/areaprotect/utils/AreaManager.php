<?php

declare(strict_types=1);

namespace wavycraft\areaprotect\utils;

use pocketmine\player\Player;

use pocketmine\math\Vector3;

use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat as TextColor;

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
        $player->sendMessage("Left-click a block to set Position 1 for area '" . TextColor::GREEN . $tag . TextColor::WHITE . "'.");
    }

    public function setPosition(Player $player, Vector3 $position) {
        if (isset($this->positionBuffer[$player->getName()])) {
            $buffer = &$this->positionBuffer[$player->getName()];

            if ($buffer['pos1'] === null) {
                $buffer['pos1'] = $position;
                $player->sendMessage("Position 1 set! Now click another block for Position 2.");
            } elseif ($buffer['pos2'] === null) {
                $buffer['pos2'] = $position;

                $worldFolderName = $player->getWorld()->getFolderName();

                $this->saveArea($buffer['tag'], $buffer['pos1'], $buffer['pos2'], $worldFolderName);

                $player->sendMessage("Position 2 set! Area '" . TextColor::GREEN . $buffer['tag'] . TextColor::WHITE . "' has been created.");
                unset($this->positionBuffer[$player->getName()]);
            } else {
                $player->sendMessage("Both positions are already set for area '" . TextColor::GREEN . $buffer['tag'] . TextColor::WHITE . "'.");
            }
        }
    }

    public function saveArea(string $tag, Vector3 $pos1, Vector3 $pos2, string $worldFolderName) {
        $world = Loader::getInstance()->getServer()->getWorldManager()->getWorldByName($worldFolderName);
    
        $this->areas[$tag] = [
            'world' => [
                'folder_name' => $world->getFolderName()
            ],
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
                'item_use' => true,
                'shoot_bow' => true,
                'interaction' => true,
                'farmland_trample' => true,
                'explosion' => true,
                'projectiles' => true,
                'bed_enter' => true,
                'buckets' => true,
                'skin_change' => true,
                'chat' => true,
                'edit_book' => true,
                'hunger' => true,
                'consume' => true,
                'enchanting' => true,
                'craft_item' => true,
                'open_inventory' => true,
                'refuel_furnace' => true,
                'furnace_smelt' => true,
                'block_burn' => true,
                'brew_potions' => true,
                'refuel_brewing_stand' => true,
                'leaf_decay' => true,
                'edit_sign' => true,
                'sapling_growth' => true,
                'fall_damage' => true,
                'invincible' => true,//not actually enabled yet...
                'flint_and_steel' => true,
                'lava' => true,
                'fire' => true,
                'fire_tick' => true,
                'projectile_damage' => true,
                'drowning' => true,
                'entity_explosion' => true,
                'suffocation' => true
            ]
        ];

        $this->saveAreas();
    }

    public function toggleFlag(string $tag, string $flag, Player $player) {
        if (isset($this->areas[$tag]['flags'][$flag])) {
            $this->areas[$tag]['flags'][$flag] = !$this->areas[$tag]['flags'][$flag];
            $status = $this->areas[$tag]['flags'][$flag] ? "§aenabled" : "§cdisabled";
            $player->sendMessage("Flag '" . TextColor::YELLOW . $flag . TextColor::WHITE . "' for area '" . TextColor::GREEN . $tag . TextColor::WHITE . "' is now $status.");
            $this->saveAreas();
        }
    }

    public function isSettingPosition(Player $player) : bool{
        return isset($this->positionBuffer[$player->getName()]);
    }

    public function getAreaAtPosition(Vector3 $pos, string $worldName) : ?array {
        foreach ($this->areas as $tag => $area) {
            if ($area['world']['folder_name'] === $worldName && $this->isPositionInsideArea($pos, $area['pos1'], $area['pos2'])) {
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
            $player->sendMessage("Area '" . TextColor::RED . $tag . TextColor::WHITE . "' does not exist.");
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
                    $this->toggleFlag($tag, 'interaction', $player);
                    break;
                case 5:
                    $this->toggleFlag($tag, 'item_use', $player);
                    break;
                case 6:
                    $this->toggleFlag($tag, 'shoot_bow', $player);
                    break;
                case 7:
                    $this->toggleFlag($tag, 'farmland_trample', $player);
                    break;
                case 8:
                    $this->toggleFlag($tag, 'explosion', $player);
                    break;
                case 9:
                    $this->toggleFlag($tag, 'projectiles', $player);
                    break;
                case 10:
                    $this->toggleFlag($tag, 'bed_enter', $player);
                    break;
                case 11:
                    $this->toggleFlag($tag, 'buckets', $player);
                    break;
                case 12:
                    $this->toggleFlag($tag, 'skin_change', $player);
                    break;
                case 13:
                    $this->toggleFlag($tag, 'chat', $player);
                    break;
                case 14:
                    $this->toggleFlag($tag, 'edit_book', $player);
                    break;
                case 15:
                    $this->toggleFlag($tag, 'hunger', $player);
                    break;
                case 16:
                    $this->toggleFlag($tag, 'consume', $player);
                    break;
                case 17:
                    $this->toggleFlag($tag, 'enchanting', $player);
                    break;
                case 18:
                    $this->toggleFlag($tag, 'craft_item', $player);
                    break;
                case 19:
                    $this->toggleFlag($tag, 'open_inventory', $player);
                    break;
                case 20:
                    $this->toggleFlag($tag, 'refuel_furnace', $player);
                    break;
                case 21:
                    $this->toggleFlag($tag, 'furnace_smelt', $player);
                    break;
                case 22:
                    $this->toggleFlag($tag, 'block_burn', $player);
                    break;
                case 23:
                    $this->toggleFlag($tag, 'brew_potions', $player);
                    break;
                case 24:
                    $this->toggleFlag($tag, 'refuel_brewing_stand', $player);
                    break;
                case 25:
                    $this->toggleFlag($tag, 'leaf_decay', $player);
                    break;
                case 26:
                    $this->toggleFlag($tag, 'edit_sign', $player);
                    break;
                case 27:
                    $this->toggleFlag($tag, 'sapling_growth', $player);
                    break;
                case 28:
                    $this->toggleFlag($tag, 'fall_damage', $player);
                    break;
                case 29:
                    $this->toggleFlag($tag, 'invincible', $player);
                    break;
                case 30:
                    $this->toggleFlag($tag, 'flint_and_steel', $player);
                    break;
                case 31:
                    $this->toggleFlag($tag, 'lava', $player);
                    break;
                case 32:
                    $this->toggleFlag($tag, 'fire', $player);
                    break;
                case 33:
                    $this->toggleFlag($tag, 'fire_tick', $player);
                    break;
                case 34:
                    $this->toggleFlag($tag, 'projectile_damage', $player);
                    break;
                case 35:
                    $this->toggleFlag($tag, 'drowning', $player);
                    break;
                case 36:
                    $this->toggleFlag($tag, 'entity_explosion', $player);
                    break;
                case 37:
                    $this->toggleFlag($tag, 'suffocation', $player);
                    break;
                }
            });

        $form->setTitle("Edit Area '$tag' Flags");
        $form->setContent("Click on a flag to either enable or disable them:");
        $form->addButton("Enable/Disable PvP");
        $form->addButton("Enable/Disable Block Place");
        $form->addButton("Enable/Disable Block Break");
        $form->addButton("Enable/Disable Item Drop");
        $form->addButton("Enable/Disable Interactions");
        $form->addButton("Enable/Disable Item Use");
        $form->addButton("Enable/Disable Bow Shoot");
        $form->addButton("Enable/Disable Trample Farmland");
        $form->addButton("Enable/Disable Explosions");
        $form->addButton("Enable/Disable Projectiles");
        $form->addButton("Enable/Disable Bed");
        $form->addButton("Enable/Disable Buckets");
        $form->addButton("Enable/Disable Skin Change");
        $form->addButton("Enable/Disable Chat");
        $form->addButton("Enable/Disable Edit Book");
        $form->addButton("Enable/Disable Hunger");
        $form->addButton("Enable/Disable Consuming");
        $form->addButton("Enable/Disable Enchanting");
        $form->addButton("Enable/Disable Craft Items");
        $form->addButton("Enable/Disable Open Inventory");
        $form->addButton("Enable/Disable Refuel Furnace");
        $form->addButton("Enable/Disable Furnace Smelting");
        $form->addButton("Enable/Disable Block Burning");
        $form->addButton("Enable/Disable Brew Potions");
        $form->addButton("Enable/Disable Refuel Brewing Stand");
        $form->addButton("Enable/Disable Leaf Decaying");
        $form->addButton("Enable/Disable Edit Sign");
        $form->addButton("Enable/Disable Sapling Growth");
        $form->addButton("Enable/Disable Fall Damage");
        $form->addButton("Enable/Disable Invincibility");
        $form->addButton("Enable/Disable Flint and Steel");
        $form->addButton("Enable/Disable Lava damage");
        $form->addButton("Enable/Disable Fire damage");
        $form->addButton("Enable/Disable Fire Tick");
        $form->addButton("Enable/Disable Projectile Damage");
        $form->addButton("Enable/Disable Drowning");
        $form->addButton("Enable/Disable Entity Explosion");
        $form->addButton("Enable/Disable Suffocation");

        $player->sendForm($form);
     }
}
