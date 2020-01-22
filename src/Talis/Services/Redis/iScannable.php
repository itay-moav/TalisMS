<?php namespace Talis\Services\Redis;
/**
 * @author itaymoav
 */
interface iScannable{
    public function scan_me(?int &$cursor,$pattern=false);
}
