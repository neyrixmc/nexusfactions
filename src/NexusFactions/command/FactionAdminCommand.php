<?php

declare(strict_types=1);

namespace NexusFactions\command;

use NexusFactions\Main;
use NexusFactions\ui\AdminUI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class FactionAdminCommand extends Command {
    
    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("factionadmin", "Commandes d'administration des factions", "/factionadmin", ["fadmin", "fa"]);
        $this->setPermission("nexusfactions.admin");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("§cCette commande doit être exécutée en jeu!");
            return false;
        }

        if (!$this->testPermission($sender)) {
            $sender->sendMessage("§cVous n'avez pas la permission d'utiliser cette commande!");
            return false;
        }

        // Si FormAPI est disponible, utiliser les menus
        if (class_exists("jojoe77777\\FormAPI\\SimpleForm")) {
            AdminUI::sendMainMenu($sender);
        } else {
            $this->sendHelpMessage($sender);
        }

        return true;
    }

    private function sendHelpMessage(Player $player): void {
        $player->sendMessage("§9§l--- Commandes Admin Factions ---");
        $player->sendMessage("§9/fa delete <faction> §7- Supprimer une faction");
        $player->sendMessage("§9/fa join <faction> §7- Rejoindre une faction (mode admin)");
        $player->sendMessage("§9/fa setpower <faction> <power> §7- Définir le power");
        $player->sendMessage("§9/fa addpower <faction> <power> §7- Ajouter du power");
        $player->sendMessage("§9/fa removepower <faction> <power> §7- Retirer du power");
        $player->sendMessage("§9/fa info <faction> §7- Infos détaillées");
        $player->sendMessage("§9/fa list §7- Liste toutes les factions");
        $player->sendMessage("§9/fa reload §7- Recharger la configuration");
    }
}
