# À faire

Ce projet contient le code du TP « données réelles » : affichage des départements
et employés (base d'exemple MySQL `employees`), recherche, pagination, statistiques
et CRUD (ajout/modification de départements et d'employés).

> ⚠️ **Important** : le code contient **volontairement** des failles d'injection SQL
> (requêtes construites avec `sprintf`/concaténation). **Ne les corrigez pas ici** :
> leur correction fait l'objet d'un autre TP. Concentrez-vous sur les étapes ci-dessous.

**Travail en binôme.**

## 1. Installation

1. Créer un nouveau dépôt git (sur GitHub/GitLab) et y placer le contenu de ce dossier.
2. Prérequis : **MAMP** (ou équivalent PHP + MySQL).
3. Utiliser la base `employees`.
4. Lancer le serveur PHP et ouvrir le projet dans le navigateur.
5. Vérifier que la liste des départements s'affiche bien sur la page d'accueil.

## 2. Compréhension

Créer un document Markdown nommé `comprehension.md` (à la racine du dépôt) et y noter :

- les codes ou la logique que vous avez **maintenant compris** ;
- les codes ou la logique que vous **n'avez pas encore compris** ;
- les **fonctions utilisées que vous ne connaissez pas** (ex. `urlencode`, `htmlspecialchars`, l'opérateur `??`…).

## 3. Design

1. Ouvrir le dossier `design/` : il contient **3 thèmes** au choix
   (`theme-corporate/`, `theme-dark/`, `theme-minimal/`), plus un guide d'intégration
   dans `design/README.md`.
2. Ouvrir les `template.html` dans le navigateur pour comparer les rendus, puis
   **choisir un thème**.
3. Intégrer ce thème dans **toutes les pages** du dossier `pages/` :
   relier le `style.css` du thème et appliquer les classes décrites dans
   `design/README.md` (`.navbar`, `.table`, `.btn`, `.card`, `.alert`, `.form-*`…).

## Livrables

- Le lien du dépôt git.
- Le fichier `comprehension.md`.
- Les pages du projet intégrant le thème choisi.
