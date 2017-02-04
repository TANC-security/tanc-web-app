<?php

class Update_Main {

	public $repoListFile = 'tanc.list';
	public $aptListFile  = '/var/lib/apt/lists/getatanc.com_repo_dists_jessie_main_binary-armhf_Packages';

	public function mainAction($response) {
		$lines = array();
		exec('grep ^Package '.$this->aptListFile, $lines);
		foreach ($lines as $_line) {
			list($foo, $package) = explode(": ", $_line);
			$response->addTo('packages', $package);
		}


		$lines = array();
		exec('sudo apt-get --just-print -u upgrade  -o Dir::Etc::sourcelist="sources.list.d/'.$this->repoListFile.'" -o Dir::Etc::sourceparts="-" -o APT::Get::List-Cleanup="0" | grep "^ "', $lines);
		$response->addInto('updates', $lines);
		$this->addExtraJs($response);
	}

	public function updateAction($request, $response) {
		exec('sudo apt-get update -o Dir::Etc::sourcelist="sources.list.d/'.$this->repoListFile.'" -o Dir::Etc::sourceparts="-" -o APT::Get::List-Cleanup="0"');
		$response->redir = m_appurl('update');
	}

	public function installAction($request, $response) {
		$package = $request->cleanString('pkg');
		$matches = [];
		preg_match('/[a-z\-]+/', $package, $matches);
		
		$lines = array();
		exec('sudo apt install '.$matches[0], $lines);
		$response->addInto('main', $lines);
	}

	public function addExtraJs($response) {
		$response->addTo('extraJs', "
		<script>
$(document).ready(function() {

	var burl = $('body').data('base-url');
	$('.btn-install').on('click',function(e) {
		$.ajax(burl+'update/main/install/?pkg='+e.target.value);
	});
});
		</script>
");
	}

}
