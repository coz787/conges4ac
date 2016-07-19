# phpMyAdmin SQL Dump
# version 2.5.5-pl1
# http://www.phpmyadmin.net
#
# Serveur: localhost
# Généré le : Dimanche 20 Novembre 2005 à 20:57
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
  `a_login` varchar(16) NOT NULL default '',
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

INSERT INTO `conges_artt` (`a_login`, `sem_imp_lu_am`, `sem_imp_lu_pm`, `sem_imp_ma_am`, `sem_imp_ma_pm`, `sem_imp_me_am`, `sem_imp_me_pm`, `sem_imp_je_am`, `sem_imp_je_pm`, `sem_imp_ve_am`, `sem_imp_ve_pm`, `sem_imp_sa_am`, `sem_imp_sa_pm`, `sem_imp_di_am`, `sem_imp_di_pm`, `sem_p_lu_am`, `sem_p_lu_pm`, `sem_p_ma_am`, `sem_p_ma_pm`, `sem_p_me_am`, `sem_p_me_pm`, `sem_p_je_am`, `sem_p_je_pm`, `sem_p_ve_am`, `sem_p_ve_pm`, `sem_p_sa_am`, `sem_p_sa_pm`, `sem_p_di_am`, `sem_p_di_pm`) VALUES ('admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

# --------------------------------------------------------

#
# Structure de la table `conges_echange_rtt`
#

CREATE TABLE `conges_echange_rtt` (
  `e_login` varchar(16) NOT NULL default '',
  `e_date_jour` date NOT NULL default '0000-00-00',
  `e_absence` enum('N','J','M','A') NOT NULL default 'N',
  `e_presence` enum('N','J','M','A') NOT NULL default 'N',
  `e_comment` varchar(255) default NULL,
  PRIMARY KEY  (`e_login`,`e_date_jour`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Structure de la table `conges_groupe`
#

CREATE TABLE `conges_groupe` (
  `g_groupename` varchar(50) NOT NULL default '',
  `g_comment` varchar(250) default NULL,
  PRIMARY KEY  (`g_groupename`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Structure de la table `conges_groupe_resp`
#

CREATE TABLE `conges_groupe_resp` (
  `gr_groupename` varchar(50) NOT NULL default '',
  `gr_login` varchar(16) NOT NULL default ''
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Structure de la table `conges_groupe_users`
#

CREATE TABLE `conges_groupe_users` (
  `gu_groupename` varchar(50) NOT NULL default '',
  `gu_login` varchar(16) NOT NULL default ''
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Structure de la table `conges_periode`
#

CREATE TABLE `conges_periode` (
  `p_login` varchar(16) NOT NULL default '',
  `p_date_deb` date NOT NULL default '0000-00-00',
  `p_demi_jour_deb` enum('am','pm') NOT NULL default 'am',
  `p_date_fin` date NOT NULL default '0000-00-00',
  `p_demi_jour_fin` enum('am','pm') NOT NULL default 'pm',
  `p_nb_jours` decimal(4,2) unsigned NOT NULL default '0.00',
  `p_commentaire` varchar(50) default NULL,
  `p_etat` enum('pris','demande','refusé','annulé','formation','mission','autre','absence-annulée','rtt_annulée','rtt_prise','rtt_refusée','demande_rtt') NOT NULL default 'demande',
  `p_num` int(5) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`p_num`)
) TYPE=MyISAM PACK_KEYS=0;

# --------------------------------------------------------

#
# Structure de la table `conges_users`
#

CREATE TABLE `conges_users` (
  `u_login` varchar(16) NOT NULL default '',
  `u_nom` varchar(30) NOT NULL default '',
  `u_prenom` varchar(30) NOT NULL default '',
  `u_nb_jours_an` decimal(4,2) unsigned NOT NULL default '0.00',
  `u_solde_jours` decimal(4,2) NOT NULL default '0.00',
  `u_nb_rtt_an` decimal(4,2) unsigned NOT NULL default '0.00',
  `u_solde_rtt` decimal(4,2) NOT NULL default '0.00',
  `u_is_resp` char(1) NOT NULL default 'N',
  `u_resp_login` varchar(16) default NULL,
  `u_passwd` varchar(64) NOT NULL default '',
  `u_quotite` int(3) default '100',
  `u_email` varchar(100) default NULL,
  PRIMARY KEY  (`u_login`),
  KEY `u_login` (`u_login`)
) TYPE=MyISAM;

INSERT INTO `conges_users` (`u_login`, `u_nom`, `u_prenom`, `u_nb_jours_an`, `u_solde_jours`, `u_nb_rtt_an`, `u_solde_rtt`, `u_is_resp`, `u_resp_login`, `u_passwd`, `u_quotite`, `u_email`) VALUES ('admin', 'php_conges', 'admin', '0.00', '0.00', '0.00', '0.00', 'Y', 'admin', '636d61cf9094a62a81836f3737d9c0da', 100, NULL);
INSERT INTO `conges_users` (`u_login`, `u_nom`, `u_prenom`, `u_nb_jours_an`, `u_solde_jours`, `u_nb_rtt_an`, `u_solde_rtt`, `u_is_resp`, `u_resp_login`, `u_passwd`, `u_quotite`, `u_email`) VALUES ('conges', 'conges', 'responsable-virtuel', '0.00', '0.00', '0.00', '0.00', 'Y', NULL, '3cdb69ff35635d9a3f6eccb6a5e269e6', 100, NULL);

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
