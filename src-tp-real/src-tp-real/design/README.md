# Template de design — TP « données réelles »

Ce dossier fournit un **design standard et neutre** à intégrer dans les pages PHP
du projet. Le code PHP existant est volontairement minimal côté présentation :
votre travail consiste à lui appliquer ce design.

## Fichiers

| Fichier        | Rôle                                                        |
|----------------|-------------------------------------------------------------|
| `style.css`    | La feuille de style par défaut (couleurs, tableaux, menu…)   |
| `template.html`| Une page de démonstration de **tous** les composants         |
| `README.md`    | Ce guide                                                     |

## Trois thèmes au choix

Trois variantes de design sont fournies dans des sous-dossiers. Elles utilisent
**exactement les mêmes classes CSS** : pour changer de thème, il suffit de pointer
le `<link>` vers le `style.css` du thème voulu, sans modifier le HTML.

| Dossier            | Style                                                  |
|--------------------|--------------------------------------------------------|
| `theme-corporate/` | Clair, bleu, dense — profil « application de gestion » |
| `theme-dark/`      | Mode sombre, accents violets, coins arrondis           |
| `theme-minimal/`   | Épuré, typographique (serif), peu de bordures          |

Chaque sous-dossier contient son `style.css` et un `template.html` de démonstration
à ouvrir dans le navigateur pour comparer les rendus.

## Comment l'intégrer dans une page PHP

1. Dans le `<head>` de la page (ex. `pages/index.php`), copier la feuille de style.
depuis le dossier `design/`  :

   ```html
   <head>
       <meta charset="utf-8">
       <link rel="stylesheet" href="../assets/css/style.css">
   </head>
   ```

1. Reprendre le **menu** en haut de chaque page (bloc `<nav class="navbar">`).

2. Envelopper le contenu dans un conteneur centré :

   ```html
   <div class="container">
       ... contenu de la page ...
   </div>
   ```

## Personnalisation

Les couleurs et espacements sont centralisés en haut de `style.css` dans le bloc
`:root { --color-primary: ...; }`. Changez ces variables pour adapter le thème
sans toucher au reste du CSS.


