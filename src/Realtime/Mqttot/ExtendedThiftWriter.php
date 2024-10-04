<?php

namespace InstagramAPI\Realtime\Mqttot;

use Fbns\Thrift\Compact\Types;

class ExtendedThiftWriter
{
    /**
     * @var string
     */
    private $_buffer;

    /**
     * @var int
     */
    private $_field;

    /**
     * @var int[]
     */
    private $_stack;

    public function __construct()
    {
        if (PHP_INT_SIZE === 4 && !extension_loaded('gmp')) {
            throw new \RuntimeException('You need to install GMP extension to run this code with x86 PHP build.');
        }
        $this->_buffer = '';
        $this->_field = 0;
        $this->_stack = [];
    }

    /**
     * @param int $number
     * @param int $bits
     *
     * @return int
     */
    private function _toZigZag(
        $number,
        $bits
    ) {
        if (PHP_INT_SIZE === 4) {
            $number = gmp_init($number, 10);
        }
        $result = ($number << 1) ^ ($number >> ($bits - 1));
        if (PHP_INT_SIZE === 4) {
            $result = gmp_strval($result, 10);
        }

        return $result;
    }

    /**
     * @param int $number
     */
    private function _writeByte(
        $number
    ) {
        $this->_buffer .= chr($number);
    }

    /**
     * @param int $number
     */
    private function _writeWord(
        $number
    ) {
        $this->_writeVarInt($this->_toZigZag($number, 16));
    }

    /**
     * @param int $number
     */
    private function _writeInt(
        $number
    ) {
        $this->_writeVarInt($this->_toZigZag($number, 32));
    }

    /**
     * @param int $field
     * @param int $type
     */
    private function _writeField(
        $field,
        $type
    ) {
        $delta = $field - $this->_field;
        if (($delta > 0) && ($delta <= 15)) {
            $this->_writeByte(($delta << 4) | $type);
        } else {
            $this->_writeByte($type);
            $this->_writeWord($field);
        }
        $this->_field = $field;
    }

    /**
     * @param int $number
     */
    private function _writeVarInt(
        $number
    ) {
        if (PHP_INT_SIZE === 4) {
            $number = gmp_init($number, 10);
        }
        while (true) {
            $byte = $number & (~0x7F);
            if (PHP_INT_SIZE === 4) {
                $byte = (int) gmp_strval($byte, 10);
            }
            if ($byte === 0) {
                if (PHP_INT_SIZE === 4) {
                    $number = (int) gmp_strval($number, 10);
                }
                $this->_buffer .= chr($number);
                break;
            } else {
                $byte = ($number & 0x7F) | 0x80;
                if (PHP_INT_SIZE === 4) {
                    $byte = (int) gmp_strval($byte, 10);
                }
                $this->_buffer .= chr($byte);
                $number = $number >> 7;
            }
        }
    }

    /**
     * @param int $value
     */
    private function _writeLongInt(
        $value
    ) {
        $this->_writeVarInt($this->_toZigZag($value, 64));
    }

    /**
     * @param string $data
     */
    private function _writeBinary(
        $data
    ) {
        $this->_buffer .= $data;
    }

    /**
     * @param int  $field
     * @param bool $value
     */
    public function writeBool(
        $field,
        $value
    ) {
        $this->_writeField($field, $value ? Types::TRUE : Types::FALSE);
    }

    /**
     * @param int    $field
     * @param string $string
     */
    public function writeString(
        $field,
        $string
    ) {
        $this->_writeField($field, Types::BINARY);
        $this->_writeStringDirect($string);
    }

    /**
     * @param string $string
     */
    protected function _writeStringDirect(
        $string
    ) {
        $this->_writeVarInt(strlen($string));
        $this->_writeBinary($string);
    }

    public function writeStop()
    {
        $this->_buffer .= chr(Types::STOP);
        if (count($this->_stack)) {
            $this->_field = array_pop($this->_stack);
        }
    }

    /**
     * @param int $field
     * @param int $number
     */
    public function writeInt8(
        $field,
        $number
    ) {
        $this->_writeField($field, Types::BYTE);
        $this->_writeByte($number);
    }

    /**
     * @param int $field
     * @param int $number
     */
    public function writeInt16(
        $field,
        $number
    ) {
        $this->_writeField($field, Types::I16);
        $this->_writeWord($number);
    }

    /**
     * @param int $field
     * @param int $number
     */
    public function writeInt32(
        $field,
        $number
    ) {
        $this->_writeField($field, Types::I32);
        $this->_writeInt($number);
    }

    /**
     * @param int $field
     * @param int $number
     */
    public function writeInt64(
        $field,
        $number
    ) {
        $this->_writeField($field, Types::I64);
        $this->_writeLongInt($number);
    }

    /**
     * @param                      $field
     * @param array<array<string>> $data
     */
    public function writeStrStrMap(
        $field,
        $data
    ) {
        $this->_writeField($field, Types::MAP);
        $this->_writeVarInt(sizeof($data));
        $this->_writeByte(((Types::BINARY & 0xF) << 4) | (Types::BINARY & 0xF));

        foreach ($data as $pair) {
            $this->_writeStringDirect($pair[0]);
            $this->_writeStringDirect($pair[1]);
        }
    }

    /**
     * @param int   $field
     * @param int   $type
     * @param array $list
     */
    public function writeList(
        $field,
        $type,
        array $list
    ) {
        $this->_writeField($field, Types::LIST);
        $size = count($list);
        if ($size < 0x0F) {
            $this->_writeByte(($size << 4) | $type);
        } else {
            $this->_writeByte(0xF0 | $type);
            $this->_writeVarInt($size);
        }

        switch ($type) {
            case Types::TRUE:
            case Types::FALSE:
                foreach ($list as $value) {
                    $this->_writeByte($value ? Types::TRUE : Types::FALSE);
                }
                break;
            case Types::BYTE:
                foreach ($list as $number) {
                    $this->_writeByte($number);
                }
                break;
            case Types::I16:
                foreach ($list as $number) {
                    $this->_writeWord($number);
                }
                break;
            case Types::I32:
                foreach ($list as $number) {
                    $this->_writeInt($number);
                }
                break;
            case Types::I64:
                foreach ($list as $number) {
                    $this->_writeLongInt($number);
                }
                break;
            case Types::BINARY:
                foreach ($list as $string) {
                    $this->_writeVarInt(strlen($string));
                    $this->_writeBinary($string);
                }
                break;
        }
    }

    /**
     * @param int $field
     */
    public function writeStruct(
        $field
    ) {
        $this->_writeField($field, Types::STRUCT);
        $this->_stack[] = $this->_field;
        $this->_field = 0;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->_buffer;
    }
}
