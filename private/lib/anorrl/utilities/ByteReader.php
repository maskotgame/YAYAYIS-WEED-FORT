<?php
	namespace anorrl\utilities;

	// https://github.com/AntiBoomz/BTRoblox/blob/5589a0625f4603d0a018d8c73156847e2f24dc4b/js/rbx/Parser/ByteReader.js (Translated from JavaScript to PHP)
	class ByteReader {
		public $buffer;
		private $index = 0;

		function SetIndex($n) {
			$this->index = $n;
			return;
		}

		function GetIndex() {
			return $this->index;
		}

		function GetRemaining() {
			return strlen($this->buffer) - $this->index;
		}

		function GetLength() {
			return strlen($this->buffer);
		}

		function Jump($n) {
			$this->index += $n;
			return;
		}

		function Byte() {
			$indexbak = $this->index;
			$this->Jump(1);
			return unpack("C", substr($this->buffer,$indexbak,1))[1];
		}
		
		function UInt16LE() {
			$indexbak = $this->index;
			$this->Jump(2);
			return unpack("v", substr($this->buffer,$indexbak,2))[1];
		}

		function UInt32LE() {
			$indexbak = $this->index;
			$this->Jump(4);
			return unpack("V", substr($this->buffer,$indexbak,4))[1];
		}

		function FloatLE() {
			$indexbak = $this->index;
			$this->Jump(4);
			return unpack("g", substr($this->buffer,$indexbak,4))[1];
		}

		function String($n) {
			$indexbak = $this->index;
			$this->Jump($n);
			return substr($this->buffer ?? '',$indexbak,$n);
		}
	}
?>
