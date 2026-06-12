<?php
/**
*
* @package phpBB Extension - Spamsecure from 69bruno
* @copyright (c) 2021 (cruiser-lounge.de)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
	// Information For Versionscheck Metadaten
	'SPSE_LANG_EXT_VER' 						=> '1.0.6',
	'SPSE_LANG_DESC'							=> 'Deutsch (Sie) ',
	'SPSE_LANG_AUTHOR' 							=> '69bruno',
	'SPSE_CONFIG_DESC' 							=> 'Hier können die Einstellungen für die Erweiterung <b>„%1$s“ (v%2$s)</b> geändert werden.',
	'SPSE_LANGUAGEPACK_OUTDATED'				=> 'Hinweis: Das Sprachpaket dieser Erweiterung ist nicht mehr aktuell.',
	'SPSE_SPAMSECURE_INFO'						=> 'Die Extension untersucht erstellte Beiträge und Themen vor Veröffentlichung auf durch den Admin verbotene Zeichen, Zeichenketten und reguläre Ausdrücke. Sie kann z.B. die Verwendung kyrillischer oder chinesischer Schriftzeichen unterbinden oder durch Eingabe entsprechender Zeichenketten und/oder regulärer Ausdrücke die Verwendung von Links unterbinden. Der Benutzer bekommt bei negativem Prüfergebnis eine Fehlermeldung und kann sein Posting überarbeiten.',
	'SPSE_PERM_TITLE'							=> 'Berechtigungen:',
	'SPSE_PERMISSION_BEFORE'					=> 'Mit dem <b>phpbb eigenem Berechtigungssystem</b> kann über die Einstellungen der  ',
	'SPSE_USERS_PERMISSIONS'					=> 'Benutzerrechte',
	'SPSE_ANDOR'								=> ' und bzw. oder den ',
	'SPSE_GROUPS_PERMISSIONS'					=> ' Gruppenrechten ',
	'SPSE_PERMISSION_AFTER'						=> ' unter <b>Diverses</b> eingestellt werden, ob die Einstellungen der Extension <b>„%1$s“</b> angewendet werden sollen.',
	'SPSE_SETTINGS_CHARS'						=> 'Einstellungen für nicht erlaubte Zeichen/Zeichenketten',
	'SSPE_NO_LINKS'								=> 'Links verbieten',
	'SSPE_NO_LINKS_EXPLAIN'						=> 'Hier können Sie auswählen ob es möglich sein soll, Beiträge zu verfassen die Links enthalten. Wenn die Option <b>"JA"</b> gewählt ist wird es <b>(abhängig der Eingestellten Benutzer- bzw. Gruppenberechtigungen)</b> nicht möglich sein, Beiträge mit Links abzusenden.<br><br><b>Information:</b> Der Benutzer erhält eine entsprechende Fehlermeldung.',
	'SPSE_LANG_KYRILL'							=> 'Kyrillische Zeichen Verbieten',
	'SPSE_LANG_KYRILL_EXPLAIN'					=> 'Hier können Sie auswählen ob es möglich sein soll, Beiträge zu verfassen die kyrillische Zeichen enthalten. Wenn die Option <b>"JA"</b> gewählt ist wird es <b>(abhängig der Eingestellten Benutzer- bzw. Gruppenberechtigungen)</b> nicht möglich sein, Beiträge mit kyrillischen Zeichen abzusenden.<br><br><b>Information:</b> Der Benutzer erhält eine entsprechende Fehlermeldung.',
	'SPSE_LANG_CHINESE'							=> 'Chinesische Zeichen verbieten',
	'SPSE_LANG_CHINESE_EXPLAIN'					=> 'Hier können Sie auswählen ob es möglich sein soll, Beiträge zu verfassen die chinesische Zeichen enthalten. Wenn die Option <b>"JA"</b> gewählt ist wird es <b>(abhängig der Eingestellten Benutzer- bzw. Gruppenberechtigungen)</b> nicht möglich sein, Beiträge mit chinesischen Zeichen abzusenden.<br><br><b>Information:</b> Der Benutzer erhält eine entsprechende Fehlermeldung.',
	'SPSE_LANG_HINDI'							=> 'Hindi-Zeichen verbieten',
	'SPSE_LANG_HINDI_EXPLAIN'					=> 'Hier können Sie auswählen ob es möglich sein soll, Beiträge zu verfassen die Hindi-Zeichen enthalten. Wenn die Option <b>"JA"</b> gewählt ist wird es <b>(abhängig der Eingestellten Benutzer- bzw. Gruppenberechtigungen)</b> nicht möglich sein, Beiträge mit Hindi-Zeichen abzusenden.<br><br><b>Information:</b> Der Benutzer erhält eine entsprechende Fehlermeldung.',
	'SPSE_LANG_CUSTOM'							=> 'Benutzerdefinierte Eingabe aktivieren',
	'SPSE_LANG_CUSTOM_EXPLAIN'					=> 'Hier können Sie individuell Zeichen oder Zeichenketten eingeben, die nicht durch die obigen Vorgaben abgedeckt sind.',
	'SPSE_INVALID_CHARS'						=> 'Benutzerdefinierte Eingabe der nicht erlaubten Zeichen/Zeichenketten',
	'SPSE_INVALID_CHARS_EXPLAIN'				=> 'Liste aller Zeichen die nicht erwünscht sind. Die Zeichen sind ohne Beachtung von Groß- und Kleinschreibung mit einem Komma getrennt einzugeben.<br><br><b>Beispiel:</b> <b style="color: green">a</b><b style="color: red">,</b><b style="color: green">b</b><b style="color: red">,</b><b style="color: green">c</b><b style="color: red">,</b><b style="color: green">d</b><b style="color: red">,</b><b style="color: green">http://</b> usw.<br><br><b>Achtung:</b> Die maximal zulässige Eingabe inklusive Kommata beträgt <b>255</b> Zeichen',
	'SPSE_INVALID_CHARS_COUNTER_SWITCH'			=> 'Anzeige der "nicht erlaubten Zeichen/Zeichenketten" bei der Fehlermeldung',
	'SPSE_INVALID_CHARS_COUNTER_SWITCH_EXPLAIN'	=> 'Hier kann eingestellt werden, ob nach der Meldung: <b>Die Nachricht enthält die nachfolgende Anzahl unerlaubter Zeichen/Zeichenketten: (Zahl)</b> eine weitere Zeile mit folgendem Text angezeigt werden soll: <b>Folgende Zeichen/Zeichenketten sind nicht erlaubt:</b>, gefolgt von den nicht erlaubten Zeichen/Zeichenketten. Dann werden dem Benutzer zusätzlich noch die Zeichen angezeigt die er verwendet hat, aber nicht verwenden darf. Die Anzahl der Zeichen/Zeichenketten können Sie in nachfolgender Option auch nochmal festlegen.',
	'SPSE_INVALID_CHARS_COUNTER'				=> 'Option für den Zähler der "nicht erlaubten Zeichen"',
	'SPSE_INVALID_CHARS_COUNTER_EXPLAIN'		=> 'Hier kann bei aktivierter Option: <b>Anzeige der "nicht erlaubten Zeichen/Zeichenketten" bei der Fehlermeldung</b> eingestellt werden, bei bis zu wieviel gefundenen unerlaubten Zeichen/Zeichenketten die Ausgabe der "nicht erwünschten Zeichen" in der Fehlermeldung eingeblendet werden soll.<br><br>Beispiel: Ist ein Wert von 5 eingestellt, wird die Ausgabe <b>Folgende Zeichen/Zeichenketten sind nicht erlaubt:</b> gefolgt von den nicht erlaubten Zeichen/Zeichenketten solange angezeigt, bis mehr als 5 nicht erlaubter Zeichen/Zeichenketten eingegeben werden.<br><br>Bei deaktivierter Option <b>Anzeige der "nicht erlaubten Zeichen/Zeichen/Zeichenketten" bei der Fehlermeldung</b> erfolgt keine zusätzliche Ausgabe der Meldung.<br><br>Mit dieser Funktion soll ermöglicht werden dass User, die versehentlich wenige unerlaubte Zeichen/Zeichenketten verwendet haben in die Lage versetzt werden, ihr Posting entsprechend anzupassen. Bei einer Vielzahl an unerlaubten Zeichen/Zeichenketten, bei denen von Spam ausgegangen werden kann, soll keine Analysemöglichkeit geboten werden.<br><br><b>Information:</b> Entsprechend der Voreinstellung werden dem Benutzer nur die Zeichen seines Textes angezeigt, die nicht erlaubt sind.',
	'SSPE_REGEX_INDIVIDUAL_CHECK'				=> 'Benutzerdefinierter Regex-Code',
	'SSPE_REGEX_INDIVIDUAL_CHECK_EXPLAIN'		=> 'Hier könne Sie aktivieren ob Sie zusätzlich noch Individuellen Regex-Code nutzen möchten.',
	'SPSE_SETTINGS_REGEX'						=> 'Einstellungen Prüfung durch "Regex" Codierung',
	'SPSE_INVALID_REGEX'						=> 'Eingabe des "Regex" Codes',
	'SPSE_INVALID_REGEX_EXPLAIN'				=> 'Hier können Sie eine Prüfung nutzen, bei der Sie vollwertigem "Regex" Code eingeben können.<br><br>Das Eingabeformat der regulären Ausdrücke ist mit besonderer Vorsicht zu behandeln. Ich empfehle dringend, sich vor der Eingabe regulärer Ausdrücke die Referenz zu RegEx(en) zur Hand zu nehmen.<br><br>Hier einige Beispiele wenn man z.B. Links oder andere Prüfungen vornehmen möchte:<br><br><b>Einfaches Beispiel:</b><br><b style="color: red">/</b><b style="color: green">http:</b><b style="color: red">|</b><b style="color: green">ftp:</b><b style="color: red">|</b><b style="color: green">www.</b><b style="color: red">/</b><br><br><b>Komplexeres Beispiel:</b><br> <b style="color: red">/</b><b style="color: green">(((http</b><b style="color: red">|</b><b style="color: green">https):\/\/)</b><b style="color: red">|</b><b style="color: green">(www\.))[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}(:[0-9]+)?(\/\S*)?</b><b style="color: red">/</b><b style="color: green">i</b>',
	'SPSE_INVALID_REGEX_MSG'					=> 'Hinweis: Der eingegebene RegEx Ausdruck ist ungültig und wurde nicht übernommen!',
	'SPSE_SETTING_SAVED'						=> 'Die Einstelllungen für <b>Spamsecure by 69bruno</b> wurden erfolgreich übernommen.',
	//New1
	'SPSE_SETTINGS_VARIANT'						=> 'Einstellungen der Überprüfungsvariante',
	'SPSE_SEARCH_COMPLETE'						=> 'Überprüfungsvariante',
	'SPSE_SEARCH_COMPLETE_EXPLAIN'				=> 'Hier können Sie auswählen, welche Felder in der Eingabemaske durchsucht werden sollen. Sie können zwischen folgenden Optionen auswählen: <br><br><b>Alle vorhandenen Eingabefelder überprüfen</b><br>&bull; Hier werden aus dem Eingabefenster alle Felder, wo eine indivduelle Zeicheneingabe möglich ist, auf unerlaubte Zeichen untersucht.<br><br><b>Nur die Eingabe des Nachrichtenfeldes überprüfen</b><br>&bull; Hierbei wird nur die Eingabe in dem Textfeld der Benachrichtigung auf individuelle verbotene Zeichen geprüft.<br><br><b>Standard: </b>Nur die Eingabe des Nachrichtenfeldes überprüfen',
	'SPSE_ALL'									=> 'Alle vorhandenen Eingabefelder überprüfen',
	'SPSE_ONLY_MESSAGE'							=> 'Nur die Eingabe des Nachrichtenfeldes überprüfen',
]);
