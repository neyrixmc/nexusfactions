<?php

declare(strict_types=1);

namespace NexusFactions\utils;

use NexusFactions\Main;
use pocketmine\utils\Config;

class MessageManager {
    
    private Main $plugin;
    private Config $messages;
    private string $prefix;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
        $this->messages = new Config($plugin->getDataFolder() . "messages.yml", Config::YAML);
        $this->prefix = $this->messages->get("prefix", "§9[§1NexusFactions§9]§r ");
    }

    public function getMessage(string $key, array $replacements = []): string {
        $message = $this->messages->get($key, $key);
        
        foreach ($replacements as $search => $replace) {
            $message = str_replace("{" . $search . "}", $replace, $message);
        }
        
        return $message;
    }

    public function sendMessage(mixed $player, string $key, array $replacements = []): void {
        if (method_exists($player, 'sendMessage')) {
            $player->sendMessage($this->prefix . $this->getMessage($key, $replacements));
        }
    }

    public function getPrefix(): string {
        return $this->prefix;
    }

    public function reload(): void {
        $this->messages->reload();
        $this->prefix = $this->messages->get("prefix", "§9[§1NexusFactions§9]§r ");
    }
}
