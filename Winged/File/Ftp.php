<?php

namespace Winged\File;

use Masterminds\HTML5\Exception;
use Winged\Directory\Directory;
use Winged\Error\Error;
use Winged\Formater\Formater;
use Winged\Utils\WingedLib;

/**
 * Class Ftp
 * @package Winged\File
 */
class Ftp
{

    /**
     * @var bool
     */
    public $ssl;
    /**
     * @var string
     */
    public $host;
    /**
     * @var int
     */
    public $port;
    /**
     * @var string
     */
    public $login;
    /**
     * @var null|string
     */
    public $password;

    /**
     * @var null|array
     */
    public $currentDir;

    /**
     * @var bool|resource
     */
    public $connection;

    /**
     * @var null|string
     */
    public $currentDirName;


    public $path = '';

    /**
     * Ftp constructor.
     * @param string $host
     * @param string $username
     * @param string $passaword
     * @param int $port
     * @param bool $ssl
     */
    public function __construct($host = 'ftp.example.com', $username = 'anonymous', $passaword = '', $port = 21, $ssl = false)
    {
        $connection = false;
        try {
            if ($ssl) {
                $this->ssl = true;
                $connection = @ftp_ssl_connect($host, $port, 30);
            } else {
                $this->ssl = false;
                $connection = @ftp_connect($host, $port, 30);
            }
        } catch (Exception $error) {
            Error::_die($error->getMessage(), false, __FILE__, __LINE__);
        }
        if ($connection) {
            $this->host = $host;
            $this->port = $port;
            $this->login = $username;
            $this->password = $passaword;
            $this->connection = $connection;
            $login = ftp_login($this->connection, $username, $passaword);
            if (!$login) {
                Error::_die('could not authenticate to FTP server', __LINE__, __FILE__, __LINE__);
            } else {
                $this->ls();
                $this->path = './';
            }
        }
    }

    /**
     * @param string $directory
     * @return $this
     */
    public function ls($directory = '.')
    {
        $list = ftp_nlist($this->connection, $directory);
        $this->currentDir = $list;
        return $this;
    }

    /**
     * @return $this
     */
    public function up()
    {
        ftp_cdup($this->connection);
        $this->currentDirName = ftp_pwd($this->connection);
        if (begstr($this->currentDirName) === '/') {
            begstr_replace($this->currentDirName);
        }
        if (endstr_replace($this->currentDirName) === '/') {
            endstr_replace($this->currentDirName, 1);
        }
        $exp = explode('/', $this->path);
        array_pop($exp);
        array_pop($exp);
        $this->path = implode('/', $exp) . '/';
        $this->ls();
        return $this;
    }

    /**
     * @return $this
     */
    public function upRoot()
    {
        while ($this->path != './') {
            $this->up();
        }
        return $this;
    }

    /**
     * @param string $directory
     * @return $this
     */
    public function down($directory = '.')
    {
        if ($this->exists($directory)) {
            ftp_chdir($this->connection, $directory);
            $this->path .= $directory . '/';
            $this->currentDirName = $directory;
            $this->ls();
        }
        return $this;
    }

    /**
     * @param $path
     * @return $this
     */
    public function access($path)
    {
        $deep = $this->deepExists($path);
        if ($deep && $deep === 'dir') {
            $paths = $this->pathNormalize($path);
            $paths = $this->parseDirectory($paths);
            if (count7($paths['dirs']) > 0) {
                foreach ($paths['dirs'] as $key => $value) {
                    if ($value !== '.') {
                        if ($this->exists($value)) {
                            if ($value === '..') {
                                $this->up();
                            } else {
                                $this->down($value);
                            }
                        }
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @param $directory
     * @param bool $createJump
     * @return $this
     */
    public function mkdir($directory, $createJump = true)
    {
        if($directory === 'images'){
            pre_clear_buffer_die('asdasd');
        }
        if (!$this->exists($directory)) {
            ftp_mkdir($this->connection, $directory);
        }
        if ($createJump) {
            $this->down($directory);
        }
        return $this;
    }


    /**
     * @param $path
     * @return $this|bool
     */
    public function delete($path)
    {
        $exist = $this->deepExists($path);
        if ($exist && $exist === 'file') {
            ftp_delete($this->connection, $path);
            return $this;
        }
        return false;
    }

    /**
     * @param $path
     * @return $this
     */
    public function rmdir($path)
    {
        $exists = $this->deepExists($path);
        if ($exists && $exists === 'dir') {
            $path = $this->pathNormalize($path);
            $currentPath = $this->path;
            $exp = explode('/', $path);
            $dir = array_pop($exp);
            $path = implode('/', $exp);
            $this->access($path);
            $result = [];
            $this->gemTree($dir, $result);
            $this->upRoot();
            $this->access($currentPath);
            if (count7($result['files']) > 0) {
                foreach ($result['files'] as $file) {
                    $this->delete(str_replace($currentPath, './', $file));
                }
            }
            if (count7($result['dirs']) > 0) {
                foreach ($result['dirs'] as $dir) {
                    ftp_rmdir($this->connection, str_replace($currentPath, './', $dir));
                }
            }
        }
        return $this;
    }

    /**
     * @param $directory
     * @param $result
     */
    private function gemTree($directory, &$result)
    {
        if (!array_key_exists('dirs', $result)) {
            $result['dirs'] = [];
        }
        if (!array_key_exists('files', $result)) {
            $result['files'] = [];
        }
        if (!array_key_exists('calls', $result)) {
            $result['calls'] = 1;
            $result['total_calls'] = 1;
        } else {
            $result['calls'] = $result['calls'] + 1;
            $result['total_calls'] = $result['total_calls'] + 1;
        }
        $this->down($directory);
        $tree = $this->currentDir;
        array_shift($tree);
        array_shift($tree);
        $tree = array_values($tree);
        if (count7($tree) > 0) {
            foreach ($tree as $value) {
                if ($this->isDir($value)) {
                    $result['dirs'][] = $this->path . $value . '/';
                    $this->gemTree($value, $result);
                    $result['calls'] = $result['calls'] - 1;
                    $this->up();
                } else {
                    $result['files'][] = $this->path . $value;
                }
            }
        }
        if ($result['calls'] === 1) {
            $arr = [
                $this->path
            ];
            $arr = array_merge($arr, $result['dirs']);
            $result['dirs'] = array_reverse($arr);
            $this->up();
        }
    }

    /**
     * @param $path
     * @return bool|string
     */
    public function deepExists($path)
    {
        $paths = $this->pathNormalize($path);
        $paths = $this->parseDirectory($paths);
        $commands = [];
        $ret = false;
        $type = null;
        if (count7($paths['dirs']) > 0) {
            foreach ($paths['dirs'] as $key => $value) {
                if ($key + 1 === count7($paths['dirs'])) {
                    if ($this->exists($value)) {
                        $ret = true;
                        if ($this->isDir($value)) {
                            $type = 'dir';
                        } else {
                            $type = 'file';
                        }
                    } else {
                        $ret = false;
                    }
                } else {
                    if ($value !== '.') {
                        if ($this->exists($value)) {
                            if ($value === '..') {
                                $commands[] = 'down:::' . $this->currentDirName;
                                $this->up();
                            } else {
                                $commands[] = 'up:::' . $this->currentDirName;
                                $this->down($value);
                            }
                        }
                    }
                }
            }
        }
        $commands = array_reverse($commands);
        foreach ($commands as $command) {
            $command = explode(':::', $command);
            if ($command[0] === 'up') {
                $this->up();
            } else {
                $this->down($command[1]);
            }
        }
        if ($ret) {
            return $type;
        }
        return false;
    }

    /**
     * @param $name
     * @return bool
     */
    public function exists($name)
    {
        return in_array($name, $this->currentDir);
    }

    /**
     * @param $path
     * @return bool
     */
    public function isFile($path)
    {
        return ftp_size($this->connection, $path) !== -1 ? true : false;
    }

    /**
     * @param $path
     * @return bool
     */
    public function isDir($path)
    {
        return ftp_size($this->connection, $path) === -1 ? true : false;
    }

    /**
     * @param $path
     * @return bool
     */
    public function deepIsFile($path)
    {
        return $this->deepExists($path) === 'file' ? true : false;
    }

    /**
     * @param $path
     * @return bool
     */
    public function deepIsDir($path)
    {
        return $this->deepExists($path) === 'dir' ? true : false;
    }

    /**
     * @param $local
     * @param $remote
     * @param bool $forceCreate
     * @return bool | $this
     */
    public function asyncPut($local, $remote, $forceCreate = true)
    {
        $local = new File($local, false);
        if ($local->exists()) {
            $file = $this->parseFile($remote);
            if ($forceCreate) {
                if (count7($file['dirs']) > 0) {
                    foreach ($file['dirs'] as $dir) {
                        if ($dir !== '.') {
                            if ($dir === '..') {
                                $this->up();
                            } else {
                                $this->mkdir($dir);
                            }
                        }
                    }
                }
            }
            ftp_nb_put($this->connection, $file['file'], $local->file_path, FTP_BINARY);
            return $this;
        }
        return false;
    }

    public function asyncGet()
    {
        //ftp_nb_get
    }

    /**
     * @param $local
     * @param $remote
     * @param bool $forceCreate
     * @return $this|bool
     */
    public function put($local, $remote, $forceCreate = true)
    {
        $local = new File($local, false);
        $oldPath = $this->path;
        if ($local->exists()) {
            $file = $this->parseFile($remote);
            if ($forceCreate) {
                if (count7($file['dirs']) > 0) {
                    foreach ($file['dirs'] as $dir) {
                        if ($dir !== '.') {
                            if ($dir === '..') {
                                $this->up();
                            } else {
                                $this->mkdir($dir);
                            }
                        }
                    }
                }
            }
            ftp_put($this->connection, $file['file'], $local->file_path, FTP_BINARY);
            $this->upRoot();
            $this->access($oldPath);
            return $this;
        }
        return false;
    }

    public function get()
    {
        //ftp_get
    }

    /**
     * @param $path
     * @return string
     */
    private function pathNormalize($path)
    {
        if (begstr($path) != '.' && begstr($path) === '/') {
            $path = '.' . $path;
        }
        if (endstr($path) === '/') {
            endstr_replace($path, 1);
        }
        return $path;
    }

    /**
     * @param $file
     * @return array
     */
    private function parseFile($file)
    {
        $file = explode('/', $file);
        return [
            'file' => array_pop($file),
            'dirs' => $file
        ];
    }

    /**
     * @param $path
     * @return array
     */
    private function parseDirectory($path)
    {
        $path = explode('/', $path);
        return [
            'file' => null,
            'dirs' => $path
        ];
    }

}