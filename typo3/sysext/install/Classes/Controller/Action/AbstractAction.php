<?php
namespace TYPO3\CMS\Install\Controller\Action;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * General purpose controller action helper methods and bootstrap
 */
abstract class AbstractAction implements ActionInterface
{
    /**
     * @var StandaloneView
     */
    protected $view = null;

    /**
     * @var string Name of controller. One of the strings 'step', 'tool' or 'common'
     */
    protected $controller = '';

    /**
     * @var string Name of target action, set by controller
     */
    protected $action = '';

    /**
     * @var string Form token for CSRF protection
     */
    protected $token = '';

    /**
     * @var array Values in $_POST['install']
     */
    protected $postValues = [];

    /**
     * @var array<\TYPO3\CMS\Install\Status\StatusInterface> Optional status message from controller
     */
    protected $messages = [];

    /**
     * @var string
     */
    protected $context = self::CONTEXT_STANDALONE;

    /**
     * Handles the action
     *
     * @return ResponseInterface
     */
    public function handle(): ResponseInterface
    {
        $this->initializeHandle();
        return new HtmlResponse($this->executeAction(), 200, [
            'Cache-Control' => 'no-cache, must-revalidate',
            'Pragma' => 'no-cache'
        ]);
    }

    /**
     * Initialize the handle action, sets up fluid stuff and assigns default variables.
     */
    protected function initializeHandle()
    {
        $viewRootPath = GeneralUtility::getFileAbsFileName('EXT:install/Resources/Private/');
        $controllerActionDirectoryName = ucfirst($this->controller);
        $mainTemplate = ucfirst($this->action);
        $this->view = GeneralUtility::makeInstance(StandaloneView::class);
        $this->view->getRequest()->setControllerExtensionName('Install');
        $this->view->setTemplatePathAndFilename($viewRootPath . 'Templates/Action/' . $controllerActionDirectoryName . '/' . $mainTemplate . '.html');
        $this->view->setLayoutRootPaths([$viewRootPath . 'Layouts/']);
        $this->view->setPartialRootPaths([$viewRootPath . 'Partials/']);
        $this->view
            // time is used in js and css as parameter to force loading of resources
            ->assign('time', time())
            ->assign('action', $this->action)
            ->assign('controller', $this->controller)
            ->assign('token', $this->token)
            ->assign('context', $this->context)
            ->assign('backendContext', $this->context === self::CONTEXT_BACKEND)
            ->assign('messages', $this->messages)
            ->assign('typo3Version', TYPO3_version)
            ->assign('siteName', $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']);
    }

    /**
     * Executes the action
     *
     * @return string|array Rendered content
     */
    abstract protected function executeAction();

    /**
     * Set form protection token
     *
     * @param string $token Form protection token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * Set action group. Either string 'step', 'tool' or 'common'
     *
     * @param string $controller Controller name
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * Set action name. This is usually similar to the class name,
     * only for loginForm, the action is login
     *
     * @param string $action Name of target action for forms
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * Set POST form values of install tool
     *
     * @param array $postValues
     */
    public function setPostValues(array $postValues)
    {
        $this->postValues = $postValues;
    }

    /**
     * Status messages from controller
     *
     * @param array<\TYPO3\CMS\Install\Status\StatusInterface> $messages
     */
    public function setMessages(array $messages = [])
    {
        $this->messages = $messages;
    }

    /**
     * Context determines if the install tool is called within backend or standalone
     *
     * @param $context string One of the `CONTEXT_*` constants.
     */
    public function setContext($context)
    {
        switch ($context) {
            case self::CONTEXT_STANDALONE:
            case self::CONTEXT_BACKEND:
                $this->context = $context;
                break;
            default:
                $this->context = self::CONTEXT_STANDALONE;
        }
    }

    /**
     * Some actions like the database analyzer and the upgrade wizards need additional
     * bootstrap actions performed.
     *
     * Those actions can potentially fatal if some old extension is loaded that triggers
     * a fatal in ext_localconf or ext_tables code! Use only if really needed.
     */
    protected function loadExtLocalconfDatabaseAndExtTables()
    {
        \TYPO3\CMS\Core\Core\Bootstrap::getInstance()
            ->ensureClassLoadingInformationExists()
            ->loadTypo3LoadedExtAndExtLocalconf(false)
            ->unsetReservedGlobalVariables()
            ->loadBaseTca(false)
            ->loadExtTables(false);
    }

    /**
     * This function returns a salted hashed key.
     *
     * @param string $password
     * @return string
     */
    protected function getHashedPassword($password)
    {
        $saltFactory = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance(null, 'BE');
        return $saltFactory->getHashedPassword($password);
    }
}
