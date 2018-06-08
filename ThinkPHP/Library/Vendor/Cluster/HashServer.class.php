<?php
namespace Vendor\Cluster;
use Vendor\Hash\Flexihash;
use Vendor\Hash\Flexihash_Crc32Hasher;
class HashServer {

	static public function hash($key, $servers = array(), $type = 'consistent') {
		if ('consistent' == $type) {
			$consistent = new Flexihash(new Flexihash_Crc32Hasher(), 128);
			$consistent->addTargets($servers);
			$server = $consistent->lookup($key);
		} else {
			$hash = sprintf('%u', crc32(md5($key)));
			$total = count($servers);
			$key = $hash%$total;
			$server = $servers[$key];
		}
		return $server;
	}
}