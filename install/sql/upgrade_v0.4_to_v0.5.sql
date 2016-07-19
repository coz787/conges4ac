#
# ATTENTION :  toutes les requetes doivent se terminer par un point virgule ";"
#
# Structure de la table `conges_users`
#
ALTER TABLE `conges_users` ADD `u_nb_rtt_an` DECIMAL( 4, 2 ) UNSIGNED NOT NULL AFTER `u_nb_jours_reste` ;
ALTER TABLE `conges_users` ADD `u_solde_rtt` DECIMAL( 4, 2 ) NOT NULL AFTER `u_nb_rtt_an` ;
ALTER TABLE `conges_users` CHANGE `u_nb_jours_reste` `u_solde_jours` DECIMAL( 4, 2 ) DEFAULT '0.00' NOT NULL ;

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
