<?php

declare(strict_types=1);

namespace wavycraft\areaprotect\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;

use pocketmine\player\Player;

use wavycraft\areaprotect\Loader;

use wavycraft\areaprotect\utils\AreaManager;

class AreaCommand extends Command implements PluginOwned {

    private $plugin;
    private $areaManager;

    public function __construct() {
        parent::__construct("area");//no need for setLabel() :p
        //$this->setLabel("area");
        $this->setDescription("Manage or Create a protected area");
        $this->setUsage("/area <create|edit> <args>");
        $this->setPermission("areaprotect.cmd");

        $this->plugin = Loader::getInstance();
        $this->areaManager = AreaManager::getInstance();
    }

    public function getOwningPlugin() : Plugin{
        return $this->plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
        if(!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game.");
            return false;
        }

        if(count($args) < 2) {
            $sender->sendMessage($this->getUsage());
            return false;
        }

        switch($args[0]) {
            case "create":
                $tag = $args[1];
                $this->areaManager->initiateAreaCreation($sender, $tag);
                return true;
            case "edit":
                $tag = $args[1];
                $this->areaManager->openAreaEditForm($sender, $tag);
                return true;
            default:
                $sender->sendMessage($this->getUsage());
                return false;
        }
    }
}