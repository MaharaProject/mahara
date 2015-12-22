<?php

use Behat\Behat\Context\Context;
use Behat\Testwork\Hook\HookDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcher;

interface MaharaAwareInterface extends Context {

  /**
   * Set event dispatcher.
   */
  public function setDispatcher(HookDispatcher $dispatcher);

  /**
   * Sets parameters provided for Mahara.
   *
   * @param array $parameters
   */
  public function setMaharaParameters(array $parameters);
}
