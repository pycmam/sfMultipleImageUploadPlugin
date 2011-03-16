<?php

/**
 * Т.к. во флеше куки передаются от IE, берем ID сессии из параметров запроса
 */
class sfMultipleImageUploadSessionStorage extends sfSessionStorage
{
    public function initialize($options = null)
    {
        $context = sfContext::getInstance();
        $sessionName = $options["session_name"];

        if($value = $context->getRequest()->getParameter($sessionName)) {
            session_name($sessionName);
            session_id($value);
        }

        // общая сессия между сабдоменами
        if (isset($options['session_cookie_domain']) && '.' == $options['session_cookie_domain']) {
            preg_match('/([^.]+\.[^.]+)$/', $_SERVER['SERVER_NAME'], $matches);
            $options['session_cookie_domain'] = '.' . $matches[1];
        }

        parent::initialize($options);
    }
}