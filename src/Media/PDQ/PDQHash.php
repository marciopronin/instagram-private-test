<?php

namespace InstagramAPI\Media\PDQ;

// ================================================================
// Copyright (c) Facebook, Inc. and its affiliates. All Rights Reserved
// ================================================================

/**
 * A PDQ hash is simply 256 bits. The only string representation is 64 hex
 * digits. Bit-ordering is a concern, but is a non-issue as long as the
 * to-string and from-string methods in this class are consistent.
 */
class PDQHash
{
    const PDQHASH_NUM_SLOTS = 16;
    const PDQHASH_HEX_LENGTH = 64;

    // 16 16-bit words: sliced this way for mutually indexed hashing (MIH).
    private $_slots = [];

    /**
     * The constructor is private since the only valid representation is
     * hex-string. Yet we have a PDQHash class, not just strings, due to lessons
     * learned from other projects. Separating ser/des format (string) from
     * internal representation helps avoid false negatives.
     */
    public function __construct()
    {
    }

    public function makeZeroesHash()
    {
        for ($i = 0; $i < self::PDQHASH_NUM_SLOTS; $i++) {
            $this->_slots[$i] = 0;
        }

        return $this;
    }

    /**
     * Creates a new instance of the hash from a hexadecimal-formatted PDQ
     * hash string. Throws MalformedPDQHashException for improperly
     * formatted hashes.
     *
     * @param hex_string string representation of the hash in 'hex' format.
     * @param mixed $hex_string
     *
     * @throws MalformedPDQHashException if the given hash is not valid.
     *
     * @return PDQHash newly created hash.
     */
    public function fromHexString(
    /*string*/ $hex_string
  )/*: PDQHash*/ {
        if (strlen($hex_string) !== self::PDQHASH_HEX_LENGTH) {
            throw new MalformedPDQHashException(
        $hex_string,
        sprintf(
          'Expected hash to have length %d, received hash with length %d',
          self::PDQHASH_HEX_LENGTH,
          strlen($hex_string)
        )
      );
        }

        try {
            // Unpack goes 1-up not 0-up
            $slots = unpack('n*', hex2bin($hex_string));
        } catch (ErrorException $_) {
            throw new MalformedPDQHashException(
        $hex_string,
        'Length is 64 but is not pure hexadecimal.'
      );
        }

        $this->_slots = [];
        $k = self::PDQHASH_NUM_SLOTS - 1;
        foreach ($slots as $slot) {
            $this->_slots[$k] = $slot;
            $k--;
        }

        return $this;
    }

    public function toHexString()
    {
        $string = '';
        for ($i = self::PDQHASH_NUM_SLOTS - 1; $i >= 0; $i--) {
            // dechex doesn't zero-pad
            $string .= sprintf('%04x', $this->_slots[$i]);
        }

        return $string;
    }

    public function to64BitStrings()
    {
        $strings = [];
        for ($i = self::PDQHASH_NUM_SLOTS - 1; $i >= 0; $i -= 4) {
            $strings[] =
        sprintf('%04x', $this->_slots[$i]).
        sprintf('%04x', $this->_slots[$i - 1]).
        sprintf('%04x', $this->_slots[$i - 2]).
        sprintf('%04x', $this->_slots[$i - 3]);
        }

        return $strings;
    }

    public function to32BitStrings()
    {
        $strings = [];
        for ($i = self::PDQHASH_NUM_SLOTS - 1; $i >= 0; $i -= 2) {
            $strings[] =
        sprintf('%04x', $this->_slots[$i]).
        sprintf('%04x', $this->_slots[$i - 1]);
        }

        return $strings;
    }

    public function to16BitStrings()
    {
        $strings = [];
        for ($i = self::PDQHASH_NUM_SLOTS - 1; $i >= 0; $i--) {
            $strings[] = sprintf('%04x', $this->_slots[$i]);
        }

        return $strings;
    }

    public function setBit(
        $bit_index)
    {
        $slot_index = (int) ($bit_index / self::PDQHASH_NUM_SLOTS);
        $slot_bit_index = (int) ($bit_index % self::PDQHASH_NUM_SLOTS);
        $this->_slots[$slot_index] |= 1 << $slot_bit_index;
    }

    /**
     * The Hamming distance between this hash and another.
     *
     * @param The hash to compare this hash to.
     * @param PDQHash $that
     *
     * @return int
     */
    public function hammingDistanceTo(
        $that)
    {
        $sum = 0;
        for ($i = 0; $i < self::PDQHASH_NUM_SLOTS; $i++) {
            $sum += self::__popCount16($this->_slots[$i] ^ $that->_slots[$i]);
        }

        return $sum;
    }

    public function isWithinHammingDistanceOf(
        $that,
        $threshold)
    {
        $current = 0;
        for ($i = 0; $i < self::PDQHASH_NUM_SLOTS; $i++) {
            $current += self::__popCount16($this->_slots[$i] ^ $that->_slots[$i]);
            if ($current > $threshold) {
                return false;
            }
        }

        return true;
    }

    public function hammingNorm()
    {
        $sum = 0;
        for ($i = 0; $i < self::PDQHASH_NUM_SLOTS; $i++) {
            $sum += self::__popCount16($this->_slots[$i]);
        }

        return $sum;
    }

    private static function __popCount16(
        $n)
    {
        $n -= (($n >> 1) & 0x5555);
        $n = ((($n >> 2) & 0x3333) + ($n & 0x3333));
        $n = ((($n >> 4) + $n) & 0x0f0f);
        $n += ($n >> 8);

        return $n & 0x1f;
    }

    private static function __popCount16Slow(
        $n)
    {
        $count = 0;
        for ($count = 0; $n != 0; $count++, $n &= $n - 1) {
        }

        return $count;
    }
} // class PDQHash
