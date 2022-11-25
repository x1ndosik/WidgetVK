<?php

namespace ddosnik\protocol;

use ddosnik\binary\Binary;
use ddosnik\binary\BinaryStream;

abstract class Packet{
	public static int $ID = -1;

	protected int $offset = 0;
	public string $buffer;
	public float $sendTime;

	protected function get(mixed $len) : mixed{
		if($len < 0){
			$this->offset = strlen($this->buffer) - 1;

			return "";
		}elseif($len === true){
			return substr($this->buffer, $this->offset);
		}

		return $len === 1 ? $this->buffer[$this->offset++] : substr($this->buffer, ($this->offset += $len) - $len, $len);
	}

	protected function getLong(bool $signed = true) : int{
		return Binary::readLong($this->get(8), $signed);
	}

	protected function getInt() : int{
		return Binary::readInt($this->get(4));
	}

	protected function getShort(bool $signed = true) : int|bool{
		return $signed ? Binary::readSignedShort($this->get(2)) : Binary::readShort($this->get(2));
	}

	protected function getTriad() : int{
		return Binary::readTriad($this->get(3));
	}

	protected function getLTriad() : int{
		return Binary::readLTriad($this->get(3));
	}

	protected function getByte() : int{
		return ord($this->buffer[$this->offset++]);
	}

	protected function getString() : string{
		return $this->get($this->getShort());
	}

	protected function getAddress(&$addr, &$port, &$version = null){
		$version = $this->getByte();
		if($version === 4){
			$addr = ((~$this->getByte()) & 0xff) .".". ((~$this->getByte()) & 0xff) .".". ((~$this->getByte()) & 0xff) .".". ((~$this->getByte()) & 0xff);
			$port = $this->getShort(false);
		}else{
			//TODO: IPv6
		}
	}

	protected function feof() : bool{
		return !isset($this->buffer[$this->offset]);
	}

	protected function put(string $str) : void{
		$this->buffer .= $str;
	}

	protected function putLong($v){
		$this->buffer .= Binary::writeLong($v);
	}

	protected function putInt($v){
		$this->buffer .= writeSignedVarInt($v);
	}

	protected function putShort(int $value) : void{
		$this->buffer .= Binary::writeShort($value);
	}

	protected function putTriad(int $value) : void{
		$this->buffer .= Binary::writeTriad($value);
	}

	protected function putLTriad(int $value) : void{
		$this->buffer .= Binary::writeLTriad($value);
	}

	protected function putByte(int $value) : void{
		$this->buffer .= chr($value);
	}

	protected function putString(string $value) : void{
		$this->putShort(strlen($value));
		$this->put($value);
	}

	protected function writeMagic() : void{
		$this->put("\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78");
	}
	
	protected function putAddress($addr, $port, $version = 4){
		$this->putByte($version);
		if($version === 4){
			foreach(explode(".", $addr) as $b){
				$this->putByte((~((int) $b)) & 0xff);
			}
			$this->putShort($port);
		}else{
			//IPv6
		}
	}

	public function encode() : void{
		$this->buffer = chr(static::$ID);
	}

	public function decode() : void{
		$this->offset = 1;
	}

	public function getBuffer() : string{
		return $this->buffer;
	}

	public function clean() : Packet{
		$this->buffer = null;
		$this->offset = 0;
		$this->sendTime = null;
		return $this;
	}
}
