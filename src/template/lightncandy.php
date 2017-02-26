<?php

use LightnCandy\LightnCandy;

class Template_Lightncandy {

	public $logService;
	public $fileExtension = '.tpl';


	public function template($request, $response, $template_section) {
		if ($response->statusCode == 500) {
			return;
		}
		$viewFileList   = [];
		$mainFile       = _get('template.main.file', $request->modName.'_'.$request->actName);
		$viewFileList[] = 'src/'.$request->appName.'/views/'.$mainFile;
		$mainFile       = _get('template.main.file', $request->appName.'/'.$request->modName);
		$viewFileList[] = _get('template_basedir') . _get('template_name') .'/'.str_replace('.', '/', $mainFile);



        $flags = 
//            LightnCandy::FLAG_INSTANCE |
//            LightnCandy::FLAG_SPVARS |
			LightnCandy::FLAG_HANDLEBARS | 
            LightnCandy::FLAG_RUNTIMEPARTIAL |
//			LightnCandy::FLAG_RENDER_DEBUG |
			LightnCandy::FLAG_ERROR_LOG |
//			LightnCandy::FLAG_EXTHELPER |
			LightnCandy::FLAG_ERROR_EXCEPTION |
            LightnCandy::FLAG_HANDLEBARSJS;

		$fileIncluded = FALSE;

		foreach ($viewFileList as $viewFile) {
			try {
				// Quick and deprecated way to get render function
				if (!file_exists($viewFile.$this->fileExtension)) {
					continue;
				}
				$resolved = array();
				$phpCode = LightnCandy::compile(
					file_get_contents($viewFile.$this->fileExtension),
					[
						'flags'   => $flags,
						'debug' => 1,
						'partialresolver' => function (&$cx, $name) {
							$file = $name;
							$file = _get('template_basedir') . _get('template_name') . '/'.$name.$this->fileExtension;
							if (substr($name, 1) == '/') {
								$file = _get('template_basedir') . _get('template_name') . $name.$this->fileExtension;
							}
							if (file_exists($file)) {
								return file_get_contents($file);
							}
							return "[partial (file:$name.tpl) not found]";
						},
						'helperresolver' => function (&$cx, $name) use (&$resolved){
							if (!strpos($name, '::')) {
								return; 
							}

							list($class, $fun) = explode('::', $name);

							if (!class_exists($class)) {
								$file = strtolower( str_replace('_', '/', $class) ) .'.php';
								_didef('templatehelper_'.$class, $file);
								_make('templatehelper_'.$class);
							}
							$cx['helpers'][$name]       = $name;
							$resolved[$name]            = $name;
							return $name; //array($class, $fun);
						},
						'renderex' => "	if (isset(\$options['helperresolver']) ) {  \$cx['helperresolver'] = \$options['helperresolver'];}",
					]
				);

/*
echo "<pre>";
echo htmlspecialchars($phpCode);
echo "</pre>";
*/
				if (!$phpCode) continue;
				$renderer = LightnCandy::prepare(
					$phpCode
				);

				echo $renderer($response->sectionList, [
						'flags'   => $flags,
						'helperresolver' => function (&$cx, $name) use (&$resolved){
							if (!strpos($name, '::')) {
								return; 
							}

							list($class, $fun) = explode('::', $name);

							if (!class_exists($class)) {
								$file = strtolower( str_replace('_', '/', $class) ) .'.php';
								_didef('templatehelper_'.$class, $file);
								_make('templatehelper_'.$class);
							}
							$resolved[$name] = $name;
							return array($class, $fun);
						},
					]);
				$fileIncluded = TRUE;
				$this->logService->debug('Template: parsed file '.$viewFile.$this->fileExtension);
				break;
			} catch (\BarfException $e) {
				$this->logService->error('Template: caught exception: '.$e->getMessage());
				return;
			}
		}
		if (!$fileIncluded && $template_section == 'main') {
			if (is_array($response->main)) {
				foreach($response->main as $_itm) {
					echo $_itm;
				}
			}
		}
		return;
	}
}
