<?php

declare(strict_types=1);

namespace ddosnik\thread;

use ddosnik\Widget;
use ClassLoader;
use const PTHREADS_INHERIT_ALL;

abstract class Thread extends \Thread {

	public ?ClassLoader $classLoader = null;
	public bool $isQuited = false;

  public function getClassLoader() : ?ClassLoader{
    return $this->classLoader;
  }

	public function setClassLoader(\ClassLoader $loader = null) : void{
		if ($loader === null) {
			$loader = Widget::getInstance()->getLoader();
		}
		$this->classLoader = $loader;
	}

	public function registerClassLoader() : void{
		if ($this->classLoader !== null) {
			$this->classLoader->register(true);
		}
	}

	public function start(?int $options = PTHREADS_INHERIT_ALL) : bool{
		if(!$this->isRunning() and !$this->isJoined() and !$this->isTerminated()){
		//if (!$this->running && !$this->joined && !$this->terminated) {
			if ($this->classLoader === NULL) {
				$this->setClassLoader();
			}
			return parent::start($options);
		}
		return true;
	}

	public function quit() : void{
		$this->isQuited = true;
		$this->notify();

		if (!$this->isTerminated() && !$this->isJoined()) {
			$this->join();
		}
	}

	public function getThreadName() : string{
		return (new \ReflectionClass($this))->getShortName();
	}
}
