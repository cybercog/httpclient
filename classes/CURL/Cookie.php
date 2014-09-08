<?php
namespace CURL;

class Cookie
{
	private $storage = '';
	private $storagePath = '';

	public function __construct() {
	}

	public function __destruct() {
		unlink($this->storage);
	}

	public function setStoragePath($storagePath) {
        // :TODO: What if APPLICATION_PATH isn't setted up
        $storagePath = APPLICATION_PATH . '/' . $storagePath;
        if (is_dir($storagePath)) {
            $this->storagePath = $storagePath;
        }
    }

	public function createStorage() {
		$this->storage = $this->fetchStorage();

		$handle = fopen($this->storage, 'w+');
        // :TODO: Add check for cookie write availability
		fwrite($handle, '');
		fclose($handle);
		chmod($this->storage, 0666);
	}

	public function getStorage() {
		return $this->storage;
	}

	private function fetchStorage() {
		if(empty($this->storagePath)) {
			$this->setStoragePath('tmp');
		}
		return $this->storagePath . '/' . md5(time() . rand(0, 99));
	}
}