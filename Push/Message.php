<?php
// declare(encoding='UTF-8');
namespace library;
/**
 * @package push
 * @category messaging
 */
class Push_Message
{
    /**
     * @var int
     */
    protected $_badge;

    /**
     * @var string
     */
    protected $_customIdentifer;

    /**
     * @var int
     */
    protected $_expiry;

    /**
     * @var string
     */
    protected $_sound;

    /**
     * @var string
     */
    protected $_text;

    /**
     * @var string[string]
     */
    protected $_properties;

    /**
     * @param string $text UTF-8 。
     */
    public function __construct($text)
    {
        $this->_text = $text;

        $this->_badge = null;
        $this->_customIdentifer = null;
        $this->_expiry = null;
        $this->_sound = null;
        $this->_properties = array();
    }

    /**
     * @param int $value
     * @return int|self
     */
    public function badge($value = null)
    {
        if (!func_num_args()) {
            return $this->_badge;
        }
        $this->_badge = $value === null ? null : (int)$value;
        return $this;
    }

    /**
     * @param string $value
     * @return string|self
     */
    public function customIdentifier($value = null)
    {
        if (!func_num_args()) {
            return $this->_customIdentifier;
        }
        $this->_customIdentifier = $value === null ? null : (string)$value;
        return $this;
    }

    /**
     * @param int $value
     * @return int|self
     */
    public function expiry($value = null)
    {
        if (!func_num_args()) {
            return $this->_expiry;
        }
        $this->_expiry = $value === null ? null : (int)$value;
        return $this;
    }

    /**
     * @param string $value
     * @return string|self
     */
    public function sound($value = null)
    {
        if (!func_num_args()) {
            return $this->_sound;
        }
        $this->_sound = $value === null ? null : (string)$value;
        return $this;
    }

    /**
     * @param string $value
     * @return string|self
     */
    public function text($value = null)
    {
        if (!func_num_args()) {
            return $this->_text;
        }
        $this->_text = $value === null ? null : (string)$value;
        return $this;
    }

    /**
     * @param array $properties
     * @return array|self
     */
    public function properties(array $properties = null)
    {
        if (!func_num_args()) {
            return $this->_properties;
        }
        $this->_properties = $properties === null ? array() : $properties;
        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     * @return string|self
     */
    public function property($name, $value = null)
    {
        if (func_num_args() === 1) {
            return isset($this->_properties[$name]) ? $this->_properties[$name] : null;
        }
        $this->_properties[$name] = $value;
        return $this;
    }
}