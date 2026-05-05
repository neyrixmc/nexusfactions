# NexusFactions

Plugin de factions complet pour PocketMine-MP 5.0+ avec système d'îles et interface Form UI.

## 🌟 Fonctionnalités

### Système de Factions
- ✅ Création et gestion de factions
- ✅ Système de grades (Leader, Officier, Membre)
- ✅ Invitations et gestion des membres
- ✅ Système d'alliances et d'ennemis
- ✅ Économie de faction (argent partagé)
- ✅ Système de power

### Système d'Îles
- ✅ Île automatique pour chaque faction
- ✅ Téléportation à l'île
- ✅ Définition du spawn de l'île
- ✅ Verrouillage/déverrouillage de l'île
- ✅ Gestion des membres autorisés

### Système de Claims
- ✅ Claim de chunks par faction
- ✅ Protection des territoires
- ✅ Gestion des permissions de construction
- ✅ Respect des alliances

### Interface Utilisateur
- ✅ Menus Form UI complets et intuitifs
- ✅ Navigation facile entre les menus
- ✅ Design moderne avec icônes
- ✅ Fallback en commandes si FormAPI absent

### Protection
- ✅ Protection contre le grief dans les claims
- ✅ Protection PvP (friendly fire désactivé)
- ✅ Protection des alliés
- ✅ Protection des coffres et blocs importants

## 📦 Installation

1. Téléchargez le plugin
2. Placez le dossier dans `plugins/`
3. **(Recommandé)** Installez [FormAPI](https://github.com/jojoe77777/FormAPI) pour les menus UI
4. Redémarrez le serveur
5. Configurez `config.yml` selon vos besoins

## 🎮 Commandes

### Commande Principale
- `/faction` ou `/f` - Ouvre le menu principal (avec FormAPI)

### Commandes Sans FormAPI
Si FormAPI n'est pas installé, utilisez ces commandes :
- `/f create <nom>` - Créer une faction
- `/f disband` - Dissoudre votre faction
- `/f invite <joueur>` - Inviter un joueur
- `/f join <faction>` - Rejoindre une faction
- `/f leave` - Quitter votre faction
- `/f kick <joueur>` - Expulser un membre
- `/f promote <joueur>` - Promouvoir en officier
- `/f demote <joueur>` - Rétrograder un officier
- `/f info [faction]` - Voir les infos d'une faction
- `/f list` - Liste des factions
- `/f ally <faction>` - Demander une alliance
- `/f enemy <faction>` - Déclarer ennemi
- `/f neutral <faction>` - Devenir neutre
- `/f claim` - Claim le chunk actuel
- `/f unclaim` - Unclaim le chunk actuel
- `/f island` - Téléporter à l'île
- `/f sethome` - Définir le spawn de l'île

## ⚙️ Configuration

### config.yml
```yaml
general:
  max-members: 20          # Membres max par faction
  creation-cost: 0         # Coût de création
  default-power: 10        # Power de départ
  max-power: 100          # Power maximum
  power-per-member: 5     # Power par membre

islands:
  default-size: 100       # Taille de l'île
  island-spacing: 500     # Distance entre îles
  world-name: "world"     # Monde des îles
  auto-create: true       # Création auto

claims:
  max-claims: 10          # Claims max
  claim-cost: 100         # Coût par claim
  power-per-claim: 5      # Power requis

pvp:
  faction-pvp: true       # PvP entre factions
  friendly-fire: false    # PvP dans faction
  ally-fire: false        # PvP entre alliés
```

## 🎨 Menus Form UI

Le plugin utilise FormAPI pour créer des menus interactifs :

1. **Menu Principal** - Hub central avec toutes les options
2. **Ma Faction** - Informations détaillées de votre faction
3. **Gestion** - Options de gestion (leader/officier)
4. **Liste des Factions** - Voir toutes les factions
5. **Île de Faction** - Gestion de l'île
6. **Alliances** - Gérer les relations diplomatiques

## 🔧 Dépendances

### Requises
- PocketMine-MP 5.0.0+
- PHP 8.1+

### Recommandées
- [FormAPI](https://github.com/jojoe77777/FormAPI) - Pour les menus UI

## 📝 Permissions

- `nexusfactions.command` - Utiliser les commandes de base (défaut: true)
- `nexusfactions.admin` - Permissions administrateur (défaut: op)

## 🐛 Support

Pour signaler un bug ou demander une fonctionnalité, créez une issue sur GitHub.

## 📄 Licence

Ce plugin est sous licence MIT.

## 🙏 Crédits

Inspiré par PiggyFactions et d'autres plugins de factions populaires.

---

**Développé avec ❤️ pour la communauté PocketMine-MP**
