<?php
class Config_Main {

	public $iface = 'wlan0';

	/**
	 * Show a list of wifi APs
	 * Show a template
	 */
	public function mainAction($response) {
		$response->addTo('extraJs', 'scripts/pages/wificonfig.js');
	}

	public function searchAction($request, $response) {
		$connectedAp = $this->findConnectedAp();
		$list = [];
		$status = 0;
		$wifiApList = array();
		$wifiAp     = array();

		if ($request->isDevelopment()) {
			$wifiApList = [
			['address'=>'EE:22:33:44:AA:77','ssid'=>'ABCD', 'connected'=>false, 'quality'=>95]
			];
		} else {
			$ret = exec('sudo /sbin/iwlist '.$this->iface.' scanning', $list, $status);
		}
		foreach ($list as $line) {
			if (strstr($line, 'Cell ')) {
				if (!empty($wifiAp)) {
					$wifiApList[] = $wifiAp;
				}
				$wifiAp     = array();

				$address = explode(' - ', $line);
				$wifiAp['address'] = str_replace('Address: ', '', $address[1]);
				if ($wifiAp['address'] == $connectedAp['address']) {
					$wifiAp['connected'] = TRUE;
				}
			}
			if (strstr($line, 'Quality')) {
				$quality = explode('=', $line);
				$percent = explode('/', $quality[1]);
				$wifiAp['quality'] = round(($percent[0] / $percent[1])*100);
			}
			if (strstr($line, 'ESSID')) {
				$ssid = explode(':', $line);
				$wifiAp['ssid'] = str_replace('"', '', $ssid[1]);
			}
		}
		if (!empty($wifiAp)) {
			$wifiApList[] = $wifiAp;
		}
		$response->wifiApList = $wifiApList;
		$response->updateUrl = m_appurl('config/main/update');
	}

	public function updateAction($request, $response) {
		$pwd     = $request->cleanString('psk');
		$address = $request->cleanString('address');
		$ssid    = $request->cleanString('ssid');
		$response->psk     = $pwd;
		$response->address = $address;
		$response->ssid    = $ssid;

		exec('sudo /opt/tanc/bin/wifi-connect.sh '.$ssid.' '.$pwd .'&');
	}

	public function findConnectedAp() {
		$list = array();
		$ret = 0;
		exec('iwgetid '.$this->iface, $list, $ret);
		if ($ret) {
			return $list;
		}
		$line = $list[0];
		$ssid = explode(':', $line);
		$connectedAp = array();
		$connectedAp['ssid'] = str_replace('"', '', $ssid[1]);

		$list = array();
		$ret = exec('iwgetid -a '.$this->iface, $list);
		$line = $list[0];
		$addr = explode('Cell: ', $line);
		$connectedAp['address'] = $addr[1];

		return $connectedAp;
	}
}
