***************************************
****  php_conges / conges4ac       ****
***************************************

SOMMAIRE :
----------
-> Descriptif
-> Fonctionnalités
-> Prérequis
-> Licence
-> Installation
-> configuration
-> Contacts

-------------------------------------------------------------------------------------
CONGES4AC : quesako : "conges for aviation civile" est la version utilisée depuis 2008
de php_conges et mis en oeuvre par :
DGAC/SG/SSIM 
1 rue George Pelletier-d-Oisy 
91200 Athis-Mons . 

Elle dérive de php_conges_1_4_2 . Cette version a bénéficié de sa mise en oeuvre sur 1200 agents, 
correction de bug, nouvelles règles métier,  une ergonomie "client-riche" (emploi de jquery).
Sa publication n'est qu'un juste retour des choses ; elle pourra servir de
" bac à idées " pour les dévelopoements comme LiberTempo et autre ... 

-> DESCRIPTIF :
-----------------
	Application web intéractive de gestion des congés du personnels d'un service .
	
	** PHP_conges se veut très paramètrable afin de fournir ou non diverses fonctionnalités aux utilisateurs. **
	** Depuis la version 1.1.1, php_conges est multi-langues. **
	
	Cette application se présente en 4 volets :
	
	1 - volet utilisateur :
	   Les utilisateurs ont accès au bilan et à l'historique de leurs congés. Ils ont également accès au calendrier
	   des congés de tous les personnels du service.
	   Ce calendrier donne une représentation graphique des absences des personnes (congés, artt, temps partiels).
	   Dans sa version par défaut, les utilisateurs peuvent également saisir leurs demandes de congés. Chaque demande 
	   est ensuite acceptée ou refusée par le responsable. L'utilisateur à alors également accès à l'historique de ces
	   demandes.
	   Cependant, une option de configuration permet de supprimer cette possibilité. Dans ce cas, c'est le responsable
	   qui saisi les congés des personnels.
	
	2 - volet responsable :
	   permet à un ou plusieurs responsables de gérer les demandes de congés des utilisateurs, de remettre les congés 
	   à jour en début d'année, etc ....
	   L'application peut également fonctionner en mode "responsable générique virtuel", ce qui permet d'avoir plusieurs 
	   responsables rééls (physiques) qui se connectent avec le même login pour gérer les congés des personnels.
	   Choisir ce mode de fonctionnement entraine que tous les utilisateurs de php_conges sont traités comme des utilisateurs
	   classiques (même s'ils sont enregistrés comme responsable dans la database !!!).
	   (le login du responsable virtuel est "conges" et le mot de passe par défaut est "conges" ... à changer au + vite)
	
	3 - volet administrateur : 
	   Ce volet ne sert qu'a administrer les utilisateurs ou les groupes dans la base de données. (ajout, suppression, modification, 
	   changement de mot de passe, ...). On peut également y trouver des outils pour géréer les jours fériés, gérer les types 
	   de congés, configurer l'application.
	
	Le principe de fonctionnement utilisateurs/responsables est simple :
	Chaque utilisateur est rattaché à un responsable (cf structure de la base de données). C'est ce responsable
	qui valide des demandes de congés de l'utilisateur, ou saisi les congés de ce dernier (en fonction des options de
	configuration choisies).
	
	4 - volet installation /configuration :
	   En principe, ce volet ne sert qu'une fois, lors de la mise en place de l'application. Il sert à installer (ou mettre 
	   à jour) l'application, et à la configurer selon le mode de fonctionnement voulu par l'établissement.


----------------------------------------------------------------------------------------
----------------------------------------------------------------------------------------

-> FONCTIONNALITES :
----------------------------
	PHP_conges propose de nombreuses fonctionalités. La plupart de celles ci sont paramétrables dans la configuration d u logiciel.
	Elles peuvent alors être activées ou désactivées, ou autorisées pour certains utilisateur plutôt que d'autres ....
	
	Les Prinbcipales fonctionnalités sont les suivantes :
	---------------------------------------------------------------------------------------
		- gestion des congés soit par le responsable seul , soit par système de demande par l'utilisateur / validation par le responsable.
		- gestion des absences pour mission, formation, etc ...(absences sans perte de congés) par l'utilisateur ou par le responsable.
		- gestion des congés et absences par demi-journées.
		- calcul automatique du nombre de jours pris (lors d'une demande de conges) 
		- possibilité de validation des demandes de congés par "double validation" (par le responsable direct + un responsable supérieur).
		- gestion des rtt et des temps partiels.
		- affichage de l'historiques des congés, de l'historiques des absences, et de l'historiques des demandes en cours .
		- paramètrege des types de conges/absence : Possibilité d'ajouter / supprimmer des types de congés ou d'absences qui seront gérées par l'application.
		- possibilité de fonctionner avec un responsable virtuel. (Cela permet d'avoir plusieurs responsables rééls identifiés avec le même login pour gérer les congés des personnels.)
		- possibilité de fonctionnement par groupes d'utilisateurs.
		- possibilité pour le responsable de refuser et d'annuler les absences d'un utilisateur.
		- gestion des utilisateurs (ajout, suprpession, modification, ...)
		- possibilité, pour de responsable, d'ajouter des congés par utilisateur, par groupe, ou pour tous (une seule saisie) .
			(avec possibilité d'indiquer si l'ajout est proportionnel à la quotité (temps partiel) des utilisateurs ou non)
		- possibilité d'authentifier les utilisateurs sur un annuaire de type LDAP ou Active-Directory.
		- possibilité d'exporter les utilisateurs depuis un annuaire LDAP
		- possibilité d'authentification des utilisateur sur un serveur CAS.
		- module de saisie les jours chômés/jours fériés (nécéssaire pour la fonction de calcul automatique)
		- envoi possible de mail d'avertissement (en cas de demande de congès par un utilisateur, de validation, de refus ou d'annulation par un responsable)
		- possibilité, pour les utilisateurs et/ou les responsables, d'afficher le calendrier des congés / absences de tous ou partie des utilisateurs..
		- possibilité pour les utilisateurs d'exporter leurs congés dans un fichier au format ics ou vcs (calendriers, agenda, et plannings électroniques)
		- Possibilité de prise en compte des samedis et dimanches ouvrés.
		- éditions papier : génération d'état imprimables ou au format PDF.
		- possibilité d'afficher dans les historiques et les éditions papier, les dates et heures de demande de congés par l'utilisateur, et de traitement de la demande par le responsable.
		- lisibilité du calendrier accrue : surlignage automatique de la ligne du calendrier/ sélection colorisé d'une ligne
		- gestion des sessions utilisateurs
		- application multi-langues
		- Module d'installation
		- Module de configuration
		- Possibilité d'avoir sur la page administrateur, un bouton d'accès au formulaire de config de l'appli.
		- possibilité que certains utilisateurs privilégiés puissent voir les conges de tout le monde dans le calendrier.
		- fonctionnalité de sauvegarde/restauration de la database (dans le module administrateur).
		- fourniture d'un jeu d'utilisateurs de test (pour prise en main du logiciel après installation)
		- mise en page basée sur feuille de style (css)
		- choix du positionnement du menu du responsable (en haut ou à gauche)


----------------------------------------------------------------------------------------
----------------------------------------------------------------------------------------

-> PREREQUIS :
-----------------
	serveur web + PHP + MySQL
	PHP_conges a été testé sous apache (v1.3.x et v2) et PHP (v4.2.x , 4.3.x et 5.x) et MySQL (v3.23.x et v4.x)
	(configuration de PHP  : "track_vars" à "enable" et "magic_quotes_gpc" à "on" )
	(Notez que depuis PHP 4.0.3, track_vars est toujours activée.)

    Conges4ac : est employé en env. Linux Sles 12 et/ou Ubuntu 14.04 : compatible php5.5,
    utilise mysqli (php7 ready) , compatible mysql5 et mariadb10 (utilusation innodb). 
    Code et données au format utf-8. 
    Conges4ac utilise jquery : les ressources suivantes doivent etre déposées à la racine
    du site : 
    /jquery/js/jquery-1.10.2.min.js 
	/jquery/js/jquery-ui-1.10.3.custom.min.js 
	/jquery/development-bundle/ui/i18n/jquery.ui.datepicker-fr.js
    ( Merci à John Resig pour jquery (travail formidable!) jeresig@gmail.com )
 
    Les outils ./actools sont écrits en Python2 (i love it!) .  

----------------------------------------------------------------------------------------
----------------------------------------------------------------------------------------

-> LICENCE :
-------------
	(voir fichier license.txt ou http://www.linux-france.org/article/these/gpl.html )
	/*************************************************************************************************
	PHP_CONGES : Gestion Interactive des Congés
	Copyright (C) 2005 (cedric chauvineau)
	
	Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les 
	termes de la Licence Publique Générale GNU publiée par la Free Software Foundation.
	Ce programme est distribué car potentiellement utile, mais SANS AUCUNE GARANTIE, 
	ni explicite ni implicite, y compris les garanties de commercialisation ou d'adaptation 
	dans un but spécifique. Reportez-vous à la Licence Publique Générale GNU pour plus de détails.
	Vous devez avoir reçu une copie de la Licence Publique Générale GNU en même temps 
	que ce programme ; si ce n'est pas le cas, écrivez à la Free Software Foundation, 
	Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, États-Unis.
	*************************************************************************************************
	This program is free software; you can redistribute it and/or modify it under the terms
	of the GNU General Public License as published by the Free Software Foundation; either 
	version 2 of the License, or any later version.
	This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
	without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
	See the GNU General Public License for more details.
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
	*************************************************************************************************/




----------------------------------------------------------------------------------------
----------------------------------------------------------------------------------------

-> INSTALLATION :
----------------
voir le fichier INSTALL.txt



----------------------------------------------------------------------------------------
----------------------------------------------------------------------------------------

-> CONFIGURATION :
-------------------
voir le fichier INSTALL.txt



----------------------------------------------------------------------------------------
----------------------------------------------------------------------------------------

-> CONTACT :
---------------
http://www.ced.univ-montp2.fr/php_conges/
mail : support.php_conges@univ-montp2.fr

Didier Pavet 
mail: dpa4aviation@gmail.com

Depot github : conges4ac . 
