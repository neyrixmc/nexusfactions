<?php

declare(strict_types=1);

namespace NexusFactions\listener;

use NexusFactions\Main;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\player\Player;

class EventListener implements Listener {
    
    private Main $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onBlockBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        $position = $event->getBlock()->getPosition();
        
        $claimManager = $this->plugin->getClaimManager();
        $factionManager = $this->plugin->getFactionManager();
        
        $chunkFaction = $claimManager->getChunkFactionFromPosition($position);
        
        if ($chunkFaction === null) {
            return; // Pas de claim
        }
        
        $playerFaction = $factionManager->getPlayerFaction($player->getName());
        
        if ($playerFaction === null || strtolower($playerFaction->getName()) !== strtolower($chunkFaction)) {
            // Vérifier si allié
            if ($playerFaction !== null && $playerFaction->isAlly($chunkFaction)) {
                return; // Allié autorisé
            }
            
            $event->cancel();
            $player->sendMessage("§cVous ne pouvez pas casser de blocs ici! (Territoire de §e" . $chunkFaction . "§c)");
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event): void {
        $player = $event->getPlayer();
        $position = $event->getBlock()->getPosition();
        
        $claimManager = $this->plugin->getClaimManager();
        $factionManager = $this->plugin->getFactionManager();
        
        $chunkFaction = $claimManager->getChunkFactionFromPosition($position);
        
        if ($chunkFaction === null) {
            return;
        }
        
        $playerFaction = $factionManager->getPlayerFaction($player->getName());
        
        if ($playerFaction === null || strtolower($playerFaction->getName()) !== strtolower($chunkFaction)) {
            if ($playerFaction !== null && $playerFaction->isAlly($chunkFaction)) {
                return;
            }
            
            $event->cancel();
            $player->sendMessage("§cVous ne pouvez pas placer de blocs ici! (Territoire de §e" . $chunkFaction . "§c)");
        }
    }

    public function onPlayerInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        
        $claimManager = $this->plugin->getClaimManager();
        $factionManager = $this->plugin->getFactionManager();
        
        $chunkFaction = $claimManager->getChunkFactionFromPosition($block->getPosition());
        
        if ($chunkFaction === null) {
            return;
        }
        
        $playerFaction = $factionManager->getPlayerFaction($player->getName());
        
        if ($playerFaction === null || strtolower($playerFaction->getName()) !== strtolower($chunkFaction)) {
            if ($playerFaction !== null && $playerFaction->isAlly($chunkFaction)) {
                return;
            }
            
            // Bloquer l'interaction avec certains blocs
            $blockId = $block->getTypeId();
            $protectedBlocks = [
                54, // Coffre
                61, // Fourneau
                145, // Enclume
                // Ajoutez d'autres IDs de blocs à protéger
            ];
            
            if (in_array($blockId, $protectedBlocks)) {
                $event->cancel();
                $player->sendMessage("§cVous ne pouvez pas interagir ici! (Territoire de §e" . $chunkFaction . "§c)");
            }
        }
    }

    public function onEntityDamage(EntityDamageByEntityEvent $event): void {
        $entity = $event->getEntity();
        $damager = $event->getDamager();
        
        if (!$entity instanceof Player || !$damager instanceof Player) {
            return;
        }
        
        $factionManager = $this->plugin->getFactionManager();
        
        $entityFaction = $factionManager->getPlayerFaction($entity->getName());
        $damagerFaction = $factionManager->getPlayerFaction($damager->getName());
        
        if ($entityFaction === null || $damagerFaction === null) {
            return;
        }
        
        // Empêcher le PvP entre membres de la même faction
        if (strtolower($entityFaction->getName()) === strtolower($damagerFaction->getName())) {
            $event->cancel();
            $damager->sendMessage("§cVous ne pouvez pas attaquer un membre de votre faction!");
            return;
        }
        
        // Empêcher le PvP entre alliés
        if ($damagerFaction->isAlly($entityFaction->getName())) {
            $event->cancel();
            $damager->sendMessage("§cVous ne pouvez pas attaquer un allié!");
        }
    }
}
