<?php

namespace PetrSladek\SkautIS\DI;

use Nette;

/**
 * Skautis extension for Nette Framework 2.3. Creates 'connection' & 'panel' services.
 *
 * @author     Hána František
 * @author     Petr /Peggy/ Sladek
 */
class SkautISClientExtension extends Nette\DI\CompilerExtension {

    public function loadConfiguration() {
        $container = $this->getContainerBuilder();
        $config = $this->getConfig(array("applicationId"=>NULL, "testMode"=>NULL, "profiler"=>true));

        $skautisService = $container->addDefinition("skautis")
                ->setFactory('SkautIS\SkautIS::getInstance', array($config['applicationId'], $config['testMode'], $config['profiler']));

        if (class_exists('Tracy\Debugger') && $container->parameters['debugMode'] && $config['profiler'] != false) {
            $panel = $container->addDefinition($this->prefix('panel'))
                    ->setClass('PetrSladek\SkautIS\DI\Tracy\Panel');
            $skautisService->addSetup(array($panel, 'register'), array($skautisService));
        }
    }

    public function afterCompile(Nette\PhpGenerator\ClassType $class) {
//        $container = $this->getContainerBuilder();
//        $config = $this->getConfig($this->defaults);
        // metoda initialize
        //$initialize = $class->methods['initialize'];
        //$initialize->addBody('$storage = $this->session->getSection("__SkautisExtension__");$this->skautis->setStorage($storage, TRUE);');
    }

}
