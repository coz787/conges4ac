#
# Base de données: `db_conges`
#

# --------------------------------------------------------
#
# ATTENTION :  toutes les requetes doivent se terminer par un point virgule ";"

#
# Structure de la table `conges_artt`
#
# ac3 : la taille du champs login est amene a 48 varbinary(48)
# considerant que 32 (base dgac contient un exemple a 36c) 
# 128 trop important 


CREATE TABLE `conges_artt` (
  `a_login` varbinary(48) NOT NULL default '',
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
)  ;

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
  `e_login` varbinary(48) NOT NULL default '',
  `e_date_jour` date NOT NULL default '0000-00-00',
  `e_absence` enum('N','J','M','A') NOT NULL default 'N',
  `e_presence` enum('N','J','M','A') NOT NULL default 'N',
  `e_comment` varchar(255) default NULL,
  PRIMARY KEY  (`e_login`,`e_date_jour`)
)  ;

#
# Contenu de la table `conges_echange_rtt`
#


# --------------------------------------------------------

#
# Structure de la table `conges_edition_papier`
#

CREATE TABLE `conges_edition_papier` (
  `ep_id` int(11) NOT NULL auto_increment,
  `ep_login` varbinary(48)  NOT NULL default '',
  `ep_date` date NOT NULL default '0000-00-00',
  `ep_num_for_user` int(5) unsigned NOT NULL default '1',
  PRIMARY KEY  (`ep_id`)
)  ;

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
  `g_double_valid` enum('Y','N') default 'N',
  PRIMARY KEY  (`g_gid`)
)  ;

#
# Contenu de la table `conges_groupe`
#


# --------------------------------------------------------

#
# Structure de la table `conges_groupe_resp`
#

CREATE TABLE `conges_groupe_resp` (
  `gr_gid` int(11) NOT NULL default '0',
  `gr_login` varbinary(48) NOT NULL default '',
  PRIMARY KEY  (`gr_gid`,`gr_login`)
)  ;

#
# Contenu de la table `conges_groupe_resp`
#

# --------------------------------------------------------

#
# Structure de la table `conges_groupe_grd_resp`
#

CREATE TABLE `conges_groupe_grd_resp` (
  `ggr_gid` int(11) NOT NULL default '0',
  `ggr_login` varbinary(48) NOT NULL default '',
  PRIMARY KEY  (`ggr_gid`,`ggr_login`)
)  ;

#
# Contenu de la table `conges_groupe_resp`
#

# --------------------------------------------------------

#
# Structure de la table `conges_groupe_users`
#

CREATE TABLE `conges_groupe_users` (
  `gu_gid` int(11) NOT NULL default '0',
  `gu_login` varbinary(48) NOT NULL default '',
  `gu_nature` enum('membre', 'visiteur') NOT NULL default 'membre' COMMENT 'ac3: profil',
  PRIMARY KEY  (`gu_gid`,`gu_login`)
)  ;

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
)  ;

#
# Contenu de la table `conges_jours_feries`
#

# --------------------------------------------------------

#
# Structure de la table `conges_periode`
#

CREATE TABLE `conges_periode` (
  `p_login` varbinary(48) NOT NULL default '',
  `p_date_deb` date NOT NULL default '0000-00-00',
  `p_demi_jour_deb` enum('am','pm') NOT NULL default 'am',
  `p_date_fin` date default '0000-00-00',
  `p_demi_jour_fin` enum('am','pm') NOT NULL default 'pm',
  `p_nb_jours` decimal(5,2) NOT NULL default '0.00',
  `p_commentaire` varchar(50) default NULL,
  `p_type` int(2) UNSIGNED NOT NULL default '1',
  `p_etat` enum('ok', 'valid','demande','ajout','refus','annul','hp') NOT NULL default 'demande',
  `p_edition_id` int(11) default NULL,
  `p_motif_refus` varchar(110) default NULL,
  `p_date_demande` datetime default NULL,
  `p_date_traitement` datetime default NULL,
  `p_fermeture_id` int(5),
  `p_num` int(5) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`p_num`)
)  ;

#
# Contenu de la table `conges_periode`
#

# --------------------------------------------------------

#
# Structure de la table `conges_users`
#

CREATE TABLE `conges_users` (
  `u_login` varbinary(48)  NOT NULL default '',
  `u_nom` varchar(30) NOT NULL default '',
  `u_prenom` varchar(30) NOT NULL default '',
  `u_is_resp` enum('Y','N') NOT NULL default 'N',
  `u_resp_login` varbinary(48) default NULL,
  `u_is_admin` enum('Y','N') NOT NULL default 'N',
  `u_see_all` enum('Y','N') NOT NULL default 'N',
  `u_passwd` varchar(64) NOT NULL default '',
  `u_quotite` int(3) default '100',
  `u_email` varchar(100) default NULL,
  `u_has_int_calendar` enum('Y','N') DEFAULT 'Y' COMMENT 'integration conges calendar',
  `u_is_gest` enum('Y','N') NOT NULL default 'N' COMMENT 'gestionnaires',
  PRIMARY KEY  (`u_login`),
  KEY `u_login` (`u_login`)
)  ;

#
# Contenu de la table `conges_users`
#

INSERT INTO `conges_users` VALUES ('admin', 'php_conges', 'admin', 'N', 'admin', 'Y', 'N', 'c132e6998d305dad1c43bc3f897791fb', 100, NULL,'N','N');
INSERT INTO `conges_users` VALUES ('conges', 'conges', 'responsable-virtuel', 'Y', NULL, 'Y', 'Y', '3cdb69ff35635d9a3f6eccb6a5e269e6', 100, NULL,'N','N');

# --------------------------------------------------------

#
# Structure de la table `conges_config`
#

CREATE TABLE IF NOT EXISTS `conges_config` (
  `conf_nom` varchar(100) binary NOT NULL default '',
  `conf_valeur` varchar(200) binary default '',
  `conf_groupe` varchar(200) NOT NULL default '',
  `conf_type` varchar(200) NOT NULL default 'texte',
  `conf_commentaire` text NOT NULL,
  PRIMARY KEY  (`conf_nom`)
)  ;

#
# Contenu de la table `conges_config`
#

INSERT INTO `conges_config` VALUES ('installed_version', '0', '00_php_conges', 'texte', 'config_comment_installed_version');
INSERT INTO `conges_config` VALUES ('installed_state', 'v3rc16', '00_php_conges', 'texte', 'config_comment_installed_state');
# 
INSERT INTO `conges_config` VALUES ('url_conges_assistance', 'http://portail-dgac.aviation-civile.gouv.fr/portal/server.pt/', '00_php_conges', 	'texte', 'config_comment_url_conges_assistance'); 
INSERT INTO `conges_config` VALUES ('message_for_all', '', '00_php_conges',     'texte', 'config_comment_message_for_all'); 

INSERT INTO `conges_config` VALUES ('lang', 'fr', '00_php_conges', 'enum=fr/test', 'config_comment_lang');

INSERT INTO `conges_config` VALUES ('URL_ACCUEIL_CONGES', 'http://mon-serveur/mon-chemin/php_conges', '01_Serveur Web', 'texte', 'config_comment_URL_ACCUEIL_CONGES');

INSERT INTO `conges_config` VALUES ('img_login', 'img/dgac.png', '02_PAGE D\'AUTENTIFICATION', 'texte', 'config_comment_img_login');
INSERT INTO `conges_config` VALUES ('texte_img_login', 'Cliquez ici pour retourner à ...', '02_PAGE D\'AUTENTIFICATION', 'texte', 'config_comment_texte_img_login');
INSERT INTO `conges_config` VALUES ('lien_img_login', 'http://mon-serveur/mon-site/', '02_PAGE D\'AUTENTIFICATION', 'texte', 'config_comment_lien_img_login');
INSERT INTO `conges_config` VALUES ('texte_page_login', '---- CONGES AC3 beta (évaluation)', '02_PAGE D\'AUTENTIFICATION', 'texte', 'config_comment_texte_page_login');

INSERT INTO `conges_config` VALUES ('titre_application', 'CONGES4AC', '03_TITRES', 'texte', 'config_comment_titre_application');
INSERT INTO `conges_config` VALUES ('titre_calendrier', ' : Calendrier', '03_TITRES', 'texte', 'config_comment_titre_calendrier');
INSERT INTO `conges_config` VALUES ('titre_user_index', ' : Utilisateur', '03_TITRES', 'texte', 'config_comment_titre_user_index');
INSERT INTO `conges_config` VALUES ('titre_resp_index', ' : Page Responsable', '03_TITRES', 'texte', 'config_comment_titre_resp_index');
INSERT INTO `conges_config` VALUES ('titre_admin_index', ' : Administrateur', '03_TITRES', 'texte', 'config_comment_titre_admin_index');

INSERT INTO `conges_config` VALUES ('auth', 'TRUE', '04_Authentification', 'boolean', 'config_comment_auth');
INSERT INTO `conges_config` VALUES ('how_to_connect_user', 'dbconges', '04_Authentification', 'enum=dbconges/ldap/CAS', 'config_comment_how_to_connect_user');
INSERT INTO `conges_config` VALUES ('check_user_in_ldap', 'TRUE', '04_Authentification', 'boolean', 'config_comment_check_user_in_ldap');
INSERT INTO `conges_config` VALUES ('export_users_from_ldap', 'FALSE', '04_Authentification', 'boolean', 'config_comment_export_users_from_ldap');
INSERT INTO `conges_config` VALUES ('consult_calendrier_sans_auth', 'FALSE', '04_Authentification', 'boolean', 'config_comment_consult_calendrier_sans_auth');

INSERT INTO `conges_config` VALUES ('user_saisie_demande', 'TRUE', '05_Utilisateur', 'boolean', 'config_comment_user_saisie_demande');
INSERT INTO `conges_config` VALUES ('user_affiche_calendrier', 'TRUE', '05_Utilisateur', 'boolean', 'config_comment_user_affiche_calendrier');
INSERT INTO `conges_config` VALUES ('user_saisie_mission', 'TRUE', '05_Utilisateur', 'boolean', 'config_comment_user_saisie_mission');
INSERT INTO `conges_config` VALUES ('user_ch_passwd', 'TRUE', '05_Utilisateur', 'boolean', 'config_comment_user_ch_passwd');

INSERT INTO `conges_config` VALUES ('responsable_virtuel', 'FALSE', '06_Responsable', 'boolean', 'config_comment_responsable_virtuel');
INSERT INTO `conges_config` VALUES ('resp_affiche_calendrier', 'TRUE', '06_Responsable', 'boolean', 'config_comment_resp_affiche_calendrier');
INSERT INTO `conges_config` VALUES ('resp_saisie_mission', 'TRUE', '06_Responsable', 'boolean', 'config_comment_resp_saisie_mission');
INSERT INTO `conges_config` VALUES ('resp_ajoute_conges', 'FALSE', '06_Responsable', 'boolean', 'config_comment_resp_ajoute_conges');
INSERT INTO `conges_config` VALUES ('gestion_cas_absence_responsable', 'TRUE', '06_Responsable', 'boolean', 'config_comment_gestion_cas_absence_responsable');

INSERT INTO `conges_config` VALUES ('admin_see_all', 'FALSE', '07_Administrateur', 'boolean', 'config_comment_admin_see_all');
INSERT INTO `conges_config` VALUES ('admin_change_passwd', 'TRUE', '07_Administrateur', 'boolean', 'config_comment_admin_change_passwd');
INSERT INTO `conges_config` VALUES ('affiche_bouton_config_pour_admin', 'FALSE', '07_Administrateur', 'boolean', 'config_comment_affiche_bouton_config_pour_admin');
INSERT INTO `conges_config` VALUES ('affiche_bouton_config_absence_pour_admin', 'FALSE', '07_Administrateur', 'boolean', 'config_comment_affiche_bouton_config_absence_pour_admin');
INSERT INTO `conges_config` VALUES ('affiche_bouton_config_mail_pour_admin', 'FALSE', '07_Administrateur', 'boolean', 'config_comment_affiche_bouton_config_mail_pour_admin');

INSERT INTO `conges_config` VALUES ('mail_new_demande_alerte_resp', 'TRUE', '08_Mail', 'boolean', 'config_comment_mail_new_demande_alerte_resp');
INSERT INTO `conges_config` VALUES ('mail_valid_conges_alerte_user', 'TRUE', '08_Mail', 'boolean', 'config_comment_mail_valid_conges_alerte_user');
INSERT INTO `conges_config` VALUES ('mail_prem_valid_conges_alerte_user', 'TRUE', '08_Mail', 'boolean', 'config_comment_mail_prem_valid_conges_alerte_user');
INSERT INTO `conges_config` VALUES ('mail_refus_conges_alerte_user', 'TRUE', '08_Mail', 'boolean', 'config_comment_mail_refus_conges_alerte_user');
INSERT INTO `conges_config` VALUES ('mail_annul_conges_alerte_user', 'TRUE', '08_Mail', 'boolean', 'config_comment_mail_annul_conges_alerte_user');
INSERT INTO `conges_config` VALUES ('serveur_smtp', '', '08_Mail', 'texte', 'config_comment_serveur_smtp');
INSERT INTO `conges_config` VALUES ('where_to_find_user_email', 'dbconges', '08_Mail', 'enum=dbconges/ldap', 'config_comment_where_to_find_user_email');
INSERT INTO `conges_config` VALUES ('mail_echange_rtt_alerte_resp','TRUE','08_Mail','boolean','config_comment_mail_echange_rtt_alerte_resp');

INSERT INTO `conges_config` VALUES ('samedi_travail', 'FALSE', '09_jours ouvrables', 'boolean', 'config_comment_samedi_travail');
INSERT INTO `conges_config` VALUES ('dimanche_travail', 'FALSE', '09_jours ouvrables', 'boolean', 'config_comment_dimanche_travail');

INSERT INTO `conges_config` VALUES ('gestion_groupes', 'TRUE', '10_Gestion par groupes', 'boolean', 'config_comment_gestion_groupes');
INSERT INTO `conges_config` VALUES ('affiche_groupe_in_calendrier', 'FALSE', '10_Gestion par groupes', 'boolean', 'config_comment_affiche_groupe_in_calendrier');
INSERT INTO `conges_config` VALUES ('calendrier_select_all_groups', 'FALSE', '10_Gestion par groupes', 'boolean', 'config_comment_calendrier_select_all_groups');
INSERT INTO `conges_config` VALUES ('fermeture_par_groupe', 'FALSE', '10_Gestion par groupes', 'boolean', 'config_comment_fermeture_par_groupe');

INSERT INTO `conges_config` VALUES ('editions_papier', 'TRUE', '11_Editions papier', 'boolean', 'config_comment_editions_papier');
INSERT INTO `conges_config` VALUES ('texte_haut_edition_papier', '- php_conges : édition des congés -', '11_Editions papier', 'texte', 'config_comment_texte_haut_edition_papier');
INSERT INTO `conges_config` VALUES ('texte_bas_edition_papier', '- édité par php_conges -', '11_Editions papier', 'texte', 'config_comment_texte_bas_edition_papier');

INSERT INTO `conges_config` VALUES ('user_echange_rtt', 'TRUE', '12_Fonctionnement de l\'Etablissement', 'boolean', 'config_comment_user_echange_rtt');
INSERT INTO `conges_config` VALUES ('double_validation_conges', 'FALSE', '12_Fonctionnement de l\'Etablissement', 'boolean', 'config_comment_double_validation_conges');
INSERT INTO `conges_config` VALUES ('grand_resp_ajout_conges', 'FALSE', '12_Fonctionnement de l\'Etablissement', 'boolean', 'config_comment_grand_resp_ajout_conges');
INSERT INTO `conges_config` VALUES ('gestion_conges_exceptionnels', 'FALSE', '12_Fonctionnement de l\'Etablissement', 'boolean', 'config_comment_gestion_conges_exceptionnels');
INSERT INTO `conges_config` VALUES ('solde_toujours_positif', 'TRUE', '12_Fonctionnement de l\'Etablissement', 'boolean', 'config_comment_solde_toujours_positif');
INSERT INTO `conges_config` VALUES ('autovalidation_conges_par_responsable', 'FALSE', '12_Fonctionnement de l\'Etablissement', 'boolean', 'config_comment_autovalidation_conges_par_responsable');

INSERT INTO `conges_config` VALUES ('affiche_bouton_calcul_nb_jours_pris', 'TRUE', '13_Divers', 'boolean', 'config_comment_affiche_bouton_calcul_nb_jours_pris');
INSERT INTO `conges_config` VALUES ('rempli_auto_champ_nb_jours_pris', 'TRUE', '13_Divers', 'boolean', 'config_comment_rempli_auto_champ_nb_jours_pris');
INSERT INTO `conges_config` VALUES ('disable_saise_champ_nb_jours_pris', 'FALSE', '13_Divers', 'boolean', 'config_comment_disable_saise_champ_nb_jours_pris');
INSERT INTO `conges_config` VALUES ('interdit_saisie_periode_date_passee', 'FALSE', '13_Divers', 'boolean', 'config_comment_interdit_saisie_periode_date_passee');
INSERT INTO `conges_config` VALUES ('interdit_modif_demande', 'TRUE', '13_Divers', 'boolean', 'config_comment_interdit_modif_demande');
INSERT INTO `conges_config` VALUES ('duree_session', '1800', '13_Divers', 'texte', 'config_comment_duree_session');
INSERT INTO `conges_config` VALUES ('export_ical_vcal', 'TRUE', '13_Divers', 'boolean', 'config_comment_export_ical_vcal');
INSERT INTO `conges_config` VALUES ('affiche_date_traitement', 'FALSE', '13_Divers', 'boolean', 'config_comment_affiche_date_traitement');
INSERT INTO `conges_config` VALUES ('affiche_soldes_calendrier', 'TRUE', '13_Divers', 'boolean', 'config_comment_affiche_soldes_calendrier');
INSERT INTO `conges_config` VALUES ('affiche_demandes_dans_calendrier', 'FALSE', '13_Divers', 'boolean', 'config_comment_affiche_demandes_dans_calendrier');
INSERT INTO `conges_config` VALUES ('calcul_auto_jours_feries_france', 'FALSE', '13_Divers', 'boolean', 'config_comment_calcul_auto_jours_feries_france');

INSERT INTO `conges_config` VALUES ('stylesheet_file', 'style_dgac_2015.css', '14_Presentation', 'texte', 'config_comment_stylesheet_file');
INSERT INTO `conges_config` VALUES ('bgcolor', '#a3a8b8', '14_Presentation', 'texte', 'config_comment_bgcolor');
# '#b0c2f7' 
INSERT INTO `conges_config` VALUES ('bgimage', '', '14_Presentation', 'texte', 'config_comment_bgimage'); 
# 'img/watback.jpg' 
INSERT INTO `conges_config` VALUES ('light_grey_bgcolor', '#DEDEDE', '14_Presentation', 'texte', 'config_comment_light_grey_bgcolor');
INSERT INTO `conges_config` VALUES ('semaine_bgcolor', '#FFFFFF', '14_Presentation', 'hidden', 'config_comment_semaine_bgcolor');
INSERT INTO `conges_config` VALUES ('week_end_bgcolor', '#BFBFBF', '14_Presentation', 'hidden', 'config_comment_week_end_bgcolor');
INSERT INTO `conges_config` VALUES ('temps_partiel_bgcolor', '#00EF00', '14_Presentation', 'hidden', 'config_comment_temps_partiel_bgcolor');
INSERT INTO `conges_config` VALUES ('conges_bgcolor', '#DEDEDE', '14_Presentation', 'hidden', 'config_comment_conges_bgcolor');
INSERT INTO `conges_config` VALUES ('demande_conges_bgcolor', '#E7C4C4', '14_Presentation', 'hidden', 'config_comment_demande_conges_bgcolor');
INSERT INTO `conges_config` VALUES ('absence_autre_bgcolor', '#D3FFB6', '14_Presentation', 'hidden', 'config_comment_absence_autre_bgcolor');
INSERT INTO `conges_config` VALUES ('fermeture_bgcolor', '#7B9DE6', '14_Presentation', 'hidden', 'config_comment_fermeture_bgcolor');

INSERT INTO `conges_config` VALUES ('php_conges_fpdf_include_path', 'INCLUDE.EXTERNAL/', '15_Modules Externes', 'path', 'config_comment_php_conges_fpdf_include_path');
INSERT INTO `conges_config` VALUES ('php_conges_phpmailer_include_path', 'INCLUDE.EXTERNAL/', '15_Modules Externes', 'path', 'config_comment_php_conges_phpmailer_include_path');
INSERT INTO `conges_config` VALUES ('php_conges_cas_include_path', 'INCLUDE.EXTERNAL/', '15_Modules Externes', 'path', 'config_comment_php_conges_cas_include_path');
INSERT INTO `conges_config` VALUES ('php_conges_authldap_include_path', 'INCLUDE.EXTERNAL/', '15_Modules Externes', 'path', 'config_comment_php_conges_authldap_include_path');

INSERT INTO `conges_config` VALUES ('int_calendar', 'TRUE', '16_Int_calendar', 'boolean', 'config_comment_int_calendar');
INSERT INTO `conges_config` VALUES ('calendar_tag', 'sigp-c-ac3beta', '16_Int_calendar', 'texte', 'config_comment_calendar_tag');
INSERT INTO `conges_config` VALUES ('hdeb_periode_am', '090000', '16_Int_calendar', 'texte', 'config_comment_hdeb_periode_am');
INSERT INTO `conges_config` VALUES ('hfin_periode_am', '130000', '16_Int_calendar', 'texte', 'config_comment_hfin_periode_am');
INSERT INTO `conges_config` VALUES ('hdeb_periode_pm', '140000', '16_Int_calendar', 'texte', 'config_comment_hdeb_periode_pm');
INSERT INTO `conges_config` VALUES ('hfin_periode_pm', '180000', '16_Int_calendar', 'texte', 'config_comment_hfin_periode_pm');

INSERT INTO `conges_config` VALUES ("moisjour-debutannee","01-01","12_Fonctionnement de l\'Etablissement","texte","config_comment_moisjour-debutannee");
INSERT INTO `conges_config` VALUES ("moisjour-finannee","02-01","12_Fonctionnement de l\'Etablissement","texte","config_comment_moisjour-finannee");
INSERT INTO `conges_config` VALUES ("rtt_retroactivite","2","12_Fonctionnement de l\'Etablissement","texte","config_comment_rtt_retroactivite");
INSERT INTO `conges_config` VALUES ("rtt_anteactivite","2","12_Fonctionnement de l\'Etablissement","texte","config_comment_rtt_anteactivite");
INSERT INTO `conges_config` VALUES ("conge_retroactivite","1","12_Fonctionnement de l\'Etablissement","texte","config_comment_conge_retroactivite");
INSERT INTO `conges_config` VALUES ("date-forcee","","12_Fonctionnement de l\'Etablissement","texte","config_comment_date_forcee");
INSERT INTO `conges_config` VALUES ("inclus-selecteur-we",'FALSE', '12_Fonctionnement de l\'Etablissement', 'boolean', 'config_comment_inclus-selecteur-we');
INSERT INTO `conges_config` VALUES ("moisjour-debutete","05-01","12_Fonctionnement de l\'Etablissement","texte","config_comment_moisjour-debutete");
INSERT INTO `conges_config` VALUES ("moisjour-finete","10-31","12_Fonctionnement de l\'Etablissement","texte","config_comment_moisjour-finete");
INSERT INTO `conges_config` VALUES ("moisjour-finanneereel","12-31","12_Fonctionnement de l\'Etablissement","texte","config_comment_moisjour-finanneereel");
INSERT INTO `conges_config` VALUES ("jourshorsperiode",'TRUE', '12_Fonctionnement de l\'Etablissement', 'boolean', 'config_comment_jourshorsperiode');
INSERT INTO `conges_config` VALUES ("jourshorsperiodetype",'1', '12_Fonctionnement de l\'Etablissement', 'texte', 'config_comment_jourshorsperiodetype');
INSERT INTO `conges_config` VALUES ("jourshorsperiode-ouvert",'FALSE', '12_Fonctionnement de l\'Etablissement', 'boolean', 'config_comment_jourshorsperiode-ouvert');

# --------------------------------------------------------

#
# Structure de la table `conges_type_absence`
#

CREATE TABLE `conges_type_absence` (
  `ta_id` int(2) unsigned NOT NULL AUTO_INCREMENT,
  `ta_type` enum('conges','absences', 'conges_exceptionnels') NOT NULL default 'conges',
  `ta_libelle` varchar(20) NOT NULL default '',
  `ta_short_libelle` char(3) NOT NULL default '',
  PRIMARY KEY  (`ta_id`)
)  ;

#
# Contenu de la table `conges_type_absence`
###############################################

INSERT INTO `conges_type_absence` (`ta_id`, `ta_type`, `ta_libelle`, `ta_short_libelle`) VALUES
(1, 'conges', 'congés ', 'ca'),
(2, 'conges', 'rtt', 'rtt'),
(3, 'absences', 'formation', 'fo'),
(4, 'absences', 'mission', 'mi'),
(12, 'conges', 'conges exceptionnels', 'exc'),
(10, 'conges', 'garde enfant', 'enf'),
(11, 'conges', 'récupérations', 'rec'),
(13, 'conges', 'compte épargne temps', 'CET'),
(14, 'conges', 'temps partiel annual', 'TPA'),
(15, 'absences', 'autre ', 'ab');

# --------------------------------------------------------

#
# Structure de la table `conges_solde_user`
#

CREATE TABLE `conges_solde_user` (
  `su_login` varbinary(48) NOT NULL default '',
  `su_abs_id` int(2) unsigned NOT NULL default '0',
  `su_nb_an` decimal(4,2) NOT NULL default '0.00',
  `su_solde` decimal(4,2) NOT NULL default '0.00',
  PRIMARY KEY  (`su_login`,`su_abs_id`)
)  ;

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
       `se_solde` DECIMAL( 4, 2 ) NOT NULL,
       PRIMARY KEY  (`se_id_edition`,`se_id_absence`)
)  ;

# --------------------------------------------------------

#
# Structure de la table `conges_mail`
#

CREATE TABLE `conges_mail` (
`mail_nom` VARCHAR( 100 ) NOT NULL ,
`mail_subject` TEXT NULL ,
`mail_body` TEXT NULL ,
UNIQUE KEY `mail_nom` (`mail_nom`)
) ;

#
# Contenu de la table `conges_mail`
#

INSERT INTO `conges_mail` (`mail_nom`, `mail_subject`, `mail_body`) VALUES
('mail_new_demande', 'APPLI CONGES - Demande de congé', ' __SENDER_NAME__ a sollicité une demande de congé dans l''application de gestion des congés.\r\n\r\nMerci de consulter votre application php_conges : __URL_ACCUEIL_CONGES__/\r\n\r\n-------------------------------------------------------------------------------------------------------\r\nCeci est un message automatique.'),
('mail_valid_conges', 'APPLI CONGES - Congé accepté ', ' __SENDER_NAME__ a enregistré/accepté ce congé pour vous dans l''application de gestion des congés:  \r\ndu __DATE_DEBUT__ au __DATE_FIN__ :  __NB_OF_DAY__  jour(s) de type __TYPE_ABSENCE__\r\nMerci de consulter votre application php_conges : __URL_ACCUEIL_CONGES__/\r\n\r\n-------------------------------------------------------------------------------------------------------\r\nCeci est un message automatique.'),
('mail_refus_conges', 'APPLI CONGES - Congé refusé', ' __SENDER_NAME__ a refusé une demande de congé pour vous dans l''application de gestion des congés.\r\n\r\nMerci de consulter votre application php_conges : __URL_ACCUEIL_CONGES__/\r\n\r\n-------------------------------------------------------------------------------------------------------\r\nCeci est un message automatique.'),
('mail_annul_conges', 'APPLI CONGES - Congé annulé', ' __SENDER_NAME__ a annulé un de vos congés dans l''application de gestion des congés.\r\nMerci de consulter votre application php_conges : __URL_ACCUEIL_CONGES__/\r\n\r\n-------------------------------------------------------------------------------------------------------\r\nCeci est un message automatique.'),
('mail_prem_valid_conges', 'APPLI CONGES - Congé validé', ' __SENDER_NAME__ a validé (première validation) un congé pour vous dans l''application de gestion des congés.\r\nIl doit maintenant être accepté en deuxième validation.\r\n\r\nMerci de consulter votre application php_conges : __URL_ACCUEIL_CONGES__/\r\n\r\n-------------------------------------------------------------------------------------------------------\r\nCeci est un message automatique.'),
('mail_new_demande_resp_absent', 'APPLI CONGES - Demande de congés', ' __SENDER_NAME__ a sollicité une demande de congés dans l''application de gestion des congés.\r\n\r\nEn votre absence, cette demande a été transférée à votre (vos) propre(s) responsable(s)./\r\n\r\n-------------------------------------------------------------------------------------------------------\r\nCeci est un message automatique.'),
('mail_echange_rtt', 'APPLI CONGES - Echange de rtt', '__SENDER_NAME__ vous informe d''un échange artt...\r\npériode en artt : __DATE_FIN__  [__NB_OF_DAY__ j] au lieu de : \r\n__DATE_DEBUT__ : \r\n__COMMENT__ \r\n\r\nMerci de consulter votre application php_conges : __URL_ACCUEIL_CONGES__/\r\n\r\n-------------------------------------------------------------------------------------------------------\r\nCeci est un message automatique.');


#
# Structure de la table `conges_logs`
#

CREATE TABLE `conges_logs` (
   `log_id` integer not null auto_increment,
   `log_p_num` int(5) unsigned NOT NULL,
   `log_user_login_par` varbinary(48) NOT NULL default '',
   `log_user_login_pour` varbinary(48) default '',
   `log_etat` varchar(16) default '',
   `log_comment` TEXT NULL,
   `log_date` TIMESTAMP NOT NULL,
   PRIMARY KEY  (`log_id`)
)  ;


# --------------------------------------------------------

#
# Structure de la table `conges_jours_fermeture`
#

 CREATE TABLE `conges_jours_fermeture` (
	`jf_id` INT( 5 ) NOT NULL ,
	`jf_gid` INT( 11 ) NOT NULL DEFAULT '0',
	`jf_date` DATE NOT NULL,
   PRIMARY KEY  (`jf_id`,`jf_gid`,`jf_date`)
)  ;
