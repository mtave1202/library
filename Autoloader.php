<?php
// declare(encoding='UTF-8');
/**
 * クラスのオートローディングを行う。
 *
 * クラス名に含まれる _ をディレクトリセパレータに置換してファイルパスとする。
 * ただしネームスペースに含まれる _ は置換されない。
 * 内部では spl_autoload_register() を利用しているので、他のオートローダとの併用が可能。

 * @link http://groups.google.com/group/php-standards/web/psr-0-final-proposal?pli=1
 *
 * <code>
 * <?php
 * $al = Autoloader::getInstance();
 * $al->enable(); // 明示的に呼ばないとオートローディングは有効にならない。
 * $al->addDirectory('/foo');
 * echo $al->getFileNameByClassName('ns_A\ns_B\Foo_Bar_Baz'); // /foo/ns_A/ns_B/Foo/Bar/Baz.php
 * $al->addDirectory('/ns_A_dir', 'ns_A');
 * echo $al->getFileNameByClassName('ns_A\ns_b\Foo_Bar_Baz'); // /ns_A_dir/ns_B/Foo/Bar/Baz.php
 * $al->addDirectory('/ns_B_dir', 'ns_A\ns_B');
 * echo $al->getFileNameByClassName('ns_A\ns_B\Foo_Bar_Baz'); // /ns_B_dir/Foo/Bar/Baz.php
 * ?>
 * </code>
 */
class Autoloader
{
    /**
     * @var array
     */
    protected $_aliases;

    /**
     * @var array
     */
    protected $_directories;

    /**
     * @var bool
     */
    protected $_enabled;

    /**
     * @var callback
     */
    protected $_cacheGetter;

    /**
     * @var callback
     */
    protected $_cacheSetter;

    /**
     * @var bool
     */
    protected $_cacheModified;

    /**
     * @var array
     */
    protected $_loaded;

    /**
     * @var Autoloader
     */
    protected static $_instance = null;

    /**
     * @var array
     */
    protected static $_instances = null;

    public function __construct()
    {
        $this->_aliases = version_compare(PHP_VERSION, '5.3.0') >= 0 ? array() : null;
        $this->_directories = array();
        $this->_enabled = false;
        $this->_cacheGetter = null;
        $this->_cacheSetter = null;
        $this->_cacheModified = false;
        $this->_loaded = null;
    }

    public function __destruct()
    {
        if ($this->_cacheModified && $this->_cacheSetter !== null) {
            $setter = $this->_cacheSetter;
            @$setter($this->_loaded);
        }
    }

    /**
     * @param string $name インスタンス名。
     * @return Autoloader
     */
    public static function getInstance($name = null)
    {
        if ($name === null) {
            if (self::$_instance === null) {
                $c = __CLASS__;
                self::$_instance = new $c();
            }
            return self::$_instance;
        }
        if (!is_string($name)) {
            throw new InvalidArgumentException();
        }
        if (self::$_instances === null) {
            self::$_instances = array();
        } else if (isset(self::$_instances[$name])) {
            return self::$_instances[$name];
        }
        $c = __CLASS__;
        return self::$_instances[$name] = new $c();
    }

    public function auto()
    {
        return $this->enable()->addDirectory(dirname(__FILE__));
    }

    /**
     * @param string $alias 新しい別名。
     * @param string $className 元となるクラス名。
     * @throws LogicException
     * @throws InvalidArgumentException
     * @return Autoloader
     */
    public function addAlias($alias, $className)
    {
        if ($this->_aliases === null) {
            throw new LogicException(__METHOD__ . ' requires PHP >= 5.3.0');
        }
        if (!is_string($alias) || $alias === '') {
            throw new InvalidArgumentException("Invalid \$alias: $alias");
        }
        if (!is_string($className) || $className === '') {
            throw new InvalidArgumentException("Invalid \$className: $className");
        }
        $this->_aliases[$alias] = $className;
        return $this;
    }

    /**
     * @param array $map キーに新しい別名を、値に元となるクラス名を入れた連想配列。
     * @throws LogicException
     * @throws InvalidArgumentException
     * @return Autoloader
     */
    public function setAlias(array $map)
    {
        if ($this->_aliases === null) {
            throw new LogicException(__METHOD__ . ' requires PHP >= 5.3.0');
        }
        foreach ($map as $alias => $className) {
            if (!is_string($alias) || $alias === '') {
                throw new InvalidArgumentException("Invalid \$alias: $alias");
            }
            if (!is_string($className) || $className === '') {
                throw new InvalidArgumentException("Invalid \$className: $className");
            }
        }
        $this->_aliases = $map;
        return $this;
    }

    /**
     * @param string $path 探索対象となるディレクトリのパス。
     * @param string $ns ネームスペース。
     * @return Autoloader
     * @throws InvalidArgumentException
     */
    public function addDirectory($path, $ns = null)
    {
        if (!is_string($path)
            || $ns !== null && (!is_string($ns) || $ns === '')) {
            throw new InvalidArgumentException();
        }
        if ($ns === null) {
            $ns = '';
        }
        if (!isset($this->_directories[$ns])) {
            $this->_directories[$ns] = array();
        }
        $this->_directories[$ns][$path] = true;
        return $this;
    }

    /**
     * @param array $pathes 探索対象となるディレクトリのパスの配列。
     * @param string $ns ネームスペース。
     * @return Autoloader
     * @throws InvalidArgumentException
     */
    public function setDirectory(array $pathes, $ns = null)
    {
        if (!is_array($pathes)
            || $ns !== null && (!is_string($ns) || $ns === '')) {
            throw new InvalidArgumentException();
        }
        $values = array();
        foreach ($pathes as $value) {
            if (!is_string($value) || $value === '') {
                throw new InvalidArgumentException();
            }
            $values[$value] = true;
        }
        if ($ns === null) {
            $ns = '';
        }
        $this->_directories[$ns] = $values;
        return $this;
    }

    /**
     * @param callback $getter
     * @param callback $setter
     * @return Autoloader
     */
    public function setCacheHandler($getter, $setter)
    {
        if (!is_callable($getter) || !is_callable($setter)) {
            throw new InvalidArgumentException();
        }
        $this->_cacheGetter = $getter;
        $this->_cacheSetter = $setter;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_enabled;
    }

    /**
     * @return Autoloader
     */
    public function enable()
    {
        if ($this->_enabled) {
            return $this;
        }
        $r = spl_autoload_register(array($this, '_autoload'));
        if (!$r) {
            throw new LogicException();
        }
        $this->_enabled = true;
        if ($this->_cacheGetter) {
            $getter = $this->_cacheGetter;
            $loaded = $getter();
            $this->_loaded = is_array($loaded) ? $loaded : array();
        }
        return $this;
    }

    /**
     * @return Autoloader
     */
    public function disable()
    {
        if (!$this->_enabled) {
            return $this;
        }
        $r = spl_autoload_unregister(array($this, '_autoload'));
        if (!$r) {
            throw new LogicException();
        }
        $this->_enabled = false;
        return $this;
    }

    /**
     * @param string $className 探索するクラスの名前。
     * @return string そのクラスが含まれる (と思われる) ファイルのパス。
     */
    public function getFileNameByClassName($className)
    {
        if (!is_string($className) || !preg_match('/\A(?:[_a-z][_a-z0-9]*\\\)*[_a-z][_a-z0-9]*\z/i', $className)) {
            throw new InvalidArgumentException();
        }
        $targetNs = $subNs = $pathes = null;
        $rpos = strrpos($className, '\\');
        if ($rpos !== false) {
            $targetNs = substr($className, 0, $rpos);
            $className = substr($className, $rpos + 1);
            do {
                if (isset($this->_directories[$targetNs])) {
                    $pathes = $this->_directories[$targetNs];
                    if (empty($pathes)) {
                        return null;
                    }
                    break;
                }
                $rpos = strpos($targetNs, '\\');
                if ($rpos === false) {
                    break;
                }
                $subNs = $subNs === null ? substr($targetNs, $rpos + 1) : substr($targetNs, $rpos + 1) . '\\' . $subNs;
                $targetNs = substr($targetNs, 0, $rpos);
            } while (true);
        }
        if ($pathes === null) {
            if ($targetNs !== null) {
                $subNs = $subNs === null ? $targetNs : $targetNs . '\\' . $subNs;
            }
            if (empty($this->_directories[''])) {
                return null;
            }
            $pathes = $this->_directories[''];
        }
        $sep = DIRECTORY_SEPARATOR;
        $baseName = strtr($className, array('_' => $sep)) . '.php';
        if ($subNs !== null) {
            $baseName = strtr($subNs, array('\\' => $sep)) . $sep . $baseName;
        }
        foreach ($pathes as $directory => $_) {
            $path = $directory . $sep . $baseName;
            if (is_file($path)) {
                return $path;
            }
        }
        return null;
    }

    /**
     * @param string $className ロードするクラスの名前。
     * @return void
     */
    protected function _autoload($className)
    {
        if ($this->_loaded !== null && isset($this->_loaded[$className])) {
            require $this->_loaded[$className];
            return;
        }
        $alias = null;
        if ($this->_aliases !== null && isset($this->_aliases[$className])) {
            $alias = $className;
            $className = $this->_aliases[$className];
        }
        $fileName = $this->getFileNameByClassName($className);
        if (!$fileName) {
            return;
        }
        require $fileName;
        if (!class_exists($className, false)) {
            return;
        }
        if ($this->_loaded !== null) {
            $this->_cacheModified = true;
            $this->_loaded[$className] = $fileName;
        }
        if ($alias !== null) {
            class_alias($className, $alias);
        }
    }
}