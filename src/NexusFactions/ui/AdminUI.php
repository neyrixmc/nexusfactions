<?php

declare(strict_types=1);

namespace NexusFactions\ui;

use NexusFactions\Main;
use pocketmine\player\Player;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;

class AdminUI {
    
    public static function sendMainMenu(Player $player): void {
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if ($data === null) return;
            
            switch ($data) {
                case 0: // Gérer les factions
                    self::sendManageFactionMenu($player);
                    break;
                case 1: // Supprimer une faction
                    self::sendDeleteFactionMenu($player);
                    break;
                case 2: // Rejoindre une faction (mode admin)
                    self::sendAdminJoinMenu($player);
                    break;
                case 3: // Modifier le power
                    self::sendPowerManagementMenu($player);
                    break;
                case 4: // Liste des factions
                    self::sendAdminFactionList($player);
                    break;
                case 5: // Configuration
                    self::sendConfigMenu($player);
                    break;
                case 6: // Recharger
                    Main::getInstance()->reloadConfig();
                    $player->sendMessage("§9Configuration rechargée avec succès!");
                    break;
            }
        });
        
        $form->setTitle("§1Administration Factions");
        $form->setContent("§9Panneau d'administration des factions");
        
        $form->addButton("§9Gérer les factions");
        $form->addButton("§1Supprimer une faction");
        $form->addButton("§9Rejoindre une faction (Admin)");
        $form->addButton("§1Modifier le power");
        $form->addButton("§9Liste des factions");
        $form->addButton("§1Configuration");
        $form->addButton("§9Recharger la config");
        
        $player->sendForm($form);
    }

    public static function sendManageFactionMenu(Player $player): void {
        $plugin = Main::getInstance();
        $factions = $plugin->getFactionManager()->getAllFactions();
        
        if (empty($factions)) {
            $player->sendMessage("§cAucune faction n'existe!");
            return;
        }
        
        $form = new SimpleForm(function (Player $player, ?int $data) use ($factions) {
            if ($data === null) return;
            
            $factionList = array_values($factions);
            if (isset($factionList[$data])) {
                self::sendFactionManagementOptions($player, $factionList[$data]->getName());
            }
        });
        
        $form->setTitle("§1Gérer les factions");
        $form->setContent("§9Sélectionnez une faction à gérer:");
        
        foreach ($factions as $faction) {
            $form->addButton(
                "§9" . $faction->getName() . "\n§1Membres: " . $faction->getMemberCount() . " | Power: " . $faction->getPower()
            );
        }
        
        $player->sendForm($form);
    }

    public static function sendFactionManagementOptions(Player $player, string $factionName): void {
        $plugin = Main::getInstance();
        $faction = $plugin->getFactionManager()->getFaction($factionName);
        
        if ($faction === null) {
            $player->sendMessage("§cFaction introuvable!");
            return;
        }
        
        $form = new SimpleForm(function (Player $player, ?int $data) use ($factionName) {
            if ($data === null) return;
            
            switch ($data) {
                case 0: // Voir les infos
                    self::sendDetailedFactionInfo($player, $factionName);
                    break;
                case 1: // Modifier le power
                    self::sendSetPowerForm($player, $factionName);
                    break;
                case 2: // Ajouter un membre
                    self::sendAddMemberForm($player, $factionName);
                    break;
                case 3: // Retirer un membre
                    self::sendRemoveMemberMenu($player, $factionName);
                    break;
                case 4: // Changer le leader
                    self::sendChangeLeaderMenu($player, $factionName);
                    break;
                case 5: // Supprimer la faction
                    self::sendDeleteConfirm($player, $factionName);
                    break;
                case 6: // Retour
                    self::sendMainMenu($player);
                    break;
            }
        });
        
        $form->setTitle("§1Gérer: " . $factionName);
        $form->setContent("§9Options de gestion pour §1" . $factionName);
        
        $form->addButton("§9Voir les informations");
        $form->addButton("§1Modifier le power");
        $form->addButton("§9Ajouter un membre");
        $form->addButton("§1Retirer un membre");
        $form->addButton("§9Changer le leader");
        $form->addButton("§cSupprimer la faction");
        $form->addButton("§7Retour");
        
        $player->sendForm($form);
    }

    public static function sendDetailedFactionInfo(Player $player, string $factionName): void {
        $plugin = Main::getInstance();
        $faction = $plugin->getFactionManager()->getFaction($factionName);
        
        if ($faction === null) {
            $player->sendMessage("§cFaction introuvable!");
            return;
        }
        
        $form = new SimpleForm(function (Player $player, ?int $data) use ($factionName) {
            if ($data === null) return;
            self::sendFactionManagementOptions($player, $factionName);
        });
        
        $form->setTitle("§1Infos: " . $faction->getName());
        
        $content = "§9§lInformations détaillées:\n\n";
        $content .= "§1Leader: §9" . $faction->getLeader() . "\n";
        $content .= "§1Membres: §9" . $faction->getMemberCount() . "\n";
        $content .= "§1Power: §9" . $faction->getPower() . "/" . $faction->getMaxPower() . "\n";
        $content .= "§1Argent: §9$" . $faction->getMoney() . "\n";
        $content .= "§1Claims: §9" . count($faction->getClaims()) . "\n";
        $content .= "§1Description: §9" . ($faction->getDescription() ?: "Aucune") . "\n";
        $content .= "§1Ouverte: §9" . ($faction->isOpen() ? "Oui" : "Non") . "\n";
        $content .= "§1Île: §9" . ($faction->getIsland() ? "Oui" : "Non") . "\n\n";
        
        $content .= "§9§lMembres:\n";
        foreach ($faction->getMembers() as $member) {
            $role = $faction->isLeader($member) ? "§c[Leader]" : ($faction->isOfficer($member) ? "§6[Officier]" : "§a[Membre]");
            $content .= $role . " §1" . $member . "\n";
        }
        
        if (count($faction->getOfficers()) > 0) {
            $content .= "\n§9§lOfficiers:\n§1" . implode(", ", $faction->getOfficers()) . "\n";
        }
        
        if (count($faction->getAllies()) > 0) {
            $content .= "\n§9§lAlliés:\n§a" . implode(", ", $faction->getAllies()) . "\n";
        }
        
        if (count($faction->getEnemies()) > 0) {
            $content .= "\n§9§lEnnemis:\n§c" . implode(", ", $faction->getEnemies()) . "\n";
        }
        
        if (count($faction->getInvites()) > 0) {
            $content .= "\n§9§lInvitations en attente:\n§e" . implode(", ", $faction->getInvites()) . "\n";
        }
        
        $content .= "\n§1Créée le: §9" . date("d/m/Y H:i", $faction->getCreatedAt());
        
        $form->setContent($content);
        $form->addButton("§9Retour");
        
        $player->sendForm($form);
    }

    public static function sendDeleteFactionMenu(Player $player): void {
        $plugin = Main::getInstance();
        $factions = $plugin->getFactionManager()->getAllFactions();
        
        if (empty($factions)) {
            $player->sendMessage("§cAucune faction n'existe!");
            return;
        }
        
        $form = new SimpleForm(function (Player $player, ?int $data) use ($factions) {
            if ($data === null) return;
            
            $factionList = array_values($factions);
            if (isset($factionList[$data])) {
                self::sendDeleteConfirm($player, $factionList[$data]->getName());
            }
        });
        
        $form->setTitle("§1Supprimer une faction");
        $form->setContent("§9Sélectionnez la faction à supprimer:");
        
        foreach ($factions as $faction) {
            $form->addButton("§c" . $faction->getName() . "\n§1" . $faction->getMemberCount() . " membres");
        }
        
        $player->sendForm($form);
    }

    public static function sendDeleteConfirm(Player $player, string $factionName): void {
        $form = new SimpleForm(function (Player $player, ?int $data) use ($factionName) {
            if ($data === null || $data !== 0) return;
            
            $plugin = Main::getInstance();
            if ($plugin->getFactionManager()->deleteFaction($factionName)) {
                $player->sendMessage("§9Faction §1" . $factionName . " §9supprimée avec succès!");
            } else {
                $player->sendMessage("§cErreur lors de la suppression de la faction!");
            }
        });
        
        $form->setTitle("§cConfirmation");
        $form->setContent("§9Êtes-vous sûr de vouloir supprimer la faction §1" . $factionName . "§9?\n\n§cCette action est irréversible!");
        
        $form->addButton("§cOui, supprimer");
        $form->addButton("§9Annuler");
        
        $player->sendForm($form);
    }

    public static function sendAdminJoinMenu(Player $player): void {
        $plugin = Main::getInstance();
        $factions = $plugin->getFactionManager()->getAllFactions();
        
        if (empty($factions)) {
            $player->sendMessage("§cAucune faction n'existe!");
            return;
        }
        
        $form = new SimpleForm(function (Player $player, ?int $data) use ($factions) {
            if ($data === null) return;
            
            $factionList = array_values($factions);
            if (!isset($factionList[$data])) return;
            
            $faction = $factionList[$data];
            $plugin = Main::getInstance();
            $factionManager = $plugin->getFactionManager();
            
            // Retirer de l'ancienne faction si présent
            $oldFaction = $factionManager->getPlayerFaction($player->getName());
            if ($oldFaction !== null) {
                $oldFaction->removeMember($player->getName());
                $factionManager->saveFaction($oldFaction);
            }
            
            // Ajouter à la nouvelle faction avec tous les droits
            $faction->addMember($player->getName());
            $faction->promoteOfficer($player->getName());
            $factionManager->updatePlayerFaction($player->getName(), $faction->getName());
            $factionManager->saveFaction($faction);
            
            $player->sendMessage("§9Vous avez rejoint la faction §1" . $faction->getName() . " §9en mode admin!");
            $player->sendMessage("§9Vous avez tous les droits dans cette faction.");
        });
        
        $form->setTitle("§1Rejoindre une faction");
        $form->setContent("§9Mode administrateur - Tous les droits");
        
        foreach ($factions as $faction) {
            $form->addButton("§9" . $faction->getName() . "\n§1" . $faction->getMemberCount() . " membres");
        }
        
        $player->sendForm($form);
    }

    public static function sendPowerManagementMenu(Player $player): void {
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if ($data === null) return;
            
            switch ($data) {
                case 0: // Définir le power
                    self::sendSelectFactionForPower($player, "set");
                    break;
                case 1: // Ajouter du power
                    self::sendSelectFactionForPower($player, "add");
                    break;
                case 2: // Retirer du power
                    self::sendSelectFactionForPower($player, "remove");
                    break;
                case 3: // Retour
                    self::sendMainMenu($player);
                    break;
            }
        });
        
        $form->setTitle("§1Gestion du Power");
        $form->setContent("§9Modifier le power des factions:");
        
        $form->addButton("§9Définir le power");
        $form->addButton("§1Ajouter du power");
        $form->addButton("§9Retirer du power");
        $form->addButton("§7Retour");
        
        $player->sendForm($form);
    }

    public static function sendSelectFactionForPower(Player $player, string $action): void {
        $plugin = Main::getInstance();
        $factions = $plugin->getFactionManager()->getAllFactions();
        
        if (empty($factions)) {
            $player->sendMessage("§cAucune faction n'existe!");
            return;
        }
        
        $form = new SimpleForm(function (Player $player, ?int $data) use ($factions, $action) {
            if ($data === null) return;
            
            $factionList = array_values($factions);
            if (isset($factionList[$data])) {
                self::sendPowerAmountForm($player, $factionList[$data]->getName(), $action);
            }
        });
        
        $actionText = $action === "set" ? "Définir" : ($action === "add" ? "Ajouter" : "Retirer");
        $form->setTitle("§1" . $actionText . " le power");
        $form->setContent("§9Sélectionnez une faction:");
        
        foreach ($factions as $faction) {
            $form->addButton("§9" . $faction->getName() . "\n§1Power: " . $faction->getPower() . "/" . $faction->getMaxPower());
        }
        
        $player->sendForm($form);
    }

    public static function sendPowerAmountForm(Player $player, string $factionName, string $action): void {
        $form = new CustomForm(function (Player $player, ?array $data) use ($factionName, $action) {
            if ($data === null) return;
            
            $amount = (int)($data[0] ?? 0);
            
            if ($amount <= 0) {
                $player->sendMessage("§cMontant invalide!");
                return;
            }
            
            $plugin = Main::getInstance();
            $faction = $plugin->getFactionManager()->getFaction($factionName);
            
            if ($faction === null) {
                $player->sendMessage("§cFaction introuvable!");
                return;
            }
            
            switch ($action) {
                case "set":
                    $faction->setPower($amount);
                    $player->sendMessage("§9Power de §1" . $factionName . " §9défini à §1" . $amount);
                    break;
                case "add":
                    $faction->addPower($amount);
                    $player->sendMessage("§9+§1" . $amount . " §9power ajouté à §1" . $factionName);
                    break;
                case "remove":
                    $faction->removePower($amount);
                    $player->sendMessage("§9-§1" . $amount . " §9power retiré de §1" . $factionName);
                    break;
            }
            
            $plugin->getFactionManager()->saveFaction($faction);
        });
        
        $actionText = $action === "set" ? "Définir" : ($action === "add" ? "Ajouter" : "Retirer");
        $form->setTitle("§1" . $actionText . " le power");
        $form->addInput("§9Montant de power:", "100");
        
        $player->sendForm($form);
    }

    public static function sendSetPowerForm(Player $player, string $factionName): void {
        self::sendPowerAmountForm($player, $factionName, "set");
    }

    public static function sendAddMemberForm(Player $player, string $factionName): void {
        $form = new CustomForm(function (Player $player, ?array $data) use ($factionName) {
            if ($data === null) return;
            
            $memberName = trim($data[0] ?? "");
            
            if (empty($memberName)) {
                $player->sendMessage("§cVeuillez entrer un nom de joueur!");
                return;
            }
            
            $plugin = Main::getInstance();
            $factionManager = $plugin->getFactionManager();
            $faction = $factionManager->getFaction($factionName);
            
            if ($faction === null) {
                $player->sendMessage("§cFaction introuvable!");
                return;
            }
            
            if ($faction->isMember($memberName)) {
                $player->sendMessage("§cCe joueur est déjà membre de cette faction!");
                return;
            }
            
            // Retirer de l'ancienne faction si présent
            $oldFaction = $factionManager->getPlayerFaction($memberName);
            if ($oldFaction !== null) {
                $oldFaction->removeMember($memberName);
                $factionManager->saveFaction($oldFaction);
            }
            
            $faction->addMember($memberName);
            $factionManager->updatePlayerFaction($memberName, $faction->getName());
            $factionManager->saveFaction($faction);
            
            $player->sendMessage("§9" . $memberName . " §9a été ajouté à §1" . $factionName);
        });
        
        $form->setTitle("§1Ajouter un membre");
        $form->addInput("§9Nom du joueur:", "Joueur123");
        
        $player->sendForm($form);
    }

    public static function sendRemoveMemberMenu(Player $player, string $factionName): void {
        $plugin = Main::getInstance();
        $faction = $plugin->getFactionManager()->getFaction($factionName);
        
        if ($faction === null) {
            $player->sendMessage("§cFaction introuvable!");
            return;
        }
        
        $form = new SimpleForm(function (Player $player, ?int $data) use ($faction, $factionName) {
            if ($data === null) return;
            
            $members = array_values($faction->getMembers());
            if (!isset($members[$data])) return;
            
            $memberName = $members[$data];
            $faction->removeMember($memberName);
            
            $plugin = Main::getInstance();
            $plugin->getFactionManager()->updatePlayerFaction($memberName, null);
            $plugin->getFactionManager()->saveFaction($faction);
            
            $player->sendMessage("§9" . $memberName . " §9a été retiré de §1" . $factionName);
        });
        
        $form->setTitle("§1Retirer un membre");
        $form->setContent("§9Sélectionnez le membre à retirer:");
        
        foreach ($faction->getMembers() as $member) {
            $role = $faction->isLeader($member) ? "§c[Leader]" : ($faction->isOfficer($member) ? "§6[Officier]" : "§a[Membre]");
            $form->addButton($role . " §1" . $member);
        }
        
        $player->sendForm($form);
    }

    public static function sendChangeLeaderMenu(Player $player, string $factionName): void {
        $plugin = Main::getInstance();
        $faction = $plugin->getFactionManager()->getFaction($factionName);
        
        if ($faction === null) {
            $player->sendMessage("§cFaction introuvable!");
            return;
        }
        
        $form = new SimpleForm(function (Player $player, ?int $data) use ($faction, $factionName) {
            if ($data === null) return;
            
            $members = array_values($faction->getMembers());
            if (!isset($members[$data])) return;
            
            $newLeader = $members[$data];
            $faction->setLeader($newLeader);
            
            $plugin = Main::getInstance();
            $plugin->getFactionManager()->saveFaction($faction);
            
            $player->sendMessage("§9" . $newLeader . " §9est maintenant le leader de §1" . $factionName);
        });
        
        $form->setTitle("§1Changer le leader");
        $form->setContent("§9Sélectionnez le nouveau leader:");
        
        foreach ($faction->getMembers() as $member) {
            $role = $faction->isLeader($member) ? "§c[Leader actuel]" : ($faction->isOfficer($member) ? "§6[Officier]" : "§a[Membre]");
            $form->addButton($role . " §1" . $member);
        }
        
        $player->sendForm($form);
    }

    public static function sendAdminFactionList(Player $player): void {
        $plugin = Main::getInstance();
        $factions = $plugin->getFactionManager()->getAllFactions();
        
        $form = new SimpleForm(function (Player $player, ?int $data) use ($factions) {
            if ($data === null) return;
            
            $factionList = array_values($factions);
            if (isset($factionList[$data])) {
                self::sendDetailedFactionInfo($player, $factionList[$data]->getName());
            }
        });
        
        $form->setTitle("§1Liste des factions");
        $form->setContent("§9Total: §1" . count($factions) . " §9factions");
        
        foreach ($factions as $faction) {
            $form->addButton(
                "§9" . $faction->getName() . "\n§1Membres: " . $faction->getMemberCount() . " | Power: " . $faction->getPower()
            );
        }
        
        $player->sendForm($form);
    }

    public static function sendConfigMenu(Player $player): void {
        $plugin = Main::getInstance();
        $config = $plugin->getConfig();
        
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if ($data === null) return;
            
            switch ($data) {
                case 0: // Max membres
                    self::sendEditConfigValue($player, "max-members", "general");
                    break;
                case 1: // Max factions
                    self::sendEditConfigValue($player, "max-factions", "general");
                    break;
                case 2: // Max claims
                    self::sendEditConfigValue($player, "max-claims", "claims");
                    break;
                case 3: // Coût claim
                    self::sendEditConfigValue($player, "claim-cost", "claims");
                    break;
                case 4: // Power par claim
                    self::sendEditConfigValue($player, "power-per-claim", "claims");
                    break;
                case 5: // Retour
                    self::sendMainMenu($player);
                    break;
            }
        });
        
        $form->setTitle("§1Configuration");
        
        $content = "§9Valeurs actuelles:\n\n";
        $content .= "§1Max membres: §9" . $config->getNested("general.max-members", 20) . "\n";
        $content .= "§1Max factions: §9" . $config->getNested("general.max-factions", 0) . "\n";
        $content .= "§1Max claims: §9" . $config->getNested("claims.max-claims", 10) . "\n";
        $content .= "§1Coût claim: §9" . $config->getNested("claims.claim-cost", 100) . "\n";
        $content .= "§1Power/claim: §9" . $config->getNested("claims.power-per-claim", 5) . "\n";
        $content .= "§1Économie: §9" . ($config->getNested("economy.enabled", false) ? "Activée" : "Désactivée");
        
        $form->setContent($content);
        
        $form->addButton("§9Modifier max membres");
        $form->addButton("§1Modifier max factions");
        $form->addButton("§9Modifier max claims");
        $form->addButton("§1Modifier coût claim");
        $form->addButton("§9Modifier power/claim");
        $form->addButton("§7Retour");
        
        $player->sendForm($form);
    }

    public static function sendEditConfigValue(Player $player, string $key, string $section): void {
        $form = new CustomForm(function (Player $player, ?array $data) use ($key, $section) {
            if ($data === null) return;
            
            $value = (int)($data[0] ?? 0);
            
            $plugin = Main::getInstance();
            $config = $plugin->getConfig();
            $config->setNested($section . "." . $key, $value);
            $config->save();
            
            $player->sendMessage("§9Configuration mise à jour: §1" . $key . " = " . $value);
            self::sendConfigMenu($player);
        });
        
        $plugin = Main::getInstance();
        $currentValue = $plugin->getConfig()->getNested($section . "." . $key, 0);
        
        $form->setTitle("§1Modifier " . $key);
        $form->addInput("§9Nouvelle valeur:", (string)$currentValue, (string)$currentValue);
        
        $player->sendForm($form);
    }
}
