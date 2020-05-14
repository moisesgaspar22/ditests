<?php

echo'<pre>';
echo '<h1> Using a singleton </h1>';

// General singleton class.
class Singleton
{
    // Hold the class instance.
    private static $instance = [];
    protected $_groupId         = null;
    protected $_data            = null;

    // The constructor is private
    // to prevent initiation with outer code.
    private function __construct(string $groupId)
    {
        if (empty($groupId)) {
            return;
        }
        $this->_groupId = $groupId;
        echo 'instantiating productinfo for '. $groupId .'<p>';
    }
   
    // The object is created from within the class itself
    // only if the class has no instance.
    public static function getInstance(string $groupId)
    {
        $class = static::class;  // Late Static Bindings
        if (!isset(self::$instance[$class][$groupId])) {
            // again, Late Static Bindings comes to play
            // because "self" resolves to this class instead of the extended class
            self::$instance[$class][$groupId] = new static($groupId);
        }
        
        return self::$instance[$class][$groupId];
    }

    public static function getAll()
    {
        return self::$instance;
    }
}

echo '<h3>every instance gets immediately loaded into memory and is available <p>Also the constructor runs for every call<p>Memoisation does not allow instance update or refresh<p> </h3>';

$v = Singleton::getInstance(22);
$t = Singleton::getInstance(24);
$g = Singleton::getInstance(26);
$h = Singleton::getInstance(28);


var_dump(Singleton::getAll());


echo '<hr><h1> Using a DI with class lazy load </h1>';

class productInfo
{
    public function __construct($groupId)
    {
        $this->id = $groupId;
        echo 'instantiating productinfo for '. $groupId . '<p>';
    }

    public function getId()
    {
        return $this->id;
    }
}

// print_r($var);
class DI implements \ArrayAccess
{
    /**
     * The container
     */
    protected $container = [];

    public function getInstance($groupId)
    {
        $key = $groupId;
        if (!$this->offsetExists($key)) {
            $this->offsetSet($key, function () use ($groupId) {
                return new productInfo($groupId);
            });
        }
        
        return $this->offsetGet($key);
    }

    /**
     * Assigns a value to the specified offset
     *
     * @param string The offset to assign the value to
     * @param mixed  The value to set
     * @access public
     * @abstracting ArrayAccess
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * Whether or not an offset exists
     *
     * @param string An offset to check for
     * @access public
     * @return boolean
     * @abstracting ArrayAccess
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * Unsets an offset
     *
     * @param string The offset to unset
     * @access public
     * @abstracting ArrayAccess
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->data[$offset]);
        }
    }

    /**
     * Returns the value at specified offset
     *
     * @param string The offset to retrieve
     * @access public
     * @return mixed
     * @abstracting ArrayAccess
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->data[$offset] : null;
    }
}

echo '<h3>The classes are being lazy loaded <p>Only when a class is called effectively it gets loaded and makes it available </h3>';

$DI = new \DI();

$v = $DI->getInstance(999);
$t = $DI->getInstance(345);
$g = $DI->getInstance(657);
$h = $DI->getInstance(234);

var_dump($DI);

echo '<h3>Note, The instances are the same and no singleton is necessary</h3>';

var_dump($DI->getInstance(999));
var_dump($DI->getInstance(999));

 echo 'Are instances equal and the same ? <b>';
var_dump($DI->getInstance(999) === $DI->getInstance(999));
