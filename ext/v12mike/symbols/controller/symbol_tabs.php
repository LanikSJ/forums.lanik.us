<?php
/**
 *
 * phpBB Symbols extension
 *
 * @copyright (c) 2018 v12mike
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace v12mike\symbols\controller;

use \Symfony\Component\HttpFoundation\Response;

class symbol_tabs
{
	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/* @var */
	protected $phpbb_root_path;

	/**
	 * Constructor
	 *
	 * @param \phpbb\controller\helper  $helper
	 * @param \phpbb\template\template  $template
	 * @param \phpbb\user				$user
	 * @param 							$phpbb_root_path
	 */
	public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user, $phpbb_root_path)
	{
		$this->helper   = $helper;
		$this->template = $template;
		$this->user 	= $user;
		$this->phpbb_root_path = $phpbb_root_path;
	}

	/**
	 * Symbols controller for route /symbols/{$tab_id}
	 *
	 * @param string $tab_id
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function handle($tab_id)
	{
		$symlabels = array(
		'accent-chars' => 'SYM_ACCENTED_CHARS',
		'math-symbols' => 'SYM_MATH_SYMBOLS',
		'misc-symbols' => 'SYM_MISC_SYMBOLS',
		'greek-chars' =>  'SYM_GREEK_LETTERS',
		);

		$symlangfiles = array(
		'accent-chars' => 'accent_chars',
		'math-symbols' => 'math_symbols',
		'misc-symbols' => 'misc_symbols',
		'greek-chars' =>  'greek_chars',
		);

		$symtables = array(
		'accent-chars' => array(
			'SYM_U_AGRAVE'	=> '&Agrave;',
			'SYM_U_AACUTE'	=> '&Aacute;',
			'SYM_U_ACIRC'	=> '&Acirc;',
			'SYM_U_ATILDE'	=> '&Atilde;',
			'SYM_U_AUML'	=> '&Auml;',
			'SYM_U_ARING'	=> '&Aring;',
			'SYM_U_AELIG'	=> '&AElig;',
			'SYM_U_CCEDIL'	=> '&Ccedil;',
			'SYM_U_EGRAVE'	=> '&Egrave;',
			'SYM_U_EACUTE'	=> '&Eacute;',
			'SYM_U_ECIRC'	=> '&Ecirc;',	
			'SYM_U_EUML'	=> '&Euml;',
			'SYM_U_IGRAVE'	=> '&Igrave;',
			'SYM_U_IACUTE'	=> '&Iacute;',
			'SYM_U_ICIRC'	=> '&Icirc;',
			'SYM_U_IUML'	=> '&Iuml;',
			'SYM_U_ETH'	=> '&ETH;',
			'SYM_U_NTILDE'	=> '&Ntilde;',
			'SYM_U_OGRAVE'	=> '&Ograve;',
			'SYM_U_OACUTE'	=> '&Oacute;',
			'SYM_U_OCIRC'	=> '&Ocirc;',
			'SYM_U_OTILDE'	=> '&Otilde;',
			'SYM_U_OUML'	=> '&Ouml;',
			'SYM_U_OSLASH'	=> '&Oslash;',
			'SYM_U_OELIG'	=> '&OElig;',
			'SYM_U_SCARON'	=> '&Scaron;',
			'SYM_U_UGRAVE'	=> '&Ugrave;',
			'SYM_U_UACUTE'	=> '&Uacute;',
			'SYM_U_UCIRC'	=> '&Ucirc;',
			'SYM_U_UUML'	=> '&Uuml;',
			'SYM_U_YACUTE'	=> '&Yacute;',
			'SYM_U_YUML'	=> '&Yuml;',
			'SYM_U_THORN'	=> '&THORN;',
			'SYM_L_SZLIG'	=> '&szlig;',
			'SYM_L_AGRAVE'	=> '&agrave;',
			'SYM_L_AACUTE'	=> '&aacute;',
			'SYM_L_ACIRC'	=> '&acirc;',
			'SYM_L_ATILDE'	=> '&atilde;',
			'SYM_L_AUML'	=> '&auml;',
			'SYM_L_ARING'	=> '&aring;',
			'SYM_L_AELIG'	=> '&aelig;',
			'SYM_L_CCEDIL'	=> '&ccedil;',
			'SYM_L_EGRAVE'	=> '&egrave;',
			'SYM_L_EACUTE'	=> '&eacute;',
			'SYM_L_ECIRC'	=> '&ecirc;',
			'SYM_L_EUML'	=> '&euml;',
			'SYM_L_IGRAVE'	=> '&igrave;',
			'SYM_L_IACUTE'	=> '&iacute;',
			'SYM_L_ICIRC'	=> '&icirc;',
			'SYM_L_IUML'	=> '&iuml;',
			'SYM_L_ETH'	=> '&eth;',
			'SYM_L_NTILDE'	=> '&ntilde;',
			'SYM_L_OGRAVE'	=> '&ograve;',
			'SYM_L_OACUTE'	=> '&oacute;',
			'SYM_L_OCIRC'	=> '&ocirc;',
			'SYM_L_OTILDE'	=> '&otilde;',
			'SYM_L_OUML'	=> '&ouml;',
			'SYM_L_OSLASH'	=> '&oslash;',
			'SYM_L_OELIG'	=> '&oelig;',
			'SYM_L_SCARON'	=> '&scaron;',
			'SYM_L_UGRAVE'	=> '&ugrave;',
			'SYM_L_UACUTE'	=> '&uacute;',
			'SYM_L_UCIRC'	=> '&ucirc;',	
			'SYM_L_UUML'	=> '&uuml;',
			'SYM_L_YACUTE'	=> '&yacute;',
			'SYM_L_YUML'	=> '&yuml;',
			'SYM_L_THORN'	=> '&thorn;',	
			),
		'math-symbols' => array (
			'SYM_MINUS'	=> '&minus;',
			'SYM_TIMES'	=> '&times;',
			'SYM_DIVIDE'	=> '&divide;',
			'SYM_SUM'	=> '&sum;',
			'SYM_PROD'	=> '&prod;',
			'SYM_DOT'	=> '&sdot;',
			'SYM_AND'	=> '&and;',
			'SYM_OR'	=> '&or;',
			'SYM_NOT'	=> '&not;',
			'SYM_THETASYM'	=> '&thetasym;',
			'SYM_UPSIH'	=> '&upsih;',
			'SYM_PIV'	=> '&piv;',
			'SYM_LOWAST'	=> '&lowast;',
			'SYM_RADIC'	=> '&radic;',
			'SYM_FORALL'	=> '&forall;',
			'SYM_PART'	=> '&part;',
			'SYM_EXIST'	=> '&exist;',
			'SYM_EMPTY'	=> '&empty;',
			'SYM_NABLA'	=> '&nabla;',
			'SYM_ISIN'	=> '&isin;',
			'SYM_NOTIN'	=> '&notin;',
			'SYM_NI'	=> '&ni;',
			'SYM_PLUSMN'	=> '&plusmn;',
			'SYM_INFIN'	=> '&infin;',
			'SYM_ANG'	=> '&ang;',
			'SYM_CAP'	=> '&cap;',
			'SYM_CUP'	=> '&cup;',
			'SYM_INT'	=> '&int;',
			'SYM_THERE4'	=> '&there4;',
			'SYM_TILDE'	=> '&tilde;',
			'SYM_SIM'	=> '&sim;',
			'SYM_CONG'	=> '&cong;',
			'SYM_ASYMP'	=> '&asymp;',
			'SYM_NE'	=> '&ne;',
			'SYM_EQUIV'	=> '&equiv;',
			'SYM_PROP'	=> '&prop;',
			'SYM_LE'	=> '&le;',
			'SYM_GE'	=> '&ge;',
			'SYM_SUB'	=> '&sub;',
			'SYM_SUP'	=> '&sup;',
			'SYM_NSUB'	=> '&nsub;',
			'SYM_SUBE'	=> '&sube;',
			'SYM_SUPE'	=> '&supe;',
			'SYM_OPLUS'	=> '&oplus;',
			'SYM_OTIMES'	=> '&otimes;',
			'SYM_PERP'	=> '&perp;',
			'SYM_SUP1'	=> '&sup1;',
			'SYM_SUP2'	=> '&sup2;',
			'SYM_SUP3'	=> '&sup3;',
			'SYM_FRAC14'	=> '&frac14;',
			'SYM_FRAC12'	=> '&frac12;',
			'SYM_FRAC34'	=> '&frac34;',
			'SYM_ORDM'	=> '&ordm;',
			'SYM_ORDF'	=> '&ordf;',
			'SYM_LCEIL'	=> '&lceil;',
			'SYM_RCEIL'	=> '&rceil;',
			'SYM_LFLOOR'	=> '&lfloor;',
			'SYM_RFLOOR'	=> '&rfloor;',
			),
		'misc-symbols' => array (
			'SYM_EURO'	=> '&euro;',
			'SYM_POUND'	=> '&pound;',
			'SYM_YEN'	=> '&yen;',
			'SYM_CENT'	=> '&cent;',
			'SYM_CURREN'	=> '&curren;',
			'SYM_DEG'	=> '&deg;',
			'SYM_MIN_D'	=> '&prime;',
			'SYM_SEC_D'	=> '&Prime;',
			'SYM_MICRO'	=> '&micro;',
			'SYM_PERMIL'	=> '&permil;',
			'SYM_IEXCL'	=> '&iexcl;',
			'SYM_BRVBAR'	=> '&brvbar;',
			'SYM_SECT'	=> '&sect;',
			'SYM_IQUEST'	=> '&iquest;',
			'SYM_DAGGER'	=> '&dagger;',
			'SYM_DDAGGER'	=> '&Dagger;',
			'SYM_TRADE'	=> '&trade;',
			'SYM_REG'	=> '&reg;',
			'SYM_PARA'	=> '&para;',
			'SYM_L_FNOF'	=> '&fnof;',
			'SYM_CIRC'	=> '&circ;',
			'SYM_NDASH'	=> '&ndash;',
			'SYM_MDASH'	=> '&mdash;',
			'SYM_LSAQUO'	=> '&lsaquo;',
			'SYM_RSAQUO'	=> '&rsaquo;',
			'SYM_LAQUO'	=> '&laquo;',
			'SYM_RAQUO'	=> '&raquo;',
			'SYM_LSQUO'	=> '&lsquo;',
			'SYM_RSQUO'	=> '&rsquo;',
			'SYM_SBQUO'	=> '&sbquo;',
			'SYM_LDQUO'	=> '&ldquo;',
			'SYM_RDQUO'	=> '&rdquo;',
			'SYM_BDQUO'	=> '&bdquo;',
			'SYM_HELLIP'	=> '&hellip;',
			'SYM_OLINE'	=> '&oline;',
			'SYM_MACR'	=> '&macr;',
			'SYM_ACUTE'	=> '&acute;',
			'SYM_CEDIL'	=> '&cedil;',
			'SYM_UML'	=> '&uml;',
			'SYM_LARR'	=> '&larr;',
			'SYM_UARR'	=> '&uarr;',
			'SYM_RARR'	=> '&rarr;',
			'SYM_DARR'	=> '&darr;',
			'SYM_HARR'	=> '&harr;',
			'SYM_CRARR'	=> '&crarr;',
			'SYM_LOZ'	=> '&loz;',
			'SYM_SPADES'	=> '&spades;',
			'SYM_CLUBS'	=> '&clubs;',
			'SYM_HEARTS'	=> '&hearts;',
			'SYM_DIAMS'	=> '&diams;',
			'SYM_BULL'	=> '&bull;',
			),
		'greek-chars' => array (
			'SYM_U_ALPHA'	=> '&Alpha;',
			'SYM_U_BETA'	=> '&Beta;',
			'SYM_U_GAMMA'	=> '&Gamma;',
			'SYM_U_DELTA'	=> '&Delta;',
			'SYM_U_EPSILON'	=> '&Epsilon;',
			'SYM_U_ZETA'	=> '&Zeta;',
			'SYM_U_ETA'	=> '&Eta;',
			'SYM_U_THETA'	=> '&Theta;',
			'SYM_U_IOTA'	=> '&Iota;',
			'SYM_U_KAPPA'	=> '&Kappa;',
			'SYM_U_LAMBDA'	=> '&Lambda;',
			'SYM_U_MU'	=> '&Mu;',
			'SYM_U_NU'	=> '&Nu;',
			'SYM_U_XI'	=> '&Xi;',
			'SYM_U_OMICRON'	=> '&Omicron;',
			'SYM_U_PI'	=> '&Pi;',
			'SYM_U_RHO'	=> '&Rho;',
			'SYM_U_SIGMA'	=> '&Sigma;',
			'SYM_U_TAU'	=> '&Tau;',
			'SYM_U_UPSILON'	=> '&Upsilon;',
			'SYM_U_PHI'	=> '&Phi;',
			'SYM_U_CHI'	=> '&Chi;',
			'SYM_U_PSI'	=> '&Psi;',
			'SYM_U_OMEGA'	=> '&Omega;',
			'SYM_L_ALPHA'	=> '&alpha;',
			'SYM_L_BETA'	=> '&beta;',
			'SYM_L_GAMMA'	=> '&gamma;',
			'SYM_L_DELTA'	=> '&delta;',
			'SYM_L_EPSILON'	=> '&epsilon;',
			'SYM_L_ZETA'	=> '&zeta;',
			'SYM_L_ETA'	=> '&eta;',
			'SYM_L_THETA'	=> '&theta;',
			'SYM_L_IOTA'	=> '&iota;',
			'SYM_L_KAPPA'	=> '&kappa;',
			'SYM_L_LAMBDA'	=> '&lambda;',
			'SYM_L_MU'	=> '&mu;',
			'SYM_L_NU'	=> '&nu;',
			'SYM_L_XI'	=> '&xi;',
			'SYM_L_OMICRON'	=> '&omicron;',
			'SYM_L_PI'	=> '&pi;',
			'SYM_L_RHO'	=> '&rho;',
			'SYM_L_SIGMAF'	=> '&sigmaf;',
			'SYM_L_SIGMA'	=> '&sigma;',
			'SYM_L_TAU'	=> '&tau;',
			'SYM_L_UPSILON'	=> '&upsilon;',
			'SYM_L_PHI'	=> '&phi;',
			'SYM_L_CHI'	=> '&chi;',
			'SYM_L_PSI'	=> '&psi;',
			'SYM_L_OMEGA'	=> '&omega;',
			),
		);

		/* load the language file for this table */
		$this->user->add_lang_ext('v12mike/symbols', array('symbols', $symlangfiles[$tab_id]));

		$this->template->assign_vars(array(
			'SYMBOLS_TAB_ID'	=> $tab_id .'-panel',
			'SYMBOLS_TAB_NAME'	=> $this->user->lang[$symlabels[$tab_id]],
			)
		);

		/* output the symbol table contents */
		foreach ($symtables[$tab_id] as $name => $code)
		{
			$this->template->assign_block_vars('symbols_table', array(
				'SYMBOL_DESCRIPTION'	=> $this->user->lang[$name],
				'SYMBOL_CODE'	=> $code,
				)
			);
		}

		return $this->helper->render('symbols_tab.html', $name);
	}
}
