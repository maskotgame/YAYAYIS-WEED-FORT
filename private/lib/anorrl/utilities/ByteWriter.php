<?php
	namespace anorrl\utilities;

	class ByteWriter {
		public $buffer = "";

		function GetLength() {
			return strlen($this->buffer);
		}

		function Byte($byte) {
			$this->buffer = $this->buffer . pack("C", $byte);
		}
		
		function UInt16LE($ushort) {
			$this->buffer = $this->buffer . pack("v", $ushort);
		}

		function UInt32LE($uint) {
			$this->buffer = $this->buffer . pack("V", $uint);
		}

		function FloatLE($float) {
			$this->buffer = $this->buffer . pack("g", $float);
		}

		function String($string) {
			$this->buffer = $this->buffer . $string;
		}
	}
?>
