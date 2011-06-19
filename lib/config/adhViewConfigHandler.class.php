<?php

/**
 * Extends sfViewConfigHandler to allow custom configs to be added to app-level
 * and module-level view.yml files under the key ".custom". Adds to sfConfig using
 * "view_custom_%USER_DEFINED_KEY%" as key.
 *
 * @author Aaron Hall <adhall@gmail.com>
 */
class adhViewConfigHandler extends sfViewConfigHandler {

  private $appendStatements = array();

  public function execute($configFiles)
  {
    $retval = parent::execute($configFiles);

    $first_conditional = true;
    $in_conditional = false;
    foreach($this->yamlConfig as $viewName => $values) {
      if($viewName === 'all')
        continue;

      $addConfigs = $this->compileCustomConfigHash($this->mergeConfigValue('.custom', $viewName));

      if(count($addConfigs)) {
        $conditional = $first_conditional ? 'if' : 'elseif';
        $this->appendStatements[] = $conditional . "(\$this->actionName.\$this->viewName == '{$viewName}') {\n";
        $this->appendConfigCall($addConfigs);
        $this->appendStatements[] = "}\n";

        $in_conditional = true;
        $first_conditional = false;
      }
    }

    // add for "all"
    $addConfigs = $this->compileCustomConfigHash($this->mergeConfigValue('.custom', 'all'));
    if(count($addConfigs)) {
      if($in_conditional) $this->appendStatements[] = "else {\n";
      $this->appendConfigCall($addConfigs);
      if($in_conditional) $this->appendStatements[] = "}\n";
    }

    return $retval . "\n" . implode('', $this->appendStatements);
  }

  private function compileCustomConfigHash($values, $key_prepend='view_custom')
  {
    $param_array = array();

    foreach($values as $key => $value) {
      $use_key = $key_prepend . '_' . $key;

      if(is_array($value)) {
        $param_array = array_merge($this->compileCustomConfigHash($value, $use_key), $param_array);
      } else {
        $param_array[$use_key] = $value;
      }
    }

    return $param_array;
  }

  private function appendConfigCall(array $config)
  {
    $this->appendStatements[] = "  sfConfig::add(" . var_export($config, true) . ");\n";
  }

}