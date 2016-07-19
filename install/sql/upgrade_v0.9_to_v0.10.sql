#
# ATTENTION :  toutes les requetes doivent se terminer par un point virgule ";"
#
#
# Structure de la table `conges_jours_feries`
###############################################
CREATE TABLE `conges_jours_feries` ( `jf_date` DATE NOT NULL , PRIMARY KEY ( `jf_date` ) );

#
# Structure de la table `conges_periode`
###############################################
# modif pour pouvoir saisir des congés de + de 99 jours...
#
ALTER TABLE `conges_periode` CHANGE `p_nb_jours` `p_nb_jours` DECIMAL( 5, 2 ) UNSIGNED DEFAULT '0.00' NOT NULL ;
ALTER TABLE `conges_periode` ADD `p_motif_refus` VARCHAR( 110 ) AFTER `p_edition_id` ;

#
# modif pour supprimer les accents du champ p_etat...
#
ALTER TABLE `conges_periode` CHANGE `p_etat` `p_etat` ENUM( 'ok', 'demande', 'refusé', 'annulé', 'ajout', 'refus', 'annul' ) DEFAULT 'demande' NOT NULL ;
UPDATE `conges_periode` SET `p_etat` = 'refus' WHERE `p_etat` = 'refusé' ;
UPDATE `conges_periode` SET `p_etat` = 'annul' WHERE `p_etat` = 'annulé' ;
ALTER TABLE `conges_periode` CHANGE `p_etat` `p_etat` ENUM( 'ok', 'demande', 'ajout', 'refus', 'annul' ) DEFAULT 'demande' NOT NULL ;

#
#
###############################################
# modif pour stocker le login de maniere sensible à la casse (majuscule/minuscule)...
###############################################
#
# Structure de la table `conges_user`
###############################################
ALTER TABLE `conges_users` CHANGE `u_login` `u_login` VARCHAR( 16 ) BINARY NOT NULL ;

# Structure de la table `conges_artt`
###############################################
ALTER TABLE `conges_artt` CHANGE `a_login` `a_login` VARCHAR( 16 ) BINARY NOT NULL ;

# Structure de la table `conges_echange_rtt`
###############################################
ALTER TABLE `conges_echange_rtt` CHANGE `e_login` `e_login` VARCHAR( 16 ) BINARY NOT NULL ;

# Structure de la table `conges_edition_papier`
###############################################
ALTER TABLE `conges_edition_papier` CHANGE `ep_login` `ep_login` VARCHAR( 16 ) BINARY NOT NULL ;

# Structure de la table `conges_groupe_resp`
###############################################
ALTER TABLE `conges_groupe_resp` CHANGE `gr_login` `gr_login` VARCHAR( 16 ) BINARY NOT NULL ;

# Structure de la table `conges_groupe_users`
###############################################
ALTER TABLE `conges_groupe_users` CHANGE `gu_login` `gu_login` VARCHAR( 16 ) BINARY NOT NULL ;

# Structure de la table `conges_periode`
###############################################
ALTER TABLE `conges_periode` CHANGE `p_login` `p_login` VARCHAR( 16 ) BINARY NOT NULL ;

#
