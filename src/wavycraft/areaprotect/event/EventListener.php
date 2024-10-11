<?php

declare(strict_types=1);

namespace wavycraft\areaprotect\event;

use pocketmine\event\Listener;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\player\PlayerBucketEvent;
use pocketmine\event\player\PlayerChangeSkinEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerEditBookEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerItemEnchantEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerItemUseEvent;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\EntityTrampleFarmlandEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;

use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\inventory\FurnaceBurnEvent;
use pocketmine\event\inventory\FurnaceSmeltEvent;

use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockBurnEvent;
use pocketmine\event\block\BrewItemEvent;
use pocketmine\event\block\BrewingFuelUseEvent;
use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\block\StructureGrowEvent;

use pocketmine\player\Player;

use pocketmine\entity\Entity;

use pocketmine\math\Vector3;

use pocketmine\item\FlintSteel;

use wavycraft\areaprotect\utils\AreaManager;

class EventListener implements Listener {

    private $areaManager;

    public function __construct() {
        $this->areaManager = AreaManager::getInstance();
    }

    public function onPVP(EntityDamageByEntityEvent $event) {
        $entity = $event->getEntity();
        $damager = $event->getDamager();
        if ($damager instanceof Player) {
            $world = $damager->getWorld()->getFolderName();
            $area = $this->areaManager->getAreaAtPosition($entity->getPosition(), $world);
            if ($area && !$area['flags']['pvp']) {
                //$damager->sendMessage("pvp is disabled in this area.");
                $event->cancel();
            }
        }
    }

    public function onEntityShootBow(EntityShootBowEvent $event) {
        $shooter = $event->getEntity();
        if ($shooter instanceof Player) {
            $world = $shooter->getWorld()->getFolderName();
            $area = $this->areaManager->getAreaAtPosition($shooter->getPosition(), $world);
            if ($area && !$area['flags']['shoot_bow']) {
                //$shooter->sendMessage("Shooting bows is disabled in this area.");
                $event->cancel();
            }
        }
    }

    public function onTrampleFarmland(EntityTrampleFarmlandEvent $event) {
        $entity = $event->getEntity();
        $world = $entity->getWorld()->getFolderName();
        $pos = $event->getBlock()->getPosition();
        $area = $this->areaManager->getAreaAtPosition($pos, $world);
        if ($entity instanceof Player) {
            if ($area && !$area['flags']['farmland_trample']) {
                //$entity->sendMessage("Trampling over farmland is disabled in this area.");
                $event->cancel();
            }
        }
    }

    public function onExplosion(EntityExplodeEvent $event) {
        $pos = $event->getPosition();
        $world = $pos->getWorld()->getFolderName();
        $area = $this->areaManager->getAreaAtPosition($pos, $world);
        if ($area && !$area['flags']['explosion']) {
            $event->cancel();
        }
    }

    public function onProjectileLaunch(ProjectileLaunchEvent $event) {
        $entity = $event->getEntity();
        $area = $this->areaManager->getAreaAtPosition($entity->getPosition(), $entity->getPosition()->getWorld()->getFolderName());
        if ($area && !$area['flags']['projectiles']) {
            $event->cancel();
        }
    }

    public function onEntityDamage(EntityDamageEvent $event) {
        $entity = $event->getEntity();
        $area = $this->areaManager->getAreaAtPosition($entity->getPosition(), $entity->getPosition()->getWorld()->getFolderName());
        if ($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
            if ($area && !$area['flags']['fall_damage']) {
            $event->cancel();                
            }    
        }

        if ($event->getCause() === EntityDamageEvent::CAUSE_PROJECTILE) {
            if ($area && !$area['flags']['projectile_damage']) {
            $event->cancel();                
            }    
        }

        if ($event->getCause() === EntityDamageEvent::CAUSE_FIRE) {
            if ($area && !$area['flags']['fire']) {
            $event->cancel();                
            }    
        }

        if ($event->getCause() === EntityDamageEvent::CAUSE_FIRE_TICK) {
            if ($area && !$area['flags']['fire_tick']) {
            $event->cancel();                
            }    
        }

        if ($event->getCause() === EntityDamageEvent::CAUSE_LAVA) {
            if ($area && !$area['flags']['lava']) {
            $event->cancel();                
            }    
        }

        if ($event->getCause() === EntityDamageEvent::CAUSE_DROWNING) {
            if ($area && !$area['flags']['drowning']) {
            $event->cancel();                
            }    
        }

        if ($event->getCause() === EntityDamageEvent::CAUSE_ENTITY_EXPLOSION) {
            if ($area && !$area['flags']['entity_explosion']) {
            $event->cancel();                
            }    
        }

        if ($event->getCause() === EntityDamageEvent::CAUSE_SUFFOCATION) {
            if ($area && !$area['flags']['suffocation']) {
            $event->cancel();                
            }    
        }

        if ($area && !$area['flags']['invincible']) {
            $event->cancel();
        }
    }
    
    public function onPlayerInteract(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $pos = $block->getPosition();
        $world = $player->getWorld()->getFolderName();
        $area = $this->areaManager->getAreaAtPosition($pos, $world);
        $item = $event->getItem();
        
        if ($item instanceof FlintSteel) {
            if ($area && !$area['flags']['flint_and_steel']) {
            $event->cancel();                
            }
        }
 
        if ($this->areaManager->isSettingPosition($player)) {
            $this->areaManager->setPosition($player, $pos);
            $event->cancel();
        }

        if ($area && !$area['flags']['interaction']) {
            //$player->sendMessage("Interacting is disabled in this area.");
            $event->cancel();
        }
    }

    public function onPlayerDropItem(PlayerDropItemEvent $event) {
        $player = $event->getPlayer();
        $world = $player->getWorld()->getFolderName();
        $area = $this->areaManager->getAreaAtPosition($player->getPosition(), $world);
        if ($area && !$area['flags']['item_drop']) {
            //$player->sendMessage("Dropping items is disabled in this area.");
            $event->cancel();
        }
    }

    public function onPlayerItemUse(PlayerItemUseEvent $event) {
        $player = $event->getPlayer();
        $world = $player->getWorld()->getFolderName();
        $area = $this->areaManager->getAreaAtPosition($player->getPosition(), $world);
        if ($area && !$area['flags']['item_use']) {
            //$player->sendMessage("Using items is disabled in this area.");
            $event->cancel();
        }
    }

    public function onEnterBed(PlayerBedEnterEvent $event) {
        $player = $event->getPlayer();
        $pos = $player->getPosition();
        $world = $player->getWorld()->getFolderName();
        $area = $this->areaManager->getAreaAtPosition($pos, $world);
        if ($area && !$area['flags']['bed_enter']) {
            //$player->sendMessage("Entering is disabled in this area.");
            $event->cancel();
        }
    }

    public function onBucketUse(PlayerBucketEvent $event) {
        $player = $event->getPlayer();
        $pos = $player->getPosition();
        $world = $player->getWorld()->getFolderName();
        $area = $this->areaManager->getAreaAtPosition($pos, $world);
        if ($area && !$area['flags']['buckets']) {
            //$player->sendMessage("Using buckets is disabled in this area.");
            $event->cancel();
        }
    }

    public function onSkinChange(PlayerChangeSkinEvent $event) {
        $player = $event->getPlayer();
        $pos = $player->getPosition();
        $world = $player->getWorld()->getFolderName();
        $area = $this->areaManager->getAreaAtPosition($pos, $world);
        if ($area && !$area['flags']['skin_change']) {
            //$player->sendMessage("Changing skins is disabled in this area.");
            $event->cancel();
        }
    }

    public function onChat(PlayerChatEvent $event) {
        $player = $event->getPlayer();
        $pos = $player->getPosition();
        $world = $player->getWorld()->getFolderName();
        $area = $this->areaManager->getAreaAtPosition($pos, $world);
        if ($area && !$area['flags']['chat']) {
            //$player->sendMessage("Chatting is disabled in this area.");
            $event->cancel();
        }
    }

    public function editBook(PlayerEditBookEvent $event) {
        $player = $event->getPlayer();
        $pos = $player->getPosition();
        $world = $player->getWorld()->getFolderName();
        $area = $this->areaManager->getAreaAtPosition($pos, $world);
        if ($area && !$area['flags']['edit_book']) {
            //$player->sendMessage("Editing/Signing books is disabled in this area.");
            $event->cancel();
        }
    }

    public function onPlayerExhaust(PlayerExhaustEvent $event) {
        $player = $event->getPlayer();
        $pos = $player->getPosition();
        $world = $player->getWorld()->getFolderName();
        $area = $this->areaManager->getAreaAtPosition($pos, $world);
        if ($area && !$area['flags']['hunger']) {
            $event->cancel();
        }
    }

    public function onPlayerConsume(PlayerItemConsumeEvent $event) {
        $player = $event->getPlayer();
        $pos = $player->getPosition();
        $world = $player->getWorld()->getFolderName();
        $area = $this->areaManager->getAreaAtPosition($pos, $world);
        if ($area && !$area['flags']['consume']) {
            //$player->sendMessage("Eating/drinking is disabled in this area.");
            $event->cancel();
        }
    }

    public function onItemEnchant(PlayerItemEnchantEvent $event) {
        $player = $event->getPlayer();
        $pos = $player->getPosition();
        $world = $player->getWorld()->getFolderName();
        $area = $this->areaManager->getAreaAtPosition($pos, $world);
        if ($area && !$area['flags']['enchanting']) {
            //$player->sendMessage("Enchanting is disabled in this area.");
            $event->cancel();
        }
    }

    public function onPlayerCraft(CraftItemEvent $event) {
        $player = $event->getPlayer();
        $world = $player->getWorld()->getFolderName();
        $area = $this->areaManager->getAreaAtPosition($player->getPosition(), $world);
        if ($area && !$area['flags']['craft_item']) {
            //$player->sendMessage("Crafting items is disabled in this area.");
            $event->cancel();
        }
    }

    public function onOpenInventory(InventoryOpenEvent $event) {
        $player = $event->getPlayer();
        $world = $player->getWorld()->getFolderName();
        $area = $this->areaManager->getAreaAtPosition($player->getPosition(), $world);
        if ($area && !$area['flags']['open_inventory']) {
            //$player->sendMessage("Opening inventory's is disabled in this area.");
            $event->cancel();
        }
    }

    public function onFurnaceRefuel(FurnaceBurnEvent $event) {
        $pos = $event->getBlock()->getPosition();
        $world = $pos->getWorld()->getFolderName();
        $area = $this->areaManager->getAreaAtPosition($pos, $world);
        if ($area && !$area['flags']['refuel_furnace']) {
            $event->cancel();
        }
    }

    public function onFurnaceBurn(FurnaceSmeltEvent $event) {
        $pos = $event->getBlock()->getPosition();
        $world = $pos->getWorld()->getFolderName();
        $area = $this->areaManager->getAreaAtPosition($pos, $world);
        if ($area && !$area['flags']['furnace_smelt']) {
            $event->cancel();
        }
    }

    public function onBlockBurn(BlockBurnEvent $event) {
        $pos = $event->getBlock()->getPosition();
        $world = $pos->getWorld()->getFolderName();
        $area = $this->areaManager->getAreaAtPosition($pos, $world);
        if ($area && !$area['flags']['block_burn']) {
            $event->cancel();
        }
    }

    public function onPotionCreation(BrewItemEvent $event) {
        $pos = $event->getBlock()->getPosition();
        $world = $pos->getWorld()->getFolderName();
        $area = $this->areaManager->getAreaAtPosition($pos, $world);
        if ($area && !$area['flags']['brew_potions']) {
            $event->cancel();
        }
    }

    public function onPotionRefuel(BrewingFuelUseEvent $event) {
        $pos = $event->getBlock()->getPosition();
        $world = $pos->getWorld()->getFolderName();
        $area = $this->areaManager->getAreaAtPosition($pos, $world);
        if ($area && !$area['flags']['refuel_brewing_stand']) {
            $event->cancel();
        }
    }

    public function onLeafDecay(LeavesDecayEvent $event) {
        $pos = $event->getBlock()->getPosition();
        $world = $pos->getWorld()->getFolderName();
        $area = $this->areaManager->getAreaAtPosition($pos, $world);
        if ($area && !$area['flags']['leaf_decay']) {
            $event->cancel();
        }
    }

    public function onSignChange(SignChangeEvent $event) {
        $player = $event->getPlayer();
        $pos = $event->getBlock()->getPosition();
        $world = $pos->getWorld()->getFolderName();
        $area = $this->areaManager->getAreaAtPosition($pos, $world);
        if ($area && !$area['flags']['edit_sign']) {
            //$player->sendMessage("Editing signs is disabled in this area.");
            $event->cancel();
        }
    }

    public function onSaplingGrowth(StructureGrowEvent $event) {
        $pos = $event->getBlock()->getPosition();
        $world = $pos->getWorld()->getFolderName();
        $area = $this->areaManager->getAreaAtPosition($pos, $world);
        if ($area && !$area['flags']['sapling_growth']) {
            $event->cancel();
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event) {
        $player = $event->getPlayer();
        $block = $event->getBlockAgainst();
        $pos = $block->getPosition();
        $world = $pos->getWorld()->getFolderName();
        $area = $this->areaManager->getAreaAtPosition($pos, $world);

        if ($area && !$area['flags']['block_place']) {
            //$player->sendMessage("Block placing is disabled in this area.");
            $event->cancel();
        }
    }

    public function onBlockBreak(BlockBreakEvent $event) {
        $player = $event->getPlayer();
        $pos = $event->getBlock()->getPosition();
        $world = $pos->getWorld()->getFolderName();
        $area = $this->areaManager->getAreaAtPosition($pos, $world);

        if ($area && !$area['flags']['block_break']) {
            //$player->sendMessage("Block breaking is disabled in this area.");
            $event->cancel();
        }
    }
}
