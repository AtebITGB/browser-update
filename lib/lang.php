<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'init.php'); // just to be sure
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'gettext.inc');
define('BU_LANG_PATH', BU_PATH . 'lang' . DIRECTORY_SEPARATOR);

$lang_rewrite = array(
	"de"=>"de_DE",
	"en"=>"en_GB",
	"ja"=>"ja_JP",
	"es"=>"es_ES",
	"pl"=>"pl_PL",
	"nl"=>"nl_NL",
	"it"=>"it_IT",
	"pt"=>"pt_BR",
	"sl"=>"sl_SI",
	"ru"=>"ru_RU",
	"id"=>"id_ID",
	"kr"=>"ko_KR",        #this was wrong
	"ko"=>"ko_KR",
	"uk"=>"uk_UA",
	"rm"=>"rm_CH",
	"fr"=>"fr_FR",
        "da"=>"da_DK",
	"sq"=>"sq_AL",
	"ca"=>"ca_ES",
	"sv"=>"sv_SE",
	"hu"=>"hu_HU",
	"fa"=>"fa_IR",
	"gl"=>"gl_ES",
        "he"=>"he_IL",
        "cs"=>"cs_CZ",
        "nb"=>"nb_NO",
        "zh"=>"zh_CN",
        "fi"=>"fi_FI",
        "tr"=>"tr_TR",
        "ro"=>"ro_RO",
        "hr"=>"hr_HR",
        "bg"=>"bg_BG",
        "el"=>"el_GR",
        "ar"=>"ar_EG",
        "sr"=>"sr_RS",
        "lv"=>"lv_LV",
        "ga"=>"ga_IE",
        "no"=>"no_NO",
        "th"=>"th_TH",
        "xx"=>"xx_XX",#test
        "yy"=>"en_SE",#for crowdin inline translation tool. "Gothic"
        "sk"=>"sk_SK",
        "hi"=>"hi_IN",
        "vi"=>"vi_VN"
);

/**
 * Normalizes the language-string
 *
 * There are many different ways to id languages for the system, for example:
 *  - de_de
 *  - de-de
 *  - de_DE
 *  - ...
 *
 * This function tries to return a normalized version, regardless of the format
 * given. This is done by replacing "-" with "_" and converting the string to
 * xx_YY.
 * @return string
 */
function lang_normalize($lang)
{
	$lang = strtolower(str_replace('-', '_', $lang));
	if (strlen($lang) >= 5)
	{
		$lang = substr($lang, 0, 3) . strtoupper(substr($lang, 3, 10));
	}
	return $lang;
}

/**
 * Get language used fot this request.
 *
 * Each request needs to have its own language assigned. This function tries
 * to find the right language by searching for supported languages matching
 * the languages given in $_SERVER['HTTP_ACCEPT_LANGUAGE']. If no language
 * is supported it returns $default_lang.
 *
 * Additionally it is possible to set the language using $_GET['lang'], which
 * puts $_GET['lang'] in front of every other language given in
 * $_SERVER['HTTP_ACCEPT_LANGUAGE']. This way it is possible to force the
 * language to a particular value if neccessary.
 * @return string Language for the request.
 */
function request_lang()
{
	global $default_lang, $lang_rewrite;
	static $request_lang = null;
	if (!is_null($request_lang)) return $request_lang;
	$lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
	$lang = preg_split('#[,;]#', $lang);
	$lang = array_map('lang_normalize', $lang);
	if (isset($_GET['lang']))
	{
		$lang = array($_GET['lang']);
	}
	foreach ($lang as $ll)
	{
		if (file_exists(BU_LANG_PATH . $ll) && $ll!="")
		{
			$request_lang = $ll;
			return $request_lang;
		}
		if (isset($lang_rewrite[$ll]))
		{
			$ll = $lang_rewrite[$ll];
			if (file_exists(BU_LANG_PATH . $ll) && $ll!="")
			{
				$request_lang = $ll;
				return $request_lang;
			}
		}
	}
	// Second try, use only the first two chars
	foreach ($lang as $ll)
	{
		if (strlen($ll) <= 2) continue;
		$ll = substr($ll, 0, 2);
		if (file_exists(BU_LANG_PATH . $ll))
		{
			$request_lang = $ll;
			return $request_lang;
		}
		if (isset($lang_rewrite[$ll]))
		{
			$ll = $lang_rewrite[$ll];
			if (file_exists(BU_LANG_PATH . $ll) && $ll!="")
			{
				$request_lang = $ll;
				return $request_lang;
			}
		}
	}
	$request_lang = $default_lang;
	return $request_lang;
}

/**
 * Get language used for logging.
 *
 * Inside the log it is not neccessary to find a supported language. Instead
 * the first language can be used.
 * @return string Preferred Language for logging purposes
 */
function log_lang()
{
	$lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
	$lang = preg_split('#[,;]#', $lang);
	return lang_normalize($lang[0]);
}

/* Init i18n */
$detected_lang = request_lang();
$ll=substr($detected_lang, 0, 2);
// moved into request_lang()
//if (isset($lang_rewrite[$detected_lang]))
//	$detected_lang=$lang_rewrite[$detected_lang];

T_setlocale(LC_MESSAGES, $detected_lang);

T_bindtextdomain('site', rtrim(BU_LANG_PATH, DIRECTORY_SEPARATOR));
T_bind_textdomain_codeset('site', 'UTF-8');
T_bindtextdomain('update', rtrim(BU_LANG_PATH, DIRECTORY_SEPARATOR));
T_bind_textdomain_codeset('update', 'UTF-8');
T_bindtextdomain('customize', rtrim(BU_LANG_PATH, DIRECTORY_SEPARATOR));
T_bind_textdomain_codeset('customize', 'UTF-8');
T_textdomain('update');

//var_dump($CURRENTLOCALE);
//var_dump($EMULATEGETTEXT);
//var_dump(request_lang());
//var_dump(setlocale(LC_ALL, 'de_DE'));
//$EMULATEGETTEXT=0;
//var_dump(setlocale(LC_MESSAGES, 'deutsch'));
//var_dump([$detected_lang,locale_emulation()]);

function T_ig($str) {
    return T_($str);
}

function is_translated($str) {
    global $ll;
    if ($ll=="en" || T_($str)!=$str)
        return True;
    else
        return False;
}