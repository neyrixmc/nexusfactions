<?php

declare(strict_types=1);

namespace NexusFactions\ui;

use NexusFactions\Main;
use pocketmine\player\Player;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;

class FactionUI {
    
    public static function sendMainMenu(Player $player): void {
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if ($data === null) return;
            
            $plugin = Main::getInstance();
            $factionManager = $plugin->getFactionManager();
            $faction = $factionManager->getPlayerFaction($player->getName());
            
            switch ($data) {
                case 0: // Créer une faction
                    if ($faction !== null) {
                        $player->sendMessage("§cVous êtes déjà dans une faction!");
                        return;
                    }
                    self::sendCreateFactionForm($player);
                    break;
                    
                case 1: // Ma faction
                    if ($faction === null) {
                        $player->sendMessage("§cVous n'êtes pas dans une faction!");
                        return;
                    }
                    self::sendFactionInfoMenu($player, $faction->getName());
                    break;
                    
                case 2: // Gérer la faction
                    if ($faction === null) {
                        $player->sendMessage("§cVous n'êtes pas dans une faction!");
                        return;
                    }
                    if (!$faction->isLeader($player->getName()) && !$faction->isOfficer($player->getName())) {
                        $player->sendMessage("§cVous devez être leader ou officier!");
                        return;
                    }
                    self::sendManagementMenu($player);
                    break;
                    
                case 3: // Liste des factions
                    self::sendFactionListMenu($player);
                    break;
                    
                case 4: // Île de faction
                    if ($faction === null) {
                        $player->sendMessage("§cVous n'êtes pas dans une faction!");
                        return;
                    }
                    self::sendIslandMenu($player);
                    break;
                    
                case 5: // Quitter la faction
                    if ($faction === null) {
                        $player->sendMessage("§cVous n'êtes pas dans une faction!");
                        return;
                    }
                    self::sendLeaveFactionConfirm($player);
                    break;
            }
        });
        
        $form->setTitle("§1NexusFactions");
        
        $plugin = Main::getInstance();
        $faction = $plugin->getFactionManager()->getPlayerFaction($player->getName());
        
        if ($faction === null) {
            $form->setContent("§9Bienvenue dans le système de factions!\n§9Créez ou rejoignez une faction pour commencer.");
            $form->addButton("§9Créer une faction\n§1Fonder votre propre faction");
        } else {
            $form->setContent("§9Faction: §1" . $faction->getName() . "\n§9Membres: §1" . $faction->getMemberCount() . "\n§9Power: §1" . $faction->getPower() . "/" . $faction->getMaxPower());
            $form->addButton("§9Ma faction\n§1Voir les informations");
        }
        
        $form->addButton("§1Gérer la faction\n§9Options de gestion");
        $form->addButton("§9Liste des factions\n§1Voir toutes les factions");
        $form->addButton("§1Île de faction\n§9Gérer votre île");
        
        if ($faction !== null) {
            $form->addButton("§cQuitter la faction\n§9Abandonner votre faction");
        }
        
        $player->sendForm($form);
    }

    public static function sendCreateFactionForm(Player $player): void {
        $form = new CustomForm(function (Player $player, ?array $data) {
            if ($data === null) return;
            
            $name = trim($data[0] ?? "");
            
            if (empty($name)) {
                $player->sendMessage("§cVeuillez entrer un nom de faction!");
                return;
            }
            
            $plugin = Main::getInstance();
            $config = $plugin->getConfig();
            $minLength = $config->getNested("general.min-name-length", 3);
            $maxLength = $config->getNested("general.max-name-length", 16);
            
            if (strlen($name) < $minLength || strlen($name) > $maxLength) {
                $player->sendMessage("§cLe nom doit contenir entre " . $minLength . " et " . $maxLength . " caractères!");
                return;
            }
            
            if (!preg_match("/^[a-zA-Z0-9_]+$/", $name)) {
                $player->sendMessage("§cLe nom ne peut contenir que des lettres, chiffres et underscores!");
                return;
            }
            
            $factionManager = $plugin->getFactionManager();
            
            if ($factionManager->factionExists($name)) {
                $player->sendMessage("§cCette faction existe déjà!");
                return;
            }
            
            $faction = $factionManager->createFaction($name, $player->getName());
            if ($faction !== null) {
                $player->sendMessage("§9Faction §1" . $name . " §9créée avec succès!");
                
                // Créer automatiquement une île
                $plugin->getIslandManager()->createIsland($name, $player->getPosition());
                $player->sendMessage("§9Île de faction créée!");
            }
        });
        
        $form->setTitle("§1Créer une faction");
        $form->addInput("§9Nom de la faction:", "MonFaction");
        
        $player->sendForm($form);
    }

    public static function sendFactionInfoMenu(Player $player, string $factionName): void {
        $plugin = Main::getInstance();
        $faction = $plugin->getFactionManager()->getFaction($factionName);
        
        if ($faction === null) {
            $player->sendMessage("§cFaction introuvable!");
            return;
        }
        
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if ($data === null) return;
            self::sendMainMenu($player);
        });
        
        $form->setTitle("§l§6" . $faction->getName());
        
        $content = "§e§lInformations:\n\n";
        $content .= "§7Leader: §e" . $faction->getLeader() . "\n";
        $content .= "§7Membres: §e" . $faction->getMemberCount() . "\n";
        $content .= "§7Power: §e" . $faction->getPower() . "/" . $faction->getMaxPower() . "\n";
        $content .= "§7Argent: §e$" . $faction->getMoney() . "\n";
        $content .= "§7Description: §e" . ($faction->getDescription() ?: "Aucune") . "\n\n";
        
        $content .= "§e§lMembres:\n";
        foreach ($faction->getMembers() as $member) {
            $role = $faction->isLeader($member) ? "§c[Leader]" : ($faction->isOfficer($member) ? "§6[Officier]" : "§a[Membre]");
            $content .= $role . " §7" . $member . "\n";
        }
        
        if (count($faction->getAllies()) > 0) {
            $content .= "\n§e§lAlliés:\n§a" . implode(", ", $faction->getAllies());
        }
        
        if (count($faction->getEnemies()) > 0) {
            $content .= "\n§e§lEnnemis:\n§c" . implode(", ", $faction->getEnemies());
        }
        
        $form->setContent($content);
        $form->addButton("§cRetour", 0, "textures/ui/arrow_left");
        
        $player->sendForm($form);
    }

    public static function sendManagementMenu(Player $player): void {
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if ($data === null) return;
            
            switch ($data) {
                case 0: // Inviter
                    self::sendInvitePlayerForm($player);
                    break;
                case 1: // Expulser
                    self::sendKickPlayerMenu($player);
                    break;
                case 2: // Promouvoir
                    self::sendPromotePlayerMenu($player);
                    break;
                case 3: // Rétrograder
                    self::sendDemotePlayerMenu($player);
                    break;
                case 4: // Alliances
                    self::sendAllianceMenu($player);
                    break;
                case 5: // Dissoudre
                    self::sendDisbandConfirm($player);
                    break;
                case 6: // Retour
                    self::sendMainMenu($player);
                    break;
            }
        });
        
        $form->setTitle("§l§6Gestion de faction");
        $form->setContent("§7Choisissez une option de gestion:");
        
        $form->addButton("§aInviter un joueur\n§7Ajouter un membre", 0, "textures/ui/color_plus");
        $form->addButton("§cExpulser un membre\n§7Retirer un joueur", 0, "textures/ui/cancel");
        $form->addButton("§ePromouvoir\n§7Nommer un officier", 0, "textures/ui/arrow_up");
        $form->addButton("§6Rétrograder\n§7Retirer le grade d'officier", 0, "textures/ui/arrow_down");
        $form->addButton("§bGérer les alliances\n§7Alliés et ennemis", 0, "textures/ui/icon_deals");
        $form->addButton("§4Dissoudre la faction\n§7§lDANGER", 0, "textures/ui/trash_default");
        $form->addButton("§7Retour", 0, "textures/ui/arrow_left");
        
        $player->sendForm($form);
    }

    public static function sendInvitePlayerForm(Player $player): void {
        $form = new CustomForm(function (Player $player, ?array $data) {
            if ($data === null) return;
            
            $targetName = trim($data[0] ?? "");
            
            if (empty($targetName)) {
                $player->sendMessage("§cVeuillez entrer un nom de joueur!");
                return;
            }
            
            $plugin = Main::getInstance();
            $factionManager = $plugin->getFactionManager();
            $faction = $factionManager->getPlayerFaction($player->getName());
            
            if ($faction === null) {
                $player->sendMessage("§cVous n'êtes pas dans une faction!");
                return;
            }
            
            if ($factionManager->isInFaction($targetName)) {
                $player->sendMessage("§cCe joueur est déjà dans une faction!");
                return;
            }
            
            $faction->addInvite($targetName);
            $factionManager->saveFaction($faction);
            
            $player->sendMessage("§aInvitation envoyée à §e" . $targetName);
            
            $target = $plugin->getServer()->getPlayerByPrefix($targetName);
            if ($target !== null) {
                $target->sendMessage("§aVous avez été invité à rejoindre la faction §e" . $faction->getName());
                $target->sendMessage("§7Utilisez §e/f join " . $faction->getName() . " §7pour accepter");
            }
        });
        
        $form->setTitle("§l§6Inviter un joueur");
        $form->addInput("§eNom du joueur:", "Joueur123");
        
        $player->sendForm($form);
    }

    public static function sendKickPlayerMenu(Player $player): void {
        $plugin = Main::getInstance();
        $faction = $plugin->getFactionManager()->getPlayerFaction($player->getName());
        
        if ($faction === null) {
            $player->sendMessage("§cVous n'êtes pas dans une faction!");
            return;
        }
        
        $form = new SimpleForm(function (Player $player, ?int $data) use ($faction) {
            if ($data === null) return;
            
            $members = array_values(array_filter($faction->getMembers(), fn($m) => $m !== $faction->getLeader()));
            
            if (!isset($members[$data])) return;
            
            $targetName = $members[$data];
            
            $faction->removeMember($targetName);
            Main::getInstance()->getFactionManager()->updatePlayerFaction($targetName, null);
            Main::getInstance()->getFactionManager()->saveFaction($faction);
            
            $player->sendMessage("§e" . $targetName . " §aa été expulsé de la faction!");
            
            $target = Main::getInstance()->getServer()->getPlayerByPrefix($targetName);
            if ($target !== null) {
                $target->sendMessage("§cVous avez été expulsé de la faction §e" . $faction->getName());
            }
        });
        
        $form->setTitle("§l§6Expulser un membre");
        $form->setContent("§7Sélectionnez le membre à expulser:");
        
        foreach ($faction->getMembers() as $member) {
            if ($member !== $faction->getLeader()) {
                $role = $faction->isOfficer($member) ? "§6[Officier]" : "§a[Membre]";
                $form->addButton($role . " §7" . $member);
            }
        }
        
        $player->sendForm($form);
    }

    public static function sendPromotePlayerMenu(Player $player): void {
        $plugin = Main::getInstance();
        $faction = $plugin->getFactionManager()->getPlayerFaction($player->getName());
        
        if ($faction === null || !$faction->isLeader($player->getName())) {
            $player->sendMessage("§cVous devez être le leader!");
            return;
        }
        
        $form = new SimpleForm(function (Player $player, ?int $data) use ($faction) {
            if ($data === null) return;
            
            $members = array_values(array_filter($faction->getMembers(), fn($m) => $m !== $faction->getLeader() && !$faction->isOfficer($m)));
            
            if (!isset($members[$data])) return;
            
            $targetName = $members[$data];
            $faction->promoteOfficer($targetName);
            Main::getInstance()->getFactionManager()->saveFaction($faction);
            
            $player->sendMessage("§e" . $targetName . " §aa été promu officier!");
        });
        
        $form->setTitle("§l§6Promouvoir un membre");
        $form->setContent("§7Sélectionnez le membre à promouvoir:");
        
        foreach ($faction->getMembers() as $member) {
            if ($member !== $faction->getLeader() && !$faction->isOfficer($member)) {
                $form->addButton("§a[Membre] §7" . $member);
            }
        }
        
        $player->sendForm($form);
    }

    public static function sendDemotePlayerMenu(Player $player): void {
        $plugin = Main::getInstance();
        $faction = $plugin->getFactionManager()->getPlayerFaction($player->getName());
        
        if ($faction === null || !$faction->isLeader($player->getName())) {
            $player->sendMessage("§cVous devez être le leader!");
            return;
        }
        
        $form = new SimpleForm(function (Player $player, ?int $data) use ($faction) {
            if ($data === null) return;
            
            $officers = $faction->getOfficers();
            
            if (!isset($officers[$data])) return;
            
            $targetName = $officers[$data];
            $faction->demoteOfficer($targetName);
            Main::getInstance()->getFactionManager()->saveFaction($faction);
            
            $player->sendMessage("§e" . $targetName . " §aa été rétrogradé!");
        });
        
        $form->setTitle("§l§6Rétrograder un officier");
        $form->setContent("§7Sélectionnez l'officier à rétrograder:");
        
        foreach ($faction->getOfficers() as $officer) {
            $form->addButton("§6[Officier] §7" . $officer);
        }
        
        $player->sendForm($form);
    }

    public static function sendAllianceMenu(Player $player): void {
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if ($data === null) return;
            
            switch ($data) {
                case 0:
                    self::sendAllyRequestForm($player);
                    break;
                case 1:
                    self::sendEnemyRequestForm($player);
                    break;
                case 2:
                    self::sendManagementMenu($player);
                    break;
            }
        });
        
        $form->setTitle("§l§6Gestion des alliances");
        $form->setContent("§7Gérez vos relations diplomatiques:");
        
        $form->addButton("§aAjouter un allié\n§7Demander une alliance", 0, "textures/ui/color_plus");
        $form->addButton("§cDéclarer ennemi\n§7Hostilité", 0, "textures/ui/cancel");
        $form->addButton("§7Retour", 0, "textures/ui/arrow_left");
        
        $player->sendForm($form);
    }

    public static function sendAllyRequestForm(Player $player): void {
        $form = new CustomForm(function (Player $player, ?array $data) {
            if ($data === null) return;
            
            $targetFaction = trim($data[0] ?? "");
            
            if (empty($targetFaction)) {
                $player->sendMessage("§cVeuillez entrer un nom de faction!");
                return;
            }
            
            $plugin = Main::getInstance();
            $factionManager = $plugin->getFactionManager();
            $faction = $factionManager->getPlayerFaction($player->getName());
            $target = $factionManager->getFaction($targetFaction);
            
            if ($faction === null) {
                $player->sendMessage("§cVous n'êtes pas dans une faction!");
                return;
            }
            
            if ($target === null) {
                $player->sendMessage("§cCette faction n'existe pas!");
                return;
            }
            
            if ($faction->getName() === $target->getName()) {
                $player->sendMessage("§cVous ne pouvez pas vous allier avec vous-même!");
                return;
            }
            
            $faction->addAlly($target->getName());
            $faction->removeEnemy($target->getName());
            $factionManager->saveFaction($faction);
            
            $player->sendMessage("§aAlliance établie avec §e" . $target->getName());
        });
        
        $form->setTitle("§l§6Demander une alliance");
        $form->addInput("§eNom de la faction:", "FactionAlliée");
        
        $player->sendForm($form);
    }

    public static function sendEnemyRequestForm(Player $player): void {
        $form = new CustomForm(function (Player $player, ?array $data) {
            if ($data === null) return;
            
            $targetFaction = trim($data[0] ?? "");
            
            if (empty($targetFaction)) {
                $player->sendMessage("§cVeuillez entrer un nom de faction!");
                return;
            }
            
            $plugin = Main::getInstance();
            $factionManager = $plugin->getFactionManager();
            $faction = $factionManager->getPlayerFaction($player->getName());
            $target = $factionManager->getFaction($targetFaction);
            
            if ($faction === null) {
                $player->sendMessage("§cVous n'êtes pas dans une faction!");
                return;
            }
            
            if ($target === null) {
                $player->sendMessage("§cCette faction n'existe pas!");
                return;
            }
            
            $faction->addEnemy($target->getName());
            $faction->removeAlly($target->getName());
            $factionManager->saveFaction($faction);
            
            $player->sendMessage("§cVous êtes maintenant en guerre avec §e" . $target->getName());
        });
        
        $form->setTitle("§l§6Déclarer ennemi");
        $form->addInput("§eNom de la faction:", "FactionEnnemie");
        
        $player->sendForm($form);
    }

    public static function sendFactionListMenu(Player $player): void {
        $plugin = Main::getInstance();
        $factions = $plugin->getFactionManager()->getAllFactions();
        
        $form = new SimpleForm(function (Player $player, ?int $data) use ($factions) {
            if ($data === null) return;
            
            $factionList = array_values($factions);
            if (isset($factionList[$data])) {
                self::sendFactionInfoMenu($player, $factionList[$data]->getName());
            }
        });
        
        $form->setTitle("§l§6Liste des factions");
        $form->setContent("§7Total: §e" . count($factions) . " §7factions");
        
        foreach ($factions as $faction) {
            $form->addButton(
                "§e" . $faction->getName() . "\n§7Membres: " . $faction->getMemberCount() . " | Power: " . $faction->getPower(),
                0,
                "textures/ui/icon_recipe_item"
            );
        }
        
        $player->sendForm($form);
    }

    public static function sendIslandMenu(Player $player): void {
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if ($data === null) return;
            
            $plugin = Main::getInstance();
            $faction = $plugin->getFactionManager()->getPlayerFaction($player->getName());
            
            if ($faction === null) return;
            
            switch ($data) {
                case 0: // Téléporter
                    $island = $plugin->getIslandManager()->getIslandByFaction($faction->getName());
                    if ($island !== null) {
                        $player->teleport($island->getSpawnPoint());
                        $player->sendMessage("§aTéléportation à l'île de faction!");
                    } else {
                        $player->sendMessage("§cVotre faction n'a pas d'île!");
                    }
                    break;
                    
                case 1: // Définir spawn
                    if (!$faction->isLeader($player->getName()) && !$faction->isOfficer($player->getName())) {
                        $player->sendMessage("§cVous devez être leader ou officier!");
                        return;
                    }
                    
                    $island = $plugin->getIslandManager()->getIslandByFaction($faction->getName());
                    if ($island !== null) {
                        $island->setSpawnPoint($player->getPosition());
                        $plugin->getIslandManager()->saveIsland($island);
                        $player->sendMessage("§aSpawn de l'île défini!");
                    }
                    break;
                    
                case 2: // Verrouiller/Déverrouiller
                    if (!$faction->isLeader($player->getName()) && !$faction->isOfficer($player->getName())) {
                        $player->sendMessage("§cVous devez être leader ou officier!");
                        return;
                    }
                    
                    $island = $plugin->getIslandManager()->getIslandByFaction($faction->getName());
                    if ($island !== null) {
                        $island->setLocked(!$island->isLocked());
                        $plugin->getIslandManager()->saveIsland($island);
                        $player->sendMessage($island->isLocked() ? "§cÎle verrouillée!" : "§aÎle déverrouillée!");
                    }
                    break;
            }
        });
        
        $form->setTitle("§l§6Île de faction");
        $form->setContent("§7Gérez l'île de votre faction:");
        
        $form->addButton("§aTéléporter à l'île\n§7Rejoindre votre base", 0, "textures/ui/world_glyph_color_2x");
        $form->addButton("§eDéfinir le spawn\n§7Point d'apparition", 0, "textures/ui/icon_setting");
        $form->addButton("§cVerrouiller/Déverrouiller\n§7Accès à l'île", 0, "textures/ui/lock_color");
        
        $player->sendForm($form);
    }

    public static function sendLeaveFactionConfirm(Player $player): void {
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if ($data === null || $data !== 0) return;
            
            $plugin = Main::getInstance();
            $factionManager = $plugin->getFactionManager();
            $faction = $factionManager->getPlayerFaction($player->getName());
            
            if ($faction === null) return;
            
            if ($faction->isLeader($player->getName())) {
                $player->sendMessage("§cVous devez dissoudre la faction ou transférer le leadership!");
                return;
            }
            
            $faction->removeMember($player->getName());
            $factionManager->updatePlayerFaction($player->getName(), null);
            $factionManager->saveFaction($faction);
            
            $player->sendMessage("§aVous avez quitté la faction §e" . $faction->getName());
        });
        
        $form->setTitle("§l§cQuitter la faction");
        $form->setContent("§cÊtes-vous sûr de vouloir quitter votre faction?\n§7Cette action est irréversible!");
        
        $form->addButton("§cOui, quitter", 0, "textures/ui/realms_red_x");
        $form->addButton("§aAnnuler", 0, "textures/ui/cancel");
        
        $player->sendForm($form);
    }

    public static function sendDisbandConfirm(Player $player): void {
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if ($data === null || $data !== 0) return;
            
            $plugin = Main::getInstance();
            $factionManager = $plugin->getFactionManager();
            $faction = $factionManager->getPlayerFaction($player->getName());
            
            if ($faction === null) return;
            
            if (!$faction->isLeader($player->getName())) {
                $player->sendMessage("§cSeul le leader peut dissoudre la faction!");
                return;
            }
            
            $factionName = $faction->getName();
            $factionManager->deleteFaction($factionName);
            
            $player->sendMessage("§cLa faction §e" . $factionName . " §ca été dissoute!");
        });
        
        $form->setTitle("§l§4Dissoudre la faction");
        $form->setContent("§c§lATTENTION!\n\n§7Êtes-vous sûr de vouloir dissoudre votre faction?\n§7Tous les membres seront expulsés et l'île sera supprimée!\n§c§lCette action est IRRÉVERSIBLE!");
        
        $form->addButton("§4Oui, dissoudre", 0, "textures/ui/trash_default");
        $form->addButton("§aAnnuler", 0, "textures/ui/cancel");
        
        $player->sendForm($form);
    }
}
