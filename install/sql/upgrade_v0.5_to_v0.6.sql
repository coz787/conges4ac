#
# ATTENTION :  toutes les requetes doivent se terminer par un point virgule ";"
#
# Structure de la table `conges_users`
#
ALTER TABLE `conges_users` ADD `u_email` VARCHAR( 100 ) ;
ALTER TABLE `conges_periode` CHANGE `p_etat` `p_etat` ENUM( 'pris', 'demande', 'refusé', 'annulé', 'formation', 'mission', 'autre', 'absence-annulée', 'rtt_annulée', 'rtt_prise', 'rtt_refusée', 'demande-rtt' ) DEFAULT 'demande' NOT NULL ;
ALTER TABLE `conges_users` CHANGE `u_passwd` `u_passwd` VARCHAR( 64 ) NOT NULL ;
ALTER TABLE `conges_periode` ADD `p_demi_jour_deb` enum('am','pm') NOT NULL default 'am' AFTER `p_date_deb`;
ALTER TABLE `conges_periode` ADD   `p_demi_jour_fin` enum('am','pm') NOT NULL default 'pm' AFTER `p_date_fin`;
