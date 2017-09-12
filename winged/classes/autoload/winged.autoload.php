<?php

class WingedAutoLoad
{
    public static $_cache = [];
    public static $_class = false;
    public static $_paths = [];
    public static $_inited_keys = [];
    private static $_cache_path = './winged/classes/autoload.cache.php';
    private static $php_code = '';

    public static function _eval()
    {
        eval(str_replace('<?php', '', file_get_contents(self::$_cache_path)));
    }

    public static function persists($class = false, $path = false)
    {
        if ($path != false) {
            include_once $path;
        }
        if ($path === false) {
            self::_eval();
            unset(self::$_cache[$class]);
        } else {
            self::_eval();
            self::$_cache[$class] = $path;
        }

        $tofile = 'WingedAutoLoad::$_cache = [';

        $first = true;
        foreach (self::$_cache as $class => $path) {
            self::$_inited_keys[$class] = $class;
            $classes = self::file_get_php_classes($path);
            if (!in_array($class, $classes)) {
                continue;
            }
            if ($first) {
                $tofile .= "'" . $class . "' => '" . $path . "'";
                $first = false;
            } else {
                $tofile .= ", '" . $class . "' => '" . $path . "'";
            }
        }

        $tofile .= '];';
        $tofile = '<?php ' . $tofile;

        $handle = fopen(CLASS_PATH . 'autoload.cache.php', 'w+');
        fwrite($handle, $tofile);
        fclose($handle);
    }

    public static function verify($class)
    {
        $exp = explode("\\", $class);
        if (count($exp) > 1) {
            $class = array_pop($exp);
        }

        self::$_class = $class;

        if (empty(self::$_cache)) {
            self::_eval();
        }

        if (!empty(self::$_cache)) {
            $find = false;
            foreach (self::$_cache as $class => $path) {
                if (self::$_class == $class) {
                    $classes = self::file_get_php_classes($path);
                    if (in_array(self::$_class, $classes)) {
                        include_once $path;
                        $find = $path;
                        break;
                    }
                }
            }
            if (!$find) {
                self::persists(self::$_class, false);
                self::search();
            }
        } else {
            self::search();
        }
    }

    private static function search()
    {
        if (empty(self::$_paths)) {
            include_once './winged/utils/filetree.class.php';
            $tree = new FileTree();
            $struct = $tree->gemTree('./', 'php');
            foreach ($struct as $row => $file) {
                self::$_paths[] = $file['path'];
            }
        }

        foreach (self::$_paths as $row => $path) {
            $classes = self::file_get_php_classes($path, $row);
            if (in_array(self::$_class, $classes)) {
                self::persists(self::$_class, $path);
                break;
            }
        }
    }

    private static function file_get_php_classes($filepath, $countable = 0)
    {
        self::$php_code = file_get_contents($filepath);
        $classes = array();
        $tokens = token_get_all(self::$php_code);
        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
            if ($tokens[$i - 2][0] == T_CLASS
                && $tokens[$i - 1][0] == T_WHITESPACE
                && $tokens[$i][0] == T_STRING
            ) {

                $class_name = $tokens[$i][1];
                $classes[] = $class_name;
            }
        }
        self::$php_code = null;
        return $classes;
    }
}