# phpMyAdmin SQL Dump
# version 2.5.5-pl1
# http://www.phpmyadmin.net
#
# Serveur: localhost
# Généré le : Mardi 14 Mars 2006 à 22:29
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
  `p_type` enum('conges','rtt','formation','mission','autre') NOT NULL default 'conges',
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
  `u_nb_jours_an` decimal(4,2) unsigned NOT NULL default '0.00',
  `u_solde_jours` decimal(4,2) NOT NULL default '0.00',
  `u_nb_rtt_an` decimal(4,2) unsigned NOT NULL default '0.00',
  `u_solde_rtt` decimal(4,2) NOT NULL default '0.00',
  `u_is_resp` enum('Y','N') NOT NULL default 'N',
  `u_resp_login` varchar(16) default NULL,
  `u_is_admin` enum('Y','N') NOT NULL default 'N',
  `u_passwd` varchar(64) NOT NULL default '',
  `u_quotite` int(3) default '100',
  `u_email` varchar(100) default NULL,
  PRIMARY KEY  (`u_login`),
  KEY `u_login` (`u_login`)
) TYPE=MyISAM;

#
# Contenu de la table `conges_users`
#

INSERT INTO `conges_users` VALUES ('admin', 'php_conges', 'admin', '0.00', '0.00', '0.00', '0.00', 'N', 'admin', 'Y', '636d61cf9094a62a81836f3737d9c0da', 100, NULL);
INSERT INTO `conges_users` VALUES ('conges', 'conges', 'responsable-virtuel', '0.00', '0.00', '0.00', '0.00', 'Y', NULL, 'Y', '3cdb69ff35635d9a3f6eccb6a5e269e6', 100, NULL);

# --------------------------------------------------------

#
# Structure de la table `session_appli_conges`
#

CREATE TABLE `session_appli_conges` (
  `user` varchar(255) binary NOT NULL default '',
  `session` varchar(255) NOT NULL default '',
  `connexion_start` bigint(20) NOT NULL default '0',
  `connexion_last` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`session`),
  KEY `user` (`user`),
  KEY `session` (`session`)
) TYPE=MyISAM;

#
# Contenu de la table `session_appli_conges`
#

