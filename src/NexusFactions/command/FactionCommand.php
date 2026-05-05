<?php

declare(strict_types=1);

namespace NexusFactions\command;

use NexusFactions\Main;
use NexusFactions\ui\FactionUI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class FactionCommand extends Command {
    
    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("faction", "Commande principale des factions", "/faction", ["f", "fac"]);
        $this->setPermission("nexusfactions.command");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("§cCette commande doit être exécutée en jeu!");
            return false;
        }

        if (!$this->testPermission($sender)) {
            return false;
        }

        // Si FormAPI est disponible, utiliser les menus
        if (class_exists("jojoe77777\\FormAPI\\SimpleForm")) {
            FactionUI::sendMainMenu($sender);
        } else {
            // Fallback sans FormAPI
            $this->sendHelpMessage($sender);
        }

        return true;
    }

    private function sendHelpMessage(Player $player): void {
        $player->sendMessage("§9§l--- Commandes Factions ---");
        $player->sendMessage("§9/f create <nom> §7- Créer une faction");
        $player->sendMessage("§9/f disband §7- Dissoudre votre faction");
        $player->sendMessage("§9/f invite <joueur> §7- Inviter un joueur");
        $player->sendMessage("§9/f join <faction> §7- Rejoindre une faction");
        $player->sendMessage("§9/f leave §7- Quitter votre faction");
        $player->sendMessage("§9/f kick <joueur> §7- Expulser un membre");
        $player->sendMessage("§9/f promote <joueur> §7- Promouvoir en officier");
        $player->sendMessage("§9/f demote <joueur> §7- Rétrograder un officier");
        $player->sendMessage("§9/f info [faction] §7- Infos d'une faction");
        $player->sendMessage("§9/f list §7- Liste des factions");
        $player->sendMessage("§9/f ally <faction> §7- Demander une alliance");
        $player->sendMessage("§9/f enemy <faction> §7- Déclarer ennemi");
        $player->sendMessage("§9/f neutral <faction> §7- Devenir neutre");
        $player->sendMessage("§9/f claim §7- Claim un chunk");
        $player->sendMessage("§9/f unclaim §7- Unclaim un chunk");
        $player->sendMessage("§9/f island §7- Téléporter à l'île");
        $player->sendMessage("§9/f sethome §7- Définir le home");
    }
}
