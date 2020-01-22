<?php namespace Talis\Services\Redis;
/**
 * @author itaymoav
 */
interface iDataBuilder{
	public function build();
	/**
	 * number of seconds until dies.
	 * Send 0 for infinite
	 * @return int
	 */
	public function ttl():int;
}