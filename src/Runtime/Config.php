<?php
/**
 * This file is part of the teamneusta/php-cli-magedev package.
 *
 * Copyright (c) 2017 neusta GmbH | Ein team neusta Unternehmen
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 *
 * @license https://opensource.org/licenses/mit-license MIT License
 */

namespace TeamNeusta\Magedev\Runtime;

use Symfony\Component\Console\Input\InputInterface;

/**
 * Class Config
 */
class Config
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var FileHelper
     */
    protected $fileHelper;

    /**
     * configData
     *
     * @var mixed
     */
    protected $configData;

    /**
     * __construct
     *
     * @param Runtime $runtime
     */
    public function __construct(Runtime $runtime)
    {
        $this->input = $runtime->getInput();
        $this->fileHelper = $runtime->getHelper('FileHelper');
        $this->load();
    }

    /**
     * load
     */
    public function load()
    {
        $this->configData = $this->loadConfiguration();
    }

    /**
     * loadConfiguration
     *
     * @param FileHelper $fileHelper
     */
    protected function loadConfiguration()
    {
        $projectConfigFile = getcwd() . "/magedev.json";
        $defaultConfigFile = $this->fileHelper->findPath("var/config/magedev.json");

        if (file_exists($projectConfigFile)) {
            $projectConfig = $this->loadConfigFile($projectConfigFile);
            $defaultConfig = $this->loadConfigFile($defaultConfigFile);

            $homeConfigFile = $this->fileHelper->expandPath("~") . "/.magedev.json";
            if (file_exists($homeConfigFile)) {
                $homeConfig = $this->loadConfigFile($homeConfigFile);
            }
            return array_merge(array_merge($defaultConfig, $homeConfig), $projectConfig);
        } else {
            throw new \Exception("it seems this is not a magento project I can handle: ".$projectConfigFile." file was not found");
        }
    }

    /**
     * loadConfigFile
     *
     * @param string $path
     */
    protected function loadConfigFile($path)
    {
        if (!file_exists($path)) {
            throw new \Exception("File " . $path . " not found");
        }
        $data = json_decode($this->fileHelper->read($path), true);
        if (json_last_error()) {
            throw new \Exception("Parse error in " . $path . ": ".json_last_error_msg());
        }
        if (!is_array($data)) {
            throw new \Exception("Parse error in " . $path . ": ".json_last_error_msg());
        }
        return $data;
    }

    /**
     * get
     *
     * @param string $key
     * @return string
     */
    public function get($key)
    {
        $value = null;

        if ($this->input) {
            try {
                $value = $this->input->getArgument($key);
            } catch (\Symfony\Component\Console\Exception\InvalidArgumentException $e) {
            }
        }

        if ($value) {
            return $value;
        }

        if (!isset($this->configData[$key])) {
            throw new \Exception($key." not found in config");
        }
        $value = $this->configData[$key];

        return $value;
    }

    /**
     * optionExists
     *
     * @param string $key
     * @return bool
     */
    public function optionExists($key)
    {
        try {
            $value = $this->get($key);
            return $value != "";
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * getMagentoVersion
     *
     * @return string
     */
    public function getMagentoVersion()
    {
        $version = $this->get("magento_version");
        if (!in_array($version, ["1", "2"])) {
            throw new \Exception("supplied magento version ".$version." not available");
        }
        return $version;
    }
}