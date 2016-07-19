#
# ATTENTION :  toutes les requetes doivent se terminer par un point virgule ";"
#
# Structure de la table `conges_groupe`
#

CREATE TABLE `conges_groupe` (
`g_groupename` VARCHAR( 50 ) NOT NULL ,
`g_comment` VARCHAR( 250 ) ,
PRIMARY KEY ( `g_groupename` )
);

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
`gu_groupename` VARCHAR( 50 ) NOT NULL ,
`gu_login` VARCHAR( 16 ) NOT NULL
);
