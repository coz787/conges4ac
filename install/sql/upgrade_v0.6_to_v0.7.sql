#
# ATTENTION :  toutes les requetes doivent se terminer par un point virgule ";"
#
# Structure de la table `conges_artt`
#

ALTER TABLE `conges_artt` 
ADD `sem_imp_sa_am` VARCHAR( 10 ) AFTER `sem_imp_ve_pm` ,
ADD `sem_imp_sa_pm` VARCHAR( 10 ) AFTER `sem_imp_sa_am` ,
ADD `sem_imp_di_am` VARCHAR( 10 ) AFTER `sem_imp_sa_pm` ,
ADD `sem_imp_di_pm` VARCHAR( 10 ) AFTER `sem_imp_di_am` ;

ALTER TABLE `conges_artt` 
ADD `sem_p_sa_am` VARCHAR( 10 ) ,
ADD `sem_p_sa_pm` VARCHAR( 10 ) ,
ADD `sem_p_di_am` VARCHAR( 10 ) ,
ADD `sem_p_di_pm` VARCHAR( 10 ) ;

ALTER TABLE `conges_artt` 
ADD `a_date_debut_grille` DATE DEFAULT '0000-00-00' NOT NULL ,
ADD `a_date_fin_grille` DATE DEFAULT '9999-12-31' NOT NULL ;

ALTER TABLE `conges_periode` CHANGE `p_etat` `p_etat` 
ENUM( 'pris', 'demande', 'refusé', 'annulé', 'formation', 'mission', 'autre', 'absence-annulée', 
      'rtt_annulée', 'rtt_prise', 'rtt_refusée', 'demande_rtt' ) DEFAULT 'demande' NOT NULL ;

	 
ALTER TABLE `conges_artt` DROP PRIMARY KEY ;

ALTER TABLE `conges_artt` ADD PRIMARY KEY ( `a_login` , `a_date_fin_grille` ) ;
