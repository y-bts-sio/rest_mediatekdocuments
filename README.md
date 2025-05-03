# rest_mediatekdocuments
Cette API est utilisée par l'application MediatekDocuments écrite en C# et récupérable dans le dépôt suivant :<br>
https://github.com/Gotlub/MediatekDocuments<br>

## Présentation
[Page de presentation du projet](https://www.nicolasfrere.fr/pages/mediatekDocuments.html)

Cette API, écrite en PHP, permet d'exécuter des requêtes SQL sur la BDD Mediatek86 créée avec le SGBDR MySQL.<br>
Elle est accessible via une authentification "basique" (avec login="admin", pwd="adminpwd").<br>
Sa vocation actuelle est de répondre aux demandes de l'applicaton MediatekDocuments.

Le projet est un fork de [rest_mediatekdocuments](https://github.com/CNED-SLAM/rest_mediatekdocuments), auquel des fonctionnalités ont été ajoutées.

## Fichiers de l'API
Cette API contient les fichiers suivants :<br>
- .htaccess : contient les règles de réécriture pour l'accès à mediatekdocuments.php avec les bons paramètres.<br>
- Mediatekdocuments.php : point d'entrée de l'api, contrôle l'accès sécurisé, récupère les paramètres puis, suivant le verbe utilisé (GET, POST, PUT, DELETE), appelle la méthode concernée dans la classe Controle.<br>
- Controle.php : suivant les demandes, appelle les méthodes concernées dans la classe AccesBDD puis retourne le résultat au format json.<br>
- AccesBDD.php : construit les requêtes SQL avec les collections de paramètres, demande à la classe ConnexionPDO de les exécuter et retourne le résultat.<br>
- ConnexionPDO.php : se connecte à la BDD, construit les requêtes en intégrant les paramètres, les exécute et retourne le résultat.
## Installation de l'API en local
Pour tester l'application MediatekDocuments en local, il faut aussi installer l'API. Voici le mode opératoire :<br>
- Installer les outils nécessaires (WampServer ou équivalent, Netbeans ou équivalent, Postman pour les tests).<br>
- Télécharger le zip du code de l'API et le dézipper dans le dossier www de wampserver (renommer le dossier en "rest_mediatekdocuments", donc en enlevant "_master").<br>
- Récupérer le script Mission4.sql en racine du projet puis, avec phpMyAdmin, créer la BDD mediatek86 et, dans cette BDD, exécuter le script pour remplir la BDD.<br>

## Gestion des utilisateurs
![img1](https://raw.githubusercontent.com/Gotlub/MediatekDocuments/main/images/ap3Utilisateurs.png)
<br>La base de données dispose de quatre utilisateurs de service différents, leur mot de passe est "password".
