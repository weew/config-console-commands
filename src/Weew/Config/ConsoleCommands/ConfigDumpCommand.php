<?php

namespace Weew\Config\ConsoleCommands;

use Weew\Config\IConfig;
use Weew\Console\IInput;
use Weew\Console\IOutput;
use Weew\ConsoleArguments\ArgumentType;
use Weew\ConsoleArguments\ICommand;

class ConfigDumpCommand {
    /**
     * @var IOutput
     */
    private $input;

    /**
     * @var IOutput
     */
    private $output;

    /**
     * @param ICommand $command
     */
    public function setup(ICommand $command) {
        $command->setName('config:dump')
            ->setDescription('Dump configuration');

        $command->argument(ArgumentType::SINGLE_OPTIONAL, 'search')
            ->setDescription('Config node name to dump');

        $command->setHelp(<<<EOT
You can dump all config with
 <keyword>config:dump</keyword>

You can filter config by key
 <keyword>config:dump keyword</keyword>

This will match a config starting with "<yellow>keyword</yellow>"
 <keyword>config:dump keyword*</keyword>

This will match a config ending with "<yellow>keyword</yellow>"
 <keyword>config:dump *keyword</keyword>

This will match everything that has "<yellow>keyword</yellow>" somewhere in the string
 <keyword>config:dump *keyword*</keyword>

EOT
        );
    }

    /**
     * @param IInput $input
     * @param IOutput $output
     * @param IConfig $config
     */
    public function run(IInput $input, IOutput $output, IConfig $config) {
        $this->input = $input;
        $this->output = $output;

        $config = $config->toArray();
        $search = $input->getArgument('search');
        $config = array_dot($config);
        ksort($config);

        if ($search !== null) {
            $config = $this->findConfig($config, $search);
        }

        $this->output->writeLine(" <header>Config:</header>");

        if (count($config)) {
            $this->renderConfig($config);
        } else {
            $output->writeLineIndented('No configuration found');
        }
    }

    /**
     * @param array $config
     * @param $search
     *
     * @return array
     */
    private function findConfig(array $config, $search) {
        $filteredConfig = [];
        $search = preg_quote($search);
        $search = str_replace('\*', '(.*)', $search);

        foreach ($config as $key => $value) {
            if (preg_match("#^$search#", $key) === 1) {
                $filteredConfig[$key] = $value;
            }
        }

        return $filteredConfig;
    }

    /**
     * @param array $config
     */
    private function renderConfig(array $config) {
        foreach ($config as $key => $value) {
            if ($value === true) {
                $value = 'true';
            } else if ($value === false) {
                $value = 'false';
            }

            $this->output->writeLineIndented("<green>$key</green>: $value");
        }
    }
}
