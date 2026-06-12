<?php
/**
*
* acp_inactive_users [French]
*
* @package language
* @version $Id: acp_inactive_users.php,v 2.0 2007/09/07 20:54:24
* @translation (c) 2007 by cotp (Baptiste Caraux)  http://www.abdomain.com
* @copyright (c) 2007 Waleed Zuberi / Double U Designs [http://www.doubleudesigns.com/]
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

// Inactive Users MOD 2.0
$lang = array_merge($lang, array(
	'INACTIVE_USERS_LIST'			=> 'Inactive Users MOD',
	'INACTIVE_USERS_LIST_EXPLAIN'	=> '<p>Depuis ce module vous pouvez choisir de désactiver ou effacer les comptes de membres qui ne se sont pas connectés au forum depuis longtemps. Le temps de la <strong>dernière visite</strong> est affiché en fonction de la configuration de l\'heure du forum actuelle.</p> <p>Les membres inactifs avec le moins de posts seront affichés en premier mais vous pouvez trier la liste avec les options ci dessous. Le statut du compte courant (par exemple <em>enregistré</em>, <em>désactivé</em>) de chaque membre peut être vu dans la colonne <strong>Etat</strong>.<br>NB: les fondateurs ne sont pas affichés par sécurité.</p> <p>Vous devrez confirmer que vous souhaitez bien effacer le ou les membres choisis. Cette action ne pourra pas être annulée.</p>',
	'USER_DELETE_DONE'	=> 'Membre(s) effacé(s) avec succès',
	'USER_DEACTIVATED_DONE'	=> 'Compte(s) désactivé(s) avec succès',
	'USER_DEACTIVATED_ALREADY'	=> 'Un ou plusieurs des membres séléctionnés sont déjà désactivés. Merci de resoumettre le formulaire.',
	'YESTERDAY'	=> 'Hier',
	'TODAY'	=> 'Aujourd\'hui',
	'INACTIVE_DELETE_CONFIRM'	=> 'Etes vous sûr que vous souhaitez effacer le(s) membre(s) séléctionné(s)? Cette action ne pourra <strong>pas être anullée.</strong>',
	'YEAR'	=> ' année',
	'YEARS'	=> ' années',
	'MONTH'	=> ' mois',
	'MONTHS'	=> ' mois',
	'DAY'	=> ' jour',
	'DAYS'	=> ' jours',
	'HOUR'	=> ' heure',
	'HOURS'	=> ' heures',
	'MIN'	=> ' minute',
	'MINS'	=> ' minutes',
	'SECS'	=> ' secondes',
	'SEC'	=> ' seconde',
	'AGO'	=> ' ago',
	'ONLINE_NOW'	=> 'En ligne.',
	'NEVER_VISIT'	=> 'Jamais',
	'USERNAME'	=> 'Pseudo',
	'ACC_STATUS'	=> 'Etat',
	'POSTS'	=> 'Posts',
	'REG_DATE'	=> 'Date d\'inscription',
	'LAST_VISIT'	=> 'Dernière visite',
	'MARK'	=> 'Mark',
	'SORT_DEFAULT'	=> 'Plus inactifs, moins de posts',
	'SORT_ACC_STATUS'	=> 'Etat du compte',
	'SORT_POSTS'	=> 'Posts',
	'SHOW_ONLY'	=> 'Afficher seulement les membres qui ont visité le forum dans ces derniers:',
	'CANNOT_DELETE_FOUNDER'	=> 'Vous n\'êtes pas autorisé à effacer les comptes des membres fondateurs.',
));
?>
