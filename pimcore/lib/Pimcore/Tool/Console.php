<?php
/**
 * Pimcore
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.pimcore.org/license
 *
 * @copyright  Copyright (c) 2009-2013 pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     New BSD License
 */

class Pimcore_Tool_Console {
	/**
	 * @var string system environment
	 */
	private static $systemEnvironment;
    /**
     * @static
     * @return string "windows" or "unix"
     */
    public static function getSystemEnvironment(){
		if (self::$systemEnvironment == null) {
			if(stripos(php_uname("s"), "windows") !== false) {
				self::$systemEnvironment = 'windows';
			}else{
				self::$systemEnvironment = 'unix';
			}
		}
		return self::$systemEnvironment;
    }

    /**
     * @static
     * @return string
     */
    public static function getPhpCli () {

        if(Pimcore_Config::getSystemConfig()->general->php_cli) {
            if(@is_executable(Pimcore_Config::getSystemConfig()->general->php_cli)) {
                return (string) Pimcore_Config::getSystemConfig()->general->php_cli;
            } else {
                Logger::critical("PHP-CLI binary: " . Pimcore_Config::getSystemConfig()->general->php_cli . " is not executable");
            }
        }

        $paths = array(
            "/usr/bin/php",
            "/usr/local/bin/php",
            "/usr/local/zend/bin/php",
            "/bin/php",
            realpath(PIMCORE_DOCUMENT_ROOT . "/../php/php.exe") // for windows sample package (XAMPP)
        );

        foreach ($paths as $path) {
            if(@is_executable($path)) {
                return $path;
            }
        }

        throw new Exception("No php executable found, please configure the correct path in the system settings");
    }

    public static function getTimeoutBinary () {
        $paths = array("/usr/bin/timeout","/usr/local/bin/timeout","/bin/timeout");

        foreach ($paths as $path) {
            if(@is_executable($path)) {
                return $path;
            }
        }

        return false;
    }

    /**
     * @static
     * @param $cmd
     * @param null $outputFile
     */
    public static function exec ($cmd, $outputFile = null, $timeout = null) {

        if(!$outputFile) {
            if(self::getSystemEnvironment() == 'windows') {
                $outputFile = "NUL";
            } else {
                $outputFile = "/dev/null";
            }
        }

        if($timeout && self::getTimeoutBinary()) {
            $cmd = self::getTimeoutBinary() . " -k 1m " . $timeout . "s " . $cmd;
        } else if($timeout) {
            Logger::warn("timeout binary not found, executing command without timeout");
        }

        if($outputFile) {
            $cmd = $cmd . " > ". $outputFile ." 2>&1";
        }

        Logger::debug("Executing command `" . $cmd . "´ on the current shell");
        $return = shell_exec($cmd);

        return $return;
    }

    /**
     * @static
     * @param string $cmd
     * @param null|string $outputFile
     * @return int
     */
    public static function execInBackground($cmd, $outputFile = null) {

        // windows systems
        if(self::getSystemEnvironment() == 'windows') {
            return self::execInBackgroundWindows($cmd, $outputFile);
        } else {
            return self::execInBackgroundUnix($cmd, $outputFile);
        }
    }

    /**
     * @static
     * @param string $cmd
     * @param string $outputFile
     * @return int
     */
    protected static function execInBackgroundUnix ($cmd, $outputFile) {

        if(!$outputFile) {
            $outputFile = "/dev/null";
        }

        $nice = "";
        if(@is_executable("/usr/bin/nice")) {
            $nice = "/usr/bin/nice -n 19 ";
        }

        $commandWrapped = "/usr/bin/nohup " . $nice . $cmd . " > ". $outputFile ." 2>&1 & echo $!";
        Logger::debug("Executing command `" . $commandWrapped . "´ on the current shell in background");
        $pid = shell_exec($commandWrapped);

        Logger::debug("Process started with PID " . $pid);

        return $pid;
    }

    /**
     * @static
     * @param string $cmd
     * @param string $outputFile
     * @return int
     */
    protected static function execInBackgroundWindows($cmd, $outputFile) {

        if(!$outputFile) {
            $outputFile = "NUL";
        }

        $commandWrapped = "cmd /c " . $cmd . " > ". $outputFile . " 2>&1";
        Logger::debug("Executing command `" . $commandWrapped . "´ on the current shell in background");

        $WshShell = new COM("WScript.Shell");
        $WshShell->Run($commandWrapped, 0, false);
        Logger::debug("Process started - returning the PID is not supported on Windows Systems");

        return 0;
    }



    /**
     * Returns a hash with all options passed to a cli script
     *
     * @return array
     */
    public static function getOptions($onlyFullNotationArgs = false){
        GLOBAL $argv;
        $options = array();
        $tmpOptions = $argv;
        array_shift($tmpOptions);

        foreach($tmpOptions as $optionString){
            if($onlyFullNotationArgs && substr($optionString,0,2) != '--'){
                continue;
            }
            $exploded = explode("=",$optionString,2);
            $options[str_replace('-','',$exploded[0])] =  $exploded[1];
        }

        return $options;
    }

    public static function getOptionString($options,$concatenator = '=',$arrayConcatenator = ','){
        $string = '';

        foreach($options as $key => $value){
            $string .= '--' . $key;
            if($value){
                if(is_array($value)){
                    $value = implode($arrayConcatenator,$value);
                }
                $string .= $concatenator . "'" . $value . "'";
            }
            $string .= ' ';
        }

        return $string;
    }

    /**
     * checks the user which executes a cli script
     *
     * @param array $allowedUsers
     *
     * @throws Exception
     */
    public static function checkExecutingUser($allowedUsers = array()){
        $owner = fileowner(PIMCORE_CONFIGURATION_SYSTEM);
        if($owner == false){
            throw new Exception("Couldn't get user from file " . PIMCORE_CONFIGURATION_SYSTEM);
        }
        $userData = posix_getpwuid($owner);
        $allowedUsers[] = $userData['name'];

        $scriptExecutingUserData = posix_getpwuid(posix_geteuid());
        $scriptExecutingUser = $scriptExecutingUserData['name'];

        if(!in_array($scriptExecutingUser,$allowedUsers)){
            throw new Exception("The current system user is not allowed to execute this script. Allowed users: '" . implode(',',$allowedUsers) ."' Executing user: '$scriptExecutingUser'.");
        }
    }
}
