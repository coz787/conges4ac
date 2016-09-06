<?php
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


//appel de PHP-IDS que si version de php > 5.1.2
if(phpversion() > "5.1.2") { include("../controle_ids.php") ;}
$session=(isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : session_id()) ) ;

include("fonctions_edition.php") ;
include("../fonctions_conges.php") ;
include("../INCLUDE.PHP/fonction.php");
include("../INCLUDE.PHP/session.php");

//$DEBUG = TRUE ;
$DEBUG = FALSE ;
// define('FPDF_FONTPATH','font/');
define("_SYSTEM_TTFONTS",$_SESSION['config']['php_conges_fpdf_include_path']."/tfpdf/font/");
// define('_SYSTEM_TTFONTS',"/srv/www/htdocs/tfpdf/font/");
$fpdf_filename = $_SESSION['config']['php_conges_fpdf_include_path']."/tfpdf/tfpdf.php";
// verif si la librairie fpdf est présente 
if (!is_readable($fpdf_filename)) {
  echo $_SESSION['lang']['fpdf_not_valid']."<br> !";
  echo "located in ".  $fpdf_filename."<br> !";
}  else	{
  // require_once($fpdf_filename);
  require($fpdf_filename);
  // include($fpdf_filename);
}

class uPDF extends tFPDF {
  /* public function __construct(){
      error_log("class pdf __construct");
      parent::__construct();
      $this->AddFont('DvSerif','','unifont/DejaVuSerif.ttf',true);
      $this->AddFont('DvSans','','unifont/DejaVuSans.ttf',true);
      } */ 
  function Header() {
    /**************************************/
    /* affichage du texte en haut de page */
    /**************************************/
    $this->SetFont('DvSans','',10);
    $this->Cell(0,3, $_SESSION['config']['texte_haut_edition_papier'],0,1,'C');
    $this->Ln(10);
  }
			
  function Footer()  {
    /**************************************/
    /* affichage du texte de bas de page */
    /**************************************/
    $this->SetFont('DvSans','',10);
    //$pdf->Cell(0,6, 'texte_haut_edition_papier',0,1,'C');
    $this->Cell(0,3, $_SESSION['config']['texte_bas_edition_papier'],0,1,'C');
    $this->Ln(10);
  }
}
	

	/*************************************/
	// recup des parametres reçus :
	// GET / POST
	$user_login = getpost_variable("user_login") ;
	$edit_id = getpost_variable("edit_id", 0) ;
	/*************************************/

	/************************************/

	
	//connexion mysql
	$mysql_link = connexion_mysql();

	if($edit_id==0)   // si c'est une nouvelle édition, on insert dans la base avant d'éditer et on renvoit l'id de l'édition
		$edit_id=enregistrement_edition($user_login, $mysql_link, $DEBUG);
	
	edition_pdf($user_login, $edit_id, $mysql_link, $DEBUG);

	$comment_log = "edition PDF (num_edition = $edit_id) ($user_login) ";
	log_action(0, "", $user_login, $comment_log, $mysql_link, $DEBUG);
	
	mysql_close($mysql_link);

	
	
	
/**************************************************************************************/
/********  FONCTIONS      ******/
/**************************************************************************************/

/* dgac/dpa passage de fpdf a tfpdf pour utilisation utf8 */ 
function edition_pdf($login, $edit_id, $mysql_link, $DEBUG=FALSE)
{
  /*
  // by default  php_conges_fpdf_include_path is "/srv/www/htdocs" 
  $fpdf_filename = $_SESSION['config']['php_conges_fpdf_include_path']."/tfpdf/tfpdf.php";
  // verif si la librairie fpdf est présente 
  if (!is_readable($fpdf_filename)) {
    echo $_SESSION['lang']['fpdf_not_valid']."<br> !";
    echo "located in ".  $fpdf_filename."<br> !";
  }
  else	{
    // require_once($fpdf_filename);
    require($fpdf_filename);
*/
		
		// recup du tableau des types de conges (seulement les conges)
		$tab_type_cong=recup_tableau_types_conges($mysql_link);
		// recup du tableau des types de conges exceptionnels (seulement les conges exceptionnels)
		if ($_SESSION['config']['gestion_conges_exceptionnels']==TRUE) 
			 $tab_type_conges_exceptionnels=recup_tableau_types_conges_exceptionnels($mysql_link, $DEBUG);
		else
			$tab_type_conges_exceptionnels=array();
		// recup du tableau de tous les types de conges
		$tab_type_all_cong=recup_tableau_tout_types_abs($mysql_link, $DEBUG);
	
		// recup infos du user
		$tab_info_user=recup_info_user_pour_edition($login, $mysql_link);
	
		// recup infos de l'édition
		$tab_info_edition=recup_info_edition($edit_id, $mysql_link);
	
		
		/**************************************/
		/* on commence l'affichage ...        */
		/**************************************/
		header('content-type: application/pdf');
		//header('content-Disposition: attachement; filename="downloaded.pdf"');    // pour IE
		
		$pdf = new uPDF();
		//$pdf->Open();
        $pdf->AddFont('DvSerif','','unifont/DejaVuSerif.ttf',true); // Times
        $pdf->AddFont('DvSans','','unifont/DejaVuSans.ttf',true); // Arial  
		$pdf->AddPage();
        /*
        error_log("epdf: ".count($pdf->fonts));
        foreach($pdf->fonts as $k => $v){
          error_log("epdf: ".$k." ".print_r($v, true) ." "); 
        }
        error_log("epdf: ".print_r($pdf->fonts, true)); */ 
			
		$pdf->SetFillColor(200);
		
		/**************************************/
		/* affichage du texte en haut de page */
		/**************************************/
		// fait dans le header de la classe (cf + haut)
		
		/**************************************/
		/* affichage du TITRE                 */
		/**************************************/
		$pdf->SetFont('DvSerif', '', 18);				
		$pdf->Cell(0, 5, $tab_info_user['nom']." ".  $tab_info_user['prenom'],0,1,'C');
		$pdf->Ln(5);
		$pdf->SetFont('DvSerif', '', 13);				
		$tab_date=explode("-", $tab_info_edition['date']);
		$pdf->Cell(0, 5, $_SESSION['lang']['editions_bilan_au']." ".$tab_date[2]." / ".$tab_date[1]." / ".$tab_date[0],0,1,'C');
		$pdf->Ln(4);
		
		/****************************/
		/* tableau Bilan des Conges */
		/****************************/
		// affichage en pdf du tableau récapitulatif des solde de congés d'un user
		affiche_pdf_tableau_bilan_conges_user_edtion($pdf, $tab_info_user, $tab_info_edition, $tab_type_cong, $tab_type_conges_exceptionnels, $mysql_link, $DEBUG) ;
	
		// affichage de la quotité
		$pdf->SetFont('DvSerif', '', 13);				
		$quotite=$tab_info_user['quotite'];
		$pdf->Cell(0, 5, $_SESSION['lang']['divers_quotite']."  :  $quotite % ",0,1,'C');
		$pdf->Ln(4);
		$pdf->Ln(8);
	
	
		$pdf->SetFont('DvSerif', '', 11);				
		$pdf->Cell(0, 5, $_SESSION['lang']['editions_historique']." :",0,1,'C');
		$pdf->Ln(5);
		/*********************************************/
		/* Tableau Historique des Conges et demandes */
		/*********************************************/
	
			$pdf->SetFont('DvSerif', '', 10);

			//test d'une ligne à 120 caractères
			//$ligne120="123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890";
			//$pdf->Cell(0, 5, $ligne120 ,0,1,'C');
			
			// Récupération des informations
			// on ne recup QUE les periodes de l'edition choisie
			$sql2 = "SELECT p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_etat, p_date_demande, p_date_traitement ";
			$sql2=$sql2."FROM conges_periode ";
			$sql2=$sql2."WHERE p_edition_id = $edit_id ";
			$sql2=$sql2."ORDER BY p_date_deb ASC ";
			$ReqLog2 = mysql_query($sql2, $mysql_link) or die("ERREUR : mysql_query : ".$sql2." --> ".mysql_error());
	
			$count2=mysql_num_rows($ReqLog2);
			if($count2==0)
			{
				$pdf->Cell(0, 5, $_SESSION['lang']['editions_aucun_conges']." ...",0,1,'C');
				$pdf->Ln(5);
			}
			else
			{
				// AFFICHAGE TABLEAU
				// decalage pour centrer 
				$decalage = 5;
				
				/*************************************/
				/* affichage anciens soldes          */
				/*************************************/
				// affichage en pdf des anciens soldes de congés d'un user
				affiche_pdf_ancien_solde($pdf, $login, $edit_id, $tab_type_cong, $tab_type_conges_exceptionnels, $decalage, $mysql_link, $DEBUG) ;
	
				$pdf->Ln(2);
	
				// (largeur totale page = 210 ( - 2x10 de marge))
				// tailles des cellules du tableau
				if($_SESSION['config']['affiche_date_traitement']==TRUE)
				{
					affiche_tableau_conges_avec_date_traitement($pdf, $ReqLog2, $decalage, $tab_type_all_cong, $DEBUG);
				}
				else
				{
					affiche_tableau_conges_normal($pdf, $ReqLog2, $decalage, $tab_type_all_cong, $DEBUG);
				}
				
				$pdf->Ln(2);
				
				/*************************************/
				/* affichage nouveaux soldes         */
				/*************************************/
				// affichage en pdf des nouveaux soldes de congés d'un user
				affiche_pdf_nouveau_solde($pdf, $login, $tab_info_edition, $tab_type_cong, $tab_type_conges_exceptionnels, $decalage, $mysql_link, $DEBUG) ;
	
			}
		
			$pdf->Ln(8);
		
		/*************************************/
		/* affichage des zones de signature  */
		/*************************************/
		$pdf->SetFont('DvSerif', '', 10);				
		// decalage pour centrer 
		$pdf->Cell(20); 
		$pdf->Cell(70, 5, $_SESSION['lang']['editions_date']." :",0,0);
		$pdf->Cell(70, 5, $_SESSION['lang']['editions_date']." :",0,1);
		// decalage pour centrer 
		$pdf->Cell(20); 
		$pdf->Cell(70, 5, $_SESSION['lang']['editions_signature_1']." :",0,0);
		$pdf->Cell(70, 5, $_SESSION['lang']['editions_signature_2']." :",0,1);
		
		$pdf->SetFont('DvSerif', '', 10);				
		// decalage pour centrer 
		$pdf->Cell(20); 
		$pdf->Cell(70, 5, "",0,0);
		$pdf->Cell(70, 5, "(".$_SESSION['lang']['editions_cachet_etab'].")",0,1);
		
		$pdf->Ln(30);
		
		/*************************************/
		/* affichage du texte en bas de page */
		/*************************************/
		// fait dans le footer de la classe (cf + haut)
		
		$pdf->Output();
	
}



// affichage en pdf du tableau récapitulatif des solde de congés d'un user
function affiche_pdf_tableau_bilan_conges_user_edtion(&$pdf, $tab_info_user, $tab_info_edition, $tab_type_cong, $tab_type_conges_exceptionnels, $mysql_link, $DEBUG=FALSE)
{
	// calcul du décalage pour centrer ( = (21cm - (marges x 2) - (sommes des cell définies en dessous) )/2  ) (marges=10mm)
	$decalage = 55 ;

	// affichage :
	$pdf->SetFont('DvSerif', '', 9);				
		
	$pdf->Cell($decalage); 
	$pdf->Cell(40, 5, " ", 1, 0, 'C');
	$pdf->Cell(20, 5, " ".$_SESSION['lang']['editions_jours_an'], 1, 0, 'C');
	$pdf->Cell(20, 5, $_SESSION['lang']['divers_solde_maj_1']." ", 1, 1, 'C');

	foreach($tab_type_cong as $id_abs => $libelle)
	{
		$pdf->Cell($decalage); 
		$pdf->Cell(40, 5, " $libelle ", 1, 0, 'C');
		$pdf->Cell(20, 5, $tab_info_user['conges'][$libelle]['nb_an'], 1, 0, 'C');
		$pdf->Cell(20, 5, $tab_info_edition['conges'][$id_abs], 1, 1, 'C', 1);
	}
	foreach($tab_type_conges_exceptionnels as $id_abs => $libelle)
	{
		$pdf->Cell($decalage); 
		$pdf->Cell(40, 5, " $libelle ", 1, 0, 'C');
		$pdf->Cell(20, 5, $tab_info_user['conges'][$libelle]['nb_an'], 1, 0, 'C');
		$pdf->Cell(20, 5, $tab_info_edition['conges'][$id_abs], 1, 1, 'C', 1);
	}
	// passage à la ligne
	$pdf->Ln();

}


// affichage en pdf des anciens soldes de congés d'un user
function affiche_pdf_ancien_solde(&$pdf, $login, $edit_id, $tab_type_cong, $tab_type_conges_exceptionnels, $decalage, $mysql_link, $DEBUG=FALSE)
{
//	$pdf->SetFont('Arial', '', 10);

	$edition_precedente_id=get_id_edition_precedente_user($login, $edit_id, $mysql_link, $DEBUG);
	if($edition_precedente_id==0)
	{
		$pdf->Cell($decalage); 
		$pdf->SetFont('DvSans', '', 8);
		$pdf->Cell(50, 5, $_SESSION['lang']['editions_soldes_precedents_inconnus']." !...",0,1);
	}
	else
	{
		$tab_edition_precedente=recup_info_edition($edition_precedente_id, $mysql_link, $DEBUG);
			
		foreach($tab_type_cong as $id_abs => $libelle)
		{
			$pdf->Cell($decalage); 
			$pdf->SetFont('DvSans', '', 8);
			$pdf->Cell(26, 5, $_SESSION['lang']['editions_solde_precedent']." ",0,0);
			$pdf->SetFont('DvSans', '', 8);
			$pdf->Cell(10, 5, $libelle." : ".$tab_edition_precedente['conges'][$id_abs], 0, 1);
		}
		foreach($tab_type_conges_exceptionnels as $id_abs => $libelle)
		{
			$pdf->Cell($decalage); 
			$pdf->SetFont('DvSans', '', 8);
			$pdf->Cell(26, 5, $_SESSION['lang']['editions_solde_precedent']." ",0,0);
			$pdf->SetFont('DvSans', '', 8);
			$pdf->Cell(10, 5, $libelle." : ".$tab_edition_precedente['conges'][$id_abs], 0, 1);
		}
	}
}


// affichage en pdf des nouveaux soldes de congés d'un user
function affiche_pdf_nouveau_solde(&$pdf, $login, $tab_info_edition, $tab_type_cong, $tab_type_conges_exceptionnels, $decalage, $mysql_link, $DEBUG=FALSE)
{

	foreach($tab_type_cong as $id_abs => $libelle)
	{
		$pdf->Cell($decalage); 
		$pdf->SetFont('DvSans', '', 8);
		$pdf->Cell(24, 5, $_SESSION['lang']['editions_nouveau_solde']." ",0,0);
		$pdf->SetFont('DvSans', '', 8);
		$pdf->Cell(40, 5, $libelle." : ".$tab_info_edition['conges'][$id_abs], 0, 1);
	}
	foreach($tab_type_conges_exceptionnels as $id_abs => $libelle)
	{
		$pdf->Cell($decalage); 
		$pdf->SetFont('DvSans', '', 8);
		$pdf->Cell(24, 5, $_SESSION['lang']['editions_nouveau_solde']." ",0,0);
		$pdf->SetFont('DvSans', '', 8);
		$pdf->Cell(40, 5, $libelle." : ".$tab_info_edition['conges'][$id_abs], 0, 1);
	}
}


function affiche_tableau_conges_avec_date_traitement(&$pdf, $ReqLog2, $decalage, $tab_type_all_cong, $DEBUG=FALSE)
{

	// (largeur totale page = 210 ( - 2x10 de marge))
	// tailles des cellules du tableau
	$size_cell_type = 25; 
	$size_cell_etat = 15;
	$size_cell_nb_jours = 15;  //90
	$size_cell_debut = 25;
	$size_cell_fin = 25;
	$size_cell_comment = 40;
	$size_cell_date_traitement = 40;
	//          total = 190 (185+decalage)

	/*************************************/
	/* affichage lignes de l'edition     */
	/*************************************/
	// decalage pour centrer 
	$pdf->Cell($decalage); 
	
	$pdf->SetFont('DvSerif', '', 8);				
	$pdf->Cell($size_cell_type, 5, $_SESSION['lang']['divers_type_maj_1'], 1, 0, 'C', 1); 
	$pdf->Cell($size_cell_etat, 5, $_SESSION['lang']['divers_etat_maj_1'], 1, 0, 'C', 1);
	$pdf->Cell($size_cell_nb_jours, 5, $_SESSION['lang']['divers_nb_jours_maj_1'], 1, 0, 'C', 1);
	$pdf->Cell($size_cell_debut, 5, $_SESSION['lang']['divers_debut_maj_1'], 1, 0, 'C', 1);
	$pdf->Cell($size_cell_fin, 5, $_SESSION['lang']['divers_fin_maj_1'], 1, 0, 'C', 1);
	$pdf->Cell($size_cell_comment, 5, $_SESSION['lang']['divers_comment_maj_1'], 1, 1, 'C', 1);
				
	while ($resultat2 = mysql_fetch_array($ReqLog2)) 
	{
		$sql_p_date_deb = eng_date_to_fr($resultat2["p_date_deb"]);
		$sql_p_demi_jour_deb = $resultat2["p_demi_jour_deb"];
		if($sql_p_demi_jour_deb=="am")
			$demi_j_deb = $_SESSION['lang']['divers_am_short'];
		else
			$demi_j_deb = $_SESSION['lang']['divers_pm_short'];
		$sql_p_date_fin = eng_date_to_fr($resultat2["p_date_fin"]);
		$sql_p_demi_jour_fin = $resultat2["p_demi_jour_fin"];
		if($sql_p_demi_jour_fin=="am")
			$demi_j_fin = $_SESSION['lang']['divers_am_short'];
		else
			$demi_j_fin = $_SESSION['lang']['divers_pm_short'];
		$sql_p_nb_jours = $resultat2["p_nb_jours"];
		$sql_p_commentaire = $resultat2["p_commentaire"];
		$sql_p_type = $resultat2["p_type"];
		$sql_p_etat = $resultat2["p_etat"];
		$sql_p_date_demande = $resultat2["p_date_demande"];
		$sql_p_date_traitement = $resultat2["p_date_traitement"];
					
		// decalage pour centrer 
		$pdf->Cell($decalage);
		$hauteur_cellule=5;
						
		$taille_font=8;
							
		$pdf->SetFont('DvSerif', '', $taille_font);				

		$pdf->Cell($size_cell_type, $hauteur_cellule*2, $tab_type_all_cong[$sql_p_type]['libelle'], 1, 0, 'C'); 
	
		if($sql_p_etat=="refus")
			$text_etat = $_SESSION['lang']['divers_refuse'];
		elseif($sql_p_etat=="annul")
			$text_etat = $_SESSION['lang']['divers_annule'];
		else
			$text_etat=$sql_p_etat;
		$pdf->Cell($size_cell_etat, $hauteur_cellule*2, $text_etat, 1, 0, 'C');
	
		if( ($sql_p_etat=="refus") || ($sql_p_etat=="annul") )
			$pdf->SetFont('DvSerif', '', $taille_font);
		else			
			$pdf->SetFont('DvSerif', '', $taille_font);	
										
		if($sql_p_etat=="ok")
			$text_nb_jours="-".$sql_p_nb_jours;
		elseif($sql_p_etat=="ajout")
			$text_nb_jours="+".$sql_p_nb_jours;
		else
			$text_nb_jours=$sql_p_nb_jours;
		$pdf->Cell($size_cell_nb_jours, $hauteur_cellule*2, $text_nb_jours, 1, 0, 'C');
	
		$pdf->SetFont('DvSerif', '', $taille_font);				
		$pdf->Cell($size_cell_debut, $hauteur_cellule*2, $sql_p_date_deb." _ ".$demi_j_deb, 1, 0, 'C');
		$pdf->Cell($size_cell_fin, $hauteur_cellule*2, $sql_p_date_fin." _ ".$demi_j_fin, 1, 0, 'C');
		// reduction de la taille du commentaire pour rentrer dans la cellule
		if(strlen($sql_p_commentaire)>39)
			$sql_p_commentaire = substr($sql_p_commentaire, 0, 35)." ..." ;

		$pdf->Cell($size_cell_comment, $hauteur_cellule*2, $sql_p_commentaire."\n ", 1, 'L');
		$pdf->MultiCell($size_cell_date_traitement, $hauteur_cellule, "demande : ".$sql_p_date_demande."\ntraitement : ".$sql_p_date_traitement , 1, 'L' );
	}
}


function affiche_tableau_conges_normal(&$pdf, $ReqLog2, $decalage, $tab_type_all_cong, $DEBUG=FALSE)
{

	// (largeur totale page = 210 ( - 2x10 de marge))
	// tailles des cellules du tableau
	$size_cell_type = 30; 
	$size_cell_etat = 15;
	$size_cell_nb_jours = 20;  //90
	$size_cell_debut = 30;
	$size_cell_fin = 30;
	$size_cell_comment = 60;
	//          total = 190 (185+decalage)

	/*************************************/
	/* affichage lignes de l'edition     */
	/*************************************/
	// decalage pour centrer 
	$pdf->Cell($decalage); 
	
	//$pdf->SetFont('DvSerif', '', 10);				
	$pdf->SetFont('DvSerif', '', 8);				
	$pdf->Cell($size_cell_type, 5, $_SESSION['lang']['divers_type_maj_1'], 1, 0, 'C', 1); 
	$pdf->Cell($size_cell_etat, 5, $_SESSION['lang']['divers_etat_maj_1'], 1, 0, 'C', 1);
	$pdf->Cell($size_cell_nb_jours, 5, $_SESSION['lang']['divers_nb_jours_maj_1'], 1, 0, 'C', 1);
	$pdf->Cell($size_cell_debut, 5, $_SESSION['lang']['divers_debut_maj_1'], 1, 0, 'C', 1);
	$pdf->Cell($size_cell_fin, 5, $_SESSION['lang']['divers_fin_maj_1'], 1, 0, 'C', 1);
	$pdf->Cell($size_cell_comment, 5, $_SESSION['lang']['divers_comment_maj_1'], 1, 1, 'C', 1);
				
	while ($resultat2 = mysql_fetch_array($ReqLog2)) 
	{
		$sql_p_date_deb = eng_date_to_fr($resultat2["p_date_deb"]);
		$sql_p_demi_jour_deb = $resultat2["p_demi_jour_deb"];
		if($sql_p_demi_jour_deb=="am")
				$demi_j_deb = $_SESSION['lang']['divers_am_short'];
		else
				$demi_j_deb = $_SESSION['lang']['divers_pm_short'];
		$sql_p_date_fin = eng_date_to_fr($resultat2["p_date_fin"]);
		$sql_p_demi_jour_fin = $resultat2["p_demi_jour_fin"];
		if($sql_p_demi_jour_fin=="am")
			$demi_j_fin = $_SESSION['lang']['divers_am_short'];
		else
			$demi_j_fin = $_SESSION['lang']['divers_pm_short'];
		$sql_p_nb_jours = $resultat2["p_nb_jours"];
		$sql_p_commentaire = $resultat2["p_commentaire"];
		$sql_p_type = $resultat2["p_type"];
		$sql_p_etat = $resultat2["p_etat"];
		$sql_p_date_demande = $resultat2["p_date_demande"];
		$sql_p_date_traitement = $resultat2["p_date_traitement"];
						
		// decalage pour centrer 
		$pdf->Cell($decalage);
		$hauteur_cellule=5;
						
		$taille_font=8;
							
		$pdf->SetFont('DvSerif', '', $taille_font);				

		$pdf->Cell($size_cell_type, $hauteur_cellule, $tab_type_all_cong[$sql_p_type]['libelle'], 1, 0, 'C'); 
	
		if($sql_p_etat=="refus")
			$text_etat = $_SESSION['lang']['divers_refuse'];
		elseif($sql_p_etat=="annul")
			$text_etat = $_SESSION['lang']['divers_annule'];
		else
			$text_etat=$sql_p_etat;
		$pdf->Cell($size_cell_etat, $hauteur_cellule, $text_etat, 1, 0, 'C');
	
		if( ($sql_p_etat=="refus") || ($sql_p_etat=="annul") )
			$pdf->SetFont('DvSerif', '', $taille_font);
		else			
			$pdf->SetFont('DvSerif', '', $taille_font);	
							
		if($sql_p_etat=="ok")
			$text_nb_jours="-".$sql_p_nb_jours;
		elseif($sql_p_etat=="ajout")
			$text_nb_jours="+".$sql_p_nb_jours;
		else
			$text_nb_jours=$sql_p_nb_jours;
		$pdf->Cell($size_cell_nb_jours, $hauteur_cellule, $text_nb_jours, 1, 0, 'C');
	
		$pdf->SetFont('DvSerif', '', $taille_font);				
		$pdf->Cell($size_cell_debut, $hauteur_cellule, $sql_p_date_deb." _ ".$demi_j_deb, 1, 0, 'C');
		$pdf->Cell($size_cell_fin, $hauteur_cellule, $sql_p_date_fin." _ ".$demi_j_fin, 1, 0, 'C');
		// reduction de la taille du commentaire pour rentrer dans la cellule
		if(strlen($sql_p_commentaire)>39)
		$sql_p_commentaire = substr($sql_p_commentaire, 0, 35)." ..." ;
		$pdf->Cell($size_cell_comment, $hauteur_cellule, $sql_p_commentaire, 1, 1, 'C');
	}

}

?>
