# phpMyAdmin SQL Dump
# version 2.5.5-pl1
# http://www.phpmyadmin.net
#
# Serveur: localhost
# Généré le : Dimanche 04 Juin 2006 à 15:31
# Version du serveur: 3.23.58
# Version de PHP: 4.2.2
# 
# Base de données: `db_conges`
# 

# --------------------------------------------------------
#
# ATTENTION :  toutes les requetes doivent se terminer par un point virgule ";"

#
# Structure de la table `conges_artt`
#

CREATE TABLE `conges_artt` (
  `a_login` varchar(16) binary NOT NULL default '',
  `sem_imp_lu_am` varchar(10) default NULL,
  `sem_imp_lu_pm` varchar(10) default NULL,
  `sem_imp_ma_am` varchar(10) default NULL,
  `sem_imp_ma_pm` varchar(10) default NULL,
  `sem_imp_me_am` varchar(10) default NULL,
  `sem_imp_me_pm` varchar(10) default NULL,
  `sem_imp_je_am` varchar(10) default NULL,
  `sem_imp_je_pm` varchar(10) default NULL,
  `sem_imp_ve_am` varchar(10) default NULL,
  `sem_imp_ve_pm` varchar(10) default NULL,
  `sem_imp_sa_am` varchar(10) default NULL,
  `sem_imp_sa_pm` varchar(10) default NULL,
  `sem_imp_di_am` varchar(10) default NULL,
  `sem_imp_di_pm` varchar(10) default NULL,
  `sem_p_lu_am` varchar(10) default NULL,
  `sem_p_lu_pm` varchar(10) default NULL,
  `sem_p_ma_am` varchar(10) default NULL,
  `sem_p_ma_pm` varchar(10) default NULL,
  `sem_p_me_am` varchar(10) default NULL,
  `sem_p_me_pm` varchar(10) default NULL,
  `sem_p_je_am` varchar(10) default NULL,
  `sem_p_je_pm` varchar(10) default NULL,
  `sem_p_ve_am` varchar(10) default NULL,
  `sem_p_ve_pm` varchar(10) default NULL,
  `sem_p_sa_am` varchar(10) default NULL,
  `sem_p_sa_pm` varchar(10) default NULL,
  `sem_p_di_am` varchar(10) default NULL,
  `sem_p_di_pm` varchar(10) default NULL,
  `a_date_debut_grille` date NOT NULL default '0000-00-00',
  `a_date_fin_grille` date NOT NULL default '9999-12-31',
  PRIMARY KEY  (`a_login`,`a_date_fin_grille`)
) TYPE=MyISAM;

#
# Contenu de la table `conges_artt`
#

INSERT INTO `conges_artt` VALUES ('admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0000-00-00', '9999-12-31');
INSERT INTO `conges_artt` VALUES ('conges', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0000-00-00', '9999-12-31');

# --------------------------------------------------------

#
# Structure de la table `conges_echange_rtt`
#

CREATE TABLE `conges_echange_rtt` (
  `e_login` varchar(16) binary NOT NULL default '',
  `e_date_jour` date NOT NULL default '0000-00-00',
  `e_absence` enum('N','J','M','A') NOT NULL default 'N',
  `e_presence` enum('N','J','M','A') NOT NULL default 'N',
  `e_comment` varchar(255) default NULL,
  PRIMARY KEY  (`e_login`,`e_date_jour`)
) TYPE=MyISAM;

#
# Contenu de la table `conges_echange_rtt`
#


# --------------------------------------------------------

#
# Structure de la table `conges_edition_papier`
#

CREATE TABLE `conges_edition_papier` (
  `ep_id` int(11) NOT NULL auto_increment,
  `ep_login` varchar(16) binary NOT NULL default '',
  `ep_date` date NOT NULL default '0000-00-00',
  `ep_solde_jours` decimal(4,2) NOT NULL default '0.00',
  `ep_solde_rtt` decimal(4,2) NOT NULL default '0.00',
  `ep_num_for_user` int(5) unsigned NOT NULL default '1',
  PRIMARY KEY  (`ep_id`)
) TYPE=MyISAM;

#
# Contenu de la table `conges_edition_papier`
#

# --------------------------------------------------------

#
# Structure de la table `conges_groupe`
#

CREATE TABLE `conges_groupe` (
  `g_gid` int(11) NOT NULL auto_increment,
  `g_groupename` varchar(50) NOT NULL default '',
  `g_comment` varchar(250) default NULL,
  PRIMARY KEY  (`g_gid`)
) TYPE=MyISAM;

#
# Contenu de la table `conges_groupe`
#


# --------------------------------------------------------

#
# Structure de la table `conges_groupe_resp`
#

CREATE TABLE `conges_groupe_resp` (
  `gr_gid` int(11) NOT NULL default '0',
  `gr_login` varchar(16) binary NOT NULL default ''
) TYPE=MyISAM;

#
# Contenu de la table `conges_groupe_resp`
#

# --------------------------------------------------------

#
# Structure de la table `conges_groupe_users`
#

CREATE TABLE `conges_groupe_users` (
  `gu_gid` int(11) NOT NULL default '0',
  `gu_login` varchar(16) binary NOT NULL default ''
) TYPE=MyISAM;

#
# Contenu de la table `conges_groupe_users`
#

# --------------------------------------------------------

#
# Structure de la table `conges_jours_feries`
#

CREATE TABLE `conges_jours_feries` (
  `jf_date` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`jf_date`)
) TYPE=MyISAM;

#
# Contenu de la table `conges_jours_feries`
#

# --------------------------------------------------------

#
# Structure de la table `conges_periode`
#

CREATE TABLE `conges_periode` (
  `p_login` varchar(16) binary NOT NULL default '',
  `p_date_deb` date NOT NULL default '0000-00-00',
  `p_demi_jour_deb` enum('am','pm') NOT NULL default 'am',
  `p_date_fin` date NOT NULL default '0000-00-00',
  `p_demi_jour_fin` enum('am','pm') NOT NULL default 'pm',
  `p_nb_jours` decimal(5,2) unsigned NOT NULL default '0.00',
  `p_commentaire` varchar(50) default NULL,
  `p_type` int(2) UNSIGNED NOT NULL default '1',
  `p_etat` enum('ok','demande','ajout','refus','annul') NOT NULL default 'demande',
  `p_edition_id` int(11) default NULL,
  `p_motif_refus` varchar(110) default NULL,
  `p_num` int(5) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`p_num`)
) TYPE=MyISAM;

#
# Contenu de la table `conges_periode`
#

# --------------------------------------------------------

#
# Structure de la table `conges_users`
#

CREATE TABLE `conges_users` (
  `u_login` varchar(16) binary NOT NULL default '',
  `u_nom` varchar(30) NOT NULL default '',
  `u_prenom` varchar(30) NOT NULL default '',
  `u_is_resp` enum('Y','N') NOT NULL default 'N',
  `u_resp_login` varchar(16) default NULL,
  `u_is_admin` enum('Y','N') NOT NULL default 'N',
  `u_see_all` enum('Y','N') NOT NULL default 'N',
  `u_passwd` varchar(64) NOT NULL default '',
  `u_quotite` int(3) default '100',
  `u_email` varchar(100) default NULL,
  PRIMARY KEY  (`u_login`),
  KEY `u_login` (`u_login`)
) TYPE=MyISAM;

#
# Contenu de la table `conges_users`
#

INSERT INTO `conges_users` VALUES ('admin', 'php_conges', 'admin', 'N', 'admin', 'Y', 'N', '636d61cf9094a62a81836f3737d9c0da', 100, NULL);
INSERT INTO `conges_users` VALUES ('conges', 'conges', 'responsable-virtuel', 'Y', NULL, 'Y', 'Y', '3cdb69ff35635d9a3f6eccb6a5e269e6', 100, NULL);

# --------------------------------------------------------

#
# Structure de la table `conges_config`
#

CREATE TABLE IF NOT EXISTS `conges_config` (
  `conf_nom` varchar(100) binary NOT NULL default '',
  `conf_valeur` varchar(200) binary NOT NULL default '',
  `conf_groupe` varchar(200) NOT NULL default '',
  `conf_type` varchar(200) NOT NULL default 'texte',
  `conf_commentaire` text NOT NULL,
  PRIMARY KEY  (`conf_nom`)
) TYPE=MyISAM;

#
# Contenu de la table `conges_config`
#

INSERT INTO `conges_config` VALUES ('installed_version', '0', '00_version', 'texte', 'numéro de version installée');
INSERT INTO `conges_config` VALUES ('URL_ACCUEIL_CONGES', 'http://mon-serveur/mon-chemin/php_conges_v1.1', '01_Serveur Web', 'texte', '// URL DE BASE DE VOTRE INSTALLATION DE PHP_CONGES<br>\n//-------------------------------------------------<br>\n// URL de base de php_conges sur votre serveur (ce que vous devez taper pour obtenir la page d\'authentification.<br>\n// (PAS terminé par un / et sans le index.php à la fin)<br>\n// $URL_ACCUEIL_CONGES = "http://monserveurweb.mondomaine/php_conges"');
INSERT INTO `conges_config` VALUES ('img_login', 'img/logo_um2_v.gif', '02_PAGE D\'AUTENTIFICATION', 'texte', '// IMAGE DE LA PAGE DE LOGIN<br>\n//---------------------------<br>\n// image qui apparait en haut de la page d\'authentification de php_conges');
INSERT INTO `conges_config` VALUES ('texte_img_login', 'Cliquez ici pour retourner à ...', '02_PAGE D\'AUTENTIFICATION', 'texte', '// TEXTE DE L\'IMAGE<br>\n//-------------------<br>\n// texte de l\'image');
INSERT INTO `conges_config` VALUES ('lien_img_login', 'http://mon-serveur/mon-site/', '02_PAGE D\'AUTENTIFICATION', 'texte', '// LIEN DE L\'IMAGE<br>\n//------------------<br>\n// URL où renvoit l\'image de la page de login');
INSERT INTO `conges_config` VALUES ('titre_page_accueil', 'PHP_CONGES V1.1', '03_TITRES', 'texte', 'Titre de la page d\'accueil de php_conges');
INSERT INTO `conges_config` VALUES ('titre_calendrier', 'CONGES : Calendrier', '03_TITRES', 'texte', 'Titre de la page calendrier de php_conges');
INSERT INTO `conges_config` VALUES ('titre_user_index', 'CONGES : Utilisateur', '03_TITRES', 'texte', 'Titre des pages Utilisateur (sera suivi du login de l\'utilisateur)');
INSERT INTO `conges_config` VALUES ('titre_resp_index', 'CONGES : Page Responsable', '03_TITRES', 'texte', 'Titre des pages Responsable');
INSERT INTO `conges_config` VALUES ('titre_admin_index', 'CONGES : Administrateur', '03_TITRES', 'texte', 'Titre des pages Administrateur');
INSERT INTO `conges_config` VALUES ('auth', 'TRUE', '04_Authentification', 'boolean', '// Autentification :<br>\n//---------------------<br>\n// si = FALSE : pas d\'authetification au démarrage , il faut passer le parametre login à l\'appel de php_conges<br>\n// si = TRUE  : la page d\'autentification apparait à l\'appel de php_conges (TRUE est la valeur par defaut)');
INSERT INTO `conges_config` VALUES ('how_to_connect_user', 'dbconges', '04_Authentification', 'enum=dbconges/ldap/CAS', '// Comment vérifier le login et mot de passe des utilisateurs au démarrage :<br>\n//--------------------------------------------------------------------------<br>\n// si à "dbconges" : l\'authentification des user se fait dans la table users de la database db_conges<br>\n// si à "ldap"     : l\'authentification des user se fait dans un annuaire LDAP que l\'on va intérroger (cf config_ldap.php)<br>\n// si à "CAS"      : l\'authentification des user se fait sur un serveur CAS que l\'on va intérroger (cf config_CAS.php)<br>\n// attention : toute autre valeur que "dbconges" ou "ldap" ou "CAS" entrainera une érreur !!!');
INSERT INTO `conges_config` VALUES ('export_users_from_ldap', 'FALSE', '04_Authentification', 'boolean', '// Export des Users depuis LDAP :<br>\n//--------------------------------<br>\n// si = FALSE : les users sont créés "à la main" directement dans php_conges (FALSE est la valeur par defaut)<br>\n// si = TRUE  : les user sont importés du serveur LDAP (graceà une iste déroulante) (cf config_ldap.php)');
INSERT INTO `conges_config` VALUES ('user_saisie_demande', 'TRUE', '05_Utilisateur', 'boolean', '//  DEMANDES DE CONGES<br>\n//---------------------------------------<br>\n// si à FALSE : pas de saisie de demande par l\'utilisateur, pas de gestion des demandes par le responsable<br>\n// si à TRUE : saisie de demande par l\'utilisateur, et gestion des demandes par le responsable (TRUE est la valeur par defaut)');
INSERT INTO `conges_config` VALUES ('user_affiche_calendrier', 'TRUE', '05_Utilisateur', 'boolean', '//  AFFICHAGE DU BOUTON DE CALENDRIER POUR L\'UTILISATEUR<br>\n//--------------------------------------------------------------------------------------<br>\n// si à FALSE : les utilisateurs n\'ont pas la possibilité d\'afficher le calendrier des congés<br>\n// si à TRUE : les utilisateurs ont la possibilité d\'afficher le calendrier des congés (TRUE est la valeur par defaut)');
INSERT INTO `conges_config` VALUES ('user_saisie_mission', 'TRUE', '05_Utilisateur', 'boolean', '//  SAISIE  DES ABSENCES POUR MISSIONS, FORMATIONS, CONGRES, ETC .... PAR L\'UTILISATEUR<br>\n//--------------------------------------------------------------------------------------<br>\n// ( les absences de ce type n\'enlèvent pas de jours de congès ! )<br>\n// si à FALSE : pas de saisie par l\'utilisateur des absences pour mission, formation, congrés, etc ....<br>\n// si à TRUE : saisie par l\'utilisateur des absences pour mission, formation, congrés, etc .... (TRUE est la valeur par defaut)');
INSERT INTO `conges_config` VALUES ('user_ch_passwd', 'TRUE', '05_Utilisateur', 'boolean', '//  CHANGER SON PASSWORD<br>\n//---------------------------------------<br>\n// si à FALSE : l\'utilisateur ne peut pas changer son password<br>\n// si à TRUE : l\'utilisateur peut changer son password (TRUE est la valeur par defaut)');
INSERT INTO `conges_config` VALUES ('responsable_virtuel', 'FALSE', '06_Responsable', 'boolean', '//  RESPONSABLE GENERIQUE VIRTUEL OU NON<br>\n//-------------------------------------------<br>\n// si à FALSE : le responsable qui traite les congés des personnels est une personne reelle (utilisateur de php_conges) (FALSE est la valeur par defaut)<br>\n// si à TRUE : le responsable qui traite les congés des personnels est un utilisateur generique virtuel (login=conges)');
INSERT INTO `conges_config` VALUES ('resp_affiche_calendrier', 'TRUE', '06_Responsable', 'boolean', '//  AFFICHAGE DU BOUTON DE CALENDRIER POUR LE RESPONSABLE<br>\n//--------------------------------------------------------------------------------------<br>\n// si à FALSE : les responsables n\'ont pas la possibilité d\'afficher le calendrier des congés<br>\n// si à TRUE : les responsables ont la possibilité d\'afficher le calendrier des congés (TRUE est la valeur par defaut)');
INSERT INTO `conges_config` VALUES ('resp_saisie_mission', 'FALSE', '06_Responsable', 'boolean', '//  SAISIE  DES ABSENCES POUR MISSIONS, FORMATIONS, CONGRES, ETC .... PAR LE RESPONSABLE<br>\n//---------------------------------------------------------------------------------------<br>\n// ( les absences de ce type n\'enlèvent pas de jours de congès ! )<br>\n// si à FALSE : pas de saisie par le responsable des absences pour mission, formation, congrés, etc ....(FALSE est la valeur par defaut)<br>\n// si à TRUE : saisie par le responsable des absences pour mission, formation, congrés, etc ....');
INSERT INTO `conges_config` VALUES ('resp_vertical_menu', 'TRUE', '06_Responsable', 'boolean', '//  CONFIG  DU MENU DU RESPONSABLE<br>\n//---------------------------------------<br>\n// si à TRUE : dans la fenetre responsable, le menu est vertical (à gauche) (TRUE est la valeur par defaut)<br>\n// si à FALSE : dans la fenetre responsable, le menu est horizontal (en haut)');
INSERT INTO `conges_config` VALUES ('admin_see_all', 'FALSE', '07_Administrateur', 'boolean', '//  CONFIG  DU MODE ADMINISTRATEUR<br>\n//---------------------------------------<br>\n// si à FALSE : l\'admin ne gere que les users dont il est responsable (FALSE est la valeur par defaut)<br>\n// si à TRUE : l\'admin gere tous les users');
INSERT INTO `conges_config` VALUES ('admin_change_passwd', 'TRUE', '07_Administrateur', 'boolean', '//  CHANGER LE PASSWORD D\'UN UTILSATEUR<br>\n//-----------------------------------------<br>\n// si à FALSE : l\'administrateur ne peut pas changer le password des utilisateurs<br>\n// si à TRUE : l\'administrateur peut changer le password des utilisateurs (TRUE est la valeur par defaut)');
INSERT INTO `conges_config` VALUES ('mail_new_demande_alerte_resp', 'FALSE', '08_Mail', 'boolean', '// ENVOI DE MAIL AU RESPONSABLE POUR UNE NOUVELLE DEMANDE :<br>\n//----------------------------------------------------------<br>\n// si à FALSE : le responsable ne reçoit pas de mail lors d\'une nouvelle demande de congés par un utilisateur (FALSE est la valeur par defaut)<br>\n// si à TRUE : le responsable reçoit un mail d\'avertissement à chaque nouvelle demande de congés d\'un utilisateur<br>\n// (ATTENTION : vous pouvez personaliser le sujet et le corps du mail d\'alerte plus bas dans ce fichier)');
INSERT INTO `conges_config` VALUES ('mail_valid_conges_alerte_user', 'FALSE', '08_Mail', 'boolean', '// ENVOI DE MAIL AU USER POUR UN NOUVEAU CONGES SAISI OU VALIDE :<br>\n//----------------------------------------------------------------<br>\n// si à FALSE : le user ne reçoit pas de mail lorsque le responsable lui saisi ou accepte un nouveau conges (FALSE est la valeur par defaut)<br>\n// si à TRUE : le user reçoit un mail d\'avertissement à chaque que le responsable saisi un nouveau congés ou accepte une demande pour lui<br>\n// (ATTENTION : vous pouvez personaliser le sujet et le corps du mail d\'alerte plus bas dans ce fichier)');
INSERT INTO `conges_config` VALUES ('mail_refus_conges_alerte_user', 'FALSE', '08_Mail', 'boolean', '// ENVOI DE MAIL AU USER POUR LE REFUS D\'UNE DEMANDE DE CONGES :<br>\n//----------------------------------------------------------------<br>\n// si à FALSE : le user ne reçoit pas de mail lorsque le responsable refuse une de ses demandes de conges (FALSE est la valeur par defaut)<br>\n// si à TRUE : le user reçoit un mail d\'avertissement à chaque que le responsable refuse une de ses demandes de congés <br>\n// (ATTENTION : vous pouvez personaliser le sujet et le corps du mail d\'alerte plus bas dans ce fichier)');
INSERT INTO `conges_config` VALUES ('mail_annul_conges_alerte_user', 'FALSE', '08_Mail', 'boolean', '// ENVOI DE MAIL AU USER POUR L\'ANNULATION PAR LE RESP D\'UN CONGES DEJA VALIDE :<br>\n//---------------------------------------------------------------------------------<br>\n// si à FALSE : le user ne reçoit pas de mail lorsque le responsable lui annule un conges (FALSE est la valeur par defaut)<br>\n// si à TRUE : le user reçoit un mail d\'avertissement à chaque que le responsable annule un de ses congés<br>\n// (ATTENTION : vous pouvez personaliser le sujet et le corps du mail d\'alerte plus bas dans ce fichier)');
INSERT INTO `conges_config` VALUES ('serveur_smtp', '', '08_Mail', 'texte', '//  SERVEUR SMTP A UTILSER<br>\n//---------------------------------------<br>\n// adresse ip  ou  nom du serveur smpt à utiliser pour envoyer les mails<br>\n// Si vous ne maîtriser pas le serveur SMTP ou si, à l\'utilisation, vous avez une érreur de connexion au serveur, laissez cette variable vide ("")');
INSERT INTO `conges_config` VALUES ('where_to_find_user_email', 'dbconges', '08_Mail', 'enum=dbconges/ldap', '//  OU TROUVER LES ADRESSES MAIL DES UTILISATEURS<br>\n//-------------------------------------------------<br>\n// plusieurs possibilité pour retrouver les adresses mail des users :<br>\n// si à "dbconges" : le mail des user se trouve dans la table users de la database db_conges<br>\n// si à "ldap"     : le mail des user se trouve dans un annuaire LDAP que l\'on va intérroger (cf fichier config_ldap.php)<br>\n// ATTENTION : toute autre valeur que "dbconges" ou "ldap" entrainera une érreur !!!');
INSERT INTO `conges_config` VALUES ('samedi_travail', 'FALSE', '09_jours ouvrables', 'boolean', '//  GESTION DES SAMEDI COMME TRAVAILLES OU NON<br>\n//--------------------------------------------------------------------------------------<br>\n// on définit ici si les samedis peuvent être travaillés ou pas.<br>\n// si à TRUE : le jour considéré est travaillé ....<br>\n// si à FALSE : le jour considéré n\'est pas travaillé (weeekend).... (FALSE est la valeur par defaut)');
INSERT INTO `conges_config` VALUES ('dimanche_travail', 'FALSE', '09_jours ouvrables', 'boolean', '//  GESTION DES DIMANCHES COMME TRAVAILLES OU NON<br>\n//--------------------------------------------------------------------------------------<br>\n// on définit ici si les dimanches peuvent être travaillés ou pas.<br>\n// si à TRUE : le jour considéré est travaillé ....<br>\n// si à FALSE : le jour considéré n\'est pas travaillé (weeekend).... (FALSE est la valeur par defaut)');
INSERT INTO `conges_config` VALUES ('gestion_groupes', 'FALSE', '10_Gestion par groupes', 'boolean', '//  GESTION DES GROUPES D\'UTILISATEURS<br>\n//--------------------------------------<br>\n// on définit ici si l\'on veut pouvoir gèrer les utilisateurs par groupe ou pas.<br>\n// si à TRUE : les groupes d\'utilisateurs sont gèrés dans l\'application ....<br>\n// si à FALSE : les groupes d\'utilisateurs ne sont PAS gèrés dans l\'application .... (FALSE est la valeur par defaut)');
INSERT INTO `conges_config` VALUES ('affiche_groupe_in_calendrier', 'FALSE', '10_Gestion par groupes', 'boolean', '//  AFFICHAGE DU CALENDRIER : tous les utilisateurs ou les utilisateurs d\'un groupe seulement<br>\n//--------------------------------------------------------------------------------------------<br>\n// si à FALSE : tous les personnes apparaissent sur le calendrier des congès (FALSE est la valeur par defaut)<br>\n// si à TRUE : seuls les personnes du même  groupe que l\'utilisateur apparaissent sur le calendrier des congés');
INSERT INTO `conges_config` VALUES ('editions_papier', 'TRUE', '11_Editions papier', 'boolean', '//  EDITIONS PAPIER<br>\n//--------------------------------------<br>\n// on définit ici si le responsable peut générer des états papier des congés d\'un user.<br>\n// si à TRUE : les éditions papier sont disponibles ....(TRUE est la valeur par defaut)<br>\n// si à FALSE : les éditions papier ne sont pas disponibles dans l\'application ....');
INSERT INTO `conges_config` VALUES ('texte_haut_edition_papier', '- php_conges : édition des congés -', '11_Editions papier', 'texte', '//  Texte en haut des EDITIONS PAPIER<br>\n//--------------------------------------<br>\n// on définit ici le texte événtuel qui figurera en haut de page des états papier des congés d\'un user.');
INSERT INTO `conges_config` VALUES ('texte_bas_edition_papier', '- édité par php_conges -', '11_Editions papier', 'texte', '//  Texte au bas des EDITIONS PAPIER<br>\n//--------------------------------------<br>\n// on définit ici le texte événtuel qui figurera en bas de page des états papier des congés d\'un user.');
#INSERT INTO `conges_config` VALUES ('rtt_comme_conges', 'TRUE', '12_Fonctionnement de l\'Etablissement', 'boolean', '//  GESTION DES RTT COMME DES CONGES (épargne temps)<br>\n//---------------------------------------------------------------------------------------<br>\n// on gére les rtt comme des congés (demande, validation par le responsable , etc ...)<br>\n// si à FALSE : pas de gestion jours rtt comme des jours de congés<br>\n// si à TRUE : gestion jours rtt comme des jours de congés (TRUE est la valeur par defaut)');
INSERT INTO `conges_config` VALUES ('user_echange_rtt', 'FALSE', '12_Fonctionnement de l\'Etablissement', 'boolean', '//  ECHANGE RTT OU TEMPS PARTIEL AUTORISé POUR LES UTILISATEURS<br>\n//---------------------------------------------------------------------------------------<br>\n// on autorise ou non l\'utilisateur à inverser ponctuellement une jour travaillé et un jour d\'absence (de rtt ou temps partiel)<br>\n// si à FALSE : pas d\'échange autorisé pour l\'utilisateur (FALSE est la valeur par defaut)<br>\n// si à TRUE : échange autorisé pour l\'utilisateur');
INSERT INTO `conges_config` VALUES ('affiche_bouton_calcul_nb_jours_pris', 'TRUE', '13_Divers', 'boolean', '//  BOUTON DE CALCUL DU NB DE JOURS PRIS<br>\n//--------------------------------------------------------------------------------------<br>\n// si à FALSE : on n\'affiche pas le bouton du calcul du nb de jours pris lors de la saisie d\'une nouvelle abscence<br>\n// si à TRUE : affiche le bouton du calcul du nb de jours pris lors de la saisie d\'une nouvelle abscence (TRUE est la valeur par defaut)<br>\n// ATTENTION : si est à TRUE : les jours chaumés doivent être saisis (voir le module d\'administration)');
INSERT INTO `conges_config` VALUES ('rempli_auto_champ_nb_jours_pris', 'TRUE', '13_Divers', 'boolean', '//  REMPLISSAGE AUTOMATIQUE DU CHAMP LORS DE L\'APPEL AU CALCUL DU NB DE JOURS PRIS<br>\n//--------------------------------------------------------------------------------------<br>\n// si à FALSE : l\'appel au bouton de calcul du nb de jours pris ne rempli pas automatiquement le champ du formulaire (saisi à la main)<br>\n// si à TRUE : l\'appel au bouton de calcul du nb de jours pris rempli automatiquement le champ du formulaire (TRUE est la valeur par defaut)');
INSERT INTO `conges_config` VALUES ('duree_session', '1800', '13_Divers', 'texte', '// Durée max d\'inactivité d\'une session avant expiration (en secondes)');
INSERT INTO `conges_config` VALUES ('verif_droits', 'FALSE', '13_Divers', 'boolean', '// Vérification des Droits d\'accés :<br>\n//------------------------------------<br>\n// mettre a TRUE Pour gérer les droits d\'accés aux pages (est a FALSE par defaut)<br>\n/* parametre propre à certains environnements d\'install seulement !!!...... */');
INSERT INTO `conges_config` VALUES ('stylesheet_file', 'style_basic.css', '14_Présentation', 'texte', '// FEUILLE DE STYLE<br>\n//--------------------------<br>\n// nom du fichier de la feuille de style à utiliser (avec chemin relatif depuis la racine de php_conges)');
INSERT INTO `conges_config` VALUES ('bgcolor', '#b0c2f7', '14_Présentation', 'texte', '// couleur de fond des pages');
INSERT INTO `conges_config` VALUES ('bgimage', 'img/watback.jpg', '14_Présentation', 'texte', '// image de fond des pages (PAS de / au début !!)');
INSERT INTO `conges_config` VALUES ('light_grey_bgcolor', '#DEDEDE', '14_Présentation', 'texte', '// couleurs diverses (gris clair)');
INSERT INTO `conges_config` VALUES ('affiche_bouton_config_pour_admin', 'FALSE', '07_Administrateur', 'boolean', '// ACCES A LA CONFIG DE L\'APPLI POUR LES ADMINS<br>\n//-------------------------------------------------------<br>\n// si à FALSE : le bouton d\'acces à la configuration de php_conges n\'apparait pas sur la page administrateur (FALSE est la valeur par defaut)<br>\n// si à TRUE : le bouton d\'acces à la configuration de php_conges apparait sur la page administrateur ');
INSERT INTO `conges_config` VALUES ('affiche_bouton_config_absence_pour_admin', 'FALSE', '07_Administrateur', 'boolean', '// ACCES A LA CONFIG DES TYPES D\'ABSENCES DE L\'APPLI POUR LES ADMINS<br>\n//---------------------------------------------------------------------<br>\n// si à FALSE : le bouton d\'acces à la configuration des types d\'absences gérées par php_conges n\'apparait pas sur la page administrateur (FALSE est la valeur par defaut)<br>\n// si à TRUE : le bouton d\'acces à la configuration des types d\'absences gérées par php_conges apparait sur la page administrateur ');
INSERT INTO `conges_config` VALUES ('php_conges_fpdf_include_path', 'INCLUDE.EXTERNAL/fpdf/', '15_Modules Externes', 'path', '// CHEMIN VERS LE REPERTOIRE DE fpdf<br>\n//-------------------------------------------------------<br>\n// On défini ici le chemin pour accéder au répertoire de la librairie PHP \"fpdf\".<br>\n// Le chemin doit etre relatif depuis la racine de l\'application php_conges.');
INSERT INTO `conges_config` VALUES ('php_conges_phpmailer_include_path', 'INCLUDE.EXTERNAL/', '15_Modules Externes', 'path', '// CHEMIN VERS LE REPERTOIRE DE phpmailer<br>\n//-------------------------------------------------------<br>\n// On défini ici le chemin pour accéder au répertoire de la librairie PHP \"phpmailer\".<br>\n// Le chemin doit etre relatif depuis la racine de l\'application php_conges.');
INSERT INTO `conges_config` VALUES ('php_conges_cas_include_path', 'INCLUDE.EXTERNAL/', '15_Modules Externes', 'path', '// CHEMIN VERS LE REPERTOIRE DE cas<br>\n//-------------------------------------------------------<br>\n// On défini ici le chemin pour accéder au répertoire de la librairie PHP \"CAS\".<br>\n// Le chemin doit etre relatif depuis la racine de l\'application php_conges.');
INSERT INTO `conges_config` VALUES ('php_conges_authldap_include_path', 'INCLUDE.EXTERNAL/', '15_Modules Externes', 'path', '// CHEMIN VERS LE fichier authLDAP.php<br>\n//-------------------------------------------------------<br>\n// On défini ici le chemin pour accéder au répertoire de la librairie PHP \"authLDAP.php\".<br>\n// Le chemin doit etre relatif depuis la racine de l\'application php_conges.');

# --------------------------------------------------------

#
# Structure de la table `conges_type_absence`
#

CREATE TABLE `conges_type_absence` (
  `ta_id` int(2) unsigned NOT NULL AUTO_INCREMENT,
  `ta_type` enum('conges','absence') NOT NULL default 'conges',
  `ta_libelle` varchar(20) NOT NULL default '',
  `ta_short_libelle` char(3) NOT NULL default '',
  PRIMARY KEY  (`ta_id`)
) TYPE=MyISAM;

#
# Contenu de la table `conges_type_absence`
###############################################

INSERT INTO `conges_type_absence` VALUES (1, 'conges', 'congés payés', 'cp');
INSERT INTO `conges_type_absence` VALUES (2, 'conges', 'rtt', 'rtt');
INSERT INTO `conges_type_absence` VALUES (3, 'absence', 'formation', 'fo');
INSERT INTO `conges_type_absence` VALUES (4, 'absence', 'misson', 'mi');
INSERT INTO `conges_type_absence` VALUES (5, 'absence', 'autre', 'ab');

 
# --------------------------------------------------------

#
# Structure de la table `conges_solde_user`
#

CREATE TABLE `conges_solde_user` (
  `su_login` varchar(16) NOT NULL default '',
  `su_abs_id` int(2) unsigned NOT NULL default '0',
  `su_nb_an` decimal(4,2) NOT NULL default '0.00',
  `su_solde` decimal(4,2) NOT NULL default '0.00'
) TYPE=MyISAM;

#
# Contenu de la table `conges_solde_user`
#

# --------------------------------------------------------

#
# Structure de la table `conges_solde_edition`
#

CREATE TABLE `conges_solde_edition` (
`se_id_edition` INT( 11 ) NOT NULL ,
`se_id_absence` INT( 2 ) NOT NULL ,
`se_solde` DECIMAL( 4, 2 ) NOT NULL
); 


