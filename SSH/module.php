<?php

define('__ROOT__', dirname(dirname(__FILE__)));
require_once(__ROOT__ . '/libs/helpers/autoload.php');
require_once(__ROOT__ . '/libs/SSH/SSH2.php');


/**
 * Class SSH
 * IP-Symcon SSH Module
 *
 * @version     1.1
 * @category    Symcon
 * @package     de.codeking.symcon.ssh
 * @author      Frank Herrmann <frank@codeking.de>
 * @link        https://codeking.de
 * @link        https://github.com/CodeKing/de.codeking.symcon.ssh
 *
 */
class SSH extends Module
{
    use InstanceHelper;

    /**
     * @var Net_SSH2 $ssh
     */
    private $ssh;

    private $host;
    private $port;
    private $user;
    private $password;

    /**
     * create instance
     */
    public function Create()
    {
        parent::Create();

        // register public properties
        $this->RegisterPropertyString('host', '');
        $this->RegisterPropertyInteger('port', 22);
        $this->RegisterPropertyString('user', '');
        $this->RegisterPropertyString('password', '');
    }

    /**
     * execute, when kernel is ready
     */
    protected function onKernelReady()
    {
        // check connection
        $this->EstablishConnection();
    }

    /**
     * Read config
     */
    private function ReadConfig()
    {
        $this->host = $this->ReadPropertyString('host');
        $this->port = $this->ReadPropertyInteger('port');
        $this->user = $this->ReadPropertyString('user');
        $this->password = $this->ReadPropertyString('password');
    }

    /**
     * Check if connection can be established
     */
    public function EstablishConnection()
    {
        // read config
        $this->ReadConfig();

        // check for required fields
        if (!$this->host) {
            return false;
        }

        // check connection
        $this->ssh = new Net_SSH2($this->host, $this->port);
        if (!$this->ssh->login($this->user, $this->password)) {
            $this->SetStatus(201);
            $this->_log('SSH', 'Error: Could not connect to ssh server! Please check your credentials!');
            print_r($this->Translate('Error: Could not connect to ssh server! Please check your credentials!'));
            exit(-1);
        }

        // valid login
        $this->SetStatus(102);
    }

    /**
     * Execute command
     * @param string $command
     * @return string;
     */
    public function Execute(string $command): string
    {
        if (!$command) {
            return '';
        }

        // read config
        $this->ReadConfig();

        // establish connection
        $this->EstablishConnection();

        // execute command
        $result = $this->ssh->exec($command);

        return trim($result);
    }

}